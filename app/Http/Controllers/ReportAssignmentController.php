<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Team;
use App\Services\ReportAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportAssignmentController extends Controller
{
    public function create(Report $report)
    {
        $isInspectionCategory = in_array(
            $report->category,
            ['pju_rusak', 'area_gelap'],
            true
        );

        $canAssignInspection = $isInspectionCategory
            && in_array($report->status, ['pending', 'verifying'], true);

        $canAssignHandling = in_array(
            $report->status,
            ['verified', 'investigating'],
            true
        );

        if (!$canAssignInspection && !$canAssignHandling) {
            return redirect()
                ->route('governance.reports.show', $report)
                ->withErrors([
                    'assignment' => 'Laporan tidak dapat ditugaskan pada tahap ini.',
                ]);
        }

        $report->load([
            'updates.updater',
            'activeAssignment.officer',
            'activeAssignment.team.members',
        ]);

        $teams = Team::query()
            ->where('is_active', true)
            ->whereHas('members', function ($query) {
                $query->where('role', 'officer');
            })
            ->with('members:id,name,email')
            ->orderBy('name')
            ->get();

        $officers = User::query()
            ->where('role', 'officer')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('governance.reports.assignment', compact(
            'report',
            'officers',
            'teams'
        ));
    }

    public function store(
        Request $request,
        Report $report,
        ReportAssignmentService $assignmentService
    ) {
        $validated = $request->validate([
            'assignment_type' => [
                'required',
                Rule::in(['inspection', 'handling']),
            ],

            'officer_id' => [
                'nullable',
                'required_if:assignment_type,inspection',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', 'officer'),
            ],

            'team_id' => [
                'nullable',
                'required_if:assignment_type,handling',
                'integer',
                Rule::exists('teams', 'id')
                    ->where('is_active', true),
            ],

            'team_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'institution_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'priority' => [
                'required',
                Rule::in(['normal', 'high', 'urgent']),
            ],

            'due_at' => [
                'required',
                'date',
                'after:now',
            ],

            'note' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $assignmentType = $validated['assignment_type'];

        $isInspectionCategory = in_array(
            $report->category,
            ['pju_rusak', 'area_gelap'],
            true
        );

        /*
        |--------------------------------------------------------------------------
        | PENUGASAN INSPEKSI PRA-VERIFIKASI
        |--------------------------------------------------------------------------
        */

        if ($assignmentType === 'inspection') {
            if (
                !$isInspectionCategory ||
                !in_array($report->status, ['pending', 'verifying'], true)
            ) {
                return back()
                    ->withErrors([
                        'assignment_type' => 'Inspeksi hanya tersedia untuk laporan PJU Rusak atau Area Gelap yang belum selesai diverifikasi.',
                    ])
                    ->withInput();
            }

            $assignmentService->assign(
                reportId: $report->id,
                officerId: (int) $validated['officer_id'],
                assignedBy: $request->user()->id,
                note: $validated['note'],
                institutionName: $validated['institution_name'] ?? null,
                priority: $validated['priority'],
                dueAt: $validated['due_at'],
                assignmentType: 'inspection'
            );

            // Ubah status laporan menjadi sedang diperiksa Officer.
            if ($report->status === 'pending') {
                $report->update([
                    'status' => 'verifying',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PENUGASAN PENANGANAN OLEH TIM
        |--------------------------------------------------------------------------
        */

        else {
            if (!in_array($report->status, ['verified', 'investigating'], true)) {
                return back()
                    ->withErrors([
                        'assignment' => 'Penugasan penanganan hanya dapat dibuat setelah laporan diverifikasi.',
                    ])
                    ->withInput();
            }

            $assignmentService->assignToTeam(
                reportId: $report->id,
                teamId: (int) $validated['team_id'],
                assignedBy: $request->user()->id,
                note: $validated['note'],
                institutionName: $validated['institution_name'] ?? null,
                priority: $validated['priority'],
                dueAt: $validated['due_at'],
                assignmentType: 'handling'
            );
        }

        return redirect()
            ->route('governance.reports.show', $report)
            ->with(
                'success',
                $assignmentType === 'inspection'
                    ? 'Tugas inspeksi berhasil diberikan. Laporan sekarang sedang diperiksa oleh petugas.'
                    : 'Laporan berhasil ditugaskan kepada tim.'
            );
    }
}
