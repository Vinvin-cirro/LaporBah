<?php

namespace App\Http\Controllers;

use App\Models\ReportAssignment;
use Illuminate\Http\Request;

class GovernanceOperationController extends Controller
{
    public function index(Request $request)
    {
        $activeAssignmentCount = ReportAssignment::whereNull('ended_at')
            ->count();

        $activeOfficerCount = ReportAssignment::whereNull('ended_at')
            ->distinct('officer_id')
            ->count('officer_id');

        $investigatingCount = ReportAssignment::whereNull('ended_at')
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
