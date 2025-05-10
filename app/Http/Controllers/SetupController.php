<?php

namespace App\Http\Controllers;

use App\Models\Setup;
use App\Models\Beneficiary;
use App\Models\Province;
use App\Models\ExpectedSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF; // barryvdh/laravel-dompdf

class SetupController extends Controller
{
    public function index(Request $request)
    {
        $query = Setup::with('beneficiary');

        if ($request->filled('search')) {
            $query->where('spin_number', 'like', "%{$request->search}%")
                  ->orWhere('project_title', 'like', "%{$request->search}%")
                  ->orWhereHas('beneficiary', fn($q) =>
                      $q->where('name', 'like', "%{$request->search}%")
                  );
        }

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        $setups = $query->orderBy('created_at', 'desc')
                        ->paginate(15)
                        ->appends($request->only(['search', 'province_id']));

        $provinces     = Province::orderBy('name')->get();
        $beneficiaries = Beneficiary::orderBy('name')->get();

        return view('setups.index', compact('setups', 'provinces', 'beneficiaries'));
    }

    public function create()
    {
        $beneficiaries = Beneficiary::orderBy('name')->get();
        $provinces     = Province::orderBy('name')->get();

        return view('setups.create', compact('beneficiaries', 'provinces'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'beneficiary_id'  => 'required|exists:beneficiaries,id',
            'province_id'     => 'required|exists:provinces,id',
            'spin_number'     => 'required|string|unique:setups,spin_number',
            'project_title'   => 'required|string',
            'check_number'    => 'nullable|string',
            'amount_assisted' => 'required|numeric',
            'check_date'      => 'required|date',
            'refund_start'    => 'required|date',
            'refund_end'      => 'required|date|after_or_equal:refund_start',
        ]);

        $setup = Setup::create($data);
        $this->generateMonthlySchedule($setup);

        return redirect()
            ->route('setups.show', $setup)
            ->with('success', 'Setup created & schedule generated.');
    }

    public function show(Setup $setup)
    {
        $setup->load(['beneficiary', 'province', 'expectedSchedules.repayments']);

        $totalBorrowed  = $setup->amount_assisted;
        $totalScheduled = $setup->expectedSchedules->sum('amount_due');
        $totalPaid      = $setup->expectedSchedules
                               ->flatMap(fn($s) => $s->repayments)
                               ->sum('payment_amount');

        $totalPenalties = 0;

        $dueCount = $setup->expectedSchedules
                         ->filter(fn($s) => Carbon::now()->gte($s->due_date))
                         ->count();

        $monthly = $setup->expectedSchedules->first()->amount_due ?? 0;

        $pastDue = round($dueCount * $monthly - $totalPaid, 2);
        $totalRemaining = round($totalScheduled - $totalPaid, 2);

        return view('setups.show', compact(
            'setup',
            'totalBorrowed',
            'totalScheduled',
            'totalPaid',
            'totalPenalties',
            'pastDue',
            'totalRemaining'
        ));
    }

    public function exportPdf(Setup $setup)
    {
        $setup->load(['beneficiary', 'province', 'expectedSchedules.repayments']);

        $totalBorrowed  = $setup->amount_assisted;
        $totalScheduled = $setup->expectedSchedules->sum('amount_due');
        $totalPaid      = $setup->expectedSchedules
                               ->flatMap(fn($s) => $s->repayments)
                               ->sum('payment_amount');

        $totalPenalties = 0;

        $dueCount = $setup->expectedSchedules
                         ->filter(fn($s) => Carbon::now()->gte($s->due_date))
                         ->count();

        $monthly = $setup->expectedSchedules->first()->amount_due ?? 0;

        $pastDue = round($dueCount * $monthly - $totalPaid, 2);
        $totalRemaining = round($totalScheduled - $totalPaid, 2);

        $data = compact(
            'setup',
            'totalBorrowed',
            'totalScheduled',
            'totalPaid',
            'totalPenalties',
            'pastDue',
            'totalRemaining'
        );

        $pdf = PDF::loadView('setups.pdf', $data)
                  ->setPaper('a4', 'portrait');

        return $pdf->download("Subsidiary-Ledger-Setup-{$setup->id}.pdf");
    }

    public function edit(Setup $setup)
    {
        $beneficiaries = Beneficiary::orderBy('name')->get();
        $provinces     = Province::orderBy('name')->get();

        return view('setups.edit', compact('setup', 'beneficiaries', 'provinces'));
    }

    public function update(Request $request, Setup $setup)
    {
        $data = $request->validate([
            'beneficiary_id'  => 'required|exists:beneficiaries,id',
            'province_id'     => 'required|exists:provinces,id',
            'spin_number'     => "required|string|unique:setups,spin_number,{$setup->id}",
            'project_title'   => 'required|string',
            'check_number'    => 'nullable|string',
            'amount_assisted' => 'required|numeric',
            'check_date'      => 'required|date',
            'refund_start'    => 'required|date',
            'refund_end'      => 'required|date|after_or_equal:refund_start',
        ]);

        $setup->update($data);
        $setup->expectedSchedules()->delete();
        $this->generateMonthlySchedule($setup);

        return redirect()
            ->route('setups.show', $setup)
            ->with('success', 'Setup updated & schedule refreshed.');
    }

    public function destroy(Setup $setup)
    {
        $setup->expectedSchedules->each(fn($s) => $s->repayments()->delete());
        $setup->expectedSchedules()->delete();
        $setup->delete();

        return redirect()
            ->route('setups.index')
            ->with('success', 'Setup and its schedule deleted.');
    }

    /**
     * Generate monthly schedules such that:
     * - First N-1 installments are whole numbers (no decimals)
     * - Last installment contains the remainder (including cents),
     *   so that all payments sum to the exact principal.
     */
    private function generateMonthlySchedule(Setup $setup)
    {
        $start  = Carbon::parse($setup->refund_start)->startOfMonth();
        $end    = Carbon::parse($setup->refund_end)->startOfMonth();
        $months = $start->diffInMonths($end) + 1;

        $perInstallment  = floor($setup->amount_assisted / $months);
        $lastInstallment = $setup->amount_assisted - ($perInstallment * ($months - 1));

        for ($i = 0; $i < $months; $i++) {
            $dueDate = $start->copy()->addMonths($i)->format('Y-m-d');

            $amountDue = $i < $months - 1
                ? $perInstallment
                : $lastInstallment;

            ExpectedSchedule::create([
                'setup_id'      => $setup->id,
                'due_date'      => $dueDate,
                'amount_due'    => $amountDue,
                'months_lapsed' => now()->diffInMonths($start->copy()->addMonths($i)),
            ]);
        }
    }
}
