@extends('layouts.main')

@section('title','Beneficiaries & Setup Projects')
@section('content')
<h1 class="mb-4">Beneficiaries</h1>

{{-- Buttons for creation --}}
<button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
  + New Beneficiary
</button>
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSetupModal">
  + New Setup Project
</button>

{{-- Beneficiaries Table --}}
<table class="table table-striped mt-3">
  <thead>
    <tr>
      <th>Name</th>
      <th>Owner</th>
      <th>Address</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($beneficiaries as $b)
    <tr>
      <td>{{ $b->name }}</td>
      <td>{{ $b->owner }}</td>
      <td>{{ $b->address }}</td>
      <td>
        <a href="{{ route('beneficiaries.edit', $b) }}" class="btn btn-sm btn-warning">Edit</a>
        <form action="{{ route('beneficiaries.destroy', $b) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this beneficiary?');">
          @csrf
          @method('DELETE')
          <button class="btn btn-sm btn-danger">Delete</button>
        </form>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

{{-- Pagination --}}
{{ $beneficiaries->links() }}

{{-- Add Beneficiary Modal --}}
<div class="modal fade @if($errors->any()) show d-block @endif" id="addBeneficiaryModal" tabindex="-1" style="@if($errors->any()) display:block; background-color: rgba(0,0,0,0.5); @endif">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Beneficiary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('beneficiaries.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required value="{{ old('name') }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Owner</label>
            <input name="owner" class="form-control" value="{{ old('owner') }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control">{{ old('address') }}</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success">Save Beneficiary</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Add Setup Project Modal --}}
<div class="modal fade" id="addSetupModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Setup Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('setups.store') }}" method="POST">
        @csrf
        <div class="modal-body row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Beneficiary</label>
              <select name="beneficiary_id" class="form-select" required>
                <option value="" disabled selected>Select Beneficiary</option>
                @foreach($beneficiaries as $b)
                <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->spin_number }})</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Province</label>
              <select name="province_id" class="form-select" required>
                <option value="" disabled selected>Select Province</option>
                @foreach($provinces as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">SPIN Number</label>
              <input name="spin_number" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Project Title</label>
              <input name="project_title" class="form-control" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Check Number</label>
              <input name="check_number" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Amount Assisted</label>
              <input name="amount_assisted" type="number" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Check Date</label>
              <input name="check_date" type="date" class="form-control" required>
            </div>
            <div class="mb-3 row">
              <div class="col">
                <label class="form-label">Refund Start</label>
                <input name="refund_start" type="date" class="form-control" required>
              </div>
              <div class="col">
                <label class="form-label">Refund End</label>
                <input name="refund_end" type="date" class="form-control" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Save Setup Project</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
