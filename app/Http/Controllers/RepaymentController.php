<?php

namespace App\Http\Controllers;

use App\Models\Repayment;
use App\Models\ExpectedSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RepaymentController extends Controller
{
    /**
     * Show the form to record a payment for a single schedule.
     */
    public function create(ExpectedSchedule $expectedSchedule)
    {
        // ✅ Load setup and its beneficiary to prevent null error
        $expectedSchedule->loadMissing('setup.beneficiary');

        return view('repayments.create', compact('expectedSchedule'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expected_schedule_id' => 'required|exists:expected_schedules,id',
            'payment_amount'       => 'nullable|numeric',
            'payment_date'         => 'nullable|date',
            'or_number'            => 'nullable|string',
            'or_date'              => 'nullable|date',
            'penalty_amount'       => 'nullable|numeric',
            'returned_check'       => 'nullable|boolean',
            'deferred'             => 'nullable|boolean',
            'deferred_date'        => 'nullable|date',
            'pdc_number'           => 'nullable|string',
            'pdc_date'             => 'nullable|date',
            'remarks'              => 'nullable|string|max:255',
        ]);

        $data['returned_check'] = $request->has('returned_check');
        $data['deferred']       = $request->has('deferred');
        $data['deferred_date']  = $request->input('deferred_date');
        $data['pdc_number']     = $request->input('pdc_number');
        $data['pdc_date']       = $request->input('pdc_date');
        $data['payment_amount'] = $data['payment_amount'] ?? 0;
        $data['penalty_amount'] = $data['penalty_amount'] ?? 0;

        $repayment = Repayment::create($data);

        if ($data['deferred']) {
            $setup = $repayment->expectedSchedule->setup;

            $lastSchedule = $setup
                ->expectedSchedules()
                ->orderBy('due_date', 'desc')
                ->first();

            $nextDue = Carbon::parse($lastSchedule->due_date)
                ->addMonth()
                ->startOfMonth();

            $setup->expectedSchedules()->create([
                'due_date'      => $nextDue->format('Y-m-d'),
                'amount_due'    => $lastSchedule->amount_due,
                'months_lapsed' => now()->diffInMonths($nextDue),
            ]);
        }

        return redirect()
            ->route('setups.show', $repayment->expectedSchedule->setup_id)
            ->with('success', 'Repayment recorded.');
    }

    public function edit(Repayment $repayment)
    {
        $expectedSchedule = $repayment->expectedSchedule;
        return view('repayments.edit', compact('repayment', 'expectedSchedule'));
    }

    public function update(Request $request, Repayment $repayment)
    {
        $data = $request->validate([
            'payment_amount'       => 'nullable|numeric',
            'payment_date'         => 'nullable|date',
            'or_number'            => 'nullable|string',
            'or_date'              => 'nullable|date',
            'penalty_amount'       => 'nullable|numeric',
            'returned_check'       => 'nullable|boolean',
            'deferred'             => 'nullable|boolean',
            'deferred_date'        => 'nullable|date',
            'pdc_number'           => 'nullable|string',
            'pdc_date'             => 'nullable|date',
            'remarks'              => 'nullable|string|max:255',
        ]);

        $data['returned_check'] = $request->has('returned_check');
        $data['deferred']       = $request->has('deferred');
        $data['deferred_date']  = $request->input('deferred_date');
        $data['pdc_number']     = $request->input('pdc_number');
        $data['pdc_date']       = $request->input('pdc_date');
        $data['payment_amount'] = $data['payment_amount'] ?? 0;
        $data['penalty_amount'] = $data['penalty_amount'] ?? 0;

        $repayment->update($data);

        if ($data['deferred']) {
            $setup = $repayment->expectedSchedule->setup;

            $lastSchedule = $setup
                ->expectedSchedules()
                ->orderBy('due_date', 'desc')
                ->first();

            $nextDue = Carbon::parse($lastSchedule->due_date)
                ->addMonth()
                ->startOfMonth();

            $setup->expectedSchedules()->create([
                'due_date'      => $nextDue->format('Y-m-d'),
                'amount_due'    => $lastSchedule->amount_due,
                'months_lapsed' => now()->diffInMonths($nextDue),
            ]);
        }

        return redirect()
            ->route('setups.show', $repayment->expectedSchedule->setup_id)
            ->with('success', 'Repayment updated.');
    }

    public function destroy(Repayment $repayment)
    {
        $setupId = $repayment->expectedSchedule->setup_id;
        $repayment->delete();

        return redirect()
            ->route('setups.show', $setupId)
            ->with('success', 'Repayment deleted.');
    }
}
