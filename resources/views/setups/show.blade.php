@extends('layouts.main')

@section('title','SUBSIDIARY LEDGER - SET UP')
@section('content')

<style>
  .ledger-header {
    border: 2px solid #000;
    padding: 8px;
    margin-bottom: 16px;
    position: relative;
  }
  .ledger-header h2 {
    margin: 0;
    text-align: center;
    font-weight: bold;
  }
  .ledger-header .org {
    text-align: center;
    font-size: 1.1em;
    margin-bottom: 12px;
  }
  .ledger-header .start-btn {
    position: absolute;
    top: 8px;
    right: 8px;
  }
  .ledger-info {
    display: flex;
    margin-bottom: 8px;
  }
  .ledger-info > div {
    flex: 1;
    border: 1px solid #000;
    padding: 4px;
    font-size: 0.9em;
  }
  .ledger-info .left {
    border-right: none;
  }
  .ledger-info .right {
    border-left: none;
    text-align: right;
  }
  .ledger-info .title {
    flex: 2;
    border-top: none;
    border-bottom: none;
  }
</style>

<div class="ledger-header">
  <a href="{{ route('setups.export', $setup->id) }}" class="btn btn-success btn-sm mb-2 float-end">
  📥 Export to Excel
    </a>
  <h2>SUBSIDIARY LEDGER - SET UP</h2>
  <div class="org">DOST R02</div>

  <div class="ledger-info">
    <div class="left">
      <strong>Name of Beneficiary:</strong><br>
      {{ $setup->beneficiary->name }}
    </div>
    <div class="right">
      <strong>Spin:</strong><br>
      {{ $setup->spin_number }}
    </div>
  </div>

  <div class="ledger-info">
    <div class="left">
      <p><strong>Amount Assisted :</strong> ₱{{ number_format($setup->amount_assisted,2) }}</p>
      <p><strong>Check Number:</strong> {{ $setup->check_number }}</p>
      <p><strong>Check Date:</strong> {{ \Carbon\Carbon::parse($setup->check_date)->format('d/m/Y') }}</p>
    </div>
    <div class="title">
      <p><strong>Original Refund Schedule:</strong><br>
        {{ \Carbon\Carbon::parse($setup->refund_start)->format('M-Y') }}
        to
        {{ \Carbon\Carbon::parse($setup->refund_end)->format('M-Y') }}
      </p>
    </div>
    <div class="right">
      <p><strong>Project Title :</strong><br>{{ $setup->project_title }}</p>
    </div>
  </div>
</div>

<table class="table table-bordered">
  <thead class="table-light text-center">
    <tr>
      <th>Month Due</th>
      <th>Amount Due</th>
      <th># of Mos. Lapsed</th>
      <th>Penalty</th>
      <th>Payment</th>
      <th>OR No. / Date</th>
      <th>Returned Check</th>
      <th>PDC No. / Date</th>
      <th>Deferred?</th>
      <th>Deferred Date</th>
      <th>Remarks</th>
      <th>Balance</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    @php $running = $setup->amount_assisted; @endphp

    @foreach($setup->expectedSchedules as $sch)
      @php
        $last = $sch->repayments->last();
        $paid = $last->payment_amount  ?? 0;
        $pen  = $last->penalty_amount  ?? 0;
        $running = $running - $paid + $pen;
      @endphp
      <tr>
        <td class="text-center">
          {{ \Carbon\Carbon::parse($sch->due_date)->format('M-Y') }}
        </td>
        <td class="text-end">₱{{ number_format($sch->amount_due,2) }}</td>
        <td class="text-center">{{ $sch->months_lapsed }}</td>
        <td class="text-end">{{ $pen? '₱'.number_format($pen,2) : '-' }}</td>
        <td class="text-end">{{ $paid? '₱'.number_format($paid,2) : '-' }}</td>
        <td class="text-center">
          @if($last)
            {{ $last->or_number }}<br>
            {{ optional($last->or_date)->format('d/m/Y') }}
          @else
            -
          @endif
        </td>
        <td class="text-center">
          {{ ($last && $last->returned_check) ? 'Yes' : '-' }}
        </td>
        <td class="text-center">
          @if($last && $last->pdc_number)
            {{ $last->pdc_number }}<br>
            {{ optional($last->pdc_date)->format('d/m/Y') }}
          @else
            -
          @endif
        </td>
        <td class="text-center">
          {{ ($last && $last->deferred) ? 'Yes' : '-' }}
        </td>
        <td class="text-center">
          {{ $last && $last->deferred_date 
               ? \Carbon\Carbon::parse($last->deferred_date)->format('d/m/Y') 
               : '-' }}
        </td>
        <td>{{ $last->remarks ?? '-' }}</td>
        <td class="text-end">₱{{ number_format($running,2) }}</td>
        <td class="text-center">
          <a href="{{ route('repayments.create', $sch) }}"
             class="btn btn-sm btn-success mb-1">Payment</a>
          @if($last)
            <a href="{{ route('repayments.edit', $last) }}"
               class="btn btn-sm btn-warning mb-1">Edit</a>
            <form action="{{ route('repayments.destroy', $last) }}"
                  method="POST" class="d-inline"
                  onsubmit="return confirm('Delete payment?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger">Del</button>
            </form>
          @endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

{{-- Summary Totals --}}
<div class="mt-4 p-3 border rounded bg-light">
  <h5 class="mb-3"><strong>Summary</strong></h5>
  <div class="row mb-2">
    <div class="col-md-3"><strong>Total Amount Assisted:</strong></div>
    <div class="col-md-3 text-end">₱{{ number_format($totalBorrowed, 2) }}</div>

    <div class="col-md-3"><strong>Total Amount Due:</strong></div>
    <div class="col-md-3 text-end">₱{{ number_format($totalScheduled, 2) }}</div>
  </div>

  <div class="row mb-2">
    <div class="col-md-3"><strong>Total Past Due:</strong></div>
    <div class="col-md-3 text-end">₱{{ number_format($pastDue, 2) }}</div>

    <div class="col-md-3"><strong>Total Remaining to Pay:</strong></div>
    <div class="col-md-3 text-end">
      ₱{{ number_format(($totalScheduled + $totalPenalties - $totalPaid), 2) }}
    </div>
  </div>
</div>

@endsection
