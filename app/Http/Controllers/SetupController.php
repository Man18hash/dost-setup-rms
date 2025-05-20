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

        // Generate blank monthly schedule for customization
        $this->generateMonthlySchedule($setup, true);

        return redirect()
            ->route('setups.schedule.customize', $setup)
            ->with('success', 'Setup created & schedule ready for customization.');
    }

    public function show(Setup $setup)
    {
        $setup->load(['beneficiary', 'province', 'expectedSchedules.repayments']);

        $totalBorrowed  = $setup->amount_assisted;
        $totalScheduled = $totalBorrowed; // Always show the principal
        $totalPaid      = $setup->expectedSchedules
                               ->flatMap(fn($s) => $s->repayments)
                               ->sum('payment_amount');

        // Sum all penalties charged
        $totalPenalties = $setup->expectedSchedules
                               ->flatMap(fn($s) => $s->repayments)
                               ->sum('penalty_amount');

        // Calculate past due count (months whose due_date is past)
        $dueCount = $setup->expectedSchedules
                         ->filter(fn($s) => Carbon::now()->gte($s->due_date))
                         ->count();

        $monthly = $setup->expectedSchedules->first()->amount_due ?? 0;

        $pastDue = round($dueCount * $monthly - $totalPaid, 2);

        // Remaining = principal - paid + outstanding penalties
        $totalRemaining = round($totalScheduled - $totalPaid + $totalPenalties, 2);

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
        $totalScheduled = $totalBorrowed;
        $totalPaid      = $setup->expectedSchedules
                               ->flatMap(fn($s) => $s->repayments)
                               ->sum('payment_amount');
        $totalPenalties = $setup->expectedSchedules
                               ->flatMap(fn($s) => $s->repayments)
                               ->sum('penalty_amount');

        $dueCount = $setup->expectedSchedules
                         ->filter(fn($s) => Carbon::now()->gte($s->due_date))
                         ->count();

        $monthly = $setup->expectedSchedules->first()->amount_due ?? 0;

        $pastDue = round($dueCount * $monthly - $totalPaid, 2);
        $totalRemaining = round($totalScheduled - $totalPaid + $totalPenalties, 2);

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
        $this->generateMonthlySchedule($setup, true);

        return redirect()
            ->route('setups.schedule.customize', $setup)
            ->with('success', 'Setup updated & schedule ready for customization.');
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
     * Show form to customize monthly amounts and add extra months.
     */
    public function showCustomizeSchedule(Setup $setup)
    {
        $schedules = $setup->expectedSchedules()->orderBy('due_date')->get();
        return view('setups.schedule.customize', compact('setup', 'schedules'));
    }

    /**
     * Save user-defined monthly amounts and any new months.
     */
    public function saveCustomizeSchedule(Request $request, Setup $setup)
    {
        $rules = [
            'amount_due'        => 'required|array',
            'amount_due.*'      => 'required|numeric|min:0',
            'new_due_month.*'   => 'nullable|date_format:Y-m',
            'new_amount_due.*'  => 'nullable|numeric|min:0',
        ];
        $input = $request->validate($rules);

        // Update existing schedules
        $sumExisting = 0;
        foreach ($setup->expectedSchedules as $sch) {
            $val = $input['amount_due'][$sch->id] ?? 0;
            $sch->update(['amount_due' => $val]);
            $sumExisting += $val;
        }

        // Insert new months
        $sumNew = 0;
        if (! empty($input['new_due_month'])) {
            foreach ($input['new_due_month'] as $i => $ym) {
                if (! $ym) continue;
                $amt = $input['new_amount_due'][$i] ?? 0;
                $date = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
                $setup->expectedSchedules()->create([
                    'due_date'      => $date->format('Y-m-d'),
                    'amount_due'    => $amt,
                    'months_lapsed' => now()->diffInMonths($date),
                ]);
                $sumNew += $amt;
            }
        }

        $totalAllocated = $sumExisting + $sumNew;
        if (round($totalAllocated, 2) !== round($setup->amount_assisted, 2)) {
            return back()
                ->withErrors("You must allocate exactly ₱{$setup->amount_assisted}. You allocated ₱{$totalAllocated}.")
                ->withInput();
        }

        return redirect()
            ->route('setups.show', $setup)
            ->with('success', 'Custom schedule saved.');
    }

    /**
     * Generate monthly schedules. If $blank is true, amounts start at zero.
     */
    private function generateMonthlySchedule(Setup $setup, bool $blank = false)
    {
        $start  = Carbon::parse($setup->refund_start)->startOfMonth();
        $end    = Carbon::parse($setup->refund_end)->startOfMonth();
        $months = $start->diffInMonths($end) + 1;

        $total = $setup->amount_assisted;
        $perInstallment  = $blank
                           ? 0
                           : floor($total / $months);
        $lastInstallment = $blank
                           ? 0
                           : ($total - $perInstallment * ($months - 1));

        for ($i = 0; $i < $months; $i++) {
            $dueDate = $start->copy()->addMonths($i)->format('Y-m-d');
            $amount  = $blank
                       ? 0
                       : ($i < $months - 1 ? $perInstallment : $lastInstallment);

            ExpectedSchedule::create([
                'setup_id'      => $setup->id,
                'due_date'      => $dueDate,
                'amount_due'    => $amount,
                'months_lapsed' => now()->diffInMonths($start->copy()->addMonths($i)),
            ]);
        }
    }
}
