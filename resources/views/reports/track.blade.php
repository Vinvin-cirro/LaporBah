@extends('layouts.citizen-report')

@section('title', 'Detail Pelacakan Laporan')

@php
    $statusLabels = [
        'pending' => 'Menunggu Verifikasi',
        'verifying' => 'Sedang Diverifikasi',
        'awaiting_approval' => 'Menunggu Persetujuan',
        'verified' => 'Terverifikasi',
        'investigating' => 'Dalam Penanganan',
        'resolved' => 'Selesai',
        'rejected' => 'Ditolak',
    ];

    $statusColors = [
        'pending' => 'secondary',
        'verifying' => 'info',
        'awaiting_approval' => 'warning',
        'verified' => 'primary',
        'investigating' => 'warning',
        'resolved' => 'success',
        'rejected' => 'danger',
    ];

    $currentStatus = $report->status;
    $statusLabel = $statusLabels[$currentStatus] ?? ucfirst($currentStatus);
    $statusColor = $statusColors[$currentStatus] ?? 'secondary';
@endphp


@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
    .tracking-container {
        max-width: 1340px;
        padding-top: 30px;
        padding-bottom: 48px;
    }

    /* HEADING */

    .page-heading {
        margin-bottom: 26px;
    }

    .page-heading h1 {
        font-size: 34px;
        font-weight: 700;
        color: #172441;
        margin-bottom: 6px;
    }

    .breadcrumb-text {
        color: #586987;
        font-size: 14px;
    }

    /* CARD */

    .tracking-card {
        background: #fff;
        border: 1px solid #dfe5f1;
        border-radius: 16px;
        height: 100%;
    }

    .tracking-card .card-body {
        padding: 24px;
    }

    .card-heading {
        font-size: 19px;
        font-weight: 700;
        color: #172441;
        margin-bottom: 20px;
    }

    /* TRACKING CODE */

    .tracking-code-box {
        background: #f1f4f9;
        border-radius: 11px;
        padding: 15px;
    }

    .tracking-code-label {
        color: #586987;
        font-size: 12px;
        margin-bottom: 5px;
    }

    .tracking-code-value {
        color: #061b49;
        font-size: 17px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .btn-darkblue {
        background: #061b49;
        color: #fff;
        border: 1px solid #061b49;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 14px;
    }

    .btn-darkblue:hover {
        background: #142b58;
        color: #fff;
    }

    .btn-outline-darkblue {
        background: #fff;
        color: #061b49;
        border: 1px solid #b9c5d9;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 14px;
    }

    .btn-outline-darkblue:hover {
        background: #061b49;
        color: #fff;
    }

    /* INFORMATION */

    .information-item {
        margin-bottom: 17px;
    }

    .information-item:last-child {
        margin-bottom: 0;
    }

    .information-label {
        color: #586987;
        font-size: 12px;
        margin-bottom: 4px;
    }

    .information-value {
        color: #172441;
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
        line-height: 1.5;
    }

    .report-description {
        color: #172441;
        font-size: 14px;
        line-height: 1.6;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .report-divider {
        border-color: #e5eaf2;
        margin: 20px 0;
        opacity: 1;
    }

    /* STATUS */

    .status-badge {
        display: inline-block;
        padding: 8px 13px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-current {
        background: #f1f4f9;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 24px;
    }

    .status-current-label {
        color: #586987;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .status-current-value {
        font-size: 17px;
        font-weight: 700;
        color: #172441;
    }

    /* TIMELINE */

    .timeline {
        position: relative;
        padding-left: 27px;
        margin-left: 5px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 7px;
        bottom: 8px;
        width: 2px;
        background: #dfe5f1;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 23px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -27px;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #061b49;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dfe5f1;
    }

    .timeline-title {
        font-size: 14px;
        font-weight: 700;
        color: #172441;
        margin-bottom: 4px;
    }

    .timeline-date {
        color: #586987;
        font-size: 12px;
        margin-bottom: 0;
    }

    .timeline-description {
        color: #586987;
        font-size: 13px;
        margin-top: 6px;
        line-height: 1.5;
    }

    /* MAP */

    #trackingMap {
        height: 300px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
        background: #eef2f8;
    }

    .coordinate-box {
        background: #f8f9fc;
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    /* PHOTOS */

    .tracking-photo {
        width: 100%;
        height: 190px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #dfe5f1;
    }

    .photo-caption {
        font-size: 12px;
        color: #586987;
        margin-top: 7px;
        overflow-wrap: anywhere;
    }

    .empty-state {
        color: #586987;
        font-size: 13px;
        background: #f8f9fc;
        padding: 16px;
        border-radius: 10px;
    }

    .privacy-note {
        background: #f1f4f9;
        border-radius: 12px;
        padding: 16px;
        color: #586987;
        font-size: 13px;
        line-height: 1.6;
    }

    /* RESPONSIVE */

    @media (max-width: 767px) {
        .tracking-container {
            padding-top: 22px;
        }

        .page-heading h1 {
            font-size: 27px;
        }

        .tracking-card .card-body {
            padding: 18px;
        }

        #trackingMap {
            height: 250px;
        }

        .tracking-photo {
            height: 180px;
        }
    }
</style>
@endpush


@section('content')

<div class="container tracking-container">

    {{-- HEADING --}}

    <div class="page-heading">

        <h1>Detail Pelacakan Laporan</h1>

        <div class="breadcrumb-text">
            Beranda / Pelacakan Laporan / Detail Laporan
        </div>

    </div>


    {{-- BARIS PERTAMA --}}

    <div class="row g-4 mb-4">

        {{-- INFORMASI LAPORAN --}}

        <div class="col-lg-7">

            <div class="card tracking-card">

                <div class="card-body">

                    <h5 class="card-heading">
                        Informasi Laporan
                    </h5>


                    {{-- TRACKING CODE --}}

                    <div class="tracking-code-box mb-4">

                        <div class="tracking-code-label">
                            Tracking Code
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                            <div class="tracking-code-value"
                                 id="trackingCode">
                                {{ $report->tracking_code }}
                            </div>

                            <button type="button"
                                    class="btn btn-outline-darkblue"
                                    onclick="copyTrackingCode()">

                                <i class="bi bi-copy me-1"></i>
                                Salin

                            </button>

                        </div>

                    </div>


                    {{-- INFORMASI DETAIL --}}

                    <div class="row g-3">

                        <div class="col-md-6">

                            <div class="information-item">

                                <div class="information-label">
                                    Kategori Laporan
                                </div>

                                <div class="information-value">
                                    {{ $report->category_label }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="information-item">

                                <div class="information-label">
                                    Tanggal Laporan
                                </div>

                                <div class="information-value">
                                    {{ $report->created_at->format('d M Y, H:i') }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="information-item">

                                <div class="information-label">
                                    Waktu Kejadian
                                </div>

                                <div class="information-value">

                                    {{ $report->incident_at
                                        ? \Carbon\Carbon::parse($report->incident_at)->format('d M Y, H:i')
                                        : 'Tidak dicantumkan' }}

                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="information-item">

                                <div class="information-label">
                                    Nama / Alamat Lokasi
                                </div>

                                <div class="information-value">
                                    {{ $report->location_name ?: 'Tidak dicantumkan' }}
                                </div>

                            </div>

                        </div>

                    </div>


                    <hr class="report-divider">


                    {{-- DESKRIPSI --}}

                    <div>

                        <h6 class="fw-bold mb-2"
                            style="font-size: 14px; color: #172441;">

                            Deskripsi Laporan

                        </h6>

                        <div class="report-description">
                            {{ $report->description }}
                        </div>

                    </div>


                </div>

            </div>

        </div>


        {{-- STATUS DAN RIWAYAT --}}

        <div class="col-lg-5">

            <div class="card tracking-card">

                <div class="card-body">

                    <h5 class="card-heading">
                        Status dan Riwayat
                    </h5>


                    {{-- STATUS SAAT INI --}}

                    <div class="status-current">

                        <div class="status-current-label">
                            Status laporan saat ini
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                            <div class="status-current-value">
                                {{ $statusLabel }}
                            </div>

                            <span class="badge bg-{{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>

                        </div>

                    </div>


                    {{-- TIMELINE --}}

                    <h6 class="fw-bold mb-4"
                        style="font-size: 14px; color: #172441;">

                        Riwayat Perkembangan

                    </h6>


                    <div class="timeline">

                        {{-- LAPORAN DIBUAT --}}

                        <div class="timeline-item">

                            <span class="timeline-dot"></span>

                            <h6 class="timeline-title">
                                Laporan Dikirim
                            </h6>

                            <p class="timeline-date">
                                {{ $report->created_at->format('d M Y, H:i') }}
                            </p>

                            <p class="timeline-description">
                                Laporan berhasil diterima oleh sistem LaporBah.
                            </p>

                        </div>


                        {{-- RIWAYAT UPDATE --}}

                        @forelse ($report->updates as $update)

                            <div class="timeline-item">

                                <span class="timeline-dot"></span>

                                <h6 class="timeline-title">

                                    {{ $statusLabels[$update->new_status]
                                        ?? ucfirst(str_replace('_', ' ', $update->new_status)) }}

                                </h6>

                                <p class="timeline-date">
                                    {{ $update->created_at->format('d M Y, H:i') }}
                                </p>

                                @if ($update->description ?? false)

                                    <p class="timeline-description">
                                        {{ $update->description }}
                                    </p>

                                @endif

                            </div>

                        @empty

                            <p class="text-secondary small mb-0">
                                Belum ada perubahan status.
                                Laporan sedang menunggu proses selanjutnya.
                            </p>

                        @endforelse

                    </div>


                </div>

            </div>

        </div>

    </div>



    {{-- BARIS KEDUA --}}

    <div class="row g-4 mb-4">


        {{-- LOKASI --}}

        <div class="col-lg-7">

            <div class="card tracking-card">

                <div class="card-body">

                    <h5 class="card-heading">
                        Lokasi Kejadian
                    </h5>


                    @if ($report->latitude !== null && $report->longitude !== null)

                        <div class="coordinate-box">

                            <div class="row g-3">

                                <div class="col-6">

                                    <div class="information-label">
                                        Latitude
                                    </div>

                                    <div class="information-value">
                                        {{ $report->latitude }}
                                    </div>

                                </div>

                                <div class="col-6">

                                    <div class="information-label">
                                        Longitude
                                    </div>

                                    <div class="information-value">
                                        {{ $report->longitude }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        <div id="trackingMap"></div>


                    @else

                        <div class="empty-state">
                            Koordinat lokasi belum tersedia.
                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- FOTO PENDUKUNG --}}

        <div class="col-lg-5">

            <div class="card tracking-card">

                <div class="card-body">

                    <h5 class="card-heading">
                        Foto Pendukung
                    </h5>


                    @if ($report->images->isNotEmpty())

                        <div class="row g-3">

                            @foreach ($report->images as $image)

                                <div class="col-12">

                                    <img
                                        src="{{ asset('storage/' . $image->image_path) }}"
                                        class="tracking-photo"
                                        alt="Foto pendukung laporan"
                                        loading="lazy"
                                    >

                                    @if ($image->caption)

                                        <p class="photo-caption mb-0">
                                            {{ $image->caption }}
                                        </p>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="empty-state">
                            Tidak ada foto pendukung pada laporan ini.
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- CATATAN PRIVASI --}}

    <div class="privacy-note mb-4">

        <strong class="d-block mb-1"
                style="color: #172441;">

            Informasi Pelacakan

        </strong>

        Gunakan Tracking Code untuk memantau perkembangan laporan.
        Status laporan akan diperbarui sesuai proses verifikasi
        dan penanganan oleh petugas terkait.

    </div>


    {{-- TOMBOL KEMBALI --}}

    <div class="d-flex justify-content-end">

        <a href="{{ auth()->check()
            ? route('citizen.dashboard')
            : url('/') }}"
           class="btn btn-darkblue">

            ← Kembali ke Beranda

        </a>

    </div>


</div>

@endsection



@push('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>

    // =====================================================
    // COPY TRACKING CODE
    // =====================================================

    function copyTrackingCode() {

        const code = document
            .getElementById('trackingCode')
            ?.textContent.trim();

        if (!code) return;

        navigator.clipboard.writeText(code)

            .then(() => {

                alert('Tracking Code berhasil disalin.');

            })

            .catch(() => {

                alert('Gagal menyalin kode. Silakan salin manual.');

            });

    }


    // =====================================================
    // LEAFLET MAP
    // =====================================================

    @if ($report->latitude !== null && $report->longitude !== null)

        const latitude = Number(@json($report->latitude));

        const longitude = Number(@json($report->longitude));

        const map = L.map('trackingMap').setView(
            [latitude, longitude],
            16
        );


        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution: '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);


        L.marker([latitude, longitude])
            .addTo(map)
            .bindPopup(@json($report->location_name ?: 'Lokasi laporan'))
            .openPopup();


        setTimeout(() => {
            map.invalidateSize();
        }, 200);

    @endif

</script>

@endpush