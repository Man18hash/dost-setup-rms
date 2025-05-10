@extends('layouts.main')

@section('title','Setup Projects')

@section('content')
<h1 class="mb-4">Setup Projects</h1>

{{-- Search & Province Filter Form --}}
<form method="GET" action="{{ route('setups.index') }}" class="row g-2 mb-4">
  <div class="col-md-4">
    <input 
      type="text" 
      name="search" 
      value="{{ request('search') }}" 
      class="form-control" 
      placeholder="Search SPIN, title or beneficiary…">
  </div>
  <div class="col-md-3">
    <select name="province_id" class="form-select">
      <option value="">All Provinces</option>
      @foreach($provinces as $province)
        <option 
          value="{{ $province->id }}" 
          @selected(request('province_id') == $province->id)>
          {{ $province->name }}
        </option>
      @endforeach
    </select>
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary">Filter</button>
  </div>
</form>

{{-- Table of Setups --}}
<table class="table table-hover">
  <thead>
    <tr>
      <th>SPIN Number</th>
      <th>Beneficiary</th>
      <th>Province</th>
      <th>Project Title</th>
      <th>Amount Assisted</th>
      <th>Refund Period</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse($setups as $setup)
      <tr>
        <td>{{ $setup->spin_number }}</td>
        <td>{{ $setup->beneficiary->name }}</td>
        <td>{{ $setup->province->name }}</td>
        <td>{{ $setup->project_title }}</td>
        <td>₱{{ number_format($setup->amount_assisted, 2) }}</td>
        <td>
          {{ \Carbon\Carbon::parse($setup->refund_start)->format('M Y') }}
          – 
          {{ \Carbon\Carbon::parse($setup->refund_end)->format('M Y') }}
        </td>
        <td>
          <a href="{{ route('setups.show', $setup) }}" class="btn btn-sm btn-info">View</a>
          <a href="{{ route('setups.edit', $setup) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('setups.destroy', $setup) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this setup?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7" class="text-center">No setups found.</td>
      </tr>
    @endforelse
  </tbody>
</table>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
  {{ $setups->links() }}
</div>
@endsection
