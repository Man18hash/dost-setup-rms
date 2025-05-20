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

  <div class="row">
    {{-- Left: Total Collection Chart --}}
    <div class="col-lg-8 mb-4">
      <div class="card h-100">
        <div class="card-header">Total Collection</div>
        <div class="card-body">
          <canvas id="chartCanvas" style="max-height:300px;"></canvas>
        </div>
      </div>
    </div>

    {{-- Right: Summary & Payers --}}
    <div class="col-lg-4">

      {{-- Summary Box --}}
      <div class="card mb-4">
        <div class="card-header">Summary</div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">
            <strong>Total Beneficiaries:</strong> {{ $totalBeneficiaries }}
          </li>
          <li class="list-group-item">
            <strong>Active Setups:</strong> {{ $activeSetups }}
          </li>
          <li class="list-group-item">
            <strong>Fully Paid:</strong> {{ $fullyPaid }}
          </li>
        </ul>
      </div>

      {{-- Best / Worst Payers --}}
      <div class="card">
        <div class="card-header">Payers Ranking</div>
        <ul class="list-group list-group-flush">

          {{-- Best → Worst --}}
          <li class="list-group-item">
            <strong>Best Payers:</strong>
            <ol class="mb-0 ps-3">
              @forelse($payers as $payer)
                <li>{{ $payer['name'] }} (₱{{ number_format($payer['paid'],2) }})</li>
              @empty
                <li>— no data —</li>
              @endforelse
            </ol>
          </li>

          {{-- Worst → Best --}}
          <li class="list-group-item">
            <strong>Worst Payers:</strong>
            <ol class="mb-0 ps-3">
              @forelse($payers->reverse() as $payer)
                <li>{{ $payer['name'] }} (₱{{ number_format($payer['paid'],2) }})</li>
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

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const ctx = document.getElementById('chartCanvas').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: @json($chartLabels),
          datasets: [{
            label: '₱ Collection',
            data: @json($chartData),
            borderWidth: 1,
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: v => '₱' + v.toLocaleString()
              }
            }
          },
          plugins: { legend: { display: false } }
        }
      });
    });
  </script>
@endpush
