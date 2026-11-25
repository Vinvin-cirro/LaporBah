
@extends('layouts.citizen-report')

@php
    $selectedCategory = old('category', request('category', ''));

    $categoryLabels = \App\Models\Report::CATEGORIES;

    $categoryLabel = $categoryLabels[$selectedCategory] ?? 'Laporan';
@endphp

@section('title', 'Lapor ' . $categoryLabel)

@push('styles')
<style>
    .page-container {
        max-width: 1340px;
        padding-top: 30px;
        padding-bottom: 48px;
    }

    .page-heading {
        margin-bottom: 26px;
    }

    .page-heading h1 {
        font-size: 36px;
        font-weight: 700;
        color: #172441;
        margin-bottom: 6px;
    }

    .breadcrumb-text {
        color: #586987;
        font-size: 14px;
    }

    .step-progress {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .step-item {
        background: #eef1f7;
        color: #172441;
        padding: 12px 16px;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 600;
    }

    .step-item.active {
        background: #061b49;
        color: white;
    }

    .report-card {
        background: white;
        border: 1px solid #dfe5f1;
        border-radius: 16px;
        height: 100%;
    }

    .report-card .card-body {
        padding: 24px;
    }

    .card-heading {
        font-size: 21px;
        font-weight: 700;
        margin-bottom: 22px;
        color: #172441;
    }

    .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #172441;
        margin-bottom: 9px;
    }

    .required-mark {
        color: #dc3545;
    }

    .form-control,
    .form-select {
        min-height: 45px;
        border: 1px solid #b9c5d9;
        border-radius: 10px;
        color: #172441;
        font-size: 14px;
    }

    textarea.form-control {
        min-height: 115px;
        resize: vertical;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #061b49;
        box-shadow: 0 0 0 3px rgba(6, 27, 73, .09);
    }

    .form-group {
        margin-bottom: 20px;
    }

    #reportMap {
        height: 180px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
        background: #eef2f8;
    }

    .location-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 14px;
    }

    .btn-outline-darkblue {
        color: #061b49;
        background: white;
        border: 1px solid #b9c5d9;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        padding: 11px 14px;
    }

    .btn-outline-darkblue:hover {
        color: white;
        background: #061b49;
        border-color: #061b49;
    }

    .btn-darkblue {
        background: #061b49;
        color: white;
        border: 1px solid #061b49;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        padding: 12px 20px;
    }

    .btn-darkblue:hover {
        background: #142b58;
        color: white;
    }

    .location-info {
        font-size: 12px;
        color: #586987;
    }

    .photo-upload {
        border: 1px dashed #b9c5d9;
        background: #f8f9fc;
        border-radius: 12px;
        padding: 16px;
    }

    .photo-preview-item {
        background: #f1f4f9;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        margin-top: 8px;
    }

    .report-note {
        color: #586987;
        font-size: 12px;
        line-height: 1.5;
    }

    .consent-text {
        font-size: 13px;
        color: #172441;
    }

    @media (max-width: 767px) {
        .page-heading h1 {
            font-size: 28px;
        }

        .step-progress {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .report-card .card-body {
            padding: 18px;
        }

        .location-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="container page-container">

    {{-- JUDUL HALAMAN --}}
    <div class="page-heading">
        <h1>Lapor {{ $categoryLabel }}</h1>

        <div class="breadcrumb-text">
            Lapor / {{ $categoryLabel }} / Informasi kejadian
        </div>
    </div>

    {{-- PROGRESS --}}
    <div class="step-progress">
        <div class="step-item active">1 &nbsp; Informasi laporan</div>
        <div class="step-item">2 &nbsp; Tinjau laporan</div>
        <div class="step-item">3 &nbsp; Kirim laporan</div>
    </div>

    {{-- NOTIFIKASI --}}
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 shadow-sm">
            <h6 class="fw-bold">{{ session('success') }}</h6>
            <p class="mb-2">Tracking Code laporan kamu:</p>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <strong class="fs-5" id="trackingCode">
                    {{ session('tracking_code') }}
                </strong>

                <button type="button"
                        class="btn btn-sm btn-outline-success"
                        onclick="copyTrackingCode()">
                    Salin kode
                </button>
            </div>

            <small class="d-block mt-2">
                Simpan kode ini untuk memantau perkembangan laporan.
            </small>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3">
            <strong>Periksa kembali data laporan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reports.store') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        <div class="row g-4">

            {{-- KOLOM KIRI --}}
            <div class="col-lg-7">
                <div class="card report-card">
                    <div class="card-body">

                        <h5 class="card-heading">
                            Ceritakan kondisi yang kamu temukan
                        </h5>

                        {{-- KATEGORI --}}
                        <div class="form-group">
                            <label class="form-label">
                                Kategori laporan <span class="required-mark">*</span>
                            </label>

                            <select name="category" class="form-select" required>
                                <option value="">Pilih kategori laporan</option>

                                @foreach ($categoryLabels as $key => $label)
                                    <option value="{{ $key }}"
                                        @selected($selectedCategory === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- KECAMATAN --}}
                        <div class="form-group">
                            <label class="form-label">
                                Kecamatan <span class="required-mark">*</span>
                            </label>

                            <select name="kecamatan" class="form-select" required>
                                <option value="">Pilih kecamatan</option>

                                @foreach ([
                                    'Medan Amplas',
                                    'Medan Area',
                                    'Medan Barat',
                                    'Medan Baru',
                                    'Medan Belawan',
                                    'Medan Deli',
                                    'Medan Denai',
                                    'Medan Helvetia',
                                    'Medan Johor',
                                    'Medan Kota',
                                    'Medan Labuhan',
                                    'Medan Maimun',
                                    'Medan Marelan',
                                    'Medan Perjuangan',
                                    'Medan Petisah',
                                    'Medan Polonia',
                                    'Medan Selayang',
                                    'Medan Sunggal',
                                    'Medan Tembung',
                                    'Medan Timur',
                                    'Medan Tuntungan',
                                ] as $kecamatan)
                                    <option value="{{ $kecamatan }}"
                                        @selected(old('kecamatan') === $kecamatan)>
                                        {{ $kecamatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- DESKRIPSI --}}
                        <div class="form-group">
                            <label class="form-label">
                                Deskripsi kejadian <span class="required-mark">*</span>
                            </label>

                            <textarea name="description"
                                      class="form-control"
                                      rows="5"
                                      maxlength="5000"
                                      placeholder="Jelaskan kondisi atau kejadian secara detail..."
                                      required>{{ old('description') }}</textarea>

                            <small class="text-secondary">
                                Maksimal 5.000 karakter.
                            </small>
                        </div>

                        {{-- WAKTU --}}
                        <div class="form-group">
                            <label class="form-label">Tanggal dan waktu kejadian</label>

                            <input type="datetime-local"
                                   name="incident_at"
                                   class="form-control"
                                   value="{{ old('incident_at') }}">

                            <small class="text-secondary">
                                Kosongkan jika tidak mengetahui waktu kejadian.
                            </small>
                        </div>

                        {{-- PERNYATAAN --}}
                        <div class="form-check mt-4">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="honestyCheck"
                                   required>

                            <label class="form-check-label consent-text"
                                   for="honestyCheck">
                                Saya melaporkan dengan jujur dan tidak menyertakan
                                data pribadi orang lain.
                            </label>
                        </div>

                        {{-- TOMBOL --}}
                        <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                            <a href="{{ route('reports.categories') }}"
                               class="btn btn-outline-darkblue flex-fill text-center">
                                Batal
                            </a>

                            <button type="submit"
                                    class="btn btn-darkblue flex-fill">
                                Kirim Laporan →
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN --}}
            <div class="col-lg-5">
                <div class="card report-card">
                    <div class="card-body">

                        <h5 class="card-heading">
                            Lokasi kejadian <span class="required-mark">*</span>
                        </h5>

                        {{-- NAMA LOKASI --}}
                        <div class="form-group">
                            <input type="text"
                                   name="location_name"
                                   class="form-control"
                                   value="{{ old('location_name') }}"
                                   maxlength="255"
                                   placeholder="Nama atau alamat lokasi kejadian">
                        </div>

                        {{-- PETA --}}
                        <div id="reportMap"></div>

                        {{-- TOMBOL LOKASI --}}
                        <div class="location-actions">
                            <button type="button"
                                    id="getLocation"
                                    class="btn btn-outline-darkblue">
                                ◎ Gunakan GPS
                            </button>

                            <button type="button"
                                    id="manualLocation"
                                    class="btn btn-outline-darkblue">
                                Pilih titik manual
                            </button>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <label class="form-label">Latitude</label>
                                <input type="number"
                                       step="any"
                                       name="latitude"
                                       id="latitude"
                                       class="form-control"
                                       value="{{ old('latitude') }}"
                                       required
                                       readonly>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Longitude</label>
                                <input type="number"
                                       step="any"
                                       name="longitude"
                                       id="longitude"
                                       class="form-control"
                                       value="{{ old('longitude') }}"
                                       required
                                       readonly>
                            </div>
                        </div>

                        <div class="location-info mt-2 mb-4">
                            <small id="locationMessage">
                                Titik berada di Kota Medan. Sesuaikan pin sebelum lanjut.
                            </small>
                        </div>

                        <hr class="my-4">

                        {{-- FOTO --}}
                        <div>
                            <label class="form-label">
                                Foto pendukung (opsional)
                            </label>

                            <div class="photo-upload">
                                <input type="file"
                                       name="photos[]"
                                       class="form-control"
                                       accept=".jpg,.jpeg,.png,.webp"
                                       multiple
                                       id="photos">

                                <div id="photoPreview" class="mt-2"></div>
                            </div>

                            <p class="report-note mt-3 mb-0">
                                JPG / PNG / WEBP · Maksimal 5 foto, 5 MB per foto.
                                <br>
                                Hanya unggah dokumentasi yang relevan, tanpa data pribadi.
                            </p>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="alert alert-warning border-0 rounded-3 small mt-4">
            <strong>Perhatikan keamanan dan privasi.</strong>
            Jangan sertakan identitas korban, informasi pribadi,
            atau foto yang dapat membahayakan keselamatan orang lain.
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    const map = L.map('reportMap').setView([3.5952, 98.6722], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationMessage = document.getElementById('locationMessage');

    function setLocation(lat, lng) {
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }

        locationMessage.textContent =
            'Lokasi dipilih: ' + lat.toFixed(7) + ', ' + lng.toFixed(7);
    }

    map.on('click', function (event) {
        setLocation(event.latlng.lat, event.latlng.lng);
    });

    document.getElementById('manualLocation').addEventListener('click', function () {
        locationMessage.textContent = 'Silakan klik titik lokasi pada peta.';
        map.getContainer().scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    });

    document.getElementById('getLocation').addEventListener('click', function () {
        if (!navigator.geolocation) {
            locationMessage.textContent = 'Browser tidak mendukung fitur GPS.';
            return;
        }

        locationMessage.textContent = 'Mengambil lokasi GPS...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                setLocation(lat, lng);
                map.setView([lat, lng], 16);
            },
            function (error) {
                locationMessage.textContent =
                    error.code === error.PERMISSION_DENIED
                        ? 'Izin lokasi ditolak. Aktifkan izin GPS browser.'
                        : 'Lokasi tidak dapat diakses. Silakan coba kembali atau pilih titik pada peta.';
            },
            {
                enableHighAccuracy: true,
                timeout: 10000
            }
        );
    });

    // Pulihkan koordinat jika validasi gagal.
    const oldLatitude = @json(old('latitude'));
    const oldLongitude = @json(old('longitude'));

    if (oldLatitude !== null && oldLongitude !== null) {
        const lat = Number(oldLatitude);
        const lng = Number(oldLongitude);

        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            setLocation(lat, lng);
            map.setView([lat, lng], 16);
        }
    }

    // Preview foto.
    document.getElementById('photos').addEventListener('change', function () {
        const preview = document.getElementById('photoPreview');
        preview.innerHTML = '';

        const files = Array.from(this.files);

        if (files.length > 5) {
            alert('Maksimal 5 foto.');
            this.value = '';
            return;
        }

        const maxSize = 5 * 1024 * 1024;
        const invalidFile = files.find(file => file.size > maxSize);

        if (invalidFile) {
            alert('Ukuran setiap foto maksimal 5 MB.');
            this.value = '';
            return;
        }

        files.forEach(file => {
            const item = document.createElement('div');
            item.className = 'photo-preview-item';

            item.textContent = file.name + ' (' +
                (file.size / 1024 / 1024).toFixed(2) + ' MB)';

            preview.appendChild(item);
        });
    });

    // Salin tracking code.
    function copyTrackingCode() {
        const code = document.getElementById('trackingCode')?.textContent.trim();

        if (!code) return;

        navigator.clipboard.writeText(code)
            .then(() => alert('Tracking Code berhasil disalin.'))
            .catch(() => alert('Gagal menyalin kode. Silakan salin secara manual.'));
    }

    setTimeout(() => map.invalidateSize(), 200);
</script>
@endpush
