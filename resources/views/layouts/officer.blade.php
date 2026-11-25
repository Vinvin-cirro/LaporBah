<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon LaporBah -->
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <title>@yield('title', 'Officer Dashboard') - LaporBah!</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        
    :root {
        --officer-navy: #020d32;
        --officer-navy-light: #14234c;
        --officer-yellow: #ffed78;
        --officer-bg: #f4f6fb;
        --officer-white: #ffffff;
        --officer-text: #182542;
        --officer-muted: #718096;
        --officer-border: #e5eaf2;
    }

    * {
        box-sizing: border-box;
    }

    body {
        min-height: 100vh;
        margin: 0;
        background: var(--officer-bg);
        color: var(--officer-text);
        font-family: Arial, sans-serif;
        font-size: 14px;
    }

    /* SIDEBAR */

    .officer-sidebar {
        width: 250px;
        min-width: 250px;
        min-height: 100vh;
        background: var(--officer-navy);
        color: white;
        flex-shrink: 0;
        position: sticky;
        top: 0;
        height: 100vh;
    }

    .sidebar-brand {
        padding: 22px 24px;
    }

    .sidebar-brand img {
        width: 38px;
        height: 38px;
        object-fit: contain;
    }

    .sidebar-section {
        padding: 18px 24px 10px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .6px;
        color: #aab4cc;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 13px;
        margin: 4px 12px;
        padding: 13px 14px;
        border-radius: 11px;
        color: #e8edf8;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background .2s ease, color .2s ease;
    }

    .sidebar-link:hover {
        background: var(--officer-navy-light);
        color: white;
    }

    .sidebar-link.active {
        background: var(--officer-yellow);
        color: var(--officer-navy);
        font-weight: 700;
    }

    .sidebar-link i {
        font-size: 18px;
    }

    /* MAIN LAYOUT */

    .officer-main {
        min-width: 0;
        min-height: 100vh;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .officer-navbar {
        min-height: 70px;
        padding: 12px 28px;
        background: white;
        border-bottom: 1px solid var(--officer-border);
    }

    .officer-content {
        flex: 1;
        width: 100%;
        padding: 30px 28px;
    }

    /* TYPOGRAPHY */

    .page-title {
        color: var(--officer-navy);
        font-size: clamp(25px, 2.5vw, 34px);
        font-weight: 700;
        letter-spacing: -.7px;
        line-height: 1.25;
    }

    .officer-content h2 {
        color: var(--officer-navy);
        font-size: 21px;
        font-weight: 700;
        letter-spacing: -.3px;
    }

    .officer-content h3 {
        color: var(--officer-navy);
    }

    .muted {
        color: var(--officer-muted) !important;
    }

    /* GENERAL PANELS */

    .panel {
        background: var(--officer-white);
        border: 1px solid var(--officer-border);
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 2px 8px rgba(15, 35, 75, .035);
    }

    /* TASK CARDS */

    .task-row {
        background: white;
        border: 1px solid var(--officer-border);
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 14px;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .task-row:last-child {
        margin-bottom: 0;
    }

    .task-row:hover {
        border-color: #c6d5ed;
        box-shadow: 0 4px 12px rgba(15, 35, 75, .06);
    }

    /* BUTTONS */

    .btn-navy {
        background: var(--officer-navy);
        border: 1px solid var(--officer-navy);
        color: white;
        border-radius: 8px;
        padding: 9px 16px;
        font-weight: 600;
        transition: all .2s ease;
    }

    .btn-navy:hover,
    .btn-navy:focus {
        background: #14234c;
        border-color: #14234c;
        color: white;
    }

    .btn-outline-primary {
        border-radius: 8px;
    }

    /* BADGES */

    .badge-status {
        background: #e5efff;
        color: #245bb5;
        border-radius: 20px;
        padding: 6px 11px;
        font-size: 11px;
        font-weight: 700;
    }

    /* EMPTY STATE */

    .officer-empty-icon {
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        background: #f1f4f9;
        color: #8491a5;
        border-radius: 15px;
        font-size: 25px;
    }

    /* TIMELINE */

    .timeline-item {
        position: relative;
        padding: 0 0 22px 18px;
        border-left: 2px solid var(--officer-border);
    }

    .timeline-item:last-child {
        padding-bottom: 0;
        border-left-color: transparent;
    }

    .timeline-item::before {
        content: "";
        position: absolute;
        top: 2px;
        left: -6px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #4779c7;
        border: 2px solid white;
    }

    /* FOOTER */

    .officer-footer {
        padding: 16px 28px 20px;
        color: var(--officer-muted);
        font-size: 12px;
        border-top: 1px solid var(--officer-border);
        background: white;
        margin-top: auto;
    }

    /* RESPONSIVE */

    @media (max-width: 768px) {
        .officer-sidebar {
            width: 75px;
            min-width: 75px;
        }

        .sidebar-brand {
            padding: 20px 18px;
        }

        .sidebar-brand img {
            width: 38px;
        }

        .sidebar-brand .brand-text,
        .sidebar-section,
        .sidebar-link span,
        .sidebar-profile {
            display: none;
        }

        .sidebar-link {
            justify-content: center;
            margin: 5px 8px;
            padding: 13px 8px;
        }

        .sidebar-link i {
            font-size: 20px;
        }

        .officer-navbar {
            min-height: 65px;
            padding: 10px 16px;
        }

        .officer-content {
            padding: 22px 16px;
        }

        .panel {
            padding: 18px;
        }

        .officer-footer {
            padding: 15px 16px;
        }
    }

    @media (max-width: 480px) {
        .officer-sidebar {
            width: 60px;
            min-width: 60px;
        }

        .sidebar-brand {
            padding: 16px 11px;
        }

        .sidebar-brand img {
            width: 36px;
        }

        .officer-content {
            padding: 18px 12px;
        }

        .officer-navbar {
            padding: 10px 12px;
        }

        .panel {
            padding: 15px;
            border-radius: 11px;
        }

        .page-title {
            font-size: 24px;
        }

        .officer-footer {
            padding: 14px 12px;
        }
    }

    </style>

    @stack('styles')
</head>

<body>

<div class="d-flex">

    {{-- Sidebar --}}
    <aside class="officer-sidebar d-flex flex-column">

        <div class="sidebar-brand">
            <a href="{{ route('officer.dashboard') }}"
               class="text-decoration-none text-white">

                <div class="d-flex align-items-center gap-2">
                    <img
                        src="{{ asset('images/laporbah-logo.png') }}"
                        alt="LaporBah! Logo"
                    >

                    <span class="brand-text fw-bold fs-4 text-warning">
                        LaporBah!
                    </span>
                </div>

                <div class="brand-text small fw-semibold mt-2">
                    PEMERINTAH KOTA
                </div>
            </a>
        </div>

        <div class="sidebar-section">RUANG KERJA</div>

        <nav class="flex-grow-1">

            {{-- Dashboard --}}
            <a href="{{ route('officer.dashboard') }}"
               class="sidebar-link {{ request()->is('officer/dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i>
                <span>Dashboard</span>
            </a>

            {{-- Peta Penugasan --}}
            <a href="{{ route('officer.map') }}"
               class="sidebar-link {{ request()->is('officer/map') ? 'active' : '' }}">
                <i class="bi bi-map"></i>
                <span>Peta Penugasan</span>
            </a>

            {{-- Tugas Saya --}}
            <a href="{{ route('officer.reports.index') }}"
               class="sidebar-link {{ request()->is('officer/reports*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i>
                <span>Tugas Saya</span>
            </a>

        </nav>

        {{-- Profile --}}
        <div class="sidebar-profile p-4 mt-auto">

            <div class="fw-semibold">
                {{ auth()->user()->name }}
            </div>

            <small class="text-white-50">
                Officer
            </small>

            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf

                <button type="submit" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Logout
                </button>
            </form>

        </div>

    </aside>


    {{-- Main Content --}}
    <div class="officer-main">

        {{-- Navbar --}}
        <header class="officer-navbar d-flex align-items-center justify-content-between px-4">

            <div class="small fw-semibold">
                Pemerintah Kota
                <span class="text-secondary mx-1">/</span>
                @yield('breadcrumb', 'Dashboard')
            </div>

            <div class="d-flex align-items-center gap-4">

                <div class="text-end">
                    <div class="fw-semibold small">
                        {{ auth()->user()->name }}
                    </div>

                    <small class="text-secondary">
                        Akun Officer
                    </small>
                </div>

            </div>

        </header>


        {{-- Page Content --}}
        <main class="officer-content">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </main>


        {{-- Footer --}}
        <footer class="officer-footer d-flex justify-content-between flex-wrap gap-2">

            <span>
                LaporBah! &bull; Sistem Pemantauan Laporan Warga
            </span>

            <span>
                Panel Officer
            </span>

        </footer>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>
