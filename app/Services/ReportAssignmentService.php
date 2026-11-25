<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\User;
use App\Models\OfficerTaskUpdate;
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
        ?string $dueAt = null,
        string $assignmentType = 'handling'
    ): ReportAssignment {

        return DB::transaction(function () use (
            $reportId,
            $officerId,
            $assignedBy,
            $note,
            $institutionName,
            $teamName,
            $priority,
            $dueAt,
            $assignmentType
        ) {

            $report = Report::query()
                ->lockForUpdate()
                ->findOrFail($reportId);

            if ($assignmentType === 'inspection') {
                if (!in_array($report->category, ['pju_rusak', 'area_gelap'], true)) {
                    throw ValidationException::withMessages([
                        'assignment_type' => 'Inspeksi hanya tersedia untuk PJU Rusak dan Area Gelap.',
                    ]);
                }

                if (!in_array($report->status, ['pending', 'verifying'], true)) {
                    throw ValidationException::withMessages([
                        'assignment_type' => 'Laporan tidak berada pada status yang dapat diperiksa.',
                    ]);
                }
            }
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
                'assignment_type' => $assignmentType,
            ], fn ($value) => $value !== null);

            // Jika petugas sama, perbarui detail tanpa
            // membuat riwayat penugasan duplikat.
            if (
                $activeAssignment &&
                (int) $activeAssignment->officer_id === (int) $officer->id
            ) {
                if (!empty($assignmentData)) {
                    $activeAssignment->update($assignmentData);
                }

                if ($assignmentType === 'inspection') {
                    $report->update(['status' => 'verifying']);
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
            $assignment = ReportAssignment::create(array_merge(
                [
                    'report_id' => $report->id,
                    'officer_id' => $officer->id,
                    'assigned_at' => now(),
                    'ended_at' => null,
                    'status' => 'assigned',
                ],
                $assignmentData
            ));

            if ($assignmentType === 'inspection') {
                $report->update(['status' => 'verifying']);
            }

            OfficerTaskUpdate::create([
                'report_assignment_id' => $assignment->id,
                'user_id' => $assignedBy,
                'event' => 'assigned',
                'note' => $note ?? 'Tugas diberikan kepada petugas.',
            ]);

            return $assignment;
        });
    }

    public function assignToTeam(
        int $reportId,
        int $teamId,
        ?int $assignedBy = null,
        ?string $note = null,
        ?string $institutionName = null,
        ?string $priority = null,
        ?string $dueAt = null,
        string $assignmentType = 'handling'
    ): ReportAssignment {
        return DB::transaction(function () use ($reportId, $teamId, $assignedBy, $note, $institutionName, $priority, $dueAt, $assignmentType) {
            $report = Report::query()->lockForUpdate()->findOrFail($reportId);
            $isInspectionCategory = in_array($report->category, ['pju_rusak', 'area_gelap'], true);
            if ($assignmentType === 'inspection') {
                if (!$isInspectionCategory || $report->status !== 'pending') {
                    throw ValidationException::withMessages(['assignment_type' => 'Inspeksi sebelum verifikasi hanya tersedia untuk laporan PJU Rusak dan Area Gelap yang masih pending.']);
                }
            } elseif (!in_array($report->status, ['verified', 'investigating'], true)) {
                throw ValidationException::withMessages(['assignment' => 'Penugasan penanganan hanya dapat dibuat setelah laporan diverifikasi.']);
            }
            $team = \App\Models\Team::query()->with('members')->where('is_active', true)->findOrFail($teamId);
            $firstOfficer = $team->members->first();

            if (!$firstOfficer || $team->members->contains(fn ($member) => $member->role !== 'officer')) {
                throw ValidationException::withMessages(['team_id' => 'Tim harus memiliki minimal satu anggota Officer yang valid.']);
            }

            $activeAssignment = ReportAssignment::query()->where('report_id', $report->id)
                ->whereNull('ended_at')->lockForUpdate()->first();

            $assignmentData = [
                'assigned_by' => $assignedBy,
                'note' => $note,
                'institution_name' => $institutionName ?: $team->institution_name,
                'team_name' => $team->name,
                'team_id' => $team->id,
                'assignment_type' => $assignmentType,
                'priority' => $priority,
                'due_at' => $dueAt,
            ];

            if ($activeAssignment && (int) $activeAssignment->team_id === (int) $team->id && $activeAssignment->assignment_type === $assignmentType) {
                $activeAssignment->update($assignmentData);
                return $activeAssignment->fresh(['team.members']);
            }

            if ($activeAssignment) {
                $activeAssignment->update(['ended_at' => now()]);
            }

            $assignment = ReportAssignment::create(array_merge($assignmentData, [
                'report_id' => $report->id,
                // officer_id dipertahankan sebagai petugas representatif untuk kompatibilitas data lama.
                'officer_id' => $firstOfficer->id,
                'assigned_at' => now(),
                'ended_at' => null,
                'status' => 'assigned',
            ]));
            
            // Ubah status laporan ketika Officer ditugaskan untuk inspeksi.
            if ($assignmentType === 'inspection') {
                $report->update([
                    'status' => 'verifying',
                ]);
            }

            OfficerTaskUpdate::create([
                'report_assignment_id' => $assignment->id,
                'user_id' => $assignedBy,
                'event' => $assignmentType === 'inspection' ? 'inspection_assigned' : 'assigned',
                'note' => $note ?? (($assignmentType === 'inspection' ? 'Inspeksi laporan diberikan kepada tim ' : 'Tugas diberikan kepada tim ') . $team->name . '.'),
            ]);

            return $assignment->load('team.members');
        });
    }

}
