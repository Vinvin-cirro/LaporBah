
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Governance Dashboard') - LaporBah!</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7ff;
            color: #182542;
            font-family: Arial, sans-serif;
        }

        .governance-sidebar {
            width: 250px;
            min-height: 100vh;
            background: #020d32;
            color: white;
            flex-shrink: 0;
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
            padding: 18px 24px 8px;
            font-size: 12px;
            font-weight: 600;
            color: #aab4cc;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 13px;
            margin: 4px 12px;
            padding: 13px 14px;
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-link:hover {
            background: #14234c;
            color: white;
        }

        .sidebar-link.active {
            background: #ffed78;
            color: #020d32;
            font-weight: 600;
        }

        .sidebar-link i {
            font-size: 18px;
        }

        .governance-main {
            min-width: 0;
            flex: 1;
        }

        .governance-navbar {
            min-height: 82px;
            background: white;
            border-bottom: 1px solid #e5e9f2;
        }

        .governance-content {
            padding: 30px 32px;
        }

        .governance-footer {
            padding: 0 32px 20px;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .governance-sidebar {
                width: 75px;
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

            .governance-content {
                padding: 22px 16px;
            }

            .governance-footer {
                padding: 0 16px 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

<div class="d-flex">

    {{-- Sidebar --}}
    <aside class="governance-sidebar d-flex flex-column">

        <div class="sidebar-brand">
            <a href="{{ url('/governance/dashboard') }}"
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

            <a href="{{ url('/governance/dashboard') }}"
               class="sidebar-link {{ request()->is('governance/dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('governance.map') }}"
                class="sidebar-link {{ request()->is('governance/map') ? 'active' : '' }}">
                <i class="bi bi-map"></i>
                <span>Peta Monitoring</span>
            </a>

            <a href="{{ route('governance.reports.index') }}"
               class="sidebar-link {{ request()->is('governance/reports*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-plus"></i>
                <span>Manajemen Laporan</span>
            </a>

            <a href="{{ route('governance.operations') }}"
               class="sidebar-link {{ request()->is('governance/operations*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Operasional Lapangan</span>
            </a>

            <a href="#"
               class="sidebar-link">
                <i class="bi bi-bar-chart"></i>
                <span>Statistik</span>
            </a>

        </nav>

        <div class="sidebar-profile p-4 mt-auto">
            <div class="fw-semibold">
                {{ auth()->user()->name }}
            </div>

            <small class="text-white-50">
                Governance
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
    <div class="governance-main">

        {{-- Navbar --}}
        <header class="governance-navbar d-flex align-items-center justify-content-between px-4">

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
                        Akun Governance
                    </small>
                </div>
            </div>

        </header>

        {{-- Page Content --}}
        <main class="governance-content">
            @yield('content')
        </main>

        <footer class="governance-footer d-flex justify-content-between flex-wrap gap-2">
            <span>LaporBah! &bull; Sistem Pemantauan Laporan Warga</span>
            <span>Panel Governance</span>
        </footer>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>
</html>
