
@extends('layouts.governance')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Statistik Laporan</h4>
        <p class="text-muted mb-0">
            Ringkasan dan perkembangan laporan yang masuk ke LaporBah!.
        </p>
    </div>

    {{-- Total and category reports --}}
    <div class="row g-3 mb-4">
        @foreach (\App\Models\Report::CATEGORIES as $key => $label)
            <div class="col-6 col-lg-3"><div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small mb-2">{{ $label }}</div>
                <h3 class="fw-bold mb-0">{{ $categoryStatistics->firstWhere('category', $key)->total ?? 0 }}</h3>
                <small class="text-muted">Jumlah laporan {{ strtolower($label) }}</small>
            </div></div></div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        Distribusi Berdasarkan Kategori
                    </h6>
                    <div style="height: 300px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        Distribusi Berdasarkan Status
                    </h6>
                    <div style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        Tren Laporan 6 Bulan Terakhir
                    </h6>
                    <div style="height: 320px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const categoryLabels = @json(
        $categoryStatistics->pluck('category')->values()
    );

    const categoryValues = @json(
        $categoryStatistics->pluck('total')->values()
    );

    const statusLabels = @json(
        $statusStatistics->pluck('status')->values()
    );

    const statusValues = @json(
        $statusStatistics->pluck('total')->values()
    );

    const monthlyLabels = @json(
        $monthlyStatistics->pluck('month')->values()
    );

    const monthlyValues = @json(
        $monthlyStatistics->pluck('total')->values()
    );

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: categoryLabels.map(label => ({pju_rusak:'PJU Rusak', area_gelap:'Area Gelap', begal_curas:'Begal/Curas', lain_lain:'Lain-lain'}[label] || label)),
            datasets: [{
                data: categoryValues,
                backgroundColor: ['#0d6efd', '#ffc107', '#dc3545', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: statusLabels.map(label =>
                label.charAt(0).toUpperCase() + label.slice(1)
            ),
            datasets: [{
                label: 'Jumlah Laporan',
                data: statusValues,
                backgroundColor: '#3454a4',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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

    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Jumlah Laporan',
                data: monthlyValues,
                borderColor: '#3454a4',
                backgroundColor: 'rgba(52, 84, 164, 0.12)',
                fill: true,
                tension: 0.3,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
</script>
@endpush
