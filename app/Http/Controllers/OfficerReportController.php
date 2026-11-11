<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfficerReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = Report::query()
            ->whereHas('assignments', function ($query) use ($request) {
                $query->where('officer_id', $request->user()->id)
                    ->whereNull('ended_at');
            })
            ->select([
                'id',
                'tracking_code',
                'category',
                'description',
                'location_name',
                'latitude',
                'longitude',
                'status',
                'created_at',
            ])
            ->latest()
            ->paginate(15);

        return response()->json($reports);
    }

    public function show(Request $request, Report $report)
    {
        $isAssigned = $report->assignments()
            ->where('officer_id', $request->user()->id)
            ->whereNull('ended_at')
            ->exists();

        abort_unless($isAssigned, 403);

        $report->load('images');

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
                'in:investigating,resolved',
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = DB::transaction(function () use ($report, $validated, $request) {
            $currentReport = Report::whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            $isAssigned = $currentReport->assignments()
                ->where('officer_id', $request->user()->id)
                ->whereNull('ended_at')
                ->exists();

            abort_unless($isAssigned, 403);

            $oldStatus = $currentReport->status;

            $allowedTransitions = [
                'pending' => [],
                'verified' => ['investigating'],
                'investigating' => ['resolved'],
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
    public function history(Request $request, Report $report)
    {
        $isAssigned = $report->assignments()
            ->where('officer_id', $request->user()->id)
            ->whereNull('ended_at')
            ->exists();

        abort_unless($isAssigned, 403);

        $history = $report->updates()
            ->with('updater:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($update) {
                return [
                    'id' => $update->id,
                    'old_status' => $update->old_status,
                    'new_status' => $update->new_status,
                    'note' => $update->note,
                    'updated_by' => $update->updater?->name,
                    'created_at' => $update->created_at,
                ];
            });

        return response()->json([
            'report_id' => $report->id,
            'history' => $history,
        ]);
    }

}