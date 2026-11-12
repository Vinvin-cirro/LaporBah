
@extends('layouts.governance')

@section('title', 'Detail Laporan')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Detail Laporan</h4>
            <p class="text-muted mb-0">
                Tracking Code: {{ $report->tracking_code }}
            </p>
        </div>

        <a href="{{ route('governance.reports.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali
        </a>
    </div>

    <div class="row g-4">

        {{-- Informasi Laporan --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Informasi Laporan</h5>

                    <div class="mb-3">
                        <small class="text-muted">Kategori</small>
                        <div class="fw-semibold">
                            {{ ucfirst($report->category) }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Status</small>
                        <div>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'verified' => 'info',
                                    'investigating' => 'primary',
                                    'resolved' => 'success',
                                    'rejected' => 'danger',
                                ];
                            @endphp

                            <span class="badge bg-{{ $statusColors[$report->status] ?? 'secondary' }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Lokasi</small>
                        <div class="fw-semibold">
                            {{ $report->location_name ?? 'Tidak dicantumkan' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Deskripsi</small>
                        <p class="mb-0">
                            {{ $report->description }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Waktu Kejadian</small>
                        <div>
                            {{ $report->incident_at
                                ? $report->incident_at->format('d/m/Y H:i')
                                : 'Tidak dicantumkan' }}
                        </div>
                    </div>

                    <div>
                        <small class="text-muted">Waktu Laporan Dibuat</small>
                        <div>
                            {{ $report->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Koordinat --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Koordinat Lokasi</h5>

                    <div class="mb-3">
                        <small class="text-muted">Latitude</small>
                        <div>{{ $report->latitude ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Longitude</small>
                        <div>{{ $report->longitude ?? '-' }}</div>
                    </div>

                    @if($report->latitude && $report->longitude)
                        <a href="https://www.openstreetmap.org/?mlat={{ $report->latitude }}&mlon={{ $report->longitude }}#map=17/{{ $report->latitude }}/{{ $report->longitude }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-geo-alt me-1"></i>
                            Lihat di OpenStreetMap
                        </a>
                    @else
                        <p class="text-muted mb-0">
                            Koordinat belum tersedia.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Foto Bukti --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Foto Bukti</h5>

                    @forelse($report->images as $image)
                        <a href="{{ Storage::disk('public')->url($image->image_path) }}"
                           target="_blank"
                           rel="noopener noreferrer">
                            <img
                                src="{{ Storage::disk('public')->url($image->image_path) }}"
                                alt="Foto bukti laporan"
                                class="img-thumbnail me-2 mb-2"
                                style="width: 160px; height: 120px; object-fit: cover;"
                            >
                        </a>
                    @empty
                        <p class="text-muted mb-0">
                            Tidak ada foto bukti.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Assignment Section --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Penugasan Petugas</h5>
            </div>

            <div class="card-body">
                <h6 class="text-muted">Petugas Aktif</h6>

                @if ($report->activeAssignment)
                    <div class="alert alert-info">
                        <strong>{{ $report->activeAssignment->officer->name }}</strong>
                        <br>
                        <small>{{ $report->activeAssignment->officer->email }}</small>
                        <br>
                        <span class="badge bg-success mt-2">Sedang Bertugas</span>
                    </div>
                @elseif (in_array($report->status, ['resolved', 'rejected']))
                    <div class="alert alert-secondary">
                        <strong>Penugasan telah berakhir.</strong>
                        <br>
                        <small>
                            Laporan ini sudah
                            {{ $report->status === 'resolved' ? 'diselesaikan' : 'ditolak' }}.
                        </small>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Belum ada petugas yang ditugaskan.
                    </div>
                @endif

                <hr>

                <h6 class="mb-3">
                    {{ $report->activeAssignment ? 'Ganti Petugas' : 'Tugaskan Petugas' }}
                </h6>

               @if (in_array($report->status, ['verified', 'investigating']))
                    @if ($officers->isNotEmpty())
                        <form
                            action="{{ route('governance.reports.assign', $report->id) }}"
                            method="POST"
                        >
                            @csrf

                            <div class="mb-3">
                                <label for="officer_id" class="form-label">
                                    Pilih Petugas
                                </label>

                                <select
                                    name="officer_id"
                                    id="officer_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">-- Pilih Petugas --</option>

                                    @foreach ($officers as $officer)
                                        <option value="{{ $officer->id }}">
                                            {{ $officer->name }} ({{ $officer->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">
                                    Catatan Penugasan (Opsional)
                                </label>

                                <textarea
                                    name="note"
                                    id="note"
                                    class="form-control"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Tambahkan instruksi untuk petugas..."
                                >{{ old('note') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                {{ $report->activeAssignment ? 'Ganti Petugas' : 'Tugaskan Petugas' }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning mb-0">
                            Belum ada akun dengan role Officer.
                        </div>
                    @endif
                @else
                    <div class="alert alert-secondary mb-0">
                        Laporan sudah selesai atau ditolak. Penugasan petugas tidak dapat dilakukan.
                    </div>
                @endif
            </div>
        </div>

        {{-- Assignment History --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Riwayat Penugasan</h5>
            </div>

            <div class="card-body">
                @forelse ($report->assignments->sortByDesc('assigned_at') as $assignment)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <strong>
                                {{ $assignment->officer->name }}
                            </strong>

                            @if ($assignment->ended_at)
                                <span class="badge bg-secondary">Selesai</span>
                            @else
                                <span class="badge bg-success">Aktif</span>
                            @endif
                        </div>

                        <small class="text-muted">
                            Ditugaskan oleh:
                            {{ $assignment->assigner?->name ?? 'Tidak diketahui' }}
                        </small>

                        <div class="mt-2">
                            <small>
                                Waktu penugasan:
                                {{ $assignment->assigned_at?->format('d/m/Y H:i') ?? '-' }}
                            </small>
                        </div>

                        @if ($assignment->ended_at)
                            <div>
                                <small>
                                    Waktu berakhir:
                                    {{ $assignment->ended_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        @endif

                        @if ($assignment->note)
                            <p class="mb-0 mt-2">
                                <small>Catatan: {{ $assignment->note }}</small>
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">
                        Belum ada riwayat penugasan.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Riwayat Status --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Riwayat Perubahan Status</h5>

                    @forelse($report->updates->sortByDesc('created_at') as $update)
                        <div class="border-start border-3 ps-3 pb-3 mb-3">
                            <div class="fw-semibold">
                                {{ ucfirst($update->old_status ?? 'Laporan dibuat') }}
                                <i class="bi bi-arrow-right mx-1"></i>
                                {{ ucfirst($update->new_status) }}
                            </div>

                            <small class="text-muted">
                                {{ $update->created_at->format('d/m/Y H:i') }}
                                ·
                                {{ $update->updater?->name ?? 'Sistem' }}
                            </small>

                            @if($update->note)
                                <p class="mb-0 mt-2">{{ $update->note }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">
                            Belum ada riwayat perubahan status.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
