<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

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

        return response()->json($reports);
    }
}
