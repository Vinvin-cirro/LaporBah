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
        ?string $note = null,
        ?string $institutionName = null,
        ?string $teamName = null,
        ?string $priority = null,
        ?string $dueAt = null
    ): ReportAssignment {

        return DB::transaction(function () use (
            $reportId,
            $officerId,
            $assignedBy,
            $note,
            $institutionName,
            $teamName,
            $priority,
            $dueAt
        ) {

            $report = Report::query()
                ->lockForUpdate()
                ->findOrFail($reportId);

            $officer = User::query()
                ->where('id', $officerId)
                ->where('role', 'officer')
                ->first();

            if (!$officer) {
                throw ValidationException::withMessages([
                    'officer_id' => 'Pengguna yang dipilih bukan petugas.',
                ]);
            }

            $activeAssignment = ReportAssignment::query()
                ->where('report_id', $report->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->first();

            // Data tambahan penugasan.
            $assignmentData = array_filter([
                'assigned_by' => $assignedBy,
                'note' => $note,
                'institution_name' => $institutionName,
                'team_name' => $teamName,
                'priority' => $priority,
                'due_at' => $dueAt,
            ], fn ($value) => $value !== null);

            // Jika petugas sama, perbarui detail tanpa
            // membuat riwayat penugasan duplikat.
            if (
                $activeAssignment &&
                $activeAssignment->officer_id === $officer->id
            ) {
                if (!empty($assignmentData)) {
                    $activeAssignment->update($assignmentData);
                }

                return $activeAssignment->fresh();
            }

            // Akhiri penugasan sebelumnya.
            if ($activeAssignment) {
                $activeAssignment->update([
                    'ended_at' => now(),
                ]);
            }

            // Buat penugasan baru.
            return ReportAssignment::create(array_merge(
                [
                    'report_id' => $report->id,
                    'officer_id' => $officer->id,
                    'assigned_at' => now(),
                    'ended_at' => null,
                ],
                $assignmentData
            ));
        });
    }
}
