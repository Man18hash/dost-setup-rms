@extends('layouts.main')
@section('title','Dashboard')
@section('content')
<div class="row">
  <div class="col-md-8">
    <h2>Analytics</h2>
    <canvas id="chartCanvas"></canvas>
  </div>
  <div class="col-md-4">
    <h2>Summary</h2>
    <ul class="list-group">
      <li class="list-group-item">Total Beneficiaries: {{ $totalBeneficiaries }}</li>
      <li class="list-group-item">Active Setups: {{ $activeSetups }}</li>
      <li class="list-group-item">Fully Paid: {{ $fullyPaid }}</li>
    </ul>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('chartCanvas');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: @json($chartLabels),
      datasets: [{
        label: 'Collections',
        data: @json($chartData),
      }]
    }
  });
</script>
@endsection
