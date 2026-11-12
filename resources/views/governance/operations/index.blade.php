@extends('layouts.governance')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Operasional Lapangan</h4>
        <p class="text-muted mb-0">
            Pantau petugas yang sedang bertugas dan laporan yang ditangani.
        </p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">
                        Penugasan Aktif
                    </div>
                    <h3 class="fw-bold mb-0">
                        {{ $activeAssignmentCount }}
                    </h3>
                    <small class="text-muted">
                        Laporan yang sedang ditangani
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">
                        Petugas Aktif
                    </div>
                    <h3 class="fw-bold mb-0">
                        {{ $activeOfficerCount }}
                    </h3>
                    <small class="text-muted">
                        Petugas dengan penugasan aktif
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">
                        Sedang Investigasi
                    </div>
                    <h3 class="fw-bold mb-0">
                        {{ $investigatingCount }}
                    </h3>
                    <small class="text-muted">
                        Laporan dalam proses investigasi
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Petugas</th>
                            <th>Kode Laporan</th>
                            <th>Kategori</th>
                            <th>Status Laporan</th>
                            <th>Waktu Penugasan</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($assignments as $assignment)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $assignment->officer->name ?? 'Petugas tidak ditemukan' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $assignment->officer->email ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $assignment->report->tracking_code ?? '-' }}
                                </td>

                                <td>
                                    {{ ucfirst($assignment->report->category ?? '-') }}
                                </td>

                                <td>
                                    @php
                                        $status = $assignment->report->status ?? '-';

                                        $statusColors = [
                                            'pending' => 'secondary',
                                            'verified' => 'primary',
                                            'investigating' => 'warning',
                                            'resolved' => 'success',
                                            'rejected' => 'danger',
                                        ];
                                    @endphp

                                    <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $assignment->assigned_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                <td>
                                    {{ $assignment->note ?: '-' }}
                                </td>

                                <td>
                                    @if ($assignment->report)
                                        <a href="{{ route('governance.reports.show', $assignment->report->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Belum ada petugas yang sedang bertugas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
