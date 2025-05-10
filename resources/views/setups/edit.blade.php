{{-- resources/views/setups/edit.blade.php --}}
@extends('layouts.main')

@section('title', 'Edit Setup Project')

@section('content')
<div class="container mt-4">
  <h2>Edit Setup Project</h2>

  <form action="{{ route('setups.update', $setup) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Beneficiary</label>
      <select name="beneficiary_id" class="form-select" required>
        @foreach($beneficiaries as $b)
        <option value="{{ $b->id }}" {{ $setup->beneficiary_id == $b->id ? 'selected' : '' }}>
          {{ $b->name }} ({{ $b->spin_number }})
        </option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Province</label>
      <select name="province_id" class="form-select" required>
        @foreach($provinces as $p)
        <option value="{{ $p->id }}" {{ $setup->province_id == $p->id ? 'selected' : '' }}>
          {{ $p->name }}
        </option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">SPIN Number</label>
      <input name="spin_number" class="form-control" value="{{ $setup->spin_number }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Project Title</label>
      <input name="project_title" class="form-control" value="{{ $setup->project_title }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Check Number</label>
      <input name="check_number" class="form-control" value="{{ $setup->check_number }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Amount Assisted</label>
      <input name="amount_assisted" class="form-control" type="number" step="0.01" value="{{ $setup->amount_assisted }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Check Date</label>
      <input name="check_date" class="form-control" type="date" value="{{ $setup->check_date }}">
    </div>

    <div class="mb-3 row">
      <div class="col">
        <label class="form-label">Refund Start</label>
        <input name="refund_start" class="form-control" type="date" value="{{ $setup->refund_start }}">
      </div>
      <div class="col">
        <label class="form-label">Refund End</label>
        <input name="refund_end" class="form-control" type="date" value="{{ $setup->refund_end }}">
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Update Setup</button>
      <a href="{{ route('setups.index') }}" class="btn btn-secondary">Back</a>
    </div>
  </form>
</div>
@endsection
