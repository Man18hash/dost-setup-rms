@extends('layouts.main')

@section('title', 'Edit Beneficiary')

@section('content')
<div class="container mt-4">
  <h2>Edit Beneficiary</h2>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form action="{{ route('beneficiaries.update', $beneficiary) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">SPIN Number</label>
      <input name="spin_number" class="form-control" value="{{ old('spin_number', $beneficiary->spin_number) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input name="name" class="form-control" value="{{ old('name', $beneficiary->name) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Owner</label>
      <input name="owner" class="form-control" value="{{ old('owner', $beneficiary->owner) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control">{{ old('address', $beneficiary->address) }}</textarea>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Update</button>
      <a href="{{ route('beneficiaries.index') }}" class="btn btn-secondary">Back</a>
    </div>
  </form>
</div>
@endsection
