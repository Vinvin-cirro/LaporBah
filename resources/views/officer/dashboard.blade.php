@extends('layouts.officer')

@section('title', 'Dashboard Petugas')
@section('breadcrumb', 'Dashboard')

@section('content')

{{-- WELCOME --}}
<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="page-title mb-2">
                Selamat datang, {{ auth()->user()->name }}!
            </h1>
            <p class="muted mb-0">
                Berikut ringkasan penugasan dan aktivitas lapangan Anda.
            </p>
        </div>

        <a href="{{ route('officer.reports.index') }}"
           class="btn btn-navy">
            <i class="bi bi-clipboard2-check me-2"></i>
            Lihat Semua Tugas
        </a>
    </div>
</div>

{{-- SUMMARY CARDS --}}
<div class="row g-3 mb-4">

    <div class="col-12 col-md-6 col-xl-4">
        <div class="panel h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="muted small mb-2">Tugas Aktif</div>
                    <div class="fs-2 fw-bold">{{ $tasks->count() }}</div>
                    <div class="small muted mt-1">Penugasan yang tersedia</div>
                </div>
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-clipboard2-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="panel h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="muted small mb-2">Tugas Terbaru</div>
                    <div class="fs-2 fw-bold">{{ $tasks->take(3)->count() }}</div>
                    <div class="small muted mt-1">Penugasan terakhir</div>
                </div>
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-4">
        <div class="panel h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="muted small mb-2">Tenggat Terdekat</div>

                    <div class="fs-4 fw-bold">
                        {{ $deadline?->due_at?->format('d M Y') ?? 'Tidak ada' }}
                    </div>

                    <div class="small muted mt-1">
                        {{ $deadline?->due_at?->format('H:i') 
                            ? 'Pukul '.$deadline->due_at->format('H:i').' WIB'
                            : 'Tidak ada tenggat aktif' }}
                    </div>
                </div>

                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-calendar-event fs-4"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row g-4">

    {{-- LEFT CONTENT --}}
    <div class="col-12 col-xl-8">

        {{-- ACTIVE TASKS --}}
        <section class="panel mb-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h2 class="mb-1">Tugas yang Perlu Dikerjakan</h2>
                    <p class="muted small mb-0">
                        Prioritaskan penugasan yang menjadi tanggung jawab Anda.
                    </p>
                </div>

                <a href="{{ route('officer.reports.index') }}"
                   class="small text-decoration-none fw-semibold">
                    Lihat semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            @forelse($tasks as $assignment)
                @include('officer.tasks.partials.card', [
                    'assignment' => $assignment
                ])
            @empty

                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-clipboard-check display-4 text-secondary"></i>
                    </div>

                    <h3 class="fs-5 fw-bold">Belum Ada Penugasan</h3>

                    <p class="muted small mb-0">
                        Penugasan baru akan muncul di sini setelah diberikan oleh Pemerintah.
                    </p>
                </div>

            @endforelse

        </section>

    </div>

    {{-- RIGHT SIDEBAR --}}
    <div class="col-12 col-xl-4">

        {{-- DEADLINE --}}
        <section class="panel mb-4">

            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-alarm text-warning fs-5"></i>
                <h2 class="mb-0">Tenggat Terdekat</h2>
            </div>

            @if($deadline)

                <div class="rounded-3 bg-warning bg-opacity-10 p-3 mb-3">
                    <div class="small muted mb-1">Batas waktu penyelesaian</div>

                    <div class="fs-3 fw-bold">
                        {{ $deadline->due_at->format('d M Y') }}
                    </div>

                    <div class="small mt-1">
                        <i class="bi bi-clock me-1"></i>
                        {{ $deadline->due_at->format('H:i') }} WIB
                    </div>
                </div>

                <div class="fw-semibold mb-1">
                    {{ $deadline->report->tracking_code ?? 'TGS-'.$deadline->id }}
                </div>

                <div class="small muted mb-3">
                    <i class="bi bi-geo-alt me-1"></i>
                    {{ $deadline->report->location_name }}
                </div>

                <a href="{{ route('officer.reports.show', $deadline->report_id) }}"
                   class="btn btn-navy w-100">
                    Buka Penugasan
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>

            @else

                <div class="text-center py-4">
                    <i class="bi bi-calendar-check display-5 text-secondary"></i>
                    <p class="muted small mt-3 mb-0">
                        Tidak ada tenggat aktif yang tercatat.
                    </p>
                </div>

            @endif

        </section>

        {{-- RECENT ACTIVITY --}}
        <section class="panel">

            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-activity text-primary fs-5"></i>
                <h2 class="mb-0">Aktivitas Terakhir</h2>
            </div>

            @forelse($recent as $item)

                <div class="d-flex gap-3 pb-4 {{ !$loop->last ? 'border-bottom mb-3' : '' }}">

                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                             style="width: 38px; height: 38px;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>

                    <div class="flex-grow-1">
                        <div class="fw-semibold text-capitalize">
                            {{ str_replace('_', ' ', $item['event']) }}
                        </div>

                        <div class="small muted mt-1">
                            {{ $item['report']?->tracking_code ?? '-' }}
                        </div>

                        <div class="small muted mt-1">
                            {{ $item['created_at']->format('d M Y, H:i') }}
                        </div>

                        @if($item['note'])
                            <div class="small mt-2">
                                {{ $item['note'] }}
                            </div>
                        @endif
                    </div>

                </div>

            @empty

                <div class="text-center py-4">
                    <i class="bi bi-clock-history display-5 text-secondary"></i>
                    <p class="muted small mt-3 mb-0">
                        Aktivitas akan muncul setelah Anda melakukan pembaruan tugas.
                    </p>
                </div>

            @endforelse

        </section>

    </div>

</div>

@endsection