<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class GovernanceMapController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'category' => 'nullable|in:begal,dark',
            'status' => 'nullable|in:pending,verified,investigating,resolved,rejected',
        ]);

        $reports = Report::query()
            ->with('user:id,name')
            ->when($request->category, function ($query, $category) {
                $query->where('category', $category);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->get([
                'id',
                'user_id',
                'tracking_code',
                'category',
                'description',
                'latitude',
                'longitude',
                'location_name',
                'status',
                'created_at',
            ]);
        $mapReports = $reports->map(function ($report) {
            return [
                'id' => $report->id,
                'tracking_code' => $report->tracking_code,
                'category' => $report->category,
                'description' => $report->description,
                'latitude' => $report->latitude,
                'longitude' => $report->longitude,
                'location_name' => $report->location_name,
                'status' => $report->status,
                'created_at' => $report->created_at?->format('d/m/Y H:i'),
                'reporter' => $report->user?->name ?? 'Anonim',
            ];
        })->values();
        $statistics = [
            'total' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'investigating' => Report::where('status', 'investigating')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
        ];

        return view('governance.map', [
            'reports' => $reports,
            'mapReports' => $mapReports,
            'statistics' => $statistics,
            'selectedCategory' => $request->category,
            'selectedStatus' => $request->status,
        ]);
    }
}
