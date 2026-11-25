<?php

namespace App\Http\Controllers;

use App\Models\ReportAssignment;
use App\Models\Team;
use App\Models\OfficerTaskUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GovernanceOperationController extends Controller
{
    public function verify(Request $request, ReportAssignment $assignment)
    {
        abort_unless($assignment->assignment_type !== 'inspection', 422, 'Tugas inspeksi harus ditinjau melalui keputusan verifikasi laporan.');
        abort_unless($assignment->status === 'awaiting_confirmation' && $assignment->ended_at === null, 422, 'Penugasan belum menunggu konfirmasi.');
        DB::transaction(function () use ($assignment, $request) {
            $assignment->update(['status' => 'completed', 'completed_at' => now()]);
            OfficerTaskUpdate::create([
                'report_assignment_id' => $assignment->id,
                'user_id' => $request->user()->id,
                'event' => 'supervisor_verified',
                'note' => 'Supervisor memverifikasi penyelesaian tugas.',
            ]);
        });
        return back()->with('success', 'Penyelesaian tugas berhasil diverifikasi. Status laporan masyarakat tidak diubah.');
    }

    public function reviewInspection(Request $request, ReportAssignment $assignment)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:verify,reject,reinspect'],
            'review_note' => ['required', 'string', 'max:2000'],
        ]);

        abort_unless($assignment->assignment_type === 'inspection', 422, 'Penugasan ini bukan inspeksi laporan.');
        abort_unless($assignment->ended_at === null && $assignment->status === 'awaiting_confirmation', 422, 'Hasil inspeksi belum siap ditinjau.');
        abort_unless($assignment->report && $assignment->report->status === 'pending', 422, 'Laporan tidak lagi menunggu verifikasi.');

        DB::transaction(function () use ($assignment, $request, $data) {
            $assignment->loadMissing('report');
            if ($data['decision'] === 'verify') {
                $assignment->report->update(['status' => 'verified']);
                $assignment->update(['status' => 'completed', 'completed_at' => now(), 'ended_at' => now()]);
                $assignment->report->updates()->create([
                    'updated_by' => $request->user()->id,
                    'old_status' => 'pending',
                    'new_status' => 'verified',
                    'action' => 'verify_after_inspection',
                    'note' => $data['review_note'],
                ]);
                $event = 'inspection_verified';
                $message = 'Hasil inspeksi disetujui dan laporan berhasil diverifikasi.';
            } elseif ($data['decision'] === 'reject') {
                $assignment->report->update(['status' => 'rejected']);
                $assignment->update(['status' => 'completed', 'completed_at' => now(), 'ended_at' => now()]);
                $assignment->report->updates()->create([
                    'updated_by' => $request->user()->id,
                    'old_status' => 'pending',
                    'new_status' => 'rejected',
                    'action' => 'reject_after_inspection',
                    'note' => $data['review_note'],
                ]);
                $event = 'inspection_rejected';
                $message = 'Laporan ditolak berdasarkan hasil inspeksi.';
            } else {
                $assignment->update(['status' => 'in_progress', 'completion_requested_at' => null]);
                $event = 'inspection_recheck_requested';
                $message = 'Pemeriksaan ulang diminta. Tugas dikembalikan kepada Officer.';
            }

            OfficerTaskUpdate::create([
                'report_assignment_id' => $assignment->id,
                'user_id' => $request->user()->id,
                'event' => $event,
                'note' => $data['review_note'],
            ]);
        });

        return redirect()->route('governance.reports.show', $assignment->report_id)->with('success', $message);
    }

    public function index(Request $request)
    {
        $activeAssignmentCount = ReportAssignment::whereNull('ended_at')->where('status', '!=', 'completed')
            ->count();

        $activeAssignments = ReportAssignment::query()->with('team.members:id')->whereNull('ended_at')->where('status', '!=', 'completed')->get();
        $activeOfficerCount = $activeAssignments->flatMap(fn ($assignment) => $assignment->team?->members->pluck('id') ?? collect([$assignment->officer_id]))->unique()->count();

        $investigatingCount = ReportAssignment::whereNull('ended_at')->where('status', '!=', 'completed')
            ->whereHas('report', function ($query) {
                $query->where('status', 'investigating');
            })
            ->count();
        $assignments = ReportAssignment::query()
            ->with([
                'report',
                'officer',
                'team.members',
                'assigner',
            ])
            ->whereNull('ended_at')
            ->where('status', '!=', 'completed')
            ->latest('assigned_at')
            ->paginate(15);

        return view('governance.operations.index', compact(
            'assignments',
            'activeAssignmentCount',
            'activeOfficerCount',
            'investigatingCount'
        ));
    }
}
