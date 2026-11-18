<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Services\ReportAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportAssignmentController extends Controller
{
    public function create(Report $report)
    {
        if (!in_array($report->status, ['verified', 'investigating'], true)) {
            return redirect()
                ->route('governance.reports.show', $report)
                ->withErrors([
                    'assignment' => 'Laporan harus diverifikasi sebelum dapat ditugaskan.',
                ]);
        }

        $report->load([
            'updates.updater',
            'activeAssignment.officer',
        ]);

        $officers = User::query()
            ->where('role', 'officer')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('governance.reports.assignment', compact(
            'report',
            'officers'
        ));
    }

    public function store(
        Request $request,
        Report $report,
        ReportAssignmentService $assignmentService
    ) {
        if (!in_array($report->status, ['verified', 'investigating'], true)) {
            return back()->withErrors([
                'assignment' => 'Laporan belum dapat ditugaskan atau sudah selesai.',
            ]);
        }

        $validated = $request->validate([
            'officer_id' => ['required', 'integer', 'exists:users,id'],
            'institution_name' => ['required', 'string', 'max:150'],
            'team_name' => ['required', 'string', 'max:150'],
            'priority' => ['required', Rule::in(['normal', 'high', 'urgent'])],
            'due_at' => ['required', 'date', 'after:now'],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $assignmentService->assign(
            reportId: $report->id,
            officerId: (int) $validated['officer_id'],
            assignedBy: $request->user()->id,
            note: $validated['note'],
            institutionName: $validated['institution_name'],
            teamName: $validated['team_name'],
            priority: $validated['priority'],
            dueAt: $validated['due_at']
        );

        return redirect()
            ->route('governance.reports.show', $report)
            ->with('success', 'Penugasan petugas berhasil disimpan.');
    }
}
