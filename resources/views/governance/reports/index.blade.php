
@extends('layouts.governance')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Manajemen Laporan</h4>
        <p class="text-muted mb-0">
            Periksa, verifikasi, dan kelola laporan masyarakat.
        </p>
    </div>

    {{-- SUMMARY --}}
    <div class="row g-3 mb-4">
        @php
            $statusCards = [
                ['label' => 'Total Laporan', 'value' => $reports->total(), 'color' => 'primary', 'icon' => 'bi-file-earmark-text'],
                ['label' => 'Pending', 'value' => $reports->where('status', 'pending')->count(), 'color' => 'warning', 'icon' => 'bi-hourglass-split'],
                ['label' => 'Investigating', 'value' => $reports->where('status', 'investigating')->count(), 'color' => 'info', 'icon' => 'bi-shield-check'],
                ['label' => 'Resolved', 'value' => $reports->where('status', 'resolved')->count(), 'color' => 'success', 'icon' => 'bi-check-circle'],
            ];
        @endphp

        @foreach ($statusCards as $card)
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">{{ $card['label'] }}</p>
                            <h4 class="fw-bold mb-0">
                                {{ number_format($card['value']) }}
                            </h4>
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

    {{-- FILTER --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <form action="{{ route('governance.reports.index') }}"
                  method="GET"
                  class="row g-3 align-items-end">

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold">Pencarian</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Kode laporan atau lokasi..."
                        value="{{ $filters['search'] ?? '' }}">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <option value="begal"
                            @selected(($filters['category'] ?? '') === 'begal')>
                            Begal
                        </option>
                        <option value="dark"
                            @selected(($filters['category'] ?? '') === 'dark')>
                            Area Gelap
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
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
                                @selected(($filters['status'] ?? '') === $value)>
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

                    <a href="{{ route('governance.reports.index') }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>

                <input type="hidden" name="per_page"
                       value="{{ $filters['per_page'] ?? 15 }}">

            </form>
        </div>
    </div>

    {{-- REPORT TABLE --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold mb-1">Daftar Laporan</h6>
                    <p class="text-muted small mb-0">
                        Menampilkan {{ $reports->count() }} dari
                        {{ $reports->total() }} laporan.
                    </p>
                </div>

                <span class="badge bg-light text-dark">
                    Halaman {{ $reports->currentPage() }}
                    / {{ $reports->lastPage() ?: 1 }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Laporan</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reports as $report)
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'verified' => 'primary',
                                    'investigating' => 'info',
                                    'resolved' => 'success',
                                    'rejected' => 'danger',
                                ];
                            @endphp

                            <tr>
                                <td>
                                    <span class="fw-semibold">
                                        {{ $report->tracking_code ?? '#'.$report->id }}
                                    </span>
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
                                    <div class="fw-semibold">
                                        {{ $report->location_name ?: 'Tidak dicantumkan' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $report->latitude }},
                                        {{ $report->longitude }}
                                    </small>
                                </td>

                                <td>
                                    <span class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </td>

                                <td class="text-muted small">
                                    {{ $report->created_at?->format('d/m/Y H:i') }}
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('governance.reports.show', $report->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Lihat detail">
                                        <i class="bi bi-eye"></i>
                                        Detail
                                    </a>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">
                                        Tidak ada laporan yang ditemukan.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center mt-3">

                <small class="text-muted">
                    Menampilkan {{ $reports->firstItem() ?? 0 }}
                    - {{ $reports->lastItem() ?? 0 }}
                    dari {{ $reports->total() }} laporan
                </small>

                {{ $reports->onEachSide(1)->links('pagination::bootstrap-5') }}

            </div>

        </div>
    </div>

</div>

@endsection
