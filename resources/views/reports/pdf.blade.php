<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: sans-serif; font-size: 9px; }
    table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
    th, td { border: 1px solid #000; padding: 3px; }
    th { background: #eee; text-align: center; }
    td { vertical-align: top; }
    .text-center { text-align: center; }
    .text-end    { text-align: right; }
    h2, h4 { margin: 0; text-align: center; }
    .header { margin-bottom: 0.5rem; }
  </style>
</head>
<body>
  <div class="header">
    <h2>Quarterly Setup Report</h2>
    <h4>Q{{ $quarter }} {{ $year }}</h4>
    <p>Deactivated Setups: {{ $deactivatedCount }}</p>
  </div>

  @if($setups->isEmpty())
    <p>No active setups in this period.</p>
  @else
    <table>
      <thead>
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
            <td class="text-center">{{ $s['spin'] }}</td>
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
</body>
</html>
