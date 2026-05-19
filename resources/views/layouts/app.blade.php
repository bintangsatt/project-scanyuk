<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Web AR Platform')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        body {
            background: #f8fafc;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary) !important;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* Status badges */
        .badge-processing { background: var(--warning); color: #000; }
        .badge-ready      { background: var(--success); color: #fff; }
        .badge-failed     { background: var(--danger); color: #fff; }

        /* Card hover effect */
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .card-hover.selected {
            border: 2px solid var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        /* Wizard steps */
        .wizard-step {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #94a3b8;
            transition: all 0.3s;
        }
        .wizard-step.active {
            background: var(--primary);
            color: white;
        }
        .wizard-step.done {
            background: #d1fae5;
            color: var(--success);
        }
        .wizard-step .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: currentColor;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }
        .wizard-step.active .step-num { background: rgba(255,255,255,0.3); }
        .wizard-step.done .step-num   { background: var(--success); color: white; }

        /* Preview container */
        #model-preview-container {
            background: #1a1a2e;
            border-radius: 12px;
            overflow: hidden;
        }

        /* Spinner animation */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spin { animation: spin 1s linear infinite; }
    </style>

    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-badge-ar me-2"></i>Web AR Platform
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="bi bi-grid me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="{{ route('ar.create') }}">
                    <i class="bi bi-plus-circle me-1"></i>Buat AR
                </a>
                <a class="nav-link" href="{{ route('templates.index') }}">
                    <i class="bi bi-collection me-1"></i>Template
                </a>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="container mb-3">
            <div class="alert alert-success alert-dismissible" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="container mb-3">
            <div class="alert alert-danger alert-dismissible" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="container pb-5">
        @yield('content')
    </main>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
