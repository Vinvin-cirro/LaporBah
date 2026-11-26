
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'LaporBah!') - LaporBah!</title>

    {{-- Bootstrap --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    {{-- Bootstrap Icons --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    {{-- Leaflet --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    {{-- Favicon --}}
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

        .navbar-toggler {
            border-color: rgba(255, 255, 255, .5);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 .2rem rgba(255, 228, 92, .2);
        }

        /* =========================
           PROFILE DROPDOWN
        ========================= */

        .profile-dropdown {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #fff;
            background: transparent;
            border: 0;
            border-radius: 10px;
            padding: 5px 9px;
            font-size: 14px;
            font-weight: 600;
            transition: .2s ease;
        }

        .profile-dropdown:hover,
        .profile-dropdown:focus {
            color: #fff;
            background: rgba(255, 255, 255, .12);
        }

        .profile-avatar {
            width: 38px;
            height: 38px;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            flex-shrink: 0;
            border: 2px solid rgba(255, 255, 255, .85);
        }

        .profile-dropdown::after {
            margin-left: 3px;
        }

        .profile-menu {
            min-width: 210px;
            padding: 8px;
            margin-top: 10px !important;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(2, 13, 50, .15);
        }

        .profile-menu .dropdown-item {
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            color: var(--text);
        }

        .profile-menu .dropdown-item:hover {
            background: #f1f4fa;
        }

        .profile-menu .dropdown-divider {
            margin: 6px 0;
        }

        .profile-menu .logout-item {
            color: #dc3545;
        }

        .profile-menu .logout-item:hover {
            background: #fff0f0;
            color: #dc3545;
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
                gap: 0 !important;
            }

            .topbar .nav-link {
                padding: 9px 0;
            }

            .navbar-actions {
                align-items: flex-start !important;
                flex-direction: column;
                flex-wrap: wrap;
                gap: 12px !important;
                padding-bottom: 12px;
            }

            .profile-dropdown {
                padding-left: 0;
            }

            .profile-menu {
                margin-top: 5px !important;
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

            {{-- BRAND --}}
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

            {{-- MOBILE TOGGLER --}}
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

            {{-- NAVBAR CONTENT --}}
            <div class="collapse navbar-collapse" id="mainNavbar">

                {{-- NAVIGATION LINKS --}}
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

                {{-- NAVBAR ACTIONS --}}
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0 navbar-actions">

                    {{-- TOMBOL LAPOR --}}
                    <a
                        href="{{ route('reports.categories') }}"
                        class="btn btn-report-nav"
                    >
                        + Lapor
                    </a>

                    @auth

                        @php
                            $user = auth()->user();

                            $profilePhoto = $user->profile_photo
                                ? asset('storage/' . $user->profile_photo)
                                : asset('images/default-profile.png');
                        @endphp

                        {{-- PROFILE DROPDOWN --}}
                        <div class="dropdown">

                            <button
                                class="profile-dropdown dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <img
                                    src="{{ $profilePhoto }}"
                                    alt="Foto Profil"
                                    class="profile-avatar"
                                >

                                <span>{{ $user->name }}</span>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end profile-menu">

                                {{-- DETAIL PROFIL --}}
                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="{{ route('citizen.profile') }}"
                                    >
                                        <i class="bi bi-person me-2"></i>
                                        Detail Profil
                                    </a>
                                </li>

                                {{-- AKTIVITAS SAYA --}}
                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                    >
                                        <i class="bi bi-clock-history me-2"></i>
                                        Aktivitas Saya
                                    </a>
                                </li>

                                {{-- PENGATURAN --}}
                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                    >
                                        <i class="bi bi-gear me-2"></i>
                                        Pengaturan
                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                {{-- LOGOUT --}}
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf

                                        <button
                                            type="submit"
                                            class="dropdown-item logout-item"
                                        >
                                            <i class="bi bi-box-arrow-right me-2"></i>
                                            Keluar
                                        </button>
                                    </form>
                                </li>

                            </ul>
                        </div>

                    @else

                        {{-- GUEST LOGIN --}}
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

    {{-- JAVASCRIPT --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')

</body>
</html>
