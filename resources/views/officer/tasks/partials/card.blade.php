
<div class="card border rounded-3 mb-3 shadow-sm">
    <div class="card-body p-3 p-md-4">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">

            <div class="flex-grow-1">
                <div class="fw-bold mb-1">
                    {{ $assignment->report->tracking_code ?? 'TGS-'.$assignment->id }}
                </div>

                <div class="small text-secondary">
                    {{ $assignment->report->category_label }}
                </div>
            </div>

            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
            </span>

        </div>

        {{-- DESCRIPTION --}}
        <p class="mb-3">
            {{ \Illuminate\Support\Str::limit($assignment->report->description, 120) }}
        </p>

        {{-- LOCATION --}}
        <div class="small text-secondary mb-3">
            <i class="bi bi-geo-alt me-1"></i>
            {{ $assignment->report->location_name ?? 'Lokasi belum tersedia' }}
        </div>

        <hr class="my-3">

        {{-- FOOTER INFORMATION --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div class="d-flex flex-wrap gap-3 small">

                <div>
                    <span class="text-secondary">Prioritas</span>
                    <div class="fw-semibold">
                        {{ ucfirst($assignment->priority ?? 'normal') }}
                    </div>
                </div>

                <div>
                    <span class="text-secondary">Tenggat</span>
                    <div class="fw-semibold">
                        {{ $assignment->due_at?->format('d M Y, H:i') ?? 'Belum ditentukan' }}
                    </div>
                </div>

            </div>

            <a href="{{ route('officer.reports.show', $assignment->report_id) }}"
               class="btn btn-navy btn-sm">
                Detail Tugas
                <i class="bi bi-arrow-right ms-1"></i>
            </a>

        </div>

    </div>
</div>
