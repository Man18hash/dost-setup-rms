<?php

namespace App\Http\Controllers;

use App\Models\Repayment;
use App\Models\ExpectedSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RepaymentController extends Controller
{
    /**
     * Show form to record a repayment or defer.
     */
    public function create(ExpectedSchedule $expectedSchedule)
    {
        $expectedSchedule->loadMissing('setup.beneficiary');
        return view('repayments.create', compact('expectedSchedule'));
    }

    /**
     * Store payment or mark as deferred.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'expected_schedule_id' => 'required|exists:expected_schedules,id',
            'payment_amount'       => 'nullable|numeric|min:0',
            'payment_date'         => 'nullable|date',
            'or_number'            => 'nullable|string',
            'or_date'              => 'nullable|date',
            'penalty_amount'       => 'nullable|numeric|min:0',
            'returned_check'       => 'nullable|boolean',
            'deferred'             => 'nullable|boolean',
            'remarks'              => 'nullable|string|max:255',
        ]);

        $schedule = ExpectedSchedule::findOrFail($data['expected_schedule_id']);
        $setup    = $schedule->setup;

        // normalize
        $data['payment_amount'] = $data['payment_amount'] ?? 0;
        $data['penalty_amount'] = $data['penalty_amount'] ?? 0;
        $data['returned_check'] = $request->has('returned_check');
        $data['deferred']       = $request->has('deferred');

        // record repayment
        $repayment = Repayment::create($data);

        // handle deferral
        if ($data['deferred']) {
            // Step 1: capture original amount
            $origAmount = $schedule->amount_due;

            // Step 2: zero out this installment
            $schedule->update(['amount_due' => 0]);

            // Step 3: append a new schedule one month after LAST due_date
            $last = $setup->expectedSchedules()
                          ->orderBy('due_date', 'desc')
                          ->first();

            $nextDue = Carbon::parse($last->due_date)
                              ->addMonth()
                              ->startOfMonth();

            $setup->expectedSchedules()->create([
                'due_date'      => $nextDue->format('Y-m-d'),
                'amount_due'    => $origAmount,
                'months_lapsed' => max(0, $nextDue->diffInMonths(now())),
            ]);
        }

        return redirect()
            ->route('setups.show', $setup->id)
            ->with('success','Repayment recorded.');
    }

    public function edit(Repayment $repayment)
    {
        $expectedSchedule = $repayment->expectedSchedule;
        return view('repayments.edit', compact('repayment','expectedSchedule'));
    }

    public function update(Request $request, Repayment $repayment)
    {
        $data = $request->validate([
            'payment_amount'       => 'nullable|numeric|min:0',
            'payment_date'         => 'nullable|date',
            'or_number'            => 'nullable|string',
            'or_date'              => 'nullable|date',
            'penalty_amount'       => 'nullable|numeric|min:0',
            'returned_check'       => 'nullable|boolean',
            'deferred'             => 'nullable|boolean',
            'remarks'              => 'nullable|string|max:255',
        ]);

        $data['payment_amount'] = $data['payment_amount'] ?? 0;
        $data['penalty_amount'] = $data['penalty_amount'] ?? 0;
        $data['returned_check'] = $request->has('returned_check');
        $data['deferred']       = $request->has('deferred');

        $repayment->update($data);

        // If toggled to deferred again, repeat logic
        if ($data['deferred']) {
            $schedule = $repayment->expectedSchedule;
            $setup    = $schedule->setup;

            $origAmount = $schedule->amount_due;
            $schedule->update(['amount_due' => 0]);

            $last = $setup->expectedSchedules()
                          ->orderBy('due_date','desc')
                          ->first();

            $nextDue = Carbon::parse($last->due_date)
                              ->addMonth()
                              ->startOfMonth();

            $setup->expectedSchedules()->create([
                'due_date'      => $nextDue->format('Y-m-d'),
                'amount_due'    => $origAmount,
                'months_lapsed' => max(0, $nextDue->diffInMonths(now())),
            ]);
        }

        return redirect()
            ->route('setups.show', $repayment->expectedSchedule->setup_id)
            ->with('success','Repayment updated.');
    }

    public function destroy(Repayment $repayment)
    {
        $setupId = $repayment->expectedSchedule->setup_id;
        $repayment->delete();

        return redirect()
            ->route('setups.show',$setupId)
            ->with('success','Repayment deleted.');
    }
}
