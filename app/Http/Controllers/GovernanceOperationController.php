<?php

namespace App\Http\Controllers;

use App\Models\ReportAssignment;
use App\Models\OfficerTaskUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class GovernanceOperationController extends Controller
{
    public function verify(Request $request, ReportAssignment $assignment)
    {
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

    public function index(Request $request)
    {
        $activeAssignmentCount = ReportAssignment::whereNull('ended_at')->where('status', '!=', 'completed')
            ->count();

        $activeOfficerCount = ReportAssignment::whereNull('ended_at')->where('status', '!=', 'completed')
            ->distinct('officer_id')
            ->count('officer_id');

        $investigatingCount = ReportAssignment::whereNull('ended_at')->where('status', '!=', 'completed')
            ->whereHas('report', function ($query) {
                $query->where('status', 'investigating');
            })
            ->count();
        $assignments = ReportAssignment::query()
            ->with([
                'report',
                'officer',
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
