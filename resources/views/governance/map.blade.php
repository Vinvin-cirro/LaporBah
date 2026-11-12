
@extends('layouts.governance')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Peta Monitoring</h4>
        <p class="text-muted mb-0">
            Pantau persebaran laporan masyarakat di Kota Medan.
        </p>
    </div>

    {{-- STATISTICS --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Laporan</p>
                    <h4 class="fw-bold mb-0">
                        {{ number_format($statistics['total']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Menunggu Verifikasi</p>
                    <h4 class="fw-bold text-warning mb-0">
                        {{ number_format($statistics['pending']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Sedang Ditangani</p>
                    <h4 class="fw-bold text-info mb-0">
                        {{ number_format($statistics['investigating']) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted small mb-1">Selesai</p>
                    <h4 class="fw-bold text-success mb-0">
                        {{ number_format($statistics['resolved']) }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

    {{-- FILTER --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">

            <form action="{{ route('governance.map') }}"
                  method="GET"
                  class="row g-3 align-items-end">

                <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold">
                        Kategori Laporan
                    </label>

                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <option value="begal"
                            {{ $selectedCategory === 'begal' ? 'selected' : '' }}>
                            Begal
                        </option>
                        <option value="dark"
                            {{ $selectedCategory === 'dark' ? 'selected' : '' }}>
                            Area Gelap
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <label class="form-label small fw-semibold">
                        Status Laporan
                    </label>

                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>

                        @foreach ([
                            'pending' => 'Pending',
                            'verified' => 'Verified',
                            'investigating' => 'Investigating',
                            'resolved' => 'Resolved',
                            'rejected' => 'Rejected',
                        ] as $value => $label)
                            <option value="{{ $value }}"
                                {{ $selectedStatus === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>
                        Filter
                    </button>

                    <a href="{{ route('governance.map') }}"
                       class="btn btn-outline-secondary"
                       title="Reset filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>

            </form>

        </div>
    </div>

    {{-- MAP --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center mb-3">

                <div>
                    <h6 class="fw-bold mb-1">Sebaran Laporan</h6>
                    <p class="text-muted small mb-0">
                        Menampilkan {{ $reports->count() }} laporan sesuai filter.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-3 mt-2 mt-md-0">

                    <span class="small">
                        <span class="badge rounded-circle bg-danger">&nbsp;</span>
                        Begal
                    </span>

                    <span class="small">
                        <span class="badge rounded-circle bg-warning">&nbsp;</span>
                        Area Gelap
                    </span>

                </div>

            </div>

            <div id="governanceMap"
                 style="height: 520px; width: 100%; border-radius: 10px;">
            </div>

            <div class="small text-muted mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Pilih titik pada peta untuk melihat informasi laporan.
                Titik menunjukkan lokasi laporan, bukan hasil verifikasi kejadian.
            </div>

        </div>
    </div>

</div>

@endsection

@push('styles')
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const reports = @json($mapReports);

    // Pusat peta Kota Medan
    const map = L.map('governanceMap').setView([3.5952, 98.6722], 12);

    // OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Menghindari HTML dari input laporan dibaca sebagai elemen HTML
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    const statusLabels = {
        pending: 'Pending',
        verified: 'Verified',
        investigating: 'Investigating',
        resolved: 'Resolved',
        rejected: 'Rejected'
    };

    const statusColors = {
        pending: 'warning',
        verified: 'primary',
        investigating: 'info',
        resolved: 'success',
        rejected: 'danger'
    };

    const markers = [];

    reports.forEach(report => {

        // Lewati laporan yang tidak memiliki koordinat
        if (
            report.latitude === null ||
            report.longitude === null ||
            !Number.isFinite(Number(report.latitude)) ||
            !Number.isFinite(Number(report.longitude))
        ) {
            return;
        }

        const isBegal = report.category === 'begal';

        const marker = L.circleMarker(
            [Number(report.latitude), Number(report.longitude)],
            {
                radius: 9,
                color: isBegal ? '#dc3545' : '#d39e00',
                fillColor: isBegal ? '#dc3545' : '#ffc107',
                fillOpacity: 0.85,
                weight: 2
            }
        );

        const categoryLabel = isBegal ? 'Begal' : 'Area Gelap';
        const statusLabel = statusLabels[report.status] ?? report.status;
        const statusColor = statusColors[report.status] ?? 'secondary';

        const popupContent = `
            <div style="min-width: 210px;">
                <div class="mb-2">
                    <strong>${escapeHtml(categoryLabel)}</strong>
                    <span class="badge bg-${statusColor} ms-1">
                        ${escapeHtml(statusLabel)}
                    </span>
                </div>

                <p class="small mb-1">
                    <strong>Kode:</strong>
                    ${escapeHtml(report.tracking_code ?? '-')}
                </p>

                <p class="small mb-1">
                    <strong>Lokasi:</strong>
                    ${escapeHtml(report.location_name ?? 'Tidak dicantumkan')}
                </p>

                <p class="small mb-1">
                    <strong>Pelapor:</strong>
                    ${escapeHtml(report.reporter)}
                </p>

                <p class="small mb-1">
                    <strong>Tanggal:</strong>
                    ${escapeHtml(report.created_at ?? '-')}
                </p>

                <p class="small mb-0">
                    <strong>Deskripsi:</strong><br>
                    ${escapeHtml(report.description ?? '-')}
                </p>
            </div>
        `;

        marker.bindPopup(popupContent);
        marker.addTo(map);

        markers.push(marker);
    });

    // Zoom ke titik laporan jika tersedia
    if (markers.length > 0) {
        const markerGroup = L.featureGroup(markers);
        map.fitBounds(markerGroup.getBounds(), {
            padding: [30, 30],
            maxZoom: 14
        });
    }
</script>
@endpush
