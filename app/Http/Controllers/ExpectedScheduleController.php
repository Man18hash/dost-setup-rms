<?php

namespace App\Http\Controllers;

use App\Models\ExpectedSchedule;
use App\Models\Setup;
use Illuminate\Http\Request;

class ExpectedScheduleController extends Controller
{
    public function index()
    {
        $schedules = ExpectedSchedule::with('setup.beneficiary')
                                     ->orderBy('due_date')
                                     ->paginate(20);
        return view('expected_schedules.index', compact('schedules'));
    }

    public function show(ExpectedSchedule $expectedSchedule)
    {
        return view('expected_schedules.show', compact('expectedSchedule'));
    }

    public function create()
    {
        $setups = Setup::with('beneficiary')->get();
        return view('expected_schedules.create', compact('setups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'setup_id'   => 'required|exists:setups,id',
            'due_date'   => 'required|date',
            'amount_due' => 'required|numeric',
        ]);

        ExpectedSchedule::create($data);
        return redirect()->route('expected_schedules.index')
                         ->with('success', 'Schedule entry added.');
    }

    public function edit(ExpectedSchedule $expectedSchedule)
    {
        $setups = Setup::with('beneficiary')->get();
        return view('expected_schedules.edit', compact('expectedSchedule','setups'));
    }

    public function update(Request $request, ExpectedSchedule $expectedSchedule)
    {
        $data = $request->validate([
            'due_date'   => 'required|date',
            'amount_due' => 'required|numeric',
        ]);

        $expectedSchedule->update($data);
        return redirect()->route('expected_schedules.index')
                         ->with('success', 'Schedule entry updated.');
    }

    public function destroy(ExpectedSchedule $expectedSchedule)
    {
        $expectedSchedule->delete();
        return redirect()->route('expected_schedules.index')
                         ->with('success', 'Schedule entry deleted.');
    }
}
