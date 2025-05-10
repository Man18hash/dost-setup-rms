<table>
  <thead>
    <tr>
      <th>Month Due</th>
      <th>Amount Due</th>
      <th>Payment</th>
      <th>Penalty</th>
      <th>Balance</th>
    </tr>
  </thead>
  <tbody>
    @php $running = $setup->amount_assisted; @endphp
    @foreach($setup->expectedSchedules as $sch)
      @php
        $last = $sch->repayments->last();
        $paid = $last->payment_amount ?? 0;
        $pen  = $last->penalty_amount ?? 0;
        $running = $running - $paid + $pen;
      @endphp
      <tr>
        <td>{{ \Carbon\Carbon::parse($sch->due_date)->format('M Y') }}</td>
        <td>{{ $sch->amount_due }}</td>
        <td>{{ $paid }}</td>
        <td>{{ $pen }}</td>
        <td>{{ number_format($running, 2) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
