@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">

  {{-- Province Filter --}}
  <form method="GET" action="{{ route('dashboard') }}" class="mb-4 row gx-2">
    <div class="col-auto">
      <select name="province_id" class="form-select">
        <option value="">All Provinces</option>
        @foreach($provinces as $prov)
          <option value="{{ $prov->id }}" {{ $provinceId == $prov->id ? 'selected' : '' }}>
            {{ $prov->name }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Filter</button>
    </div>
  </form>

  {{-- Centered Summary and Rankings --}}
  <div class="row justify-content-center">
    <div class="col-lg-6">

      {{-- Summary Box --}}
      <div class="card mb-4">
        <div class="card-header text-center">Summary</div>
        <ul class="list-group list-group-flush text-center">
          <li class="list-group-item"><strong>Total Beneficiaries:</strong> {{ $totalBeneficiaries }}</li>
          <li class="list-group-item"><strong>Active Setups:</strong> {{ $activeSetups }}</li>
          <li class="list-group-item"><strong>Deactivated Setups:</strong> {{ $deactivatedSetups }}</li>
          <li class="list-group-item"><strong>Fully Paid:</strong> {{ $fullyPaid }}</li>
        </ul>
      </div>

      {{-- Payers Ranking --}}
      <div class="card">
        <div class="card-header text-center">Payers Ranking</div>
        <ul class="list-group list-group-flush text-center">
          <li class="list-group-item">
            <strong>Best Payers:</strong>
            <ol class="mb-0 ps-3 d-inline-block text-start">
              @forelse($payers as $payer)
                <li>{{ $payer['name'] }} (₱{{ number_format($payer['paid'], 2) }})</li>
              @empty
                <li>— no data —</li>
              @endforelse
            </ol>
          </li>
          <li class="list-group-item">
            <strong>Worst Payers:</strong>
            <ol class="mb-0 ps-3 d-inline-block text-start">
              @forelse($payers->reverse() as $payer)
                <li>{{ $payer['name'] }} (₱{{ number_format($payer['paid'], 2) }})</li>
              @empty
                <li>— no data —</li>
              @endforelse
            </ol>
          </li>
        </ul>
      </div>

    </div>
  </div>
</div>
@endsection
