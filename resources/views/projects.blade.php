@extends('layouts.main')
@section('title','Project Details')
@section('content')
<h1>Project: {{ $setup->project_title }}</h1>
<div class="row mb-4">
  <div class="col-md-6">
    <p><strong>Beneficiary:</strong> {{ $setup->beneficiary->name }}</p>
    <p><strong>Amount:</strong> {{ number_format($setup->amount_assisted,2) }}</p>
  </div>
  <div class="col-md-6">
    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#paymentModal">Record Payment</button>
    <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#deferModal">Defer</button>
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#pdcModal">PDC/Return</button>
  </div>
</div>
<table class="table table-bordered">
  <thead><tr><th>Due Date</th><th>Amount</th><th>Status</th><th>Balance</th></tr></thead>
  <tbody>
    @foreach($setup->expectedSchedules as $sched)
    <tr>
      <td>{{ \Carbon\Carbon::parse($sched->due_date)->format('M-Y') }}</td>
      <td>{{ number_format($sched->amount_due,2) }}</td>
      <td>
        @if($sched->repayments->isEmpty())
          <span class="badge bg-danger">Pending</span>
        @else
          <span class="badge bg-success">Paid</span>
        @endif
      </td>
      <td>{{ number_format($setup->amount_assisted - $setup->repayments->sum('payment_amount') + $setup->repayments->sum('penalty_amount'), 2) }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

@push('modals')
<!-- Payment Modal -->
<div class="modal fade" id="paymentModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="{{ route('repayments.store') }}" method="POST">
        @csrf
        <input type="hidden" name="expected_schedule_id" value="{{ $setup->expectedSchedules->first()->id }}">
        <div class="modal-body">
          <div class="mb-3"><label>Payment Amount</label><input name="payment_amount" type="number" step="0.01" class="form-control"></div>
          <div class="mb-3"><label>Payment Date</label><input name="payment_date" type="date" class="form-control"></div>
          <div class="mb-3"><label>OR Number</label><input name="or_number" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success">Save Payment</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Defer Modal -->
<div class="modal fade" id="deferModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Defer Payment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="{{ route('repayments.store') }}" method="POST">
        @csrf
        <input type="hidden" name="expected_schedule_id" value="{{ $setup->expectedSchedules->last()->id }}">
        <input type="hidden" name="payment_amount" value="0">
        <div class="modal-body">
          <p>Are you sure you want to defer the latest due date? This will extend the refund end date by one month.</p>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning">Confirm Defer</button></div>
      </form>
    </div>
  </div>
</div>

<!-- PDC Modal -->
<div class="modal fade" id="pdcModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Record PDC / Returned Check</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form action="{{ route('repayments.store') }}" method="POST">
        @csrf
        <input type="hidden" name="expected_schedule_id" value="{{ $setup->expectedSchedules->first()->id }}">
        <div class="modal-body">
          <div class="mb-3"><label>PDC Date</label><input name="pdc_date" type="date" class="form-control"></div>
          <div class="mb-3 form-check"><input name="returned_check" type="checkbox" class="form-check-input" id="returnedCheck"><label class="form-check-label" for="returnedCheck">Check Returned</label></div>
          <div class="mb-3"><label>Remarks</label><textarea name="remarks" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-info">Save PDC</button></div>
      </form>
    </div>
  </div>
</div>
@endpush
