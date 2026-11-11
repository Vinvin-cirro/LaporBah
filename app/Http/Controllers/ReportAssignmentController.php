<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportAssignmentService;
use Illuminate\Http\Request;

class ReportAssignmentController extends Controller
{
    public function store(
        Request $request,
        Report $report,
        ReportAssignmentService $assignmentService
    ) {
        $validated = $request->validate([
            'officer_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $assignmentService->assign(
            reportId: $report->id,
            officerId: (int) $validated['officer_id'],
            assignedBy: $request->user()->id,
            note: $validated['note'] ?? null
        );

        return back()->with(
            'success',
            'Petugas berhasil ditugaskan.'
        );
    }
}
