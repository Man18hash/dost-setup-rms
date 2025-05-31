@extends('layouts.main')

@section('title','Quarterly Setup Report')

@section('content')
  <h1 class="mb-4">Quarterly Setup Report</h1>

  {{-- Filters & Export Button --}}
  <form method="GET" action="{{ route('reports') }}" class="row g-2 align-items-end mb-4">
    <div class="col-auto">
      <label class="form-label">Year</label>
      <select name="year" class="form-select">
        @foreach($years as $y)
          <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label">Quarter</label>
      <select name="quarter" class="form-select">
        @foreach($quarters as $q)
          <option value="{{ $q }}" @selected($quarter == $q)>Q{{ $q }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Apply</button>
    </div>
    <div class="col-auto">
      <a href="{{ route('reports.exportPdf', ['year'=>$year,'quarter'=>$quarter]) }}"
         class="btn btn-success">
        📥 Export PDF
      </a>
    </div>
  </form>

  <div class="mb-3">
    <strong>Deactivated Setups in Q{{ $quarter }} {{ $year }}:</strong>
    {{ $deactivatedCount }}
  </div>

  @if($setups->isEmpty())
    <p class="text-muted">No active setups in this period.</p>
  @else
    <table class="table table-sm table-bordered">
      <thead class="table-light text-center">
        <tr>
          <th>No.</th>
          <th>Account Used</th>
          <th>Name of AO/Employee</th>
          <th>Purpose</th>
          <th>Date Granted</th>
          <th>Unliquidated Amount</th>
          <th>Due Date for Liquidation</th>
          <th>Age (Months)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($setups as $i => $s)
          <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $s['spin'] }}</td>
            <td>{{ $s['owner'] }}</td>
            <td>{{ $s['title'] }}</td>
            <td class="text-center">{{ $s['date_granted'] }}</td>
            <td class="text-end">₱{{ number_format($s['amount_assisted'],2) }}</td>
            <td class="text-center">{{ $s['due_date'] }}</td>
            <td class="text-center">{{ $s['age_months'] }}</td>
            <td class="text-center">{{ $s['status'] }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
@endsection
