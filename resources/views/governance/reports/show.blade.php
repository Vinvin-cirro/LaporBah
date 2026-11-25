
@extends('layouts.governance')

@section('title', 'Detail & Verifikasi Laporan')
@section('breadcrumb', 'Detail & verifikasi laporan')

@section('content')

@php
    $reportCategory = strtolower(trim($report->category ?? ''));
    $isBegal = in_array($reportCategory, ['begal_curas', 'begal'], true);
    $isInspectionCategory = in_array($reportCategory, ['pju_rusak', 'area_gelap'], true);
    $categoryLabel = \App\Models\Report::CATEGORIES[$reportCategory] ?? $report->category;

    $statusColors = [
        'pending' => 'warning',
        'verifying' => 'info',
        'awaiting_approval' => 'primary',
        'verified' => 'info',
        'investigating' => 'primary',
        'resolved' => 'success',
        'rejected' => 'danger',
    ];

    $statusLabels = [
        'pending' => 'Menunggu verifikasi',
        'verifying' => 'Sedang diverifikasi petugas',
        'awaiting_approval' => 'Menunggu persetujuan Governance/Admin',
        'verified' => 'Terverifikasi',
        'investigating' => 'Sedang ditangani',
        'resolved' => 'Selesai',
        'rejected' => 'Ditolak',
    ];

    $priority = $report->activeAssignment?->priority ?? 'normal';

    $priorityLabels = [
        'normal' => 'Normal',
        'high' => 'Tinggi',
        'urgent' => 'Mendesak',
    ];

    $latestVerification = $report->updates
        ->whereIn('action', ['verify', 'request_info', 'mark_duplicate', 'reject'])
        ->sortByDesc('created_at')
        ->first();

    $verificationUpdate = $report->updates
        ->where('new_status', 'verified')
        ->sortByDesc('created_at')
        ->first();

    $activeAssignment = $report->activeAssignment;

    $isInspectionAssignment = $activeAssignment
        && $activeAssignment->assignment_type === 'inspection';

    $inspectionStatusLabels = [
        'assigned' => 'Menunggu pemeriksaan petugas',
        'accepted' => 'Petugas menerima tugas',
        'in_progress' => 'Sedang diperiksa petugas',
        'awaiting_confirmation' => 'Hasil pemeriksaan menunggu tinjauan Governance',
    ];
@endphp

<div class="container-fluid px-0">

    {{-- HEADER --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Detail &amp; verifikasi laporan</h2>

        <p class="text-secondary mb-3">
            Manajemen Laporan / {{ $report->tracking_code }}
        </p>

        <div class="d-flex flex-wrap align-items-center gap-3">

            <a href="{{ route('governance.reports.index') }}"
                class="btn btn-outline-secondary px-3">
                <i class="bi bi-arrow-left me-1"></i>
                Kembali ke daftar
            </a>

            <span class="badge rounded-3 bg-{{ $statusColors[$report->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$report->status] ?? 'secondary' }} px-3 py-3">

                @if ($report->status === 'pending' && $isInspectionAssignment)
                    {{ $inspectionStatusLabels[$activeAssignment->status] ?? 'Sedang diperiksa petugas' }}
                @else
                    {{ $statusLabels[$report->status] ?? ucfirst($report->status) }}
                @endif

            </span>

            <span class="badge rounded-3 bg-primary-subtle text-primary px-3 py-3">
                Prioritas: {{ $priorityLabels[$priority] ?? ucfirst($priority) }}
            </span>

        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

    {{-- STEPPER --}}
    @if (in_array($report->status, ['verified', 'investigating', 'resolved'], true))

        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="rounded-3 bg-dark text-white text-center py-2 px-3 fw-semibold">
                    <i class="bi bi-check-circle me-1"></i>
                    1 Verifikasi selesai
                </div>
            </div>

            <div class="col-md-4">
                <div class="rounded-3 bg-dark text-white text-center py-2 px-3 fw-semibold">
                    <i class="bi bi-people me-1"></i>
                    2 {{ $activeAssignment ? 'Petugas ditugaskan' : 'Pilih tim penanggung jawab' }}
                </div>
            </div>

            <div class="col-md-4">
                <div class="rounded-3 {{ in_array($report->status, ['investigating', 'resolved'], true) ? 'bg-dark text-white' : 'border bg-white text-secondary' }} text-center py-2 px-3 fw-semibold">
                    3 {{ $report->status === 'resolved' ? 'Penanganan selesai' : ($report->status === 'investigating' ? 'Penanganan berlangsung' : 'Konfirmasi penugasan') }}
                </div>
            </div>

        </div>

    @elseif (in_array($report->status, ['pending', 'verifying', 'awaiting_approval'], true))

        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="rounded-3 {{ $report->status === 'pending' ? 'bg-dark text-white' : 'bg-success text-white' }} text-center py-2 px-3 fw-semibold">
                    1 Verifikasi laporan
                </div>
            </div>

            <div class="col-md-4">
                <div class="rounded-3 {{ in_array($report->status, ['verifying', 'awaiting_approval'], true) ? 'bg-dark text-white' : 'border bg-white text-secondary' }} text-center py-2 px-3 fw-semibold">
                    @if ($report->status === 'awaiting_approval')
                        Hasil pemeriksaan selesai
                    @elseif ($report->status === 'verifying')
                        Sedang diperiksa Officer
                    @else
                        Pemeriksaan Officer (opsional)
                    @endif
                </div>
            </div>

            <div class="col-md-4">
                <div class="rounded-3 border bg-white text-secondary text-center py-2 px-3 fw-semibold">
                    3 Penanganan
                </div>
            </div>

        </div>
    @else

        <div class="alert alert-danger mb-3">
            <i class="bi bi-x-circle me-2"></i>
            Laporan ditolak. Proses verifikasi dan penugasan tidak dapat dilanjutkan.
        </div>

    @endif

    {{-- TAHAP 1: VERIFIKASI --}}
    @if (in_array($report->status, ['pending', 'verifying', 'awaiting_approval'], true))

        <div class="row g-3 align-items-start">

            {{-- KOLOM KIRI: DETAIL LAPORAN --}}
            <div class="col-xl-7">

                <div class="card border shadow-sm rounded-4 mb-3">
                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">{{ $categoryLabel }}</h5>

                        <div class="small text-secondary mb-3">
                            <span class="fw-semibold text-dark">{{ $report->tracking_code }}</span>
                            <span class="mx-1">•</span>
                            {{ $categoryLabel }}
                            <span class="mx-1">•</span>
                            Aplikasi warga
                        </div>

                        <p class="text-secondary mb-4" style="white-space: pre-line">
                            {{ $report->description }}
                        </p>

                        <div class="small text-secondary">

                            <div>
                                Kejadian:
                                {{ $report->incident_at?->translatedFormat('j F Y, H.i') ?? 'Tidak dicantumkan' }} WIB
                            </div>

                            <div>
                                Dikirim:
                                {{ $report->created_at?->translatedFormat('j F Y, H.i') }} WIB
                            </div>

                            <div>
                                Lokasi:
                                {{ $report->location_name ?? 'Tidak dicantumkan' }}
                            </div>

                            <div>
                                Koordinat:
                                {{ $report->latitude ?? '-' }},
                                {{ $report->longitude ?? '-' }}
                            </div>

                            @if ($report->latitude !== null && $report->longitude !== null)

                                <a class="d-inline-block mt-2"
                                    href="https://www.openstreetmap.org/?mlat={{ $report->latitude }}&mlon={{ $report->longitude }}#map=17/{{ $report->latitude }}/{{ $report->longitude }}"
                                    target="_blank"
                                    rel="noopener noreferrer">

                                    <i class="bi bi-geo-alt me-1"></i>
                                    Lihat lokasi di OpenStreetMap

                                </a>

                            @endif

                        </div>

                        <hr class="my-3">

                        <div class="fw-semibold small mb-2">Lampiran foto</div>

                        @forelse ($report->images as $image)

                            <a href="{{ Storage::disk('public')->url($image->image_path) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="d-inline-block me-2 mb-2">

                                <img src="{{ Storage::disk('public')->url($image->image_path) }}"
                                    alt="Foto bukti laporan"
                                    class="img-thumbnail"
                                    style="width:125px;height:95px;object-fit:cover;">

                            </a>

                        @empty

                            <span class="small text-secondary">
                                Tidak ada foto yang disertakan.
                            </span>

                        @endforelse

                    </div>
                </div>

                {{-- LAPORAN SERUPA --}}
                <div class="card border shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">
                            Laporan serupa di sekitar lokasi
                        </h5>

                        @forelse ($similarReports as $similar)

                            <div class="d-flex justify-content-between align-items-start gap-3 border-bottom pb-3 mb-3">

                                <div>

                                    <div class="fw-semibold">
                                        {{ $similar->tracking_code ?? 'Laporan #' . $similar->id }}

                                        <span class="text-secondary fw-normal">
                                            • {{ ucfirst($similar->category) }}
                                        </span>
                                    </div>

                                    <div class="small text-secondary">
                                        {{ $similar->location_name ?? 'Lokasi tidak dicantumkan' }}
                                    </div>

                                    <div class="small text-secondary">
                                        {{ $similar->created_at?->translatedFormat('j M Y, H.i') }} WIB
                                        · {{ number_format($similar->distance_meters, 0, ',', '.') }} m dari lokasi
                                    </div>

                                </div>

                                <a href="{{ route('governance.reports.show', $similar) }}"
                                    class="btn btn-sm btn-outline-secondary text-nowrap">
                                    Lihat laporan
                                </a>

                            </div>

                        @empty

                            <p class="text-secondary mb-0">
                                Belum ditemukan laporan lain dalam radius 2 km dari lokasi ini.
                            </p>

                        @endforelse

                    </div>
                </div>

            </div>

            {{-- KOLOM KANAN: VERIFIKASI DAN INSPEKSI --}}
            <div class="col-xl-5">

                <div class="card border shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Tinjauan verifikasi</h5>

                        <div class="small text-secondary mb-4">
                            Verifikator:
                            <span class="text-dark fw-semibold">{{ auth()->user()->name }}</span>
                            <br>
                            Waktu verifikasi: Belum diverifikasi
                        </div>

                        <form action="{{ route('governance.reports.update-status', $report) }}"
                            method="POST"
                            id="verificationForm">

                            @csrf
                            @method('PATCH')

                            <div class="mb-2">

                                <label for="note" class="form-label fw-semibold small">
                                    Catatan internal <span class="text-danger">*</span>
                                </label>

                                <textarea name="note"
                                    id="note"
                                    class="form-control rounded-3"
                                    rows="4"
                                    maxlength="1000"
                                    placeholder="Tuliskan hasil pemeriksaan laporan..."
                                    required>{{ old('note') }}</textarea>

                                <div class="form-text">
                                    Catatan ini hanya untuk keperluan internal dan tidak ditampilkan kepada warga.
                                </div>

                            </div>

                            <div class="mb-3 d-none" id="duplicateSelectWrap">

                                <label for="duplicate_of_report_id" class="form-label fw-semibold small">
                                    Laporan yang dianggap duplikat
                                </label>

                                <select name="duplicate_of_report_id"
                                    id="duplicate_of_report_id"
                                    class="form-select">

                                    <option value="">Pilih laporan pembanding</option>

                                    @foreach ($similarReports as $similar)

                                        <option value="{{ $similar->id }}"
                                            @selected(old('duplicate_of_report_id') == $similar->id)>

                                            {{ $similar->tracking_code }} —
                                            {{ $similar->location_name ?? 'Lokasi tidak dicantumkan' }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <button type="submit"
                                name="action"
                                value="verify"
                                class="btn btn-primary-dark w-100 rounded-3 py-2 mb-2"
                                onclick="return confirm('Verifikasi laporan ini?')">

                                <i class="bi bi-check-circle me-1"></i>
                                Verifikasi laporan

                            </button>

                            <button type="submit"
                                name="action"
                                value="request_info"
                                class="btn btn-outline-secondary w-100 rounded-3 mb-2">

                                Minta informasi tambahan

                            </button>

                            <div class="row g-2">

                                <div class="col-6">

                                    <button type="button"
                                        id="duplicateButton"
                                        class="btn btn-outline-secondary w-100 rounded-3">

                                        Tandai duplikat

                                    </button>

                                </div>

                                <div class="col-6">

                                    <button type="submit"
                                        name="action"
                                        value="reject"
                                        class="btn btn-danger-subtle text-danger w-100 rounded-3"
                                        onclick="return confirm('Tolak laporan ini?')">

                                        Tolak laporan

                                    </button>

                                </div>

                            </div>

                            <div class="small text-secondary mt-3">
                                Alasan wajib dicatat untuk penolakan, duplikat, dan permintaan informasi tambahan.
                            </div>

                        </form>

                        {{-- PEMERIKSAAN SEBELUM VERIFIKASI --}}
                        @if ($isInspectionCategory)

                            <hr class="my-4">

                            <h5 class="fw-bold mb-2">
                                <i class="bi bi-clipboard2-check me-1"></i>
                                Pemeriksaan sebelum verifikasi
                            </h5>

                            <p class="small text-secondary">
                                Jika bukti laporan belum cukup jelas, Governance dapat menugaskan
                                satu Officer untuk memeriksa kondisi sebenarnya di lapangan.
                                Status laporan tetap menunggu verifikasi sampai hasil pemeriksaan ditinjau.
                            </p>

                            @if ($isInspectionAssignment)

                                <div class="border rounded-3 p-3 mb-3">

                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">

                                        <strong>Officer pemeriksa</strong>

                                        <span class="badge bg-warning-subtle text-dark">
                                            {{ $inspectionStatusLabels[$activeAssignment->status] ?? ucfirst(str_replace('_', ' ', $activeAssignment->status)) }}
                                        </span>

                                    </div>

                                    <div class="fw-semibold">
                                        {{ $activeAssignment->officer?->name ?? 'Officer tidak ditemukan' }}
                                    </div>

                                    <div class="small text-secondary mt-2">
                                        Tenggat pemeriksaan:
                                        {{ $activeAssignment->due_at?->format('d/m/Y H:i') ?? '—' }}
                                    </div>

                                    @if ($activeAssignment->note)
                                        <div class="small mt-2">
                                            <strong>Instruksi:</strong>
                                            {{ $activeAssignment->note }}
                                        </div>
                                    @endif

                                </div>

                                @if ($activeAssignment->status === 'awaiting_confirmation')

                                    <div class="border rounded-3 p-3 mb-3">

                                        <h6 class="fw-bold mb-3">
                                            Hasil pemeriksaan Officer
                                        </h6>

                                        @forelse ($activeAssignment->updates->where('event', 'progress')->sortBy('created_at') as $inspectionUpdate)

                                            <div class="small mb-3">

                                                <strong>
                                                    {{ $inspectionUpdate->user?->name ?? 'Officer' }}
                                                </strong>

                                                <span class="text-secondary">
                                                    · {{ $inspectionUpdate->created_at?->format('d/m/Y H:i') }}
                                                </span>

                                                <div class="mt-1">
                                                    {{ $inspectionUpdate->note }}
                                                </div>

                                            </div>

                                        @empty
                                            <p class="small text-secondary">
                                                Tidak ada catatan perkembangan pemeriksaan.
                                            </p>
                                        @endforelse

                                        @foreach ($activeAssignment->updates->where('event', 'completion_requested')->sortByDesc('created_at')->take(1) as $inspectionConclusion)

                                            <div class="alert alert-light border small mb-3">

                                                <strong>Kesimpulan Officer:</strong>

                                                <div class="mt-1">
                                                    {{ $inspectionConclusion->note }}
                                                </div>

                                            </div>

                                        @endforeach

                                        <div class="row g-2">

                                            @forelse ($activeAssignment->documents as $inspectionDocument)

                                                <div class="col-4 col-md-3">

                                                    <a href="{{ Storage::disk('public')->url($inspectionDocument->file_path) }}"
                                                        target="_blank"
                                                        rel="noopener noreferrer">

                                                        <img src="{{ Storage::disk('public')->url($inspectionDocument->file_path) }}"
                                                            alt="Bukti inspeksi"
                                                            class="img-thumbnail w-100"
                                                            style="height:85px;object-fit:cover">

                                                    </a>

                                                    <small class="d-block text-secondary">
                                                        {{ ucfirst($inspectionDocument->kind) }}
                                                    </small>

                                                </div>

                                            @empty

                                                <div class="col-12">
                                                    <p class="small text-secondary mb-0">
                                                        Belum ada foto bukti pemeriksaan.
                                                    </p>
                                                </div>

                                            @endforelse

                                        </div>

                                    </div>

                                    {{-- KEPUTUSAN GOVERNANCE --}}
                                    <form action="{{ route('governance.reports.inspections.review', $activeAssignment) }}"
                                        method="POST">

                                        @csrf

                                        <label class="form-label small fw-semibold">
                                            Catatan hasil peninjauan <span class="text-danger">*</span>
                                        </label>

                                        <textarea name="review_note"
                                            class="form-control mb-2"
                                            rows="3"
                                            maxlength="2000"
                                            required
                                            placeholder="Tuliskan alasan keputusan berdasarkan bukti inspeksi...">{{ old('review_note') }}</textarea>

                                        <button type="submit"
                                            name="decision"
                                            value="verify"
                                            class="btn btn-primary-dark w-100 mb-2"
                                            onclick="return confirm('Verifikasi laporan berdasarkan hasil inspeksi?')">

                                            <i class="bi bi-check-circle me-1"></i>
                                            Setujui &amp; verifikasi laporan

                                        </button>

                                        <button type="submit"
                                            name="decision"
                                            value="reinspect"
                                            class="btn btn-outline-darkblue w-100 mb-2">

                                            <i class="bi bi-arrow-repeat me-1"></i>
                                            Minta pemeriksaan ulang

                                        </button>

                                        <button type="submit"
                                            name="decision"
                                            value="reject"
                                            class="btn btn-outline-danger w-100"
                                            onclick="return confirm('Tolak laporan berdasarkan hasil inspeksi?')">

                                            Tolak laporan

                                        </button>

                                    </form>

                                @else

                                    <div class="alert alert-warning small mb-0">

                                        <i class="bi bi-clock-history me-1"></i>

                                        Menunggu Officer menyelesaikan pemeriksaan dan mengirimkan
                                        hasil serta bukti lapangan.

                                        <hr class="my-2">

                                        Governance tetap dapat melakukan verifikasi langsung
                                        menggunakan tindakan di atas jika bukti awal sudah memadai.

                                    </div>

                                @endif

                            @else

                                {{-- FORM TUGAS INSPEKSI --}}
                                <form action="{{ route('governance.reports.assign', $report) }}"
                                    method="POST"
                                    class="border rounded-3 p-3">

                                    @csrf

                                    <input type="hidden" name="assignment_type" value="inspection">

                                    <div class="mb-3">

                                        <label class="form-label small fw-semibold">
                                            Officer pemeriksa <span class="text-danger">*</span>
                                        </label>

                                        <select name="officer_id" class="form-select" required>

                                            <option value="">Pilih Officer</option>

                                            @foreach ($officers as $officer)

                                                <option value="{{ $officer->id }}"
                                                    @selected(old('officer_id') == $officer->id)>

                                                    {{ $officer->name }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                    <div class="row g-2">

                                        <div class="col-md-6">

                                            <label class="form-label small fw-semibold">
                                                Prioritas
                                            </label>

                                            <select name="priority" class="form-select" required>

                                                <option value="normal">Normal</option>
                                                <option value="high">Tinggi</option>
                                                <option value="urgent">Mendesak</option>

                                            </select>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label small fw-semibold">
                                                Tenggat pemeriksaan
                                            </label>

                                            <input type="datetime-local"
                                                name="due_at"
                                                class="form-control"
                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                value="{{ old('due_at') }}"
                                                required>

                                        </div>

                                    </div>

                                    <div class="mt-3">

                                        <label class="form-label small fw-semibold">
                                            Instruksi pemeriksaan <span class="text-danger">*</span>
                                        </label>

                                        <textarea name="note"
                                            class="form-control"
                                            rows="3"
                                            maxlength="1000"
                                            required
                                            placeholder="Contoh: Periksa kondisi lampu di lokasi, pastikan titik dan kondisi sesuai laporan, lalu unggah foto hasil pemeriksaan.">{{ old('note') }}</textarea>

                                    </div>

                                    <button type="submit"
                                        class="btn btn-outline-darkblue w-100 mt-3"
                                        onclick="return confirm('Tugaskan Officer untuk melakukan inspeksi sebelum verifikasi?')">

                                        <i class="bi bi-person-workspace me-1"></i>
                                        Tugaskan Officer untuk inspeksi

                                    </button>

                                </form>

                            @endif

                        @endif

                    </div>
                </div>

            </div>

        </div>

    {{-- TAHAP 2: PENUGASAN TIM --}}
    @elseif ($report->status === 'verified' && !$activeAssignment)

        <form action="{{ route('governance.reports.assign', $report) }}"
            method="POST"
            id="inlineAssignmentForm">

            @csrf

            {{-- PENTING: membedakan penugasan tim dari inspeksi --}}
            <input type="hidden" name="assignment_type" value="handling">

            <div class="row g-3 align-items-stretch">

                <div class="col-lg-8">

                    <div class="card border shadow-sm rounded-4 h-100">

                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <div>
                                    <h5 class="fw-bold mb-1">Rincian penugasan</h5>

                                    <p class="small text-secondary mb-0">
                                        Tentukan tim dan instansi yang bertanggung jawab.
                                    </p>
                                </div>

                                <span class="badge bg-success-subtle text-success">
                                    Verifikasi selesai
                                </span>

                            </div>

                            <div id="assignmentStep1">

                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Laporan {{ $report->tracking_code }} sudah diverifikasi dan siap ditugaskan.
                                </div>

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <small class="text-secondary">Kategori</small>
                                        <div class="fw-semibold">{{ $categoryLabel }}</div>
                                    </div>

                                    <div class="col-md-6">
                                        <small class="text-secondary">Lokasi</small>
                                        <div class="fw-semibold">
                                            {{ $report->location_name ?? 'Tidak dicantumkan' }}
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <small class="text-secondary">Deskripsi laporan</small>
                                        <div>{{ $report->description }}</div>
                                    </div>

                                </div>

                            </div>

                            <div id="assignmentStep2" class="d-none">

                                @if ($teams->isEmpty())

                                    <div class="alert alert-warning">
                                        Belum ada tim aktif dengan anggota Officer.

                                        <a href="{{ route('governance.teams.create') }}" class="alert-link">
                                            Buat tim terlebih dahulu
                                        </a>.
                                    </div>

                                @endif

                                <div class="mb-3">

                                    <label for="team_id" class="form-label fw-semibold">
                                        Tim penanggung jawab <span class="text-danger">*</span>
                                    </label>

                                    <select name="team_id"
                                        id="team_id"
                                        class="form-select form-select-lg"
                                        required>

                                        <option value="">Pilih tim yang akan menangani laporan</option>

                                        @foreach ($teams as $team)

                                            <option value="{{ $team->id }}"
                                                data-institution="{{ $team->institution_name }}"
                                                data-members="{{ $team->members->pluck('name')->join(', ') }}"
                                                @selected(old('team_id') == $team->id)>

                                                {{ $team->name }} · {{ $team->members->count() }} Officer

                                            </option>

                                        @endforeach

                                    </select>

                                    <div class="form-text" id="teamMembersPreview">
                                        Pilih tim untuk melihat anggota yang akan menerima tugas.
                                    </div>

                                </div>

                                <div class="mb-3">

                                    <label for="institution_name" class="form-label fw-semibold">
                                        Instansi penanggung jawab
                                    </label>

                                    <input type="text"
                                        name="institution_name"
                                        id="institution_name"
                                        class="form-control form-control-lg"
                                        maxlength="150"
                                        placeholder="Contoh: Dinas Perhubungan"
                                        value="{{ old('institution_name') }}">

                                </div>

                                <div class="row g-3 mb-3">

                                    <div class="col-md-6">

                                        <label for="priority" class="form-label fw-semibold">
                                            Prioritas <span class="text-danger">*</span>
                                        </label>

                                        <select name="priority"
                                            id="priority"
                                            class="form-select form-select-lg"
                                            required>

                                            <option value="normal" @selected(old('priority', 'normal') === 'normal')>Normal</option>
                                            <option value="high" @selected(old('priority') === 'high')>Tinggi</option>
                                            <option value="urgent" @selected(old('priority') === 'urgent')>Mendesak</option>

                                        </select>

                                    </div>

                                    <div class="col-md-6">

                                        <label for="due_at" class="form-label fw-semibold">
                                            Batas penyelesaian <span class="text-danger">*</span>
                                        </label>

                                        <input type="datetime-local"
                                            name="due_at"
                                            id="due_at"
                                            class="form-control form-control-lg"
                                            value="{{ old('due_at') }}"
                                            min="{{ now()->format('Y-m-d\TH:i') }}"
                                            required>

                                    </div>

                                </div>

                                @if ($isBegal)

                                    <div class="mb-3">

                                        <label for="patrol_duration_days" class="form-label fw-semibold">
                                            Periode patroli <span class="text-danger">*</span>
                                        </label>

                                        <select name="patrol_duration_days"
                                            id="patrol_duration_days"
                                            class="form-select form-select-lg"
                                            required>

                                            <option value="">Pilih periode patroli</option>
                                            <option value="1" @selected(old('patrol_duration_days') == '1')>1 hari</option>
                                            <option value="3" @selected(old('patrol_duration_days') == '3')>3 hari</option>
                                            <option value="7" @selected(old('patrol_duration_days') == '7')>7 hari</option>
                                            <option value="14" @selected(old('patrol_duration_days') == '14')>14 hari</option>
                                            <option value="30" @selected(old('patrol_duration_days') == '30')>30 hari</option>

                                        </select>

                                        <div class="form-text">
                                            Patroli dilakukan secara berkala selama periode yang dipilih.
                                        </div>

                                    </div>

                                @endif

                                <div class="mb-3">

                                    <label for="assignment_note" class="form-label fw-semibold">
                                        Instruksi internal <span class="text-danger">*</span>
                                    </label>

                                    <textarea name="note"
                                        id="assignment_note"
                                        rows="4"
                                        maxlength="1000"
                                        class="form-control"
                                        placeholder="Tuliskan instruksi untuk petugas..."
                                        required>{{ old('note') }}</textarea>

                                </div>

                                <p class="small text-secondary mb-0">
                                    Perubahan instansi, tim, prioritas, dan tenggat akan tercatat dalam riwayat penugasan.
                                </p>

                            </div>

                            <div id="assignmentStep3" class="d-none">

                                <h6 class="fw-bold mb-3">
                                    Konfirmasi penugasan
                                </h6>

                                <div class="border rounded-3 p-3 mb-3">

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">Laporan</span>
                                        <strong>{{ $report->tracking_code }}</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">Instansi</span>
                                        <strong id="reviewInstitution">-</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">Tim</span>
                                        <strong id="reviewTeam">-</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">Anggota tim</span>
                                        <strong id="reviewOfficer">-</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-secondary">Prioritas</span>
                                        <strong id="reviewPriority">-</strong>
                                    </div>

                                    @if ($isBegal)

                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-secondary">Periode patroli</span>
                                            <strong id="reviewPatrolDuration">-</strong>
                                        </div>

                                    @endif

                                    <div class="d-flex justify-content-between">
                                        <span class="text-secondary">Batas penyelesaian</span>
                                        <strong id="reviewDue">-</strong>
                                    </div>

                                    <hr>

                                    <small class="text-secondary">Instruksi internal</small>
                                    <p class="mb-0 mt-1" id="reviewNote">-</p>

                                </div>

                                <div class="alert alert-warning mb-0">
                                    Pastikan seluruh informasi sudah benar sebelum menyimpan penugasan.
                                </div>

                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">

                                <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="assignmentPrevious"
                                    disabled>

                                    <i class="bi bi-arrow-left me-1"></i>
                                    Kembali

                                </button>

                                <div class="d-flex gap-2">

                                    <button type="button"
                                        class="btn btn-primary-dark"
                                        id="assignmentNext">

                                        Lanjut
                                        <i class="bi bi-arrow-right ms-1"></i>

                                    </button>

                                    <button type="submit"
                                        class="btn btn-success d-none"
                                        id="assignmentSubmit">

                                        <i class="bi bi-check-circle me-1"></i>
                                        Simpan &amp; kirim penugasan

                                    </button>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

                {{-- RINGKASAN KANAN --}}
                <div class="col-lg-4">

                    <div class="card border shadow-sm rounded-4 mb-3">

                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-3">Ringkasan laporan</h5>

                            <div class="fw-semibold mb-3">
                                {{ $report->tracking_code }}<br>
                                {{ $categoryLabel }}<br>
                                {{ $report->location_name ?? 'Lokasi tidak dicantumkan' }}
                            </div>

                            <div class="text-secondary small mb-3">
                                Terverifikasi oleh
                                {{ $verificationUpdate?->updater?->name ?? auth()->user()->name }}

                                <br>

                                {{ $verificationUpdate?->created_at?->translatedFormat('j M Y, H.i') ?? '-' }} WIB
                            </div>

                            <div class="text-secondary small">
                                Kategori: {{ $categoryLabel }}<br>
                                Sumber: aplikasi warga
                            </div>

                        </div>
                    </div>

                    <div class="card border shadow-sm rounded-4">

                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-3">Eskalasi &amp; koordinasi</h5>

                            @if ($isBegal)

                                <p class="text-secondary">
                                    Untuk laporan kriminalitas atau begal, koordinasikan penanganan
                                    dengan kepolisian yang berwenang. Pilih prioritas Mendesak jika
                                    kondisi membutuhkan penanganan segera.
                                </p>

                            @else

                                <p class="text-secondary">
                                    Untuk laporan {{ $categoryLabel }}, pilih instansi teknis atau
                                    penanggung jawab yang sesuai dengan kewenangannya. Pastikan
                                    instruksi penugasan menjelaskan tindakan yang perlu dilakukan
                                    di lapangan.
                                </p>

                            @endif

                            <p class="text-secondary small mb-0">
                                Pastikan jalur koordinasi sesuai dengan kategori laporan.
                            </p>

                        </div>
                    </div>

                </div>

            </div>

        </form>

    {{-- LAPORAN SUDAH DIVERIFIKASI DAN DITUGASKAN --}}
    @elseif ($report->status === 'verified' && $activeAssignment)

        <div class="row g-3">

            <div class="col-lg-8">

                <div class="card border shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Penugasan berhasil dibuat</h5>

                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Petugas sudah ditugaskan. Laporan menunggu proses penanganan lapangan.
                        </div>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <small class="text-secondary">Instansi</small>
                                <div class="fw-semibold">
                                    {{ $activeAssignment->institution_name ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-secondary">Tim</small>
                                <div class="fw-semibold">
                                    {{ $activeAssignment->team?->name ?? $activeAssignment->team_name ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-secondary">Anggota Tim</small>
                                <div class="fw-semibold">
                                    {{ $activeAssignment->team?->members?->pluck('name')->join(', ') ?: ($activeAssignment->officer?->name ?? '-') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-secondary">Prioritas</small>
                                <div class="fw-semibold">
                                    {{ $priorityLabels[$priority] ?? ucfirst($priority) }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <small class="text-secondary">Batas penyelesaian</small>
                                <div class="fw-semibold">
                                    {{ $activeAssignment->due_at?->translatedFormat('j M Y, H.i') ?? '-' }}
                                </div>
                            </div>

                            <div class="col-12">
                                <small class="text-secondary">Instruksi internal</small>
                                <p class="mb-0">{{ $activeAssignment->note ?? '-' }}</p>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card border shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Ringkasan laporan</h5>

                        <div class="fw-semibold">
                            {{ $report->tracking_code }}<br>
                            {{ $categoryLabel }}
                        </div>

                        <p class="text-secondary mt-3 mb-0">
                            {{ $report->location_name ?? 'Lokasi tidak dicantumkan' }}
                        </p>

                    </div>
                </div>

            </div>

        </div>

    {{-- TAHAP 3: PENANGANAN BERLANGSUNG --}}
    @elseif ($report->status === 'investigating')

        <div class="row g-3">

            <div class="col-lg-8">

                <div class="card border shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Penugasan sedang berjalan</h5>

                        @if ($activeAssignment)

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <small class="text-secondary">Instansi</small>
                                    <div class="fw-semibold">
                                        {{ $activeAssignment->institution_name ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-secondary">Tim</small>
                                    <div class="fw-semibold">
                                        {{ $activeAssignment->team?->name ?? $activeAssignment->team_name ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-secondary">Anggota Tim</small>
                                    <div class="fw-semibold">
                                        {{ $activeAssignment->team?->members?->pluck('name')->join(', ') ?: ($activeAssignment->officer?->name ?? '-') }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-secondary">Prioritas</small>
                                    <div class="fw-semibold">
                                        {{ $priorityLabels[$priority] ?? ucfirst($priority) }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-secondary">Batas penyelesaian</small>
                                    <div class="fw-semibold">
                                        {{ $activeAssignment->due_at?->translatedFormat('j M Y, H.i') ?? '-' }}
                                    </div>
                                </div>

                                <div class="col-12">
                                    <small class="text-secondary">Instruksi internal</small>
                                    <p class="mb-0">{{ $activeAssignment->note ?? '-' }}</p>
                                </div>

                            </div>

                        @else

                            <p class="text-secondary mb-0">
                                Status laporan sedang ditangani, tetapi penugasan aktif tidak ditemukan.
                            </p>

                        @endif

                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card border shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Ringkasan laporan</h5>

                        <div class="fw-semibold">
                            {{ $report->tracking_code }}<br>
                            {{ $categoryLabel }}
                        </div>

                        <p class="text-secondary mt-3 mb-0">
                            {{ $report->location_name ?? 'Lokasi tidak dicantumkan' }}
                        </p>

                    </div>
                </div>

            </div>

        </div>

    {{-- STATUS AKHIR --}}
    @else

        <div class="card border shadow-sm rounded-4">

            <div class="card-body p-4">

                @if ($report->status === 'resolved')

                    <h5 class="fw-bold text-success">
                        <i class="bi bi-check-circle me-2"></i>
                        Laporan telah diselesaikan
                    </h5>

                    <p class="text-secondary mb-0">
                        Penanganan laporan {{ $report->tracking_code }} telah selesai.
                    </p>

                @else

                    <h5 class="fw-bold text-danger">
                        <i class="bi bi-x-circle me-2"></i>
                        Laporan ditolak
                    </h5>

                    <p class="text-secondary mb-0">
                        Laporan {{ $report->tracking_code }} tidak dilanjutkan ke tahap penugasan.
                    </p>

                    @if ($latestVerification?->note)

                        <hr>

                        <small class="text-secondary">Catatan penolakan</small>
                        <p class="mb-0">{{ $latestVerification->note }}</p>

                    @endif

                @endif

            </div>
        </div>

    @endif

    {{-- JEJAK AUDIT --}}
    <div class="card border shadow-sm rounded-4 mt-3">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">Alur penanganan &amp; jejak audit</h5>

            <div class="d-flex flex-wrap align-items-center gap-2 small fw-semibold mb-3">
                <span>Masuk</span>
                <i class="bi bi-arrow-right"></i>
                <span>Menunggu verifikasi</span>
                <i class="bi bi-arrow-right"></i>
                <span>Diverifikasi</span>
                <i class="bi bi-arrow-right"></i>
                <span>Diteruskan</span>
                <i class="bi bi-arrow-right"></i>
                <span>Ditangani</span>
                <i class="bi bi-arrow-right"></i>
                <span>Selesai</span>
            </div>

            <div class="small text-secondary">

                <div class="mb-2">
                    {{ $report->created_at?->format('j M, H.i') }}
                    <span class="mx-1">•</span>
                    Laporan diterima sistem
                </div>

                @forelse ($report->updates->sortBy('created_at') as $update)

                    @php
                        $actionLabels = [
                            'verify' => 'Laporan diverifikasi',
                            'request_info' => 'Informasi tambahan diminta',
                            'mark_duplicate' => 'Laporan ditandai sebagai duplikat',
                            'reject' => 'Laporan ditolak',
                            'status_change' => 'Status laporan diperbarui',
                        ];
                    @endphp

                    <div class="mb-2">

                        {{ $update->created_at?->format('j M, H.i') }}

                        <span class="mx-1">•</span>

                        {{ $actionLabels[$update->action] ?? 'Status: ' . ucfirst($update->old_status ?? '-') . ' → ' . ucfirst($update->new_status) }}

                        <span class="mx-1">•</span>

                        {{ $update->updater?->name ?? 'Sistem' }}

                        @if ($update->note)

                            <div class="ms-3 mt-1">
                                Catatan: {{ $update->note }}
                            </div>

                        @endif

                    </div>

                @empty

                    <div>
                        Belum ada aktivitas verifikasi atau perubahan status.
                    </div>

                @endforelse

            </div>

        </div>
    </div>

    {{-- RIWAYAT PENUGASAN --}}
    <div class="card border shadow-sm rounded-4 mt-3">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">Riwayat penugasan</h5>

            @forelse ($report->assignments->sortByDesc('assigned_at') as $assignment)

                <div class="border-bottom pb-3 mb-3">

                    <div class="d-flex justify-content-between flex-wrap gap-2">

                        <strong>
                            @if ($assignment->assignment_type === 'inspection')
                                Inspeksi lapangan
                            @else
                                {{ $assignment->team?->name ?? $assignment->team_name ?? 'Tim tidak tersedia' }}
                            @endif
                        </strong>

                        <span class="badge bg-{{ $assignment->ended_at ? 'secondary' : 'success' }}">
                            {{ $assignment->ended_at ? 'Selesai' : 'Aktif' }}
                        </span>

                    </div>

                    <small class="text-secondary">

                        @if ($assignment->assignment_type === 'inspection')

                            Officer:
                            {{ $assignment->officer?->name ?? 'Officer tidak tersedia' }}

                        @else

                            {{ $assignment->institution_name }}
                            ·
                            {{ $assignment->team?->members?->pluck('name')->join(', ') ?: ($assignment->officer?->name ?? 'Anggota tidak tersedia') }}

                        @endif

                    </small>

                    <br>

                    <small class="text-secondary">
                        Ditugaskan oleh {{ $assignment->assigner?->name ?? 'Tidak diketahui' }}
                        · {{ $assignment->assigned_at?->format('d/m/Y H:i') ?? '-' }}
                    </small>

                    @if ($assignment->ended_at)

                        <div class="small text-secondary">
                            Berakhir: {{ $assignment->ended_at->format('d/m/Y H:i') }}
                        </div>

                    @endif

                    @if ($assignment->note)

                        <div class="small mt-1">
                            Instruksi: {{ $assignment->note }}
                        </div>

                    @endif

                </div>

            @empty

                <p class="text-secondary mb-0">
                    Belum ada riwayat penugasan.
                </p>

            @endforelse

        </div>
    </div>

    <div class="small text-secondary mt-4">
        <i class="bi bi-shield-lock me-1"></i>
        Privasi: identitas warga disamarkan. Buka data kontak hanya dengan izin dan alasan akses yang tercatat.
    </div>

</div>
@endsection

{{-- WARNA TOMBOL --}}
@push('styles')
<style>
    .btn-primary-dark {
        background-color: #020d32;
        border-color: #020d32;
        color: #fff;
    }

    .btn-primary-dark:hover,
    .btn-primary-dark:focus,
    .btn-primary-dark:active {
        background-color: #06194d;
        border-color: #06194d;
        color: #fff;
    }

    .btn-outline-darkblue {
        background-color: transparent;
        border: 1px solid #020d32;
        color: #020d32;
    }

    .btn-outline-darkblue:hover,
    .btn-outline-darkblue:focus,
    .btn-outline-darkblue:active {
        background-color: #020d32;
        border-color: #020d32;
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Kontrol penandaan duplikat.
    const duplicateButton = document.getElementById('duplicateButton');
    const duplicateWrap = document.getElementById('duplicateSelectWrap');
    const duplicateSelect = document.getElementById('duplicate_of_report_id');
    const verificationForm = document.getElementById('verificationForm');

    if (duplicateButton && duplicateWrap && duplicateSelect && verificationForm) {

        duplicateButton.addEventListener('click', function () {

            duplicateWrap.classList.remove('d-none');
            duplicateSelect.required = true;
            duplicateSelect.focus();

            if (!document.getElementById('confirmDuplicateButton')) {

                const confirmButton = document.createElement('button');

                confirmButton.type = 'submit';
                confirmButton.name = 'action';
                confirmButton.value = 'mark_duplicate';
                confirmButton.id = 'confirmDuplicateButton';
                confirmButton.className = 'btn btn-warning w-100 rounded-3 mt-2';
                confirmButton.textContent = 'Konfirmasi duplikat';

                duplicateWrap.appendChild(confirmButton);

            }

        });

        verificationForm.addEventListener('submit', function (event) {

            const isDuplicate = event.submitter
                && event.submitter.value === 'mark_duplicate';

            duplicateSelect.required = Boolean(isDuplicate);

            if (isDuplicate && !duplicateSelect.value) {
                event.preventDefault();
                duplicateSelect.reportValidity();
            }

        });

    }

    // Wizard penugasan tim.
    const assignmentForm = document.getElementById('inlineAssignmentForm');

    if (!assignmentForm) return;

    let currentStep = 1;

    const steps = [
        document.getElementById('assignmentStep1'),
        document.getElementById('assignmentStep2'),
        document.getElementById('assignmentStep3')
    ];

    const previous = document.getElementById('assignmentPrevious');
    const next = document.getElementById('assignmentNext');
    const submit = document.getElementById('assignmentSubmit');

    function showStep(step) {

        currentStep = step;

        steps.forEach((element, index) => {
            element.classList.toggle('d-none', index + 1 !== step);
        });

        previous.disabled = step === 1;
        next.classList.toggle('d-none', step === 3);
        submit.classList.toggle('d-none', step !== 3);

    }

    function validateAssignment() {

        const fields = [
            'team_id',
            'priority',
            'due_at',
            'assignment_note'
        ];

        const patrolDuration = document.getElementById('patrol_duration_days');

        if (patrolDuration) {
            fields.push('patrol_duration_days');
        }

        for (const id of fields) {

            const field = document.getElementById(id);

            if (!field || !field.checkValidity()) {

                field?.reportValidity();
                return false;

            }

        }

        return true;

    }

    const teamSelect = document.getElementById('team_id');

    if (teamSelect) {

        teamSelect.addEventListener('change', () => {

            const option = teamSelect.options[teamSelect.selectedIndex];

            document.getElementById('teamMembersPreview').textContent =
                option?.dataset.members
                    ? 'Anggota: ' + option.dataset.members
                    : 'Pilih tim untuk melihat anggota yang akan menerima tugas.';

            const institution = document.getElementById('institution_name');

            if (option?.dataset.institution && !institution.value) {
                institution.value = option.dataset.institution;
            }

        });

        teamSelect.dispatchEvent(new Event('change'));

    }

    function updateReview() {

        const team = document.getElementById('team_id');
        const priority = document.getElementById('priority');

        document.getElementById('reviewInstitution').textContent =
            document.getElementById('institution_name').value || '-';

        document.getElementById('reviewTeam').textContent =
            team.options[team.selectedIndex]?.text || '-';

        document.getElementById('reviewOfficer').textContent =
            team.options[team.selectedIndex]?.dataset.members || '-';

        document.getElementById('reviewPriority').textContent =
            priority.options[priority.selectedIndex]?.text || '-';

        const due = document.getElementById('due_at').value;

        document.getElementById('reviewDue').textContent =
            due ? new Date(due).toLocaleString('id-ID') : '-';

        document.getElementById('reviewNote').textContent =
            document.getElementById('assignment_note').value || '-';

        const patrolDuration = document.getElementById('patrol_duration_days');
        const reviewPatrol = document.getElementById('reviewPatrolDuration');

        if (patrolDuration && reviewPatrol) {

            reviewPatrol.textContent =
                patrolDuration.options[patrolDuration.selectedIndex]?.text || '-';

        }

    }

    next.addEventListener('click', function () {

        if (currentStep === 1) {
            showStep(2);
            return;
        }

        if (currentStep === 2 && validateAssignment()) {
            updateReview();
            showStep(3);
        }

    });

    previous.addEventListener('click', function () {

        if (currentStep > 1) {
            showStep(currentStep - 1);
        }

    });

    showStep(1);

});
</script>
@endpush
