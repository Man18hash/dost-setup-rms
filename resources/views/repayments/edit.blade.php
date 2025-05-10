@extends('layouts.main')

@section('title', 'Edit Repayment')

@section('content')
<div class="container mt-4">
  <h2 class="mb-4">✏️ Edit Repayment for 
    {{ optional(optional($expectedSchedule->setup)->beneficiary)->name ?? 'N/A' }}
    ({{ \Carbon\Carbon::parse($expectedSchedule->due_date)->format('F Y') }})
  </h2>

  <form action="{{ route('repayments.update', $repayment) }}" method="POST">
    @csrf
    @method('PATCH')

    {{-- 🟢 Payment Details --}}
    <div class="border rounded p-3 mb-4">
      <h5 class="mb-3">🟢 Payment Details</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label>Payment Amount</label>
          <input type="number" step="0.01" name="payment_amount" class="form-control" value="{{ $repayment->payment_amount }}">
        </div>
        <div class="col-md-6 mb-3">
          <label>Payment Date</label>
          <input type="date" name="payment_date" class="form-control" value="{{ $repayment->payment_date }}">
        </div>
        <div class="col-md-6 mb-3">
          <label>OR Number</label>
          <input type="text" name="or_number" class="form-control" value="{{ $repayment->or_number }}">
        </div>
        <div class="col-md-6 mb-3">
          <label>OR Date</label>
          <input type="date" name="or_date" class="form-control" value="{{ $repayment->or_date }}">
        </div>
      </div>
    </div>

    {{-- 🔴 Penalty --}}
    <div class="border rounded p-3 mb-4">
      <h5 class="mb-3">🔴 Penalty</h5>
      <div class="mb-3">
        <label>Penalty Amount</label>
        <input type="number" step="0.01" name="penalty_amount" class="form-control" value="{{ $repayment->penalty_amount }}">
      </div>
    </div>

    {{-- 🟡 Defer Payment --}}
    <div class="border rounded p-3 mb-4">
      <h5 class="mb-3">🟡 Defer Payment</h5>
      <div class="form-check mb-3">
        <input type="checkbox" name="deferred" value="1" class="form-check-input" id="deferred" {{ $repayment->deferred ? 'checked' : '' }}>
        <label class="form-check-label" for="deferred">Defer this payment</label>
      </div>
      <div class="mb-3">
        <label>Deferred Date</label>
        <input type="date" name="deferred_date" class="form-control" value="{{ $repayment->deferred_date }}">
      </div>
    </div>

    {{-- 🔵 Other Info --}}
    <div class="border rounded p-3 mb-4">
      <h5 class="mb-3">🔵 Other Info</h5>
      <div class="form-check mb-3">
        <input type="checkbox" name="returned_check" value="1" class="form-check-input" id="returned_check" {{ $repayment->returned_check ? 'checked' : '' }}>
        <label class="form-check-label" for="returned_check">Returned Check</label>
      </div>
      <div class="mb-3">
        <label>PDC Number</label>
        <input type="text" name="pdc_number" class="form-control" value="{{ $repayment->pdc_number }}">
      </div>
      <div class="mb-3">
        <label>PDC Date</label>
        <input type="date" name="pdc_date" class="form-control" value="{{ $repayment->pdc_date }}">
      </div>
    </div>

    {{-- 📝 Remarks --}}
    <div class="border rounded p-3 mb-4">
      <h5 class="mb-3">📝 Remarks</h5>
      <div class="mb-3">
        <textarea name="remarks" rows="3" class="form-control">{{ $repayment->remarks }}</textarea>
      </div>
    </div>

    {{-- ✅ Submit and Cancel --}}
    <div class="d-flex gap-2">
      <button class="btn btn-primary">Update Repayment</button>
      <a href="{{ route('setups.show', $expectedSchedule->setup_id) }}" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection
