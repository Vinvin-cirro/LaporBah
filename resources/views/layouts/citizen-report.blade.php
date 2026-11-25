
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Buat Laporan') - LaporBah!</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <style>
        :root {
            --navy: #020d32;
            --yellow: #ffe45c;
            --background: #f5f7ff;
            --text: #172441;
            --muted: #586987;
            --border: #dfe5f1;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--background);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        /* =========================
           NAVBAR
        ========================= */

        .topbar {
            background: var(--navy);
            color: #fff;
        }

        .topbar .container {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .brand {
            text-decoration: none;
            margin-top: 10px;
            flex-shrink: 0;
        }

        .brand-logo {
            height: 64px;
            width: auto;
            object-fit: contain;
            margin-right: 24px;
            filter:
                drop-shadow(0 0 3px rgba(255, 255, 255, 0.25))
                drop-shadow(0 0 7px rgba(255, 255, 255, 0.05));
        }

        .brand h1 {
            color: var(--yellow);
            font-size: 25px;
            font-weight: 700;
            margin: 0;
        }

        .brand p {
            color: #fff;
            font-size: 11px;
            margin: 2px 0 0;
        }

        .topbar .nav-link {
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            transition: color .2s ease;
        }

        .topbar .nav-link:hover,
        .topbar .nav-link.active {
            color: var(--yellow);
        }

        .btn-report-nav {
            color: #fff;
            background: transparent;
            border: 1px solid #fff;
            border-radius: 8px;
            padding: 9px 22px;
            font-size: 14px;
            font-weight: 600;
            transition: .2s ease;
        }

        .btn-report-nav:hover {
            color: var(--navy);
            background: var(--yellow);
            border-color: var(--yellow);
        }

        .btn-login-nav {
            color: var(--navy);
            background: var(--yellow);
            border: 1px solid var(--yellow);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            padding: 9px 18px;
        }

        .btn-login-nav:hover {
            color: var(--navy);
            background: #ffed91;
            border-color: #ffed91;
        }

        .notification-icon {
            color: #fff;
            font-size: 20px;
            line-height: 1;
        }

        .user-name {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-logout {
            color: #fff;
            background: transparent;
            border: 1px solid #fff;
            border-radius: 7px;
            font-size: 12px;
            padding: 6px 12px;
        }

        .btn-logout:hover {
            color: var(--navy);
            background: var(--yellow);
            border-color: var(--yellow);
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, .5);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 .2rem rgba(255, 228, 92, .2);
        }

        /* =========================
           MAIN CONTENT
        ========================= */

        main {
            flex: 1;
            width: 100%;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            width: 100%;
            background: #fff;
            border-top: 1px solid var(--border);
            padding: 22px 0;
            margin-top: auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
        }

        .footer-info strong {
            display: block;
            color: var(--text);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .footer-info p {
            color: var(--muted);
            font-size: 12px;
            margin: 0;
        }

        .footer-links {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .footer-links a {
            color: var(--text);
            font-size: 12px;
            text-decoration: none;
            transition: color .2s ease;
        }

        .footer-links a:hover {
            color: #526da8;
            text-decoration: underline;
        }

        .footer-copyright {
            color: var(--muted);
            font-size: 12px;
            text-align: right;
            white-space: nowrap;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 991px) {
            .topbar .navbar-nav {
                align-items: flex-start !important;
                padding-top: 15px;
            }

            .topbar .nav-link {
                padding: 9px 0;
            }

            .topbar .navbar-nav {
                gap: 0 !important;
            }

            .navbar-actions {
                flex-wrap: wrap;
                padding-bottom: 12px;
            }
        }

        @media (max-width: 768px) {
            .brand-logo {
                height: 52px;
                margin-right: 13px;
            }

            .brand {
                margin-top: 0;
            }

            .brand h1 {
                font-size: 21px;
            }

            .footer-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .footer-links {
                gap: 15px;
            }

            .footer-copyright {
                text-align: left;
                white-space: normal;
            }
        }

        @media (max-width: 480px) {
            .footer {
                padding: 19px 0;
            }

            .footer-links {
                gap: 12px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg topbar">
        <div class="container">

            <div class="d-flex align-items-center">
                <a href="{{ route('index') }}">
                    <img
                        src="{{ asset('images/laporbah-logo.png') }}"
                        alt="LaporBah!"
                        class="brand-logo"
                    >
                </a>

                <a href="{{ route('index') }}" class="brand">
                    <h1>LaporBah!</h1>
                    <p>Untuk warga Medan</p>
                </a>
            </div>

            <button
                class="navbar-toggler bg-light"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">

                <div class="navbar-nav mx-auto gap-lg-4">
                    <a class="nav-link" href="{{ route('index') }}">
                        Beranda
                    </a>

                    <a class="nav-link" href="{{ route('index') }}#securityMap">
                        Peta Keamanan
                    </a>

                    <a class="nav-link" href="#">
                        Navigasi Aman
                    </a>

                    <a class="nav-link" href="#">
                        Statistik
                    </a>
                </div>

                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0 navbar-actions">

                    {{-- Tombol Lapor --}}
                    <a
                        href="{{ route('reports.categories') }}"
                        class="btn btn-report-nav"
                    >
                        + Lapor
                    </a>

                    @auth
                        {{-- Pengguna yang sudah login --}}
                        <span class="notification-icon" title="Notifikasi">
                            ♧
                        </span>

                        <span class="user-name">
                            {{ auth()->user()->name }}
                        </span>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-logout">
                                Keluar
                            </button>
                        </form>
                    @else
                        {{-- Pengguna guest --}}
                        <a
                            href="{{ route('login') }}"
                            class="btn btn-login-nav"
                        >
                            Login
                        </a>
                    @endauth

                </div>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="container footer-content">

            <div class="footer-info">
                <strong>LaporBah! · Bersama untuk Medan</strong>

                <p>
                    Bukan layanan darurat. Utamakan keselamatan diri.
                </p>
            </div>

            <div class="footer-links">
                <a href="#">Bantuan</a>
                <a href="#">Tentang</a>
                <a href="#">Ketentuan</a>
                <a href="#">Privasi</a>
            </div>

            <div class="footer-copyright">
                © {{ date('Y') }} LaporBah · Developed by Elsya Vindy
            </div>

        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')

</body>
</html>
