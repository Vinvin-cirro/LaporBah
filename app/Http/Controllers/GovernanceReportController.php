<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GovernanceReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => ['nullable', 'in:begal,dark'],
            'status' => ['nullable', 'string', 'max:20'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $reports = Report::query()
            ->select([
                'id',
                'tracking_code',
                'category',
                'location_name',
                'latitude',
                'longitude',
                'status',
                'created_at',
            ])
            ->when($validated['category'] ?? null, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($validated['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tracking_code', 'like', "%{$search}%")
                        ->orWhere('location_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($validated['per_page'] ?? 15);

        if ($request->expectsJson()) {
            return response()->json($reports);
        }

        return view('governance.reports.index', [
            'reports' => $reports,
            'filters' => $validated,
        ]);
    }

    public function show(Request $request, Report $report)
    {
        $report->load(['images', 'updates.updater']);

        if (!$request->expectsJson()) {
            return view('governance.reports.show', compact('report'));
        }

        return response()->json([
            'id' => $report->id,
            'tracking_code' => $report->tracking_code,
            'category' => $report->category,
            'description' => $report->description,
            'location_name' => $report->location_name,
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'status' => $report->status,
            'incident_at' => $report->incident_at,
            'submitted_at' => $report->created_at,

            'photos' => $report->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                    'image_url' => Storage::disk('public')->url(
                        $image->image_path
                    ),
                ];
            }),
        ]);
    }
    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,verified,investigating,resolved,rejected',
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $transitions = [
            'pending' => ['verified', 'rejected'],
            'verified' => ['investigating', 'rejected'],
            'investigating' => ['resolved', 'rejected'],
            'resolved' => [],
            'rejected' => [],
        ];

        $result = DB::transaction(function () use ($report, $validated, $request) {
            $currentReport = Report::whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $currentReport->status;

            $allowedTransitions = [
                'pending' => ['verified', 'rejected'],
                'verified' => ['investigating', 'rejected'],
                'investigating' => ['resolved', 'rejected'],
                'resolved' => [],
                'rejected' => [],
            ];

            if (!in_array(
                $validated['status'],
                $allowedTransitions[$oldStatus] ?? [],
                true
            )) {
                throw ValidationException::withMessages([
                    'status' => 'Invalid status transition.',
                ]);
            }

            $currentReport->update([
                'status' => $validated['status'],
            ]);

            DB::table('report_updates')->insert([
                'report_id' => $currentReport->id,
                'updated_by' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'note' => $validated['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'report' => $currentReport,
                'old_status' => $oldStatus,
            ];
        });

        return response()->json([
            'message' => 'Report status updated successfully.',
            'report_id' => $result['report']->id,
            'old_status' => $result['old_status'],
            'new_status' => $result['report']->status,
        ]);
    }


}
