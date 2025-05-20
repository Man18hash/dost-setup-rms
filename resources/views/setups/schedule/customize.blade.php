@extends('layouts.main')
@section('content')
  <h3>Customize Payment Schedule for {{ $setup->spin_number }}</h3>
  <p>Total to allocate: ₱<span id="principal">{{ number_format($setup->amount_assisted,2) }}</span></p>

  <style>
    /* Hide number-input spinners */
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    input[type=number] {
      -moz-appearance: textfield;
    }
  </style>

  <form id="schedule-form" action="{{ route('setups.schedule.customize.save', $setup) }}" method="POST">
    @csrf

    {{-- hidden container for IDs of existing rows the user deletes --}}
    <div id="deleted-container"></div>

    <table id="schedule-table" class="table table-bordered">
      <thead>
        <tr>
          <th>Due Month</th>
          <th>Amount to Pay</th>
          <th style="width:1%"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($schedules as $sch)
        <tr data-id="{{ $sch->id }}">
          <td>
            <input type="month"
                   class="form-control due-month"
                   name="due_month[{{ $sch->id }}]"
                   value="{{ \Carbon\Carbon::parse($sch->due_date)->format('Y-m') }}"
                   readonly>
          </td>
          <td>
            <input type="number"
                   step="1"
                   min="0"
                   class="form-control amount-field"
                   name="amount_due[{{ $sch->id }}]"
                   value="{{ old("amount_due.{$sch->id}", (int)$sch->amount_due) }}">
          </td>
          <td>
            <button type="button" class="btn btn-sm btn-danger delete-row">×</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Auto-Fill All --}}
    <div class="mb-3 form-inline">
      <label for="fill-amount" class="me-2">Monthly Payment:</label>
      <input type="number" id="fill-amount" class="form-control me-2" step="1" min="0" placeholder="₱ per month">
      <button type="button" id="auto-fill-btn" class="btn btn-info">Auto Fill All</button>
    </div>

    <button type="button" id="add-month" class="btn btn-secondary mb-3">+ Add Month</button>
    <br>
    <button type="submit" class="btn btn-primary">Save Custom Schedule</button>
  </form>

  {{-- template for new row --}}
  <template id="new-row-template">
    <tr>
      <td>
        <input type="month"
               class="form-control due-month"
               name="new_due_month[]">
      </td>
      <td>
        <input type="number"
               step="1"
               min="0"
               class="form-control amount-field"
               name="new_amount_due[]">
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-danger delete-row">×</button>
      </td>
    </tr>
  </template>

  {{-- SweetAlert2 --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const addBtn       = document.getElementById('add-month');
      const autoFillBtn  = document.getElementById('auto-fill-btn');
      const fillInput    = document.getElementById('fill-amount');
      const tableBody    = document.querySelector('#schedule-table tbody');
      const template     = document.getElementById('new-row-template').content;
      const form         = document.getElementById('schedule-form');
      const principal    = parseFloat(
        document.getElementById('principal')
                .innerText.replace(/,/g, '')
      );
      const deletedCont  = document.getElementById('deleted-container');

      // helper: get next month "YYYY-MM"
      function getNextMonth() {
        const months = Array.from(document.querySelectorAll('.due-month'))
          .map(i => i.value)
          .filter(v => !!v);
        let last = months.sort().pop();
        if (!last) {
          const dt = new Date("{{ $setup->refund_start }}");
          dt.setMonth(dt.getMonth()+1);
          last = dt.toISOString().slice(0,7);
        }
        const [y,m] = last.split('-').map(Number);
        let ny = y, nm = m+1;
        if (nm>12) nm=1, ny++;
        return `${ny.toString().padStart(4,'0')}-${nm.toString().padStart(2,'0')}`;
      }

      // Add new month row
      addBtn.addEventListener('click', () => {
        const clone = document.importNode(template, true);
        const input = clone.querySelector('.due-month');
        const next  = getNextMonth();
        input.value = next;
        input.min   = next;
        tableBody.appendChild(clone);
      });

      // Delete any row (existing or new)
      tableBody.addEventListener('click', e => {
        if (e.target.matches('.delete-row')) {
          const row = e.target.closest('tr');
          // if it's an existing schedule, capture its ID
          if (row.dataset.id) {
            const id = row.dataset.id;
            const hidden = document.createElement('input');
            hidden.type  = 'hidden';
            hidden.name  = 'deleted_ids[]';
            hidden.value = id;
            deletedCont.appendChild(hidden);
          }
          row.remove();
        }
      });

      // Auto-fill all amounts
      autoFillBtn.addEventListener('click', () => {
        const val = parseFloat(fillInput.value) || 0;
        if (val <= 0) {
          Swal.fire({ title: 'Invalid Amount', text: 'Enter a positive monthly payment.', icon: 'warning' });
          return;
        }
        document.querySelectorAll('.amount-field')
                .forEach(inp => inp.value = val);
      });

      // On submit: validate sum
      form.addEventListener('submit', e => {
        let sum = 0;
        document.querySelectorAll('.amount-field').forEach(inp => {
          sum += parseInt(inp.value) || 0;
        });
        if (sum !== principal) {
          e.preventDefault();
          const diff = principal - sum;
          let title = diff>0 ? 'Not Enough Allocated' : 'Over-Allocated';
          let text  = diff>0
                      ? `You still need ₱${diff.toFixed(2)} more.`
                      : `You have over-allocated by ₱${Math.abs(diff).toFixed(2)}.`;
          Swal.fire({ title, text, icon: 'warning', confirmButtonText:'OK' });
        }
      });
    });
  </script>
@endsection
