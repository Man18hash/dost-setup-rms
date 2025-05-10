<?php

namespace App\Http\Controllers;

use App\Models\Setup;
use App\Models\Beneficiary;
use App\Models\Province;
use App\Models\ExpectedSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;

class SetupController extends Controller
{
    public function index(Request $request)
    {
        $query = Setup::with('beneficiary');

        // Global search
        if ($request->filled('search')) {
            $query->where('spin_number', 'like', "%{$request->search}%")
                  ->orWhere('project_title', 'like', "%{$request->search}%")
                  ->orWhereHas('beneficiary', function ($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
        }

        // Province filter
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        $setups = $query->orderBy('created_at', 'desc')
                        ->paginate(15)
                        ->appends($request->only(['search','province_id']));

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
        $totalPaid      = $setup->expectedSchedules->flatMap(fn($s) => $s->repayments)->sum('payment_amount');
        $totalPenalties = $setup->expectedSchedules->flatMap(fn($s) => $s->repayments)->sum('penalty_amount');
        $pastDue        = $setup->expectedSchedules
                                ->filter(fn($s) => Carbon::now()->gte(Carbon::parse($s->due_date)))
                                ->sum('amount_due') - $totalPaid;

        $totalRemaining = $totalScheduled + $totalPenalties - $totalPaid;

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

    private function generateMonthlySchedule(Setup $setup)
    {
        $start = Carbon::parse($setup->refund_start)->startOfMonth();
        $end   = Carbon::parse($setup->refund_end)->startOfMonth();

        $months = $start->diffInMonths($end) + 1;
        $exactInstallment = $setup->amount_assisted / $months;
        $floorInstallment = floor($exactInstallment);

        $period = new DatePeriod($start, new DateInterval('P1M'), $months);

        $totalAssigned = 0;
        $i = 0;

        foreach ($period as $dt) {
            $i++;

            if ($i < $months) {
                $installment = $floorInstallment;
                $totalAssigned += $installment;
            } else {
                $installment = $setup->amount_assisted - $totalAssigned;
            }

            ExpectedSchedule::create([
                'setup_id'      => $setup->id,
                'due_date'      => $dt->format('Y-m-d'),
                'amount_due'    => round($installment, 2),
                'months_lapsed' => now()->diffInMonths($dt),
            ]);
        }
    }
}
