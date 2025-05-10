@extends('layouts.main')

@section('title','Setup Projects')
@section('content')

{{-- Auto-trigger Add Setup Project Modal on load --}}
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const modal = new bootstrap.Modal(document.getElementById('addSetupModal'));
    modal.show();

    document.querySelectorAll('.redirect-cancel').forEach(btn => {
      btn.addEventListener('click', function () {
        window.location.href = "{{ route('projects.index') }}";
      });
    });
  });
</script>

{{-- Add Setup Project Modal --}}
<div class="modal fade show d-block" id="addSetupModal" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Setup Project</h5>
        <button type="button" class="btn-close redirect-cancel"></button>
      </div>
      <form action="{{ route('setups.store') }}" method="POST">
        @csrf
        <div class="modal-body row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Beneficiary</label>
              <select id="beneficiary-select" name="beneficiary_id" class="form-select" required>
                <option value="" disabled selected>Select Beneficiary</option>
                @foreach($beneficiaries as $b)
                  <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->spin_number }})</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Province</label>
              <select name="province_id" class="form-select" required>
                <option value="" disabled selected>Select Province</option>
                @foreach($provinces as $p)
                  <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">SPIN Number</label>
              <input name="spin_number" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Project Title</label>
              <input name="project_title" class="form-control" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Check Number</label>
              <input name="check_number" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Amount Assisted</label>
              <input name="amount_assisted" type="number" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Check Date</label>
              <input name="check_date" type="date" class="form-control" required>
            </div>
            <div class="mb-3 row">
              <div class="col">
                <label class="form-label">Refund Start</label>
                <input name="refund_start" type="date" class="form-control" required>
              </div>
              <div class="col">
                <label class="form-label">Refund End</label>
                <input name="refund_end" type="date" class="form-control" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary redirect-cancel">Cancel</button>
          <button class="btn btn-primary">Save Setup Project</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('styles')
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#beneficiary-select').select2({
        dropdownParent: $('#addSetupModal'),
        placeholder: 'Search or select a beneficiary',
        width: '100%'
      });
    });
  </script>
@endpush

@endsection
