
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelacakan Laporan - LaporBah!</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .brand-logo {
            height: 64px;
            width: auto;
            object-fit: contain;
            margin-right: 24px;
            filter: drop-shadow(0 0 3px rgba(255, 255, 255, 0.25))
            drop-shadow(0 0 7px rgba(255, 255, 255, 0.05));
        }
    </style>
</head>

<body class="bg-light">

<nav class="navbar navbar-dark" style="background:#020d32">
    <div class="container">
        <a href="{{ auth()->check() ? route('citizen.dashboard') : url('/') }}"
           class="navbar-brand fw-bold d-flex align-items-center text-warning">
            <img
                src="{{ asset('images/laporbah-logo.png') }}"
                alt="LaporBah! Logo"
                height="38"
                class="me-2 brand-logo"
            >
            LaporBah!
        </a>

        <a href="{{ auth()->check() ? route('citizen.dashboard') : url('/') }}"
           class="btn btn-outline-light btn-sm">
            Kembali
        </a>
    </div>
</nav>

@php
    $statusLabels = [
        'pending' => 'Menunggu',
        'verified' => 'Terverifikasi',
        'investigating' => 'Dalam Penanganan',
        'resolved' => 'Selesai',
        'rejected' => 'Ditolak',
    ];

    $statusColors = [
        'pending' => 'secondary',
        'verified' => 'info',
        'investigating' => 'warning',
        'resolved' => 'success',
        'rejected' => 'danger',
    ];
@endphp

<div class="container py-4">

    <div class="mb-4">
        <h2 class="fw-bold">Detail Pelacakan Laporan</h2>
        <p class="text-secondary">
            Pantau perkembangan laporan berdasarkan tracking code.
        </p>
    </div>

    {{-- Informasi utama --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <small class="text-muted">Tracking Code</small>
                    <h5 class="fw-bold mb-0">
                        {{ $report->tracking_code }}
                    </h5>
                </div>

                <span class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }} fs-6">
                    {{ $statusLabels[$report->status] ?? ucfirst($report->status) }}
                </span>
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted">Kategori Laporan</small>
                    <p class="fw-semibold mb-0">
                        {{ $report->category === 'begal' ? 'Begal / CURAS' : 'Area Minim Penerangan' }}
                    </p>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Tanggal Laporan</small>
                    <p class="fw-semibold mb-0">
                        {{ $report->created_at->format('d M Y, H:i') }}
                    </p>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Waktu Kejadian</small>
                    <p class="fw-semibold mb-0">
                        {{ $report->incident_at
                            ? \Carbon\Carbon::parse($report->incident_at)->format('d M Y, H:i')
                            : 'Tidak dicantumkan' }}
                    </p>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Nama / Alamat Lokasi</small>
                    <p class="fw-semibold mb-0">
                        {{ $report->location_name ?: 'Tidak dicantumkan' }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    {{-- Deskripsi laporan --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Deskripsi Laporan</h5>

            <p class="mb-0" style="white-space: pre-line">{{ $report->description }}</p>
        </div>
    </div>

    {{-- Lokasi kejadian --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">Lokasi Kejadian</h5>

            @if ($report->latitude !== null && $report->longitude !== null)

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <small class="text-muted">Latitude</small>
                        <p class="fw-semibold mb-0">{{ $report->latitude }}</p>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Longitude</small>
                        <p class="fw-semibold mb-0">{{ $report->longitude }}</p>
                    </div>
                </div>

                <div
                    id="trackingMap"
                    class="rounded border"
                    style="height:350px"
                ></div>

            @else
                <p class="text-secondary mb-0">
                    Koordinat lokasi belum tersedia.
                </p>
            @endif

        </div>
    </div>

    {{-- Foto pendukung --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">Foto Pendukung</h5>

            @if ($report->images->isNotEmpty())

                <div class="row g-3">
                    @foreach ($report->images as $image)
                        <div class="col-md-4 col-sm-6">
                            <div class="card h-100">
                                <img
                                    src="{{ asset('storage/' . $image->image_path) }}"
                                    class="card-img-top"
                                    alt="Foto pendukung laporan"
                                    style="height:220px; object-fit:cover"
                                    loading="lazy"
                                >

                                @if ($image->caption)
                                    <div class="card-body">
                                        <p class="card-text small mb-0">
                                            {{ $image->caption }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <p class="text-secondary mb-0">
                    Tidak ada foto pendukung pada laporan ini.
                </p>
            @endif

        </div>
    </div>

    {{-- Riwayat status --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-4">Riwayat Status</h5>

            <div class="border-start border-2 ps-4 ms-2">

                <div class="position-relative mb-4">
                    <span
                        class="position-absolute bg-primary rounded-circle"
                        style="width:12px;height:12px;left:-31px;top:5px"
                    ></span>

                    <h6 class="fw-bold mb-1">Laporan Dikirim</h6>

                    <p class="text-muted small mb-0">
                        {{ $report->created_at->format('d M Y, H:i') }}
                    </p>
                </div>

                @forelse ($report->updates as $update)

                    <div class="position-relative mb-4">
                        <span
                            class="position-absolute bg-primary rounded-circle"
                            style="width:12px;height:12px;left:-31px;top:5px"
                        ></span>

                        <h6 class="fw-bold mb-1">
                            {{ $statusLabels[$update->new_status] ?? ucfirst($update->new_status) }}
                        </h6>

                        <p class="text-muted small mb-0">
                            {{ $update->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>

                @empty
                    <p class="text-muted small mb-0">
                        Belum ada perubahan status. Laporan sedang menunggu proses verifikasi.
                    </p>
                @endforelse

            </div>
        </div>
    </div>

    <p class="text-muted text-center small mt-4">
        Simpan tracking code untuk memantau perkembangan laporan.
    </p>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@if ($report->latitude !== null && $report->longitude !== null)
<script>
    const latitude = Number(@json($report->latitude));
    const longitude = Number(@json($report->longitude));

    const map = L.map('trackingMap').setView(
        [latitude, longitude],
        16
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.marker([latitude, longitude])
        .addTo(map)
        .bindPopup(@json($report->location_name ?: 'Lokasi laporan'))
        .openPopup();
</script>
@endif

</body>
</html>
