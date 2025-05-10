{{-- resources/views/beneficiaries/index.blade.php --}}
@extends('layouts.main')

@section('title','Beneficiaries')
@section('content')
<h1 class="mb-4">Beneficiaries</h1>

{{-- Button to open “Add Beneficiary” modal --}}
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
  + New Beneficiary
</button>

{{-- Beneficiaries table --}}
<table class="table table-striped">
  <thead>
    <tr>
      <th>SPIN Number</th>
      <th>Name</th>
      <th>Owner</th>
      <th>Address</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($beneficiaries as $b)
    <tr>
      <td>{{ $b->spin_number }}</td>
      <td>{{ $b->name }}</td>
      <td>{{ $b->owner }}</td>
      <td>{{ $b->address }}</td>
      <td>
        <a href="{{ route('beneficiaries.edit', $b) }}" class="btn btn-sm btn-warning">Edit</a>
        <form action="{{ route('beneficiaries.destroy', $b) }}"
              method="POST"
              class="d-inline"
              onsubmit="return confirm('Delete this beneficiary?');">
          @csrf @method('DELETE')
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
<div class="modal fade" id="addBeneficiaryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Beneficiary</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('beneficiaries.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">SPIN Number</label>
            <input name="spin_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Owner</label>
            <input name="owner" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
