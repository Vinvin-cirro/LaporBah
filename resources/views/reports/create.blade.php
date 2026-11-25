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

    /* PROGRESS */

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
        transition: .2s ease;
    }

    .step-item.active,
    .step-item.completed {
        background: #061b49;
        color: white;
    }

    /* CARD */

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

    /* MAP */

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

    .location-info {
        font-size: 12px;
        color: #586987;
    }

    /* BUTTONS */

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

    /* PHOTO */

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

    /* REVIEW */

    .review-title {
        font-size: 22px;
        font-weight: 700;
        color: #172441;
        margin-bottom: 16px;
    }

    .review-section {
        margin-bottom: 20px;
    }

    .review-information {
        color: #172441;
        font-size: 14px;
        line-height: 1.45;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .review-information div {
        margin-bottom: 2px;
    }

    .review-section h6 {
        font-size: 14px;
        font-weight: 700;
        color: #172441;
        margin-bottom: 8px;
    }

    .review-description {
        color: #172441;
        font-size: 14px;
        line-height: 1.5;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .review-photo-item {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 12px;
        padding: 0;
    }

    .review-photo-item img {
        width: 260px;
        height: 145px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #dfe5f1;
    }

    .review-photo-name {
        font-size: 13px;
        color: #172441;
        overflow-wrap: anywhere;
    }

    .review-photo-size {
        color: #586987;
        font-size: 12px;
    }

    .review-photo-empty {
        color: #586987;
        font-size: 13px;
        font-style: italic;
    }

    .review-note {
        color: #586987;
        font-size: 14px;
        line-height: 1.6;
    }

    .review-process {
        padding-left: 20px;
        color: #172441;
        font-size: 14px;
        line-height: 1.65;
    }

    .review-process li {
        padding-left: 0;
    }

    .review-card-right {
        display: flex;
        flex-direction: column;
    }

    .review-card-right .review-bottom {
        margin-top: auto;
    }

    /* CONFIRMATION MODAL */

    .confirmation-overlay {
        position: fixed;
        inset: 0;
        background: rgba(5, 17, 49, .48);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .confirmation-overlay.show {
        display: flex;
    }

    .confirmation-modal {
        background: white;
        width: 100%;
        max-width: 590px;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, .15);
        animation: modalAppear .2s ease;
    }

    @keyframes modalAppear {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .confirmation-modal h3 {
        color: #172441;
        font-size: 25px;
        font-weight: 700;
        margin-bottom: 24px;
    }

    .confirmation-modal p {
        color: #172441;
        font-size: 14px;
        line-height: 1.6;
    }

    .confirmation-summary {
        background: #f0f3f9;
        border-radius: 13px;
        padding: 18px;
        color: #172441;
        font-size: 14px;
        line-height: 1.5;
        margin: 24px 0;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .confirmation-note {
        color: #586987 !important;
        font-size: 13px !important;
        margin-bottom: 28px;
    }

    .confirmation-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    body.modal-open {
        overflow: hidden;
    }

    /* RESPONSIVE */

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

        .review-photo-item {
            flex-direction: column;
            align-items: flex-start;
        }

        .review-photo-item img {
            width: 100%;
            height: 190px;
        }

        .confirmation-modal {
            padding: 24px 20px;
        }

        .confirmation-modal h3 {
            font-size: 22px;
        }

        .confirmation-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush


@section('content')

<div class="container page-container">

    {{-- JUDUL HALAMAN --}}

    <div class="page-heading">
        <h1 id="pageTitle">Lapor {{ $categoryLabel }}</h1>

        <div class="breadcrumb-text" id="pageBreadcrumb">
            Lapor / {{ $categoryLabel }} / Informasi kejadian
        </div>
    </div>


    {{-- PROGRESS --}}

    <div class="step-progress">

        <div class="step-item active" id="progressStep1">
            1 &nbsp; Informasi laporan
        </div>

        <div class="step-item" id="progressStep2">
            2 &nbsp; Tinjau laporan
        </div>

        <div class="step-item" id="progressStep3">
            3 &nbsp; Kirim laporan
        </div>

    </div>


    {{-- FORM UTAMA --}}

    <form action="{{ route('reports.store') }}"
          method="POST"
          enctype="multipart/form-data"
          id="reportForm"
          novalidate>

        @csrf


        {{-- ================================================= --}}
        {{-- STEP 1 : INFORMASI LAPORAN --}}
        {{-- ================================================= --}}

        <div id="stepOne">

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
                                    Kategori laporan
                                    <span class="required-mark">*</span>
                                </label>

                                <select name="category"
                                        id="category"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Pilih kategori laporan
                                    </option>

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
                                    Kecamatan
                                    <span class="required-mark">*</span>
                                </label>

                                <select name="kecamatan"
                                        id="kecamatan"
                                        class="form-select"
                                        required>

                                    <option value="">
                                        Pilih kecamatan
                                    </option>

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
                                    Deskripsi kejadian
                                    <span class="required-mark">*</span>
                                </label>

                                <textarea name="description"
                                          id="description"
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

                                <label class="form-label">
                                    Tanggal dan waktu kejadian
                                </label>

                                <input type="datetime-local"
                                       name="incident_at"
                                       id="incident_at"
                                       class="form-control"
                                       value="{{ old('incident_at') }}">

                                <small class="text-secondary">
                                    Kosongkan jika tidak mengetahui waktu kejadian.
                                </small>

                            </div>


                        </div>

                    </div>

                </div>


                {{-- KOLOM KANAN --}}

                <div class="col-lg-5">

                    <div class="card report-card">

                        <div class="card-body">

                            <h5 class="card-heading">
                                Lokasi kejadian
                                <span class="required-mark">*</span>
                            </h5>


                            {{-- NAMA LOKASI --}}

                            <div class="form-group">

                                <input type="text"
                                       name="location_name"
                                       id="location_name"
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


                            {{-- KOORDINAT --}}

                            <div class="row mt-3">

                                <div class="col-6">

                                    <label class="form-label">
                                        Latitude
                                    </label>

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

                                    <label class="form-label">
                                        Longitude
                                    </label>

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
                                    Titik berada di Kota Medan.
                                    Sesuaikan pin sebelum lanjut.
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

                                    JPG / PNG / WEBP · Maksimal 5 foto,
                                    5 MB per foto.

                                    <br>

                                    Hanya unggah dokumentasi yang relevan,
                                    tanpa data pribadi.

                                </p>

                            </div>


                        </div>

                    </div>

                </div>

            </div>


            {{-- PERINGATAN PRIVASI --}}

            <div class="alert alert-warning border-0 rounded-3 small mt-4">

                <strong>Perhatikan keamanan dan privasi.</strong>

                Jangan sertakan identitas korban, informasi pribadi,
                atau foto yang dapat membahayakan keselamatan orang lain.

            </div>


            {{-- PERNYATAAN DAN TOMBOL --}}

            <div class="d-flex flex-column flex-sm-row gap-2 mt-4">

                <a href="{{ route('reports.categories') }}"
                   class="btn btn-outline-darkblue flex-fill text-center">

                    Batal

                </a>

                <button type="button"
                        id="reviewButton"
                        class="btn btn-darkblue flex-fill">

                    Tinjau laporan →

                </button>

            </div>

        </div>



        {{-- ================================================= --}}
        {{-- STEP 2 : TINJAU LAPORAN --}}
        {{-- ================================================= --}}

        <div id="stepTwo" style="display: none;">

            <div class="row g-4">


                {{-- KOLOM KIRI --}}

                <div class="col-lg-7">

                    <div class="card report-card">

                        <div class="card-body">

                            <h4 class="review-title">
                                <span id="reviewTitle">
                                    Laporan
                                </span>
                            </h4>


                            {{-- INFORMASI UTAMA --}}

                            <div class="review-section">

                                <div class="review-information">

                                    <div>
                                        Kategori:
                                        <span id="reviewCategory">-</span>
                                    </div>

                                    <div>
                                        Kecamatan:
                                        <span id="reviewKecamatan">-</span>
                                    </div>

                                    <div>
                                        Kejadian:
                                        <span id="reviewDate">-</span>
                                    </div>

                                    <div>
                                        Lokasi:
                                        <span id="reviewLocation">-</span>
                                    </div>

                                    <div>
                                        Koordinat:
                                        <span id="reviewCoordinates">-</span>
                                    </div>

                                </div>

                            </div>


                            {{-- DESKRIPSI --}}

                            <div class="review-section">

                                <h6>Deskripsi kejadian</h6>

                                <div class="review-description"
                                     id="reviewDescription">

                                </div>

                            </div>


                            {{-- FOTO --}}

                            <div class="review-section mb-0">

                                <h6>Foto pendukung</h6>

                                <div id="reviewPhotos">

                                    <div class="review-photo-empty">
                                        Belum ada foto pendukung.
                                    </div>

                                </div>

                                <p class="report-note mt-3 mb-0">
                                    Periksa apakah foto mengandung data
                                    pribadi sebelum mengirim.
                                </p>

                            </div>


                            {{-- UBAH INFORMASI --}}

                            <button type="button"
                                    id="editButton"
                                    class="btn btn-outline-darkblue w-100 mt-4 text-start">

                                Ubah informasi laporan

                            </button>


                        </div>

                    </div>

                </div>



                {{-- KOLOM KANAN --}}

                <div class="col-lg-5">

                    <div class="card report-card">

                        <div class="card-body review-card-right">

                            <div>

                                <h4 class="card-heading">
                                    Laporanmu akan ditinjau
                                </h4>


                                {{-- ALUR LAPORAN --}}

                                <ol class="review-process">

                                    <li>Laporan terkirim</li>

                                    <li>Menunggu verifikasi</li>

                                    <li>Ditinjau dan diteruskan</li>

                                    <li>Dalam penanganan</li>

                                    <li>Selesai jika hasil dikonfirmasi</li>

                                </ol>


                                {{-- PRIVASI --}}

                                <div class="mt-5">

                                    <h6 class="fw-bold mb-3"
                                        style="font-size: 14px; color: #172441;">

                                        Informasi pribadi

                                    </h6>

                                    <p class="review-note mb-0">

                                        Identitas pelapor tidak ditampilkan
                                        kepada publik. Jangan cantumkan nama
                                        korban atau data pribadi di deskripsi
                                        maupun foto.

                                    </p>

                                </div>

                            </div>


                            {{-- PERNYATAAN DAN KIRIM --}}

                            <div class="review-bottom mt-5">

                                <div class="form-check mb-4">

                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="honestyCheck"
                                           required>

                                    <label class="form-check-label consent-text"
                                           for="honestyCheck">

                                        Saya menyatakan laporan ini dibuat
                                        dengan jujur dan tidak melanggar
                                        privasi orang lain.

                                    </label>

                                </div>


                                <button type="button"
                                        id="sendButton"
                                        class="btn btn-darkblue w-100">

                                    Kirim laporan

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- PERINGATAN --}}

            <div class="small mt-4"
                 style="color: #586987;">

                LaporBah! bukan layanan darurat.
                Mengirim laporan tidak menjamin respons langsung
                atau tindak lanjut.

            </div>

        </div>

    </form>

</div>



{{-- ================================================= --}}
{{-- MODAL KONFIRMASI --}}
{{-- ================================================= --}}

<div class="confirmation-overlay"
     id="confirmationOverlay">

    <div class="confirmation-modal"
         role="dialog"
         aria-modal="true"
         aria-labelledby="confirmationTitle">

        <h3 id="confirmationTitle">
            Kirim laporan ini?
        </h3>


        <p>
            Pastikan lokasi dan informasi kejadian sudah benar.
            Laporan baru akan berstatus Menunggu Verifikasi.
        </p>


        {{-- RINGKASAN MODAL --}}

        <div class="confirmation-summary"
             id="confirmationSummary">

            Informasi laporan akan ditampilkan di sini.

        </div>


        <p class="confirmation-note">

            Laporan tidak langsung dianggap terverifikasi.

            <br>

            Tidak ada biaya untuk mengirim laporan.

        </p>


        {{-- TOMBOL MODAL --}}

        <div class="confirmation-actions">

            <button type="button"
                    id="cancelConfirmation"
                    class="btn btn-outline-darkblue">

                Periksa lagi

            </button>

            <button type="button"
                    id="confirmSubmit"
                    class="btn btn-darkblue">

                Ya, kirim laporan

            </button>

        </div>

    </div>

</div>

@endsection



@push('scripts')

<script>

    // =====================================================
    // ELEMENT
    // =====================================================

    const reportForm = document.getElementById('reportForm');

    const stepOne = document.getElementById('stepOne');
    const stepTwo = document.getElementById('stepTwo');

    const progressStep1 = document.getElementById('progressStep1');
    const progressStep2 = document.getElementById('progressStep2');
    const progressStep3 = document.getElementById('progressStep3');

    const pageTitle = document.getElementById('pageTitle');
    const pageBreadcrumb = document.getElementById('pageBreadcrumb');

    const overlay = document.getElementById('confirmationOverlay');


    // =====================================================
    // STEP NAVIGATION
    // =====================================================

    function showStep(step) {

        if (step === 1) {

            stepOne.style.display = 'block';
            stepTwo.style.display = 'none';

            pageTitle.textContent = 'Lapor {{ $categoryLabel }}';

            pageBreadcrumb.textContent =
                'Lapor / {{ $categoryLabel }} / Informasi kejadian';

            progressStep1.className = 'step-item active';
            progressStep2.className = 'step-item';
            progressStep3.className = 'step-item';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            // Leaflet perlu menyesuaikan ukuran setelah panel ditampilkan.

            setTimeout(() => {
                map.invalidateSize();
            }, 250);

        } else {

            stepOne.style.display = 'none';
            stepTwo.style.display = 'block';

            pageTitle.textContent = 'Tinjau laporan';

            pageBreadcrumb.textContent =
                'Lapor / Tinjau laporan';

            progressStep1.className = 'step-item completed';
            progressStep2.className = 'step-item active';
            progressStep3.className = 'step-item';

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        }

    }


    // =====================================================
    // VALIDASI STEP 1
    // =====================================================

    function validateStepOne() {

        const requiredFields = stepOne.querySelectorAll('[required]');

        for (const field of requiredFields) {

            if (!field.checkValidity()) {

                showStep(1);

                setTimeout(() => {

                    field.reportValidity();

                    field.focus({
                        preventScroll: false
                    });

                }, 300);

                return false;

            }

        }

        return true;

    }


    // =====================================================
    // FORMAT TANGGAL
    // =====================================================

    function formatIncidentDate(value) {

        if (!value) {
            return 'Tidak diketahui';
        }

        const date = new Date(value);

        if (isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23'
        }).format(date) + ' WIB';

    }


    // =====================================================
    // UPDATE REVIEW
    // =====================================================

    function updateReview() {

        const category = document.getElementById('category');

        const categoryText =
            category.options[category.selectedIndex]?.text || '-';

        const kecamatan =
            document.getElementById('kecamatan').value || '-';

        const description =
            document.getElementById('description').value.trim();

        const incidentDate =
            document.getElementById('incident_at').value;

        const location =
            document.getElementById('location_name').value.trim()
            || 'Tidak dicantumkan';

        const latitude =
            document.getElementById('latitude').value;

        const longitude =
            document.getElementById('longitude').value;


        document.getElementById('reviewTitle').textContent =
            categoryText;

        document.getElementById('reviewCategory').textContent =
            categoryText;

        document.getElementById('reviewKecamatan').textContent =
            kecamatan;

        document.getElementById('reviewDate').textContent =
            formatIncidentDate(incidentDate);

        document.getElementById('reviewLocation').textContent =
            location;

        document.getElementById('reviewCoordinates').textContent =
            latitude + ', ' + longitude;

        document.getElementById('reviewDescription').textContent =
            description;


        // FOTO PREVIEW

        const reviewPhotos = document.getElementById('reviewPhotos');

        reviewPhotos.innerHTML = '';

        const files = Array.from(
            document.getElementById('photos').files
        );

        if (files.length === 0) {

            const empty = document.createElement('div');

            empty.className = 'review-photo-empty';

            empty.textContent = 'Belum ada foto pendukung.';

            reviewPhotos.appendChild(empty);

        } else {

            files.forEach(file => {

                const item = document.createElement('div');

                item.className = 'review-photo-item';

                const image = document.createElement('img');

                image.src = URL.createObjectURL(file);

                image.alt = file.name;

                const information = document.createElement('div');

                const name = document.createElement('div');

                name.className = 'review-photo-name';

                name.textContent = file.name;

                const size = document.createElement('div');

                size.className = 'review-photo-size';

                size.textContent =
                    (file.size / 1024 / 1024).toFixed(2) + ' MB';

                information.appendChild(name);

                information.appendChild(size);

                item.appendChild(image);

                item.appendChild(information);

                reviewPhotos.appendChild(item);

            });

        }


        // RINGKASAN MODAL

        const summary = [
            categoryText,
            location,
            formatIncidentDate(incidentDate),
            files.length + ' foto pendukung'
        ];

        document.getElementById('confirmationSummary').textContent =
            summary.join('\n');

    }


    // =====================================================
    // TOMBOL TINJAU
    // =====================================================

    document.getElementById('reviewButton')
        .addEventListener('click', function () {

            if (!validateStepOne()) {
                return;
            }

            updateReview();

            showStep(2);

        });


    // =====================================================
    // TOMBOL UBAH INFORMASI
    // =====================================================

    document.getElementById('editButton')
        .addEventListener('click', function () {

            showStep(1);

        });


    // =====================================================
    // MODAL KONFIRMASI
    // =====================================================

    function openConfirmation() {

        if (!document.getElementById('honestyCheck').checked) {

            document.getElementById('honestyCheck').reportValidity();

            return;

        }

        updateReview();

        overlay.classList.add('show');

        document.body.classList.add('modal-open');

        document.getElementById('cancelConfirmation').focus();

    }


    function closeConfirmation() {

        overlay.classList.remove('show');

        document.body.classList.remove('modal-open');

    }


    document.getElementById('sendButton')
        .addEventListener('click', openConfirmation);


    document.getElementById('cancelConfirmation')
        .addEventListener('click', closeConfirmation);


    // Klik area gelap untuk menutup modal.

    overlay.addEventListener('click', function (event) {

        if (event.target === overlay) {
            closeConfirmation();
        }

    });


    // Tutup modal dengan tombol Escape.

    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            overlay.classList.contains('show')
        ) {

            closeConfirmation();

        }

    });


    // =====================================================
    // KONFIRMASI PENGIRIMAN
    // =====================================================

    document.getElementById('confirmSubmit')
        .addEventListener('click', function () {

            // Pastikan data tahap pertama masih valid.

            if (!validateStepOne()) {

                closeConfirmation();

                return;

            }

            // Pastikan persetujuan privasi dicentang.

            const honestyCheck =
                document.getElementById('honestyCheck');

            if (!honestyCheck.checked) {

                closeConfirmation();

                showStep(2);

                honestyCheck.reportValidity();

                return;

            }

            // Ubah progress ke tahap ketiga.

            progressStep1.className = 'step-item completed';

            progressStep2.className = 'step-item completed';

            progressStep3.className = 'step-item active';

            // Cegah pengiriman ganda.

            this.disabled = true;

            this.textContent = 'Mengirim laporan...';

            // Tutup modal.

            closeConfirmation();

            // Kirim data ke Laravel.

            reportForm.submit();

        });


    // =====================================================
    // LEAFLET MAP
    // =====================================================

    const map = L.map('reportMap').setView(
        [3.5952, 98.6722],
        12
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(map);


    let marker = null;

    const latitudeInput =
        document.getElementById('latitude');

    const longitudeInput =
        document.getElementById('longitude');

    const locationMessage =
        document.getElementById('locationMessage');


    function setLocation(lat, lng) {

        latitudeInput.value = lat.toFixed(7);

        longitudeInput.value = lng.toFixed(7);

        if (marker) {

            marker.setLatLng([lat, lng]);

        } else {

            marker = L.marker([lat, lng]).addTo(map);

        }

        locationMessage.textContent =
            'Lokasi dipilih: ' +
            lat.toFixed(7) + ', ' +
            lng.toFixed(7);

    }


    // Pilih lokasi manual.

    map.on('click', function (event) {

        setLocation(
            event.latlng.lat,
            event.latlng.lng
        );

    });


    document.getElementById('manualLocation')
        .addEventListener('click', function () {

            locationMessage.textContent =
                'Silakan klik titik lokasi pada peta.';

            map.getContainer().scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

        });


    // Gunakan GPS.

    document.getElementById('getLocation')
        .addEventListener('click', function () {

            if (!navigator.geolocation) {

                locationMessage.textContent =
                    'Browser tidak mendukung fitur GPS.';

                return;

            }

            locationMessage.textContent =
                'Mengambil lokasi GPS...';

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


    // =====================================================
    // PULIHKAN KOORDINAT OLD INPUT
    // =====================================================

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


    // =====================================================
    // PREVIEW FOTO
    // =====================================================

    document.getElementById('photos')
        .addEventListener('change', function () {

            const preview =
                document.getElementById('photoPreview');

            preview.innerHTML = '';

            const files = Array.from(this.files);


            if (files.length > 5) {

                alert('Maksimal 5 foto.');

                this.value = '';

                return;

            }


            const maxSize = 5 * 1024 * 1024;

            const invalidFile = files.find(
                file => file.size > maxSize
            );


            if (invalidFile) {

                alert('Ukuran setiap foto maksimal 5 MB.');

                this.value = '';

                return;

            }


            files.forEach(file => {

                const item = document.createElement('div');

                item.className = 'photo-preview-item';

                item.textContent =

                    file.name + ' (' +
                    (file.size / 1024 / 1024).toFixed(2) +
                    ' MB)';

                preview.appendChild(item);

            });

        });


    // =====================================================
    // SALIN TRACKING CODE
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

                alert(
                    'Gagal menyalin kode. Silakan salin secara manual.'
                );

            });

    }


    // =====================================================
    // INITIALIZE
    // =====================================================

    setTimeout(() => {

        map.invalidateSize();

    }, 200);

</script>

@endpush