@extends('layouts.governance')



@section('title', 'Detail & Verifikasi Laporan')

@section('breadcrumb', 'Detail & verifikasi laporan')



@section('content')

@php

    $reportCategory = strtolower(trim($report->category ?? ''));

    $isBegal = str_contains($reportCategory, 'begal');

    $isDark = str_contains($reportCategory, 'dark');

    $statusLabels = [

        'pending' => 'Menunggu verifikasi',

        'verified' => 'Terverifikasi',

        'investigating' => 'Sedang ditangani',

        'resolved' => 'Selesai',

        'rejected' => 'Ditolak',

    ];

    $statusColors = [

        'pending' => 'warning',

        'verified' => 'info',

        'investigating' => 'primary',

        'resolved' => 'success',

        'rejected' => 'danger',

    ];

    $priority = $report->activeAssignment?->priority ?? 'normal';

    $priorityLabels = ['normal' => 'Normal', 'high' => 'Tinggi', 'urgent' => 'Mendesak'];

    $latestVerification = $report->updates->whereIn('action', ['verify', 'request_info', 'mark_duplicate', 'reject'])->sortByDesc('created_at')->first();

    $verificationUpdate = $report->updates->where('new_status', 'verified')->sortByDesc('created_at')->first();

    $activeAssignment = $report->activeAssignment;

@endphp



<div class="container-fluid px-0">

    {{-- Header tetap sama pada semua tahapan --}}

    <div class="mb-4">

        <h2 class="fw-bold mb-1">Detail &amp; verifikasi laporan</h2>

        <p class="text-secondary mb-3">Manajemen Laporan / {{ $report->tracking_code }}</p>

        <div class="d-flex flex-wrap align-items-center gap-3">

            <a href="{{ route('governance.reports.index') }}" class="btn btn-outline-secondary px-3">

                <i class="bi bi-arrow-left me-1"></i> Kembali ke daftar

            </a>

            <span class="badge rounded-3 bg-{{ $statusColors[$report->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$report->status] ?? 'secondary' }} px-3 py-3">

                {{ $statusLabels[$report->status] ?? ucfirst($report->status) }}

            </span>

            <span class="badge rounded-3 bg-primary-subtle text-primary px-3 py-3">

                Prioritas: {{ $priorityLabels[$priority] ?? ucfirst($priority) }}

            </span>

        </div>

    </div>



    @if (session('success'))

        <div class="alert alert-success alert-dismissible fade show" role="alert">

            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

        </div>

    @endif

    @if ($errors->any())

        <div class="alert alert-danger">

            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>

        </div>

    @endif



    {{-- Stepper mengikuti status laporan --}}

    @if (in_array($report->status, ['verified', 'investigating', 'resolved']))

        <div class="row g-3 mb-3">

            <div class="col-md-4">

                <div class="rounded-3 bg-dark text-white text-center py-2 px-3 fw-semibold">

                    <i class="bi bi-check-circle me-1"></i> 1&nbsp; Verifikasi selesai

                </div>

            </div>

            <div class="col-md-4">

                <div class="rounded-3 {{ in_array($report->status, ['verified', 'investigating', 'resolved']) ? 'bg-dark text-white' : 'border bg-white text-secondary' }} text-center py-2 px-3 fw-semibold">

                    <i class="bi bi-{{ in_array($report->status, ['investigating', 'resolved']) ? 'check-circle' : 'people' }} me-1"></i> 2&nbsp; {{ $activeAssignment ? 'Petugas ditugaskan' : 'Pilih instansi & petugas' }}

                </div>

            </div>

            <div class="col-md-4">

                <div class="rounded-3 {{ in_array($report->status, ['investigating', 'resolved']) ? 'bg-dark text-white' : 'border bg-white text-secondary' }} text-center py-2 px-3 fw-semibold">

                    3&nbsp; {{ $report->status === 'resolved' ? 'Penanganan selesai' : ($report->status === 'investigating' ? 'Penanganan berlangsung' : 'Konfirmasi penugasan') }}

                </div>

            </div>

        </div>

    @elseif ($report->status === 'pending')

        <div class="row g-3 mb-3">

            <div class="col-md-4"><div class="rounded-3 bg-dark text-white text-center py-2 px-3 fw-semibold">1&nbsp; Verifikasi laporan</div></div>

            <div class="col-md-4"><div class="rounded-3 border bg-white text-secondary text-center py-2 px-3 fw-semibold">2&nbsp; Pilih instansi &amp; petugas</div></div>

            <div class="col-md-4"><div class="rounded-3 border bg-white text-secondary text-center py-2 px-3 fw-semibold">3&nbsp; Penanganan</div></div>

        </div>

    @else

        <div class="alert alert-danger mb-3"><i class="bi bi-x-circle me-2"></i>Laporan ditolak. Proses verifikasi dan penugasan tidak dapat dilanjutkan.</div>

    @endif



    {{-- TAHAP 1: VERIFIKASI --}}

    @if ($report->status === 'pending')

        <div class="row g-3 align-items-stretch">

            <div class="col-xl-7">

                <div class="card border shadow-sm rounded-4 mb-3">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">{{ $report->category === 'dark' ? 'Penerangan jalan / area gelap' : 'Laporan begal' }}</h5>

                        <div class="small text-secondary mb-3"><span class="fw-semibold text-dark">{{ $report->tracking_code }}</span> <span class="mx-1">•</span>{{ $report->category === 'dark' ? 'PJU / area gelap' : 'Keamanan / begal' }} <span class="mx-1">•</span>Aplikasi warga</div>

                        <p class="text-secondary mb-4" style="white-space: pre-line">{{ $report->description }}</p>

                        <div class="small text-secondary">

                            <div>Kejadian: {{ $report->incident_at?->translatedFormat('j F Y, H.i') ?? 'Tidak dicantumkan' }} WIB</div>

                            <div>Dikirim: {{ $report->created_at?->translatedFormat('j F Y, H.i') }} WIB</div>

                            <div>Lokasi: {{ $report->location_name ?? 'Tidak dicantumkan' }}</div>

                            <div>Koordinat: {{ $report->latitude ?? '-' }}, {{ $report->longitude ?? '-' }}</div>

                            @if($report->latitude !== null && $report->longitude !== null)

                                <a class="d-inline-block mt-2" href="https\://www\.openstreetmap.org/?mlat={{ $report->latitude }}&mlon={{ $report->longitude }}#map=17/{{ $report->latitude }}/{{ $report->longitude }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-geo-alt me-1"></i>Lihat lokasi di OpenStreetMap</a>

                            @endif

                        </div>

                        <hr class="my-3">

                        <div class="fw-semibold small mb-2">Lampiran foto</div>

                        @forelse($report->images as $image)

                            <a href="{{ Storage::disk('public')->url($image->image_path) }}" target="_blank" rel="noopener noreferrer" class="d-inline-block me-2 mb-2"><img src="{{ Storage::disk('public')->url($image->image_path) }}" alt="Foto bukti laporan" class="img-thumbnail" style="width:125px;height:95px;object-fit:cover;"></a>

                        @empty

                            <span class="small text-secondary">Tidak ada foto yang disertakan.</span>

                        @endforelse

                    </div>

                </div>

                <div class="card border shadow-sm rounded-4">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Laporan serupa di sekitar lokasi</h5>

                        @forelse($similarReports as $similar)

                            <div class="d-flex justify-content-between align-items-start gap-3 border-bottom pb-3 mb-3">

                                <div>

                                    <div class="fw-semibold">{{ $similar->tracking_code ?? 'Laporan #' . $similar->id }} <span class="text-secondary fw-normal">• {{ ucfirst($similar->category) }}</span></div>

                                    <div class="small text-secondary">{{ $similar->location_name ?? 'Lokasi tidak dicantumkan' }}</div>

                                    <div class="small text-secondary">{{ $similar->created_at?->translatedFormat('j M Y, H.i') }} WIB · {{ number_format($similar->distance_meters, 0, ',', '.') }} m dari lokasi</div>

                                </div>

                                <a href="{{ route('governance.reports.show', $similar) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Lihat laporan</a>

                            </div>

                        @empty

                            <p class="text-secondary mb-0">Belum ditemukan laporan lain dalam radius 2 km dari lokasi ini.</p>

                        @endforelse

                    </div>

                </div>

            </div>



            <div class="col-xl-5">

                <div class="card border shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Tinjauan verifikasi</h5>

                        <div class="small text-secondary mb-4">Verifikator: <span class="text-dark fw-semibold">{{ auth()->user()->name }}</span><br>Waktu verifikasi: Belum diverifikasi</div>

                        <form action="{{ route('governance.reports.update-status', $report) }}" method="POST" id="verificationForm">

                            @csrf

                            @method('PATCH')

                            <div class="mb-2">

                                <label for="note" class="form-label fw-semibold small">Catatan internal <span class="text-danger">*</span></label>

                                <textarea name="note" id="note" class="form-control rounded-3" rows="4" maxlength="1000" placeholder="Tuliskan hasil pemeriksaan laporan..." required>{{ old('note') }}</textarea>

                                <div class="form-text">Catatan ini hanya untuk keperluan internal dan tidak ditampilkan kepada warga.</div>

                            </div>

                            <div class="mb-3 d-none" id="duplicateSelectWrap">

                                <label for="duplicate_of_report_id" class="form-label fw-semibold small">Laporan yang dianggap duplikat</label>

                                <select name="duplicate_of_report_id" id="duplicate_of_report_id" class="form-select"><option value="">Pilih laporan pembanding</option>@foreach($similarReports as $similar)<option value="{{ $similar->id }}">{{ $similar->tracking_code }} — {{ $similar->location_name ?? 'Lokasi tidak dicantumkan' }}</option>@endforeach</select>

                            </div>

                            <button type="submit" name="action" value="verify" class="btn btn-dark w-100 rounded-3 py-2 mb-2" onclick="return confirm('Verifikasi laporan ini?')"><i class="bi bi-check-circle me-1"></i>Verifikasi laporan</button>

                            <button type="submit" name="action" value="request_info" class="btn btn-outline-secondary w-100 rounded-3 mb-2">Minta informasi tambahan</button>

                            <div class="row g-2">

                                <div class="col-6"><button type="button" id="duplicateButton" class="btn btn-outline-secondary w-100 rounded-3">Tandai duplikat</button></div>

                                <div class="col-6"><button type="submit" name="action" value="reject" class="btn btn-danger-subtle text-danger w-100 rounded-3" onclick="return confirm('Tolak laporan ini?')">Tolak laporan</button></div>

                            </div>

                            <div class="small text-secondary mt-3">Alasan wajib dicatat untuk penolakan, duplikat, dan permintaan informasi tambahan.</div>

                        </form>

                    </div>

                </div>

            </div>

        </div>



    {{-- TAHAP 2: PENUGASAN (TETAP DI HALAMAN DETAIL YANG SAMA) --}}

    @elseif ($report->status === 'verified' && !$activeAssignment)

        <form action="{{ route('governance.reports.assign', $report) }}" method="POST" id="inlineAssignmentForm">

            @csrf

            <div class="row g-3 align-items-stretch">

                <div class="col-lg-8">

                    <div class="card border shadow-sm rounded-4 h-100"><div class="card-body p-4">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div><h5 class="fw-bold mb-1">Rincian penugasan</h5><p class="small text-secondary mb-0">Tentukan instansi dan petugas yang bertanggung jawab.</p></div>

                            <span class="badge bg-success-subtle text-success">Verifikasi selesai</span>

                        </div>

                        <div id="assignmentStep1">

                            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Laporan {{ $report->tracking_code }} sudah diverifikasi dan siap ditugaskan.</div>

                            <div class="row g-3">

                                <div class="col-md-6"><small class="text-secondary">Kategori</small><div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $report->category)) }}</div></div>

                                <div class="col-md-6"><small class="text-secondary">Lokasi</small><div class="fw-semibold">{{ $report->location_name ?? 'Tidak dicantumkan' }}</div></div>

                                <div class="col-12"><small class="text-secondary">Deskripsi laporan</small><div>{{ $report->description }}</div></div>

                            </div>

                        </div>

                        <div id="assignmentStep2" class="d-none">

                            <div class="mb-3"><label for="institution_name" class="form-label fw-semibold">Instansi penanggung jawab <span class="text-danger">*</span></label><input type="text" name="institution_name" id="institution_name" class="form-control form-control-lg" maxlength="150" list="institutionOptions" placeholder="Contoh: Dinas Perhubungan" value="{{ old('institution_name') }}" required><datalist id="institutionOptions"><option value="Dinas Perhubungan"><option value="Dinas Pekerjaan Umum"><option value="Kepolisian"></datalist></div>

                            <div class="mb-3"><label for="team_name" class="form-label fw-semibold">Petugas / tim lapangan <span class="text-danger">*</span></label><input type="text" name="team_name" id="team_name" class="form-control form-control-lg" maxlength="150" placeholder="Contoh: Tim Lapangan Medan" value="{{ old('team_name') }}" required></div>

                            <div class="mb-3"><label for="officer_id" class="form-label fw-semibold">Petugas yang ditugaskan <span class="text-danger">*</span></label><select name="officer_id" id="officer_id" class="form-select form-select-lg" required><option value="">Pilih petugas</option>@foreach($officers as $officer)<option value="{{ $officer->id }}" @selected(old('officer_id') == $officer->id)>{{ $officer->name }} · {{ $officer->email }}</option>@endforeach</select></div>

                            <div class="row g-3 mb-3">

                                <div class="col-md-6"><label for="priority" class="form-label fw-semibold">Prioritas <span class="text-danger">*</span></label><select name="priority" id="priority" class="form-select form-select-lg" required><option value="normal" @selected(old('priority', 'normal') === 'normal')>Normal</option><option value="high" @selected(old('priority') === 'high')>Tinggi</option><option value="urgent" @selected(old('priority') === 'urgent')>Urgen</option></select></div>

                                <div class="col-md-6"><label for="due_at" class="form-label fw-semibold">Batas penyelesaian <span class="text-danger">*</span></label><input type="datetime-local" name="due_at" id="due_at" class="form-control form-control-lg" value="{{ old('due_at') }}" min="{{ now()->format('Y-m-d\\\TH:i') }}" required></div>

                            </div>

                            @if ($isBegal)
                            <div class="mb-3">
                                <label for="patrol_duration_days" class="form-label fw-semibold">Periode patroli <span class="text-danger">*</span></label>
                                <select name="patrol_duration_days" id="patrol_duration_days" class="form-select form-select-lg" required>
                                    <option value="">Pilih periode patroli</option>
                                    <option value="1" @selected(old('patrol_duration_days') == '1')>1 hari</option>
                                    <option value="3" @selected(old('patrol_duration_days') == '3')>3 hari</option>
                                    <option value="7" @selected(old('patrol_duration_days') == '7')>7 hari</option>
                                    <option value="14" @selected(old('patrol_duration_days') == '14')>14 hari</option>
                                    <option value="30" @selected(old('patrol_duration_days') == '30')>30 hari</option>
                                </select>
                                <div class="form-text">Patroli dilakukan secara berkala selama periode yang dipilih.</div>
                            </div>
                        @endif
                        <div class="mb-3"><label for="assignment_note" class="form-label fw-semibold">Instruksi internal <span class="text-danger">*</span></label><textarea name="note" id="assignment_note" rows="4" maxlength="1000" class="form-control" placeholder="Tuliskan instruksi untuk petugas..." required>{{ old('note') }}</textarea></div>

                            <p class="small text-secondary mb-0">Perubahan instansi, petugas, prioritas, dan tenggat akan tercatat dalam riwayat penugasan.</p>

                        </div>

                        <div id="assignmentStep3" class="d-none">

                            <h6 class="fw-bold mb-3">Konfirmasi penugasan</h6>

                            <div class="border rounded-3 p-3 mb-3">

                                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Laporan</span><strong>{{ $report->tracking_code }}</strong></div>

                                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Instansi</span><strong id="reviewInstitution">-</strong></div>

                                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Tim</span><strong id="reviewTeam">-</strong></div>

                                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Petugas</span><strong id="reviewOfficer">-</strong></div>

                                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Prioritas</span><strong id="reviewPriority">-</strong></div>

                                @if ($isBegal)

                                    <div class="d-flex justify-content-between mb-2">

                                        <span class="text-secondary">Periode patroli</span>

                                        <strong id="reviewPatrolDuration">-</strong>

                                    </div>

                                @endif

                                <div class="d-flex justify-content-between"><span class="text-secondary">Batas penyelesaian</span><strong id="reviewDue">-</strong></div>

                                <hr><small class="text-secondary">Instruksi internal</small><p class="mb-0 mt-1" id="reviewNote">-</p>

                            </div>

                            <div class="alert alert-warning mb-0">Pastikan seluruh informasi sudah benar sebelum menyimpan penugasan.</div>

                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">

                            <button type="button" class="btn btn-outline-secondary" id="assignmentPrevious" disabled><i class="bi bi-arrow-left me-1"></i>Kembali</button>

                            <div class="d-flex gap-2"><button type="button" class="btn btn-dark" id="assignmentNext">Lanjut<i class="bi bi-arrow-right ms-1"></i></button><button type="submit" class="btn btn-success d-none" id="assignmentSubmit"><i class="bi bi-check-circle me-1"></i>Simpan &amp; kirim penugasan</button></div>

                        </div>

                    </div></div>

                </div>

                <div class="col-lg-4">

                    <div class="card border shadow-sm rounded-4 mb-3"><div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Ringkasan laporan</h5>

                        <div class="fw-semibold mb-3">{{ $report->tracking_code }}<br>{{ ucfirst(str_replace('_', ' ', $report->category)) }}<br>{{ $report->location_name ?? 'Lokasi tidak dicantumkan' }}</div>

                        <div class="text-secondary small mb-3">Terverifikasi oleh {{ $verificationUpdate?->updater?->name ?? auth()->user()->name }}<br>{{ $verificationUpdate?->created_at?->translatedFormat('j M Y, H.i') ?? '-' }} WIB</div>

                        <div class="text-secondary small">Kategori: {{ ucfirst(str_replace('_', ' ', $report->category)) }}<br>Sumber: aplikasi warga</div>

                    </div></div>

                    <div class="card border shadow-sm rounded-4 h-auto"><div class="card-body p-4">

                        <h5 class="fw-bold mb-3">Eskalasi &amp; koordinasi</h5>

                        @if($report->category === 'begal')

                            <p class="text-secondary">Untuk laporan kriminalitas atau begal, koordinasikan penanganan dengan kepolisian yang berwenang. Pilih prioritas Urgen jika kondisi membutuhkan penanganan segera.</p>

                        @else

                            <p class="text-secondary">Untuk laporan fasilitas dan penerangan jalan, pilih instansi teknis yang sesuai dengan kewenangannya. Pastikan instruksi penugasan menjelaskan tindakan yang perlu dilakukan di lapangan.</p>

                        @endif

                        <p class="text-secondary small mb-0">Pastikan jalur koordinasi sesuai dengan kategori laporan.</p>

                    </div></div>

                </div>

            </div>

        </form>



    {{-- Laporan sudah diverifikasi dan penugasan sudah tersimpan --}}

    @elseif ($report->status === 'verified' && $activeAssignment)

        <div class="row g-3">

            <div class="col-lg-8">

                <div class="card border shadow-sm rounded-4"><div class="card-body p-4">

                    <h5 class="fw-bold mb-3">Penugasan berhasil dibuat</h5>

                    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Petugas sudah ditugaskan. Laporan menunggu proses penanganan lapangan.</div>

                    <div class="row g-3">

                        <div class="col-md-6"><small class="text-secondary">Instansi</small><div class="fw-semibold">{{ $activeAssignment->institution_name ?? '-' }}</div></div>

                        <div class="col-md-6"><small class="text-secondary">Tim</small><div class="fw-semibold">{{ $activeAssignment->team_name ?? '-' }}</div></div>

                        <div class="col-md-6"><small class="text-secondary">Petugas</small><div class="fw-semibold">{{ $activeAssignment->officer?->name ?? '-' }}</div></div>

                        <div class="col-md-6"><small class="text-secondary">Prioritas</small><div class="fw-semibold">{{ $priorityLabels[$priority] ?? ucfirst($priority) }}</div></div>

                        @if ($isBegal)

                            <div class="mb-3">

                                <label for="patrol_duration_days" class="form-label fw-semibold">

                                    Periode patroli <span class="text-danger">*</span>

                                </label>



                                <select

                                    name="patrol_duration_days"

                                    id="patrol_duration_days"

                                    class="form-select"

                                    required

                                >

                                    <option value="">Pilih periode patroli</option>

                                    <option value="1" @selected(old('patrol_duration_days') == '1')>1 hari</option>

                                    <option value="3" @selected(old('patrol_duration_days') == '3')>3 hari</option>

                                    <option value="7" @selected(old('patrol_duration_days') == '7')>7 hari</option>

                                    <option value="14" @selected(old('patrol_duration_days') == '14')>14 hari</option>

                                    <option value="30" @selected(old('patrol_duration_days') == '30')>30 hari</option>

                                </select>



                                <div class="form-text">

                                    Petugas melakukan patroli berkala selama periode yang ditentukan.

                                </div>

                            </div>

                        @endif

                        <div class="col-md-6"><small class="text-secondary">Batas penyelesaian</small><div class="fw-semibold">{{ $activeAssignment->due_at?->translatedFormat('j M Y, H.i') ?? '-' }}</div></div>

                        <div class="col-12"><small class="text-secondary">Instruksi internal</small><p class="mb-0">{{ $activeAssignment->note ?? '-' }}</p></div>

                    </div>

                </div></div>

            </div>

            <div class="col-lg-4"><div class="card border shadow-sm rounded-4 h-100"><div class="card-body p-4"><h5 class="fw-bold mb-3">Ringkasan laporan</h5><div class="fw-semibold">{{ $report->tracking_code }}<br>{{ ucfirst(str_replace('_', ' ', $report->category)) }}</div><p class="text-secondary mt-3 mb-0">{{ $report->location_name ?? 'Lokasi tidak dicantumkan' }}</p></div></div></div>

        </div>



    {{-- TAHAP 3: PENANGANAN BERLANGSUNG --}}

    @elseif ($report->status === 'investigating')

        <div class="row g-3">

            <div class="col-lg-8">

                <div class="card border shadow-sm rounded-4"><div class="card-body p-4">

                    <h5 class="fw-bold mb-3">Penugasan sedang berjalan</h5>

                    @if($activeAssignment)

                        <div class="row g-3">

                            <div class="col-md-6"><small class="text-secondary">Instansi</small><div class="fw-semibold">{{ $activeAssignment->institution_name ?? '-' }}</div></div>

                            <div class="col-md-6"><small class="text-secondary">Tim</small><div class="fw-semibold">{{ $activeAssignment->team_name ?? '-' }}</div></div>

                            <div class="col-md-6"><small class="text-secondary">Petugas</small><div class="fw-semibold">{{ $activeAssignment->officer?->name ?? '-' }}</div></div>

                            <div class="col-md-6"><small class="text-secondary">Prioritas</small><div class="fw-semibold">{{ $priorityLabels[$priority] ?? ucfirst($priority) }}</div></div>

                            <div class="col-md-6"><small class="text-secondary">Batas penyelesaian</small><div class="fw-semibold">{{ $activeAssignment->due_at?->translatedFormat('j M Y, H.i') ?? '-' }}</div></div>

                            <div class="col-12"><small class="text-secondary">Instruksi internal</small><p class="mb-0">{{ $activeAssignment->note ?? '-' }}</p></div>

                        </div>

                    @else

                        <p class="text-secondary mb-0">Status laporan sedang ditangani, tetapi penugasan aktif tidak ditemukan.</p>

                    @endif

                </div></div>

            </div>

            <div class="col-lg-4"><div class="card border shadow-sm rounded-4 h-100"><div class="card-body p-4"><h5 class="fw-bold mb-3">Ringkasan laporan</h5><div class="fw-semibold">{{ $report->tracking_code }}<br>{{ ucfirst(str_replace('_', ' ', $report->category)) }}</div><p class="text-secondary mt-3 mb-0">{{ $report->location_name ?? 'Lokasi tidak dicantumkan' }}</p></div></div></div>

        </div>



    {{-- STATUS AKHIR --}}

    @else

        <div class="card border shadow-sm rounded-4"><div class="card-body p-4">

            @if($report->status === 'resolved')

                <h5 class="fw-bold text-success"><i class="bi bi-check-circle me-2"></i>Laporan telah diselesaikan</h5>

                <p class="text-secondary mb-0">Penanganan laporan {{ $report->tracking_code }} telah selesai.</p>

            @else

                <h5 class="fw-bold text-danger"><i class="bi bi-x-circle me-2"></i>Laporan ditolak</h5>

                <p class="text-secondary mb-0">Laporan {{ $report->tracking_code }} tidak dilanjutkan ke tahap penugasan.</p>

                @if($latestVerification?->note)<hr><small class="text-secondary">Catatan penolakan</small><p class="mb-0">{{ $latestVerification->note }}</p>@endif

            @endif

        </div></div>

    @endif



    {{-- Jejak audit selalu tampil pada semua status --}}

    <div class="card border shadow-sm rounded-4 mt-3">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">Alur penanganan &amp; jejak audit</h5>

            <div class="d-flex flex-wrap align-items-center gap-2 small fw-semibold mb-3"><span>Masuk</span><i class="bi bi-arrow-right"></i><span>Menunggu verifikasi</span><i class="bi bi-arrow-right"></i><span>Diverifikasi</span><i class="bi bi-arrow-right"></i><span>Diteruskan</span><i class="bi bi-arrow-right"></i><span>Ditangani</span><i class="bi bi-arrow-right"></i><span>Selesai</span></div>

            <div class="small text-secondary">

                <div class="mb-2">{{ $report->created_at?->format('j M, H.i') }} <span class="mx-1">•</span>Laporan diterima sistem</div>

                @forelse($report->updates->sortBy('created_at') as $update)

                    @php $actionLabels = ['verify' => 'Laporan diverifikasi', 'request_info' => 'Informasi tambahan diminta', 'mark_duplicate' => 'Laporan ditandai sebagai duplikat', 'reject' => 'Laporan ditolak', 'status_change' => 'Status laporan diperbarui']; @endphp

                    <div class="mb-2">{{ $update->created_at?->format('j M, H.i') }} <span class="mx-1">•</span>{{ $actionLabels[$update->action] ?? 'Status: ' . ucfirst($update->old_status ?? '-') . ' → ' . ucfirst($update->new_status) }} <span class="mx-1">•</span>{{ $update->updater?->name ?? 'Sistem' }}@if($update->note)<div class="ms-3 mt-1">Catatan: {{ $update->note }}</div>@endif</div>

                @empty

                    <div>Belum ada aktivitas verifikasi atau perubahan status.</div>

                @endforelse

            </div>

        </div>

    </div>



    {{-- Riwayat penugasan tetap tersedia --}}

    <div class="card border shadow-sm rounded-4 mt-3"><div class="card-body p-4">

        <h5 class="fw-bold mb-3">Riwayat penugasan</h5>

        @forelse($report->assignments->sortByDesc('assigned_at') as $assignment)

            <div class="border-bottom pb-3 mb-3">

                <div class="d-flex justify-content-between flex-wrap gap-2"><strong>{{ $assignment->officer?->name ?? 'Petugas tidak tersedia' }}</strong><span class="badge bg-{{ $assignment->ended_at ? 'secondary' : 'success' }}">{{ $assignment->ended_at ? 'Selesai' : 'Aktif' }}</span></div>

                <small class="text-secondary">{{ $assignment->institution_name }} · {{ $assignment->team_name }}</small><br>

                <small class="text-secondary">Ditugaskan oleh {{ $assignment->assigner?->name ?? 'Tidak diketahui' }} · {{ $assignment->assigned_at?->format('d/m/Y H:i') ?? '-' }}</small>

                @if($assignment->ended_at)<div class="small text-secondary">Berakhir: {{ $assignment->ended_at->format('d/m/Y H:i') }}</div>@endif

                @if($assignment->note)<div class="small mt-1">Instruksi: {{ $assignment->note }}</div>@endif

            </div>

        @empty

            <p class="text-secondary mb-0">Belum ada riwayat penugasan.</p>

        @endforelse

    </div></div>



    <div class="small text-secondary mt-4"><i class="bi bi-shield-lock me-1"></i>Privasi: identitas warga disamarkan. Buka data kontak hanya dengan izin dan alasan akses yang tercatat.</div>

</div>

@endsection



@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    // Kontrol penandaan duplikat pada tahap verifikasi.

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

            const isDuplicate = event.submitter && event.submitter.value === 'mark_duplicate';

            duplicateSelect.required = Boolean(isDuplicate);

            if (isDuplicate && !duplicateSelect.value) {

                event.preventDefault();

                duplicateSelect.reportValidity();

            }

        });

    }



    // Wizard penugasan berada di halaman detail yang sama.

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

        steps.forEach((element, index) => element.classList.toggle('d-none', index + 1 !== step));

        previous.disabled = step === 1;

        next.classList.toggle('d-none', step === 3);

        submit.classList.toggle('d-none', step !== 3);

    }



    function validateAssignment() {

        const fields = [

            'institution_name',

            'team_name',

            'officer_id',

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



            if (!field.checkValidity()) {

                field.reportValidity();

                return false;

            }

        }



        return true;

    }



    function updateReview() {

        const officer = document.getElementById('officer_id');

        const priority = document.getElementById('priority');

        document.getElementById('reviewInstitution').textContent = document.getElementById('institution_name').value;

        document.getElementById('reviewTeam').textContent = document.getElementById('team_name').value;

        document.getElementById('reviewOfficer').textContent = officer.options[officer.selectedIndex]?.text || '-';

        document.getElementById('reviewPriority').textContent = priority.options[priority.selectedIndex]?.text || '-';

        const due = document.getElementById('due_at').value;

        document.getElementById('reviewDue').textContent = due ? new Date(due).toLocaleString('id-ID') : '-';

        document.getElementById('reviewNote').textContent = document.getElementById('assignment_note').value;

        const patrolDuration = document.getElementById('patrol_duration_days');

        const reviewPatrol = document.getElementById('reviewPatrolDuration');



        if (patrolDuration && reviewPatrol) {

            reviewPatrol.textContent =

                patrolDuration.options[patrolDuration.selectedIndex]?.text || '-';

        }

    }



    next.addEventListener('click', function () {

        if (currentStep === 1) return showStep(2);

        if (currentStep === 2 && validateAssignment()) {

            updateReview();

            showStep(3);

        }

    });

    previous.addEventListener('click', function () {

        if (currentStep > 1) showStep(currentStep - 1);

    });

    showStep(1);

});

</script>

@endpush
