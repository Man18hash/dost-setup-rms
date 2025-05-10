@extends('layouts.main')
@section('title',"Ledger — {$setup->project_title}")
@section('content')

<h1>SUBSIDIARY LEDGER: projects {{ $setup->beneficiary->name }} (SPIN {{ $setup->spin_number }})</h1>

<div class="row mb-4">
  <div class="col-md-6">
    <p><strong>Amount Assisted:</strong> ₱{{ number_format($setup->amount_assisted,2) }}</p>
    <p><strong>Check # / Date:</strong> {{ $setup->check_number }} / 
       {{ \Carbon\Carbon::parse($setup->check_date)->format('d/m/Y') }}
    </p>
  </div>
  <div class="col-md-6">
    <p><strong>Refund:</strong> 
       {{ \Carbon\Carbon::parse($setup->refund_start)->format('M-Y') }}
       to
       {{ \Carbon\Carbon::parse($setup->refund_end)->format('M-Y') }}
    </p>
    <p><strong>Title:</strong> {{ $setup->project_title }}</p>
  </div>
</div>

<table class="table table-bordered">
  <thead class="table-light">
    <tr>
      <th>Month Due</th><th>Amt Due</th><th># Mos Lapsed</th>
      <th>Penalty</th><th>Payment</th><th>OR / Date</th>
      <th>Ret. Check</th><th>PDC Date</th><th>Balance</th><th>Action</th>
    </tr>
  </thead>
  <tbody>
    @php $bal = $setup->amount_assisted; @endphp

    @foreach($setup->expectedSchedules as $sched)
      @php
        $last = $sched->repayments->last();
        $paid = $last->payment_amount  ?? 0;
        $pen  = $last->penalty_amount  ?? 0;
        $bal   = $bal - $paid + $pen;
      @endphp

      <tr>
        <td>{{ \Carbon\Carbon::parse($sched->due_date)->format('M-Y') }}</td>
        <td>₱{{ number_format($sched->amount_due,2) }}</td>
        <td>{{ $sched->months_lapsed }}</td>
        <td>{{ $pen? '₱'.number_format($pen,2) : '-' }}</td>
        <td>{{ $paid? '₱'.number_format($paid,2) : '-' }}</td>
        <td>
          @if($last)
            {{ $last->or_number }}<br>
            {{ optional($last->or_date)->format('d/m/Y') }}
          @else
            -
          @endif
        </td>
        <td>{{ ($last && $last->returned_check)? 'Yes':'-' }}</td>
        <td>{{ $last && $last->pdc_date 
                   ? $last->pdc_date->format('d/m/Y') 
                   : '-' 
               }}
        </td>
        <td>₱{{ number_format($bal,2) }}</td>
        <td>
          <a href="{{ route('repayments.create', $sched) }}"
             class="btn btn-sm btn-success mb-1">Payment</a>

          @if($last)
            <a href="{{ route('repayments.edit', $last) }}"
               class="btn btn-sm btn-warning mb-1">Edit</a>
            <form action="{{ route('repayments.destroy', $last) }}"
                  method="POST" style="display:inline"
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

@endsection
