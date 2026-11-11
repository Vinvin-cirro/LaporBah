<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportAssignmentService
{
    public function assign(
        int $reportId,
        int $officerId,
        ?int $assignedBy = null,
        ?string $note = null
    ): ReportAssignment {

        return DB::transaction(function () use (
            $reportId,
            $officerId,
            $assignedBy,
            $note
        ) {

            // Lock laporan agar assignment tidak berjalan
            // bersamaan untuk laporan yang sama.
            $report = Report::query()
                ->lockForUpdate()
                ->findOrFail($reportId);

            // Pastikan user yang dipilih adalah petugas.
            $officer = User::query()
                ->where('id', $officerId)
                ->where('role', 'officer')
                ->first();

            if (!$officer) {
                throw ValidationException::withMessages([
                    'officer_id' => 'Pengguna yang dipilih bukan petugas.',
                ]);
            }

            // Cari penugasan aktif sebelumnya.
            $activeAssignment = ReportAssignment::query()
                ->where('report_id', $report->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            // Jika petugasnya sama, tidak perlu membuat
            // penugasan duplikat.
            if (
                $activeAssignment &&
                $activeAssignment->officer_id === $officer->id
            ) {
                return $activeAssignment;
            }

            // Akhiri penugasan sebelumnya.
            if ($activeAssignment) {
                $activeAssignment->update([
                    'ended_at' => now(),
                ]);
            }

            // Buat penugasan baru.
            return ReportAssignment::create([
                'report_id' => $report->id,
                'officer_id' => $officer->id,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'ended_at' => null,
                'note' => $note,
            ]);
        });
    }
}
