<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    @page { margin: 50px 25px; }
    *, *::before, *::after { box-sizing: border-box; }
    body, table { font-family: "DejaVu Sans", sans-serif; font-size: 11px; margin: 0; }
    h2 { margin: 0; }
    .text-center { text-align: center; }
    .text-end { text-align: right; }

    /* Document title */
    .header-title {
      text-align: center;
      margin-bottom: 4px;
      font-size: 16px;
      font-weight: bold;
    }
    .header-sub {
      text-align: center;
      margin-bottom: 12px;
      font-size: 12px;
    }

    /* Ledger header block */
    .ledger-header {
      border: 2px solid #000;
      padding: 8px;
      margin-bottom: 16px;
    }
    .ledger-header table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      border: none; /* remove inner table border */
    }
    .ledger-header td {
      padding: 6px;
      vertical-align: top;
      border: none; /* remove cell borders */
    }
    /* single horizontal line between rows */
    .ledger-header table tr + tr td {
      border-top: 1px solid #000;
    }
    .ledger-header .project-title {
      border-top: 1px solid #000;
      padding-top: 6px;
      font-weight: bold;
    }

    /* Main ledger table */
    table.ledger {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    table.ledger th,
    table.ledger td {
      border: 1px solid #000;
      padding: 4px 6px;
      font-size: 10px;
    }
    table.ledger th {
      background: #eee;
      text-align: center;
    }

    /* Summary row */
    .summary-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
    }
    .summary-table td {
      padding: 4px 6px;
    }

    /* Footer */
    .footer {
      position: fixed;
      bottom: 25px;
      left: 25px;
      right: 25px;
      border-top: 1px solid #000;
      padding-top: 8px;
      font-size: 11px;
    }
    .footer .col { display: inline-block; width: 48%; vertical-align: top; }
    .footer .col.right { text-align: right; }
  </style>
</head>
<body>

  {{-- Title --}}
  <div class="header-title">SUBSIDIARY LEDGER – SET UP</div>
  <div class="header-sub">DOST R02</div>

  {{-- Ledger Header Block --}}
  <div class="ledger-header">
    <table>
      <tr>
        <td style="width:70%">
          <strong>Name of Beneficiary :</strong><br>
          {{ $setup->beneficiary->name }}
          @if($setup->beneficiary->secondary_name)
            <br>{{ $setup->beneficiary->secondary_name }}
          @endif
        </td>
        <td style="width:30%">
          <strong>Spin:</strong><br>
          {{ $setup->spin_number }}
        </td>
      </tr>
      <tr>
        <td>
          <strong>Amount Assisted :</strong> ₱{{ number_format($setup->amount_assisted,2) }}<br>
          <strong>Check Number:</strong> {{ $setup->check_number }}<br>
          <strong>Check Date:</strong> {{ \Carbon\Carbon::parse($setup->check_date)->format('d/m/Y') }}
        </td>
        <td>
          <strong>Original Refund Schedule:</strong><br>
          {{ \Carbon\Carbon::parse($setup->refund_start)->format('M-Y') }}
           to
          {{ \Carbon\Carbon::parse($setup->refund_end)->format('M-Y') }}
        </td>
      </tr>
    </table>
    <div class="project-title">
      Project Title : {{ $setup->project_title }}
    </div>
  </div>

  {{-- Main Ledger --}}
  <table class="ledger">
    <thead>
      <tr>
        <th>Month Due</th>
        <th>Amount Due</th>
        <th># Mos. Lapsed</th>
        <th>Penalty</th>
        <th>Payment</th>
        <th>OR No. / Date</th>
        <th>Returned Check</th>
        <th>PDC No. / Date</th>
        <th>Balance</th>
      </tr>
    </thead>
    <tbody>
      @php $running = $setup->amount_assisted; @endphp
      @foreach($setup->expectedSchedules as $sch)
        @php
          $last   = $sch->repayments->last();
          $paid   = $last->payment_amount ?? 0;
          $running -= $paid;
        @endphp
        @if($sch->amount_due || $paid)
          <tr>
            <td class="text-center">{{ \Carbon\Carbon::parse($sch->due_date)->format('M-Y') }}</td>
            <td class="text-end">₱{{ number_format($sch->amount_due,2) }}</td>
            <td class="text-center">{{ $sch->months_lapsed }}</td>
            <td class="text-center">-</td>
            <td class="text-end">{{ $paid ? '₱'.number_format($paid,2) : '-' }}</td>
            <td class="text-center">
              {{ $last->or_number ?? '-' }}<br>
              {{ $last && $last->or_date ? $last->or_date->format('d/m/Y') : '-' }}
            </td>
            <td class="text-center">{{ $last && $last->returned_check ? 'Yes' : '-' }}</td>
            <td class="text-center">
              {{ $last->pdc_number ?? '-' }}<br>
              {{ $last && $last->pdc_date ? $last->pdc_date->format('d/m/Y') : '-' }}
            </td>
            <td class="text-end">₱{{ number_format($running,2) }}</td>
          </tr>
        @endif
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th class="text-end" colspan="8">Total:</th>
        <th class="text-end">₱{{ number_format($setup->expectedSchedules->sum('amount_due'),2) }}</th>
      </tr>
    </tfoot>
  </table>

  {{-- Summary --}}
  <table class="summary-table">
    <tr>
      <td style="width:15%"><strong>Past Due:</strong></td>
      <td style="width:20%" class="text-end">₱{{ number_format($pastDue,2) }}</td>
      <td style="width:15%"><strong>Remaining:</strong></td>
      <td style="width:20%" class="text-end">₱{{ number_format($totalRemaining,2) }}</td>
    </tr>
  </table>

  {{-- Footer --}}
  <div class="footer">
    <div class="col">
      Prepared by:<br><br>
      ___________________________<br>
      <strong>NANCY C. GUIMAYEN</strong><br>
      Accountant III
    </div>
    <div class="col right">
      Approved by:<br><br>
      ___________________________<br>
      <strong>MARY ANN P. MAGLASIN</strong><br>
      ARD-FASS
    </div>
  </div>

</body>
</html>
