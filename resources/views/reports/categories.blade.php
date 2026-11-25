
@extends('layouts.citizen-report')

@section('title', 'Pilih Kategori Laporan')

@section('content')
<div class="categories-page">
    <div class="container">

        {{-- Page Heading --}}
        <div class="page-heading">
            <h1>Pilih kategori laporan</h1>
            <p>Apa yang ingin kamu laporkan?</p>
        </div>

        {{-- Category Cards --}}
        <div class="row g-4">

            {{-- Begal / Curas --}}
            <div class="col-12 col-md-6">
                <div class="category-card">
                    <div class="category-title">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/>
                                <path d="M12 8v4"/>
                                <path d="M12 16h.01"/>
                            </svg>
                        </div>
                        <h2>CURAS / begal</h2>
                    </div>

                    <p class="category-description">
                        Laporkan pencurian dengan kekerasan atau ancaman
                        di ruang publik. Utamakan keselamatan diri.
                    </p>

                    <a href="{{ route('reports.create', ['category' => 'begal_curas']) }}"
                       class="category-button">
                        Pilih kategori <span>→</span>
                    </a>
                </div>
            </div>

            {{-- PJU Rusak --}}
            <div class="col-12 col-md-6">
                <div class="category-card">
                    <div class="category-title">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18h6"/>
                                <path d="M10 22h4"/>
                                <path d="M8.5 14.5A7 7 0 1 1 15.5 14.5c-.8.6-1.2 1.4-1.4 2.5h-4.2c-.2-1.1-.6-1.9-1.4-2.5z"/>
                                <path d="M3 3l18 18"/>
                            </svg>
                        </div>
                        <h2>PJU rusak</h2>
                    </div>

                    <p class="category-description">
                        Laporkan lampu jalan padam, berkedip, atau kondisi
                        tiang yang bermasalah.
                    </p>

                    <a href="{{ route('reports.create', ['category' => 'pju_rusak']) }}"
                       class="category-button">
                        Pilih kategori <span>→</span>
                    </a>
                </div>
            </div>

            {{-- Area Gelap --}}
            <div class="col-12 col-md-6">
                <div class="category-card">
                    <div class="category-title">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13z"/>
                            </svg>
                        </div>
                        <h2>Area gelap / minim penerangan</h2>
                    </div>

                    <p class="category-description">
                        Bantu tandai ruas jalan atau ruang publik yang
                        kurang penerangan.
                    </p>

                    <a href="{{ route('reports.create', ['category' => 'area_gelap']) }}"
                       class="category-button">
                        Pilih kategori <span>→</span>
                    </a>
                </div>
            </div>

            {{-- Lain-lain --}}
            <div class="col-12 col-md-6">
                <div class="category-card">
                    <div class="category-title">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                        </div>
                        <h2>Masalah keamanan lainnya</h2>
                    </div>

                    <p class="category-description">
                        Sampaikan masalah keselamatan lingkungan yang
                        belum tercakup kategori lain.
                    </p>

                    <a href="{{ route('reports.create', ['category' => 'lain_lain']) }}"
                       class="category-button">
                        Pilih kategori <span>→</span>
                    </a>
                </div>
            </div>

        </div>

        {{-- Information --}}
        <div class="category-note">
            Semua laporan baru berstatus Menunggu Verifikasi.
            Kamu dapat melihat perkembangan laporan melalui Aktivitas Saya.
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .categories-page {
        padding: 28px 0 38px;
        min-height: 75vh;
    }

    .page-heading {
        margin-bottom: 26px;
    }

    .page-heading h1 {
        color: #172441;
        font-size: 38px;
        font-weight: 750;
        margin-bottom: 7px;
        letter-spacing: -1px;
    }

    .page-heading p {
        color: #586987;
        font-size: 15px;
        margin: 0;
    }

    .category-card {
        height: 100%;
        min-height: 205px;
        background: #fff;
        border: 1px solid #dfe5f1;
        border-radius: 15px;
        padding: 22px 23px 17px;
        display: flex;
        flex-direction: column;
        transition: .2s ease;
    }

    .category-card:hover {
        border-color: #aebbd3;
        box-shadow: 0 7px 22px rgba(2, 13, 50, .06);
        transform: translateY(-2px);
    }

    .category-title {
        display: flex;
        align-items: center;
        gap: 17px;
        margin-bottom: 22px;
    }

    .category-icon {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        color: #09265f;
    }

    .category-icon svg {
        width: 100%;
        height: 100%;
    }

    .category-title h2 {
        color: #172441;
        font-size: 21px;
        font-weight: 650;
        margin: 0;
    }

    .category-description {
        color: #586987;
        font-size: 14.5px;
        line-height: 1.55;
        margin-bottom: 21px;
        flex-grow: 1;
    }

    .category-button {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 5px;
        width: 100%;
        min-height: 46px;
        padding: 10px 12px;
        border: 1px solid #aebbd3;
        border-radius: 11px;
        color: #172441;
        background: #fff;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: .2s ease;
    }

    .category-button:hover {
        color: #fff;
        background: #020d32;
        border-color: #020d32;
    }

    .category-button span {
        font-size: 17px;
        transition: transform .2s ease;
    }

    .category-button:hover span {
        transform: translateX(4px);
    }

    .category-note {
        margin-top: 25px;
        color: #586987;
        font-size: 13.5px;
        line-height: 1.6;
    }

    @media (max-width: 767px) {
        .categories-page {
            padding: 25px 0 35px;
        }

        .page-heading h1 {
            font-size: 29px;
        }

        .category-card {
            min-height: 190px;
            padding: 19px;
        }

        .category-title h2 {
            font-size: 18px;
        }
    }
</style>
@endpush
