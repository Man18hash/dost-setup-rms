@extends('layouts.main')

@section('title', 'Create Setup')

@section('content')
  <div class="container mt-4">
    <h2>Create New Setup</h2>

    <form action="{{ route('setups.store') }}" method="POST">
      @csrf

      <!-- Example Fields -->
      <div class="mb-3">
        <label for="project_title" class="form-label">Project Title</label>
        <input type="text" name="project_title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="amount_assisted" class="form-label">Amount Assisted</label>
        <input type="number" step="0.01" name="amount_assisted" class="form-control" required>
      </div>

      <!-- Add other fields as needed -->

      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </div>
@endsection
