<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','RMS Dashboard')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      padding-top: 80px;
      background-color: #f9f9f9;
      color: #212529;
    }

    .navbar {
      background-color: #ffffff;
      padding: 1rem 2rem;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .navbar-brand {
      font-weight: 600;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      color: #212529 !important;
    }

    .navbar-brand img {
      height: 48px;
      margin-right: 12px;
    }

    .nav-link {
      color: #333 !important;
      margin-left: 1rem;
      font-weight: 500;
      font-size: 1.05rem;
      transition: all 0.2s ease-in-out;
    }

    .nav-link:hover {
      color: #007bff !important;
      transform: translateY(-2px);
      text-decoration: none;
    }

    .navbar-toggler {
      border-color: rgba(0,0,0,0.1);
    }

    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(33,37,41, 0.7)' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('dashboard') }}">
      <img src="{{ asset('images/logo.png') }}" alt="Logo">
      RMS
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('beneficiary-setup') }}">Beneficiary/Setup</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('setups.index') }}">Setup</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('projects.index') }}">Projects</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('modals')
</body>
</html>
