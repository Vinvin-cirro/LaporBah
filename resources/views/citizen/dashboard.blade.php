
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - LaporBah!</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <style>
        body {
            background: #f5f7ff;
            color: #172441;
            font-family: Arial, sans-serif;
        }

        .topbar {
            background: #020d32;
            color: white;
        }

        .brand {
            text-decoration: none;
        }

        .brand h1 {
            color: #ffe45c;
            font-size: 25px;
            font-weight: 700;
        }

        .brand p {
            color: #ffffff;
            font-size: 11px;
            margin-top: 2px;
        }
        .brand-logo {
            height: 64px;
            width: auto;
            object-fit: contain;
            margin-right: 24px;
            filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.25))
            drop-shadow(0 0 7px rgba(255, 255, 255, 0.05));
        }

        .nav-link {
            color: white;
            font-weight: 600;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #ffe45c;
        }

        .dashboard {
            padding-top: 28px;
            padding-bottom: 38px;
        }

        .page-title {
            font-size: 36px;
            font-weight: 700;
        }

        .muted {
            color: #586987;
        }

        .stat-card,
        .updates-card {
            background: white;
            border: 1px solid #dfe5f1;
            border-radius: 15px;
            padding: 18px;
            height: 100%;
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
            margin: 5px 0;
        }

        .map-card {
            background: #eef2f8;
            border-radius: 17px;
            padding: 12px;
            height: 100%;
        }

        #securityMap {
            width: 100%;
            height: 390px;
            border-radius: 12px;
            z-index: 1;
        }

        .map-legend {
            background: white;
            border: 1px solid #dfe5f1;
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            margin-top: 10px;
        }

        .legend-dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .update-item {
            padding: 15px 0;
            border-bottom: 1px solid #dfe5f1;
        }

        .update-item:last-child {
            border-bottom: none;
        }

        .update-label {
            display: inline-block;
            background: #eaf1ff;
            color: #155eef;
            border-radius: 7px;
            padding: 6px 10px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .report-banner {
            background: #eaf1ff;
            border-radius: 16px;
            padding: 24px;
        }

        .btn-darkblue {
            background: #020d32;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 13px 20px;
            font-weight: 600;
        }

        .btn-darkblue:hover {
            background: #142653;
            color: white;
        }

        .footer {
            background: white;
            border-top: 1px solid #dfe5f1;
            padding: 22px 0;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 28px;
            }

            #securityMap {
                height: 320px;
            }
        
        
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg topbar">
    <div class="container py-2">
        <img
            src="{{ asset('images/laporbah-logo.png') }}"
            alt="LaporBah!"
            class="brand-logo"
        >
        <a href="/" class="brand">
            <h1>LaporBah!</h1>
            <p>Untuk warga Medan</p>
        </a>

        <button
            class="navbar-toggler bg-light"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <div class="navbar-nav mx-auto gap-lg-4">
                <a class="nav-link active text-warning" href="{{ route('citizen.dashboard') }}">
                    Beranda
                </a>
                <a class="nav-link" href="#securityMap">Peta Keamanan</a>
                <a class="nav-link" href="#">Navigasi Aman</a>
                <a class="nav-link" href="#">Statistik</a>
            </div>

            <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <a href="{{ route('reports.create') }}" class="btn btn-outline-light">
                    + Lapor
                </a>

                <span class="text-white">♧</span>

                <span class="text-white">
                    {{ auth()->user()->name }}
                </span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-outline-light">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container dashboard">

    <div class="mb-4">
        <h1 class="page-title">Beranda</h1>
        <p class="muted">
            Selamat datang, {{ auth()->user()->name }}.
            Bantu Bekasi lebih aman.
        </p>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>CURAS / begal</div>
                <div class="stat-number">{{ $statistics['begal'] }}</div>
                <small class="muted">Laporan warga · pekan ini</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>Laporan area gelap</div>
                <div class="stat-number">{{ $statistics['dark'] }}</div>
                <small class="muted">Laporan warga · pekan ini</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>Menunggu tindakan</div>
                <div class="stat-number">{{ $statistics['pending'] }}</div>
                <small class="muted">Laporan berstatus pending</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div>Wilayah diperbarui</div>
                <div class="stat-number">{{ $statistics['updated_areas'] }}</div>
                <small class="muted">Pembaruan pekan ini</small>
            </div>
        </div>

    </div>

    <!-- Map and Updates -->
    <div class="row g-4 mb-4">

        <div class="col-lg-8">
            <div class="map-card">
                <div id="securityMap"></div>

                <div class="map-legend">
                    <span class="me-3">
                        <span class="legend-dot bg-danger"></span>
                        Begal
                    </span>

                    <span class="me-3">
                        <span class="legend-dot bg-warning"></span>
                        Area gelap
                    </span>

                    <span class="muted">
                        Titik menunjukkan lokasi laporan, bukan kepastian tingkat bahaya.
                    </span>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="updates-card">
                <h4 class="fw-bold mb-1">Pembaruan sekitar</h4>
                <p class="muted small">
                    Informasi penanganan wilayah
                </p>

                @forelse ($updates as $update)
                    <div class="update-item">
                        <span class="update-label">
                            Pembaruan resmi
                        </span>

                        <h6 class="fw-bold">
                            {{ $update->area?->name ?? 'Pembaruan wilayah' }}
                        </h6>

                        <p class="mb-2">
                            {{ $update->action_description }}
                        </p>

                        <small class="muted">
                            {{ $update->created_at?->format('d M, H.i') }} WIB
                        </small>
                    </div>
                @empty
                    <div class="py-4 text-center muted">
                        Belum ada pembaruan wilayah.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- My Reports -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-1">Laporan Saya</h4>
                    <p class="text-secondary mb-0">
                        Pantau laporan yang pernah kamu kirim.
                    </p>
                </div>

                <a href="{{ route('reports.create') }}" class="btn btn-outline-dark">
                    + Buat laporan
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Tracking Code</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($myReports as $report)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $report->tracking_code }}
                                </td>

                                <td>
                                    {{ $report->category === 'begal'
                                        ? 'Begal / CURAS'
                                        : 'Area Gelap' }}
                                </td>

                                <td>
                                    {{ $report->location_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $report->created_at->format('d M Y') }}
                                </td>

                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'verified' => 'info',
                                            'investigating' => 'primary',
                                            'resolved' => 'success',
                                            'rejected' => 'danger',
                                        ];

                                        $statusLabels = [
                                            'pending' => 'Menunggu',
                                            'verified' => 'Terverifikasi',
                                            'investigating' => 'Diproses',
                                            'resolved' => 'Selesai',
                                            'rejected' => 'Ditolak',
                                        ];
                                    @endphp

                                    <span class="badge text-bg-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$report->status] ?? $report->status }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ url('/reports/track/' . $report->tracking_code) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <h6 class="fw-bold">Belum ada laporan</h6>
                                    <p class="text-secondary mb-0">
                                        Laporan yang kamu kirim akan muncul di sini.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $myReports->links() }}
            </div>

        </div>
    </div>

    <!-- Report CTA -->
    <div class="report-banner d-flex flex-column flex-md-row
                justify-content-between align-items-md-center gap-3">

        <div>
            <h4 class="fw-bold">Ada masalah di sekitarmu?</h4>
            <p class="mb-0">
                Laporkan dengan aman. Jangan sertakan identitas korban
                atau data pribadi orang lain.
            </p>
        </div>

        <a href="{{ route('reports.create') }}" class="btn btn-darkblue">
            + Buat laporan warga
        </a>

    </div>

</main>

<footer class="footer">
    <div class="container d-flex flex-column flex-md-row
                justify-content-between gap-3">

        <div>
            <strong>LaporBah! · Bersama untuk Bekasi</strong>
            <p class="muted small mb-0">
                Bukan layanan darurat. Utamakan keselamatan diri.
            </p>
        </div>

        <div class="small">
            <a href="#" class="text-decoration-none text-dark me-3">Bantuan</a>
            <a href="#" class="text-decoration-none text-dark me-3">Tentang</a>
            <a href="#" class="text-decoration-none text-dark me-3">Ketentuan</a>
            <a href="#" class="text-decoration-none text-dark">Privasi</a>
        </div>

    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const map = L.map('securityMap').setView([-6.2383, 106.9756], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const reports = @json($mapReports);

    const bekasiBounds = L.latLngBounds(
        [-6.45, 106.85],
        [-6.05, 107.20]
    );

    reports.forEach(report => {
        const lat = Number(report.latitude);
        const lng = Number(report.longitude);

        if (
            !Number.isFinite(lat) ||
            !Number.isFinite(lng) ||
            !bekasiBounds.contains([lat, lng])
        ) {
            return;
        }

        const color = report.category === 'begal' ? '#dc3545' : '#f0ad00';

        L.circleMarker([lat, lng], {
            radius: 8,
            color: color,
            fillColor: color,
            fillOpacity: 0.8
        })
        .bindPopup(`
            <strong>${report.category === 'begal' ? 'Laporan begal' : 'Area gelap'}</strong>
            <br>
            ${report.location_name ?? 'Lokasi laporan'}
            <br>
            Status: ${report.status}
        `)
        .addTo(map);
    });
</script>

</body>
</html>
