
@extends('layouts.citizen-report')

@section('title', 'Detail Profil')


@section('content')
@php
    $user = auth()->user();

    $profilePhoto = $user->profile_photo
        ? asset('storage/' . $user->profile_photo)
        : asset('images/default-profile.png');

    $kecamatanList = [
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
    ];
@endphp

<div class="container py-4 py-md-5 profile-page">

    <div class="mb-4">
        <h3 class="fw-bold mb-1">Detail Profil</h3>
        <p class="text-muted mb-0">
            Kelola informasi pribadi akun LaporBah kamu.
        </p>
    </div>

    {{-- ALERT --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-3 border-0 shadow-sm">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- ACCOUNT PROFILE: KANAN --}}
        <div class="col-lg-5 order-2 order-lg-1">

            <div class="card profile-card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 text-center">
                    <h5 class="fw-bold mb-1 text-start">Akun Warga</h5>

                    <img
                        src="{{ $profilePhoto }}"
                        alt="Foto Profil"
                        class="account-avatar mb-3"
                    >

                    <h5 class="fw-bold mb-1">{{ $user->name }}</h5>

                    <p class="text-muted small mb-3">
                        {{ $user->email }}
                    </p>

                    <div class="profile-location mb-3">
                        {{ $user->kecamatan ?: 'Kecamatan belum diatur' }}
                    </div>

                    <hr>

                    <div class="text-start profile-detail">
                        <div class="detail-row">
                            <span>Tanggal Bergabung</span>
                            <strong>{{ $user->created_at->format('d M Y') }}</strong>
                        </div>

                        <div class="detail-row">
                            <span>Laporan Terkirim</span>
                            <strong>{{ $user->reports_count }}</strong>
                        </div>
                    </div>

                    {{-- PROFILE MENU BUTTONS --}}
                    <div class="profile-menu-buttons mt-4">

                        <a href="#" class="profile-menu-btn btn-primary-dark">
                            <span>Aktivitas Saya</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>

                        <a href="#" class="profile-menu-btn btn-outline-dark">
                            <span>Notifikasi</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>

                        <a href="#informasi-profil" class="profile-menu-btn btn-outline-dark">
                            <span>Pengaturan</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>

                    </div>

                </div>
            </div>

        </div>
        
        {{-- INFORMASI PROFIL: KIRI --}}

        <div class="col-lg-7 order-1 order-lg-2">

            <div class="card profile-card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-md-5">

                    <div class="mb-4">
                        <h5 class="fw-bold mb-1">Informasi Profil</h5>
                        <p class="text-muted small mb-0">
                            Perbarui informasi akun kamu di bawah ini.
                        </p>
                    </div>

                    <form action="{{ route('citizen.profile.update') }}"
                          method="POST"
                          enctype="multipart/form-data">

                        @csrf
                        @method('PUT')

                        {{-- FOTO PROFIL --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Foto Profil
                            </label>

                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <img
                                    src="{{ $profilePhoto }}"
                                    alt="Foto Profil"
                                    class="form-avatar"
                                    id="profilePreview"
                                >

                                <div class="flex-grow-1">
                                    <input
                                        type="file"
                                        name="profile_photo"
                                        id="profilePhotoInput"
                                        class="form-control"
                                        accept=".jpg,.jpeg,.png,.webp"
                                    >

                                    <small class="text-muted d-block mt-2">
                                        JPG, PNG, atau WEBP. Maksimal 2 MB.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- NAMA --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Nama Tampilan
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control profile-input"
                                value="{{ old('name', $user->name) }}"
                                required
                            >
                        </div>

                        {{-- EMAIL --}}
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Email Akun
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control profile-input"
                                value="{{ old('email', $user->email) }}"
                                required
                            >

                            <small class="text-muted d-block mt-2">
                                Email hanya digunakan untuk keperluan akun
                                dan pembaruan informasi pribadi.
                            </small>
                        </div>

                        {{-- KECAMATAN --}}
                        <div class="mb-4">
                            <label for="kecamatan" class="form-label fw-semibold">
                                Kecamatan
                            </label>

                            <select
                                name="kecamatan"
                                id="kecamatan"
                                class="form-select profile-input"
                            >
                                <option value="">Pilih kecamatan</option>

                                @foreach ($kecamatanList as $kecamatan)
                                    <option
                                        value="{{ $kecamatan }}"
                                        @selected(old('kecamatan', $user->kecamatan) === $kecamatan)
                                    >
                                        {{ $kecamatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-profile-save">
                                Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>

    </div>

    {{-- PRIVACY CARD --}}
    <div class="privacy-card rounded-4 p-4 p-md-5 mt-4">

        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">

            <div class="flex-grow-1">
                <h5 class="fw-bold mb-2">Privasi identitasmu</h5>

                <p class="mb-0 privacy-description">
                    LaporBah berkomitmen menjaga informasi pribadi pengguna.
                    Informasi akun digunakan untuk mendukung layanan pelaporan
                    dan tidak ditampilkan sebagai informasi publik pada laporan.
                </p>
            </div>

            <button type="button"
                    class="btn btn-privacy"
                    disabled>
                Kelola Privasi &amp; Data
            </button>

        </div>
    </div>

</div>
@endsection



@push('styles')
<style>

    .profile-menu-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .profile-menu-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 12px 16px;
        border-radius: 9px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: all .2s ease;
    }

    .btn-primary-dark {
        background: #10234d;
        border: 1px solid #10234d;
        color: #fff;
    }

    .btn-primary-dark:hover {
        background: #1b356c;
        border-color: #1b356c;
        color: #fff;
    }

    .btn-outline-dark {
        background: transparent;
        border: 1px solid #10234d;
        color: #10234d;
    }

    .btn-outline-dark:hover {
        background: #f1f5f9;
        color: #10234d;
    }

    .profile-menu-btn i {
        font-size: 16px;
    }

    .profile-page {
        max-width: 1100px;
    }

    .profile-card {
        background: #fff;
        border: 1px solid #edf0f4 !important;
    }

    .account-avatar {
        width: 115px;
        height: 115px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f1f3f6;
    }

    .form-avatar {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
    }

    .profile-location {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 12px;
    }

    .profile-detail {
        font-size: 13px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f3f6;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-row span {
        color: #64748b;
    }

    .detail-row strong {
        color: #10234d;
        text-align: right;
    }

    .profile-input {
        min-height: 46px;
        border-radius: 9px;
        border-color: #dfe5ed;
        font-size: 14px;
    }

    .profile-input:focus {
        border-color: #d7a32a;
        box-shadow: 0 0 0 3px rgba(215, 163, 42, .12);
    }

/* CARD INFORMASI PROFIL */
#informasi-profil .card-body {
    padding: 24px !important;
}

#informasi-profil .mb-4 {
    margin-bottom: 16px !important;
}

#informasi-profil .mb-3 {
    margin-bottom: 12px !important;
}

/* JUDUL SECTION */
#informasi-profil h5 {
    font-size: 17px;
}

#informasi-profil .form-label {
    font-size: 13px;
    margin-bottom: 5px;
}

/* INPUT */
.profile-input {
    min-height: 39px;
    padding: 7px 11px;
    border-radius: 8px;
    border-color: #dfe5ed;
    font-size: 13px;
}

/* FOTO PROFIL */
.form-avatar {
    width: 65px;
    height: 65px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

    /* KETERANGAN EMAIL DAN FOTO */
    #informasi-profil small {
        font-size: 11px;
        line-height: 1.4;
    }

    /* TOMBOL SIMPAN */
    .btn-profile-save {
        padding: 9px 18px;
        font-size: 12px;
    }


    .btn-profile-save {
        background: #10234d;
        color: #fff;
        border-radius: 9px;
        padding: 11px 22px;
        font-weight: 600;
        font-size: 13px;
    }

    .btn-profile-save:hover {
        background: #1b356c;
        color: #fff;
    }

    .privacy-card {
        background: #fff8e6;
        border: 1px solid #f6e8bd;
    }

    .privacy-description {
        color: #786b4c;
        font-size: 13px;
        line-height: 1.7;
    }

    .btn-privacy {
        flex-shrink: 0;
        border: 1px solid #e6d39e;
        background: #fff;
        color: #8a681a;
        border-radius: 9px;
        padding: 10px 15px;
        font-size: 12px;
        font-weight: 600;
        opacity: .65;
        cursor: not-allowed;
    }

    @media (max-width: 991px) {
        .profile-detail {
            max-width: 450px;
            margin: auto;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const photoInput = document.getElementById('profilePhotoInput');
    const photoPreview = document.getElementById('profilePreview');

    if (photoInput && photoPreview) {
        photoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (file) {
                photoPreview.src = URL.createObjectURL(file);
            }
        });
    }
</script>
@endpush

