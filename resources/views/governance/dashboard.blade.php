
@extends('layouts.governance')

@section('content')

<div class="container-fluid py-4">

    {{-- PAGE HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Dashboard Pemerintah</h4>
            <p class="text-muted mb-0">
                Pantau laporan masyarakat dan aktivitas penanganan wilayah.
            </p>
        </div>

        <div class="text-muted small mt-2 mt-md-0">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    {{-- STATISTICS --}}
    <div class="row g-3 mb-4">

        @php
            $statCards = [
                [
                    'title' => 'Total Laporan',
                    'value' => $statistics['total_reports'],
                    'icon' => 'bi-file-earmark-text',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Menunggu Verifikasi',
                    'value' => $statistics['pending_reports'],
                    'icon' => 'bi-hourglass-split',
                    'color' => 'warning',
                ],
                [
                    'title' => 'Sedang Ditangani',
                    'value' => $statistics['investigating_reports'],
                    'icon' => 'bi-shield-check',
                    'color' => 'info',
                ],
                [
                    'title' => 'Selesai',
                    'value' => $statistics['resolved_reports'],
                    'icon' => 'bi-check-circle',
                    'color' => 'success',
                ],
            ];
        @endphp

        @foreach ($statCards as $card)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-2">
                                {{ $card['title'] }}
                            </p>
                            <h3 class="fw-bold mb-0">
                                {{ number_format($card['value']) }}
                            </h3>
                        </div>

                        <div class="rounded-3 bg-{{ $card['color'] }} bg-opacity-10
                                    text-{{ $card['color'] }} p-3">
                            <i class="bi {{ $card['icon'] }} fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- OPERATIONAL SUMMARY --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Total Petugas</p>
                            <h5 class="fw-bold mb-0">
                                {{ number_format($statistics['total_officers']) }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-info bg-opacity-10 text-info p-3">
                            <i class="bi bi-person-check fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Penugasan Aktif</p>
                            <h5 class="fw-bold mb-0">
                                {{ number_format($statistics['active_assignments']) }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3">
                            <i class="bi bi-geo-alt fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Total Area Terdata</p>
                            <h5 class="fw-bold mb-0">
                                {{ number_format($statistics['total_areas']) }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- CHARTS --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">Tren Laporan</h6>
                            <p class="text-muted small mb-0">
                                Jumlah laporan dalam 7 hari terakhir
                            </p>
                        </div>

                        <span class="badge bg-light text-dark">
                            7 Hari Terakhir
                        </span>
                    </div>

                    <div style="height: 280px;">
                        <canvas id="reportTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">Distribusi Kategori</h6>
                    <p class="text-muted small mb-3">
                        Perbandingan jenis laporan masyarakat
                    </p>

                    <div style="height: 220px;">
                        <canvas id="categoryChart"></canvas>
                    </div>

                    <div class="d-flex justify-content-around mt-3">
                        <div class="text-center">
                            <span class="badge rounded-pill bg-danger">&nbsp;</span>
                            <p class="small text-muted mb-0 mt-1">Begal</p>
                            <strong>
                                {{ number_format($categoryDistribution['begal']) }}
                            </strong>
                        </div>

                        <div class="text-center">
                            <span class="badge rounded-pill bg-warning">&nbsp;</span>
                            <p class="small text-muted mb-0 mt-1">Area Gelap</p>
                            <strong>
                                {{ number_format($categoryDistribution['dark']) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- PRIORITY AREAS --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">Area Prioritas</h6>
                            <p class="text-muted small mb-0">
                                Area dengan laporan aktif terbanyak
                            </p>
                        </div>

                        <i class="bi bi-geo-alt text-danger fs-5"></i>
                    </div>

                    @forelse ($priorityAreas as $area)
                        <div class="d-flex justify-content-between align-items-center
                                    border-bottom py-3">
                            <div>
                                <p class="fw-semibold mb-1">
                                    {{ $area->name }}
                                </p>
                                <span class="text-muted small">
                                    Area #{{ $area->id }}
                                </span>
                            </div>

                            <div class="text-end">
                                <h5 class="fw-bold text-danger mb-0">
                                    {{ $area->active_reports_count }}
                                </h5>
                                <span class="text-muted small">Laporan aktif</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-geo-alt fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">
                                Belum ada area prioritas.
                            </p>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

        {{-- STATUS SUMMARY --}}
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-1">Ringkasan Status Laporan</h6>
                    <p class="text-muted small mb-4">
                        Kondisi seluruh laporan yang tercatat
                    </p>

                    @php
                        $statusItems = [
                            ['label' => 'Pending', 'key' => 'pending_reports', 'color' => 'warning'],
                            ['label' => 'Verified', 'key' => 'verified_reports', 'color' => 'primary'],
                            ['label' => 'Investigating', 'key' => 'investigating_reports', 'color' => 'info'],
                            ['label' => 'Resolved', 'key' => 'resolved_reports', 'color' => 'success'],
                            ['label' => 'Rejected', 'key' => 'rejected_reports', 'color' => 'danger'],
                        ];

                        $totalReports = $statistics['total_reports'];
                    @endphp

                    @foreach ($statusItems as $item)
                        @php
                            $value = $statistics[$item['key']];
                            $percentage = $totalReports > 0
                                ? ($value / $totalReports) * 100
                                : 0;
                        @endphp

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-semibold">
                                    {{ $item['label'] }}
                                </span>
                                <span class="small text-muted">
                                    {{ $value }} laporan
                                </span>
                            </div>

                            <div class="progress" style="height: 8px;">
                                <div
                                    class="progress-bar bg-{{ $item['color'] }}"
                                    role="progressbar"
                                    style="width: {{ $percentage }}%"
                                    aria-valuenow="{{ $percentage }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

    </div>

    {{-- RECENT REPORTS --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Laporan Terbaru</h6>
                    <p class="text-muted small mb-0">
                        Laporan masyarakat yang baru masuk
                    </p>
                </div>

                <a href="{{ route('governance.reports.index') }}"
                   class="btn btn-sm btn-outline-primary mt-2 mt-md-0">
                    Lihat Semua
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Laporan</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Pelapor</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recentReports as $report)
                            <tr>
                                <td class="fw-semibold">
                                    {{ $report->tracking_code ?? '#'.$report->id }}
                                </td>

                                <td>
                                    @if ($report->category === 'begal')
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            Begal
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-dark">
                                            Area Gelap
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $report->location_name ?: 'Tidak dicantumkan' }}
                                </td>

                                <td>
                                    {{ $report->user?->name ?? 'Anonim' }}
                                </td>

                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'verified' => 'primary',
                                            'investigating' => 'info',
                                            'resolved' => 'success',
                                            'rejected' => 'danger',
                                        ];
                                    @endphp

                                    <span class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>

                                <td class="text-muted small">
                                    {{ $report->created_at?->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Belum ada laporan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- RECENT ACTIVITIES --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="fw-bold mb-1">Aktivitas Terbaru</h6>
            <p class="text-muted small mb-3">
                Riwayat perubahan status laporan
            </p>

            @forelse ($recentUpdates as $update)
                <div class="d-flex gap-3 border-bottom py-3">
                    <div class="rounded-circle bg-primary bg-opacity-10
                                text-primary d-flex align-items-center
                                justify-content-center flex-shrink-0"
                         style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>

                    <div class="flex-grow-1">
                        <p class="mb-1">
                            <strong>
                                {{ $update->updater?->name ?? 'Sistem' }}
                            </strong>
                            mengubah status laporan
                            <strong>
                                {{ $update->report?->tracking_code ?? 'Laporan tidak tersedia' }}
                            </strong>
                        </p>

                        <p class="text-muted small mb-1">
                            {{ ucfirst($update->old_status ?? 'Baru') }}
                            <i class="bi bi-arrow-right mx-1"></i>
                            <strong>{{ ucfirst($update->new_status) }}</strong>
                        </p>

                        @if ($update->note)
                            <p class="text-muted small mb-1">
                                {{ $update->note }}
                            </p>
                        @endif

                        <span class="text-muted" style="font-size: 12px;">
                            {{ $update->created_at?->format('d/m/Y H:i') }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center py-4 mb-0">
                    Belum ada aktivitas perubahan status.
                </p>
            @endforelse

        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const dailyReports = @json($dailyReports);

    const chartLabels = dailyReports.map(item => item.label);
    const begalData = dailyReports.map(item => item.begal);
    const darkData = dailyReports.map(item => item.dark);

    // Grafik tren laporan
    new Chart(document.getElementById('reportTrendChart'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Begal',
                    data: begalData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2
                },
                {
                    label: 'Area Gelap',
                    data: darkData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Grafik distribusi kategori
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: ['Begal', 'Area Gelap'],
            datasets: [{
                data: [
                    {{ $categoryDistribution['begal'] }},
                    {{ $categoryDistribution['dark'] }}
                ],
                backgroundColor: ['#dc3545', '#ffc107'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
@endpush
