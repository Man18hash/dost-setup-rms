@extends('layouts.main')
@section('title','All Setups')
@section('content')
<h1>All Setup Projects</h1>
<table class="table table-hover">
  <thead><tr><th>SPIN</th><th>Beneficiary</th><th>Project</th><th>Actions</th></tr></thead>
  <tbody>
    @foreach($setups as $s)
    <tr>
      <td>{{ $s->spin_number }}</td>
      <td>{{ $s->beneficiary->name }}</td>
      <td>{{ $s->project_title }}</td>
      <td><a href="{{ route('projects.show', $s) }}" class="btn btn-sm btn-outline-primary">View</a></td>
    </tr>
    @endforeach
  </tbody>
</table>
{{ $setups->links() }}
@endsection
