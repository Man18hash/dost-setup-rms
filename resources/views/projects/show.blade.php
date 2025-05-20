@extends('layouts.main')

@section('title','SUBSIDIARY LEDGER - SET UP')
@section('content')

<style>
  /* your existing styles… */
</style>

<div class="ledger-header">
  <a href="{{ route('setups.exportPdf',$setup->id) }}"
     class="btn btn-success btn-sm float-end">📥 Export to PDF</a>
  <a href="{{ route('setups.schedule.customize',$setup) }}"
     class="btn btn-outline-primary btn-sm float-end me-2">⚙️ Customize</a>
  <h2>SUBSIDIARY LEDGER - SET UP</h2>
  <div class="org">DOST R02</div>
  {{-- ledger-info… --}}
</div>

<table class="table table-bordered">
  <thead class="table-light text-center">
    <tr>
      <th>Month Due</th><th>Amount Due</th><th># Mos. Lapsed</th>
      <th>Penalty</th><th>Payment</th><th>OR No. / Date</th>
      <th>Returned Check</th><th>PDC No. / Date</th>
      <th>Deferred?</th><th>Deferred Date</th>
      <th>Remarks</th><th>Balance</th><th>Action</th>
    </tr>
  </thead>
  <tbody>
    @php
      $running = $setup->amount_assisted;
      $now     = \Carbon\Carbon::now();
      $pastDue = 0;
    @endphp

    @foreach($setup->expectedSchedules->sortBy('due_date')->sortBy('id') as $sch)
      @php
        // clamp future lapsed
        $diff   = \Carbon\Carbon::parse($sch->due_date)->diffInMonths($now,false);
        $lapsed = max(0,$diff);

        $reps   = $sch->repayments;
        $paid   = $reps->sum('payment_amount');
        $pen    = $reps->sum('penalty_amount');

        // pay schedule first, then penalty
        $excess  = max(0,$paid - $sch->amount_due);
        $towPen  = min($excess,$pen);
        $outPen  = $pen - $towPen;

        $running -= $paid;
        $running += $outPen;

        if($now->gte($sch->due_date)) {
          $pastDue += ($sch->amount_due + $pen - $paid);
        }

        $last = $reps->last();
      @endphp

      <tr>
        <td class="text-center">{{ \Carbon\Carbon::parse($sch->due_date)->format('M-Y') }}</td>
        <td class="text-end">
          @if($sch->amount_due===0 && $last && $last->deferred)
            <em>Deferred</em>
          @else
            ₱{{ number_format($sch->amount_due,2) }}
          @endif
        </td>
        <td class="text-center">{{ $lapsed }}</td>
        <td class="text-end">{{ $pen? '₱'.number_format($pen,2) : '-' }}</td>
        <td class="text-end">{{ $paid? '₱'.number_format($paid,2) : '-' }}</td>
        <td class="text-center">
          @if($last && $last->or_number)
            {{ $last->or_number }}<br>{{ optional($last->or_date)->format('d/m/Y') }}
          @else - @endif
        </td>
        <td class="text-center">{{ $last && $last->returned_check ? 'Yes':'-' }}</td>
        <td class="text-center">
          @if($last && $last->pdc_number)
            {{ $last->pdc_number }}<br>{{ optional($last->pdc_date)->format('d/m/Y') }}
          @else - @endif
        </td>
        <td class="text-center">{{ $last && $last->deferred ? 'Yes':'-' }}</td>
        <td class="text-center">
          {{ $last && $last->deferred_date
               ? \Carbon\Carbon::parse($last->deferred_date)->format('d/m/Y')
               : '-' }}
        </td>
        <td>{{ $last->remarks ?? '-' }}</td>
        <td class="text-end">₱{{ number_format($running,2) }}</td>
        <td class="text-center">
          <a href="{{ route('repayments.create',$sch) }}" class="btn btn-sm btn-success">Payment</a>
          @if($last)
            <a href="{{ route('repayments.edit',$last) }}" class="btn btn-sm btn-warning">Edit</a>
            <form method="POST" action="{{ route('repayments.destroy',$last) }}" class="d-inline" onsubmit="return confirm('Delete?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger">Del</button>
            </form>
          @endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

{{-- Summary --}}
@php
  $totalRemaining = $running;
@endphp

<div class="mt-4 p-3 border rounded bg-light">
  <h5><strong>Summary</strong></h5>
  <div class="row">
    <div class="col-md-3"><strong>Total Assisted:</strong></div>
    <div class="col-md-3 text-end">₱{{ number_format($setup->amount_assisted,2) }}</div>
    <div class="col-md-3"><strong>Total Past Due:</strong></div>
    <div class="col-md-3 text-end">₱{{ number_format($pastDue,2) }}</div>
  </div>
  <div class="row mt-2">
    <div class="col-md-3"><strong>Total Remaining:</strong></div>
    <div class="col-md-3 text-end">₱{{ number_format($totalRemaining,2) }}</div>
  </div>
</div>

@endsection
