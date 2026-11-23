<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\AreaUpdate;
use App\Models\Report;
use Illuminate\Http\Request;

class CitizenDashboardController extends Controller
{
    public function index(Request $request)
    {
        $startOfWeek = now()->startOfWeek();

        $reportsThisWeek = Report::where(
            'created_at',
            '>=',
            $startOfWeek
        );

        $statistics = [
            'pju_rusak' => (clone $reportsThisWeek)->where('category', 'pju_rusak')->count(),
            'area_gelap' => (clone $reportsThisWeek)->where('category', 'area_gelap')->count(),
            'begal_curas' => (clone $reportsThisWeek)->where('category', 'begal_curas')->count(),
            'lain_lain' => (clone $reportsThisWeek)->where('category', 'lain_lain')->count(),
            'pending' => Report::where('status', 'pending')->count(),
            'updated_areas' => Area::whereHas('updates', function ($query) use ($startOfWeek) {
                $query->where('created_at', '>=', $startOfWeek);
            })->count(),
        ];

        $updates = AreaUpdate::with(['area', 'updater'])
            ->latest()
            ->take(5)
            ->get();

        $mapReports = Report::query()
            ->select([
                'id',
                'category',
                'latitude',
                'longitude',
                'location_name',
                'status',
                'created_at',
            ])
            ->latest()
            ->get();

        // My Reports - hanya untuk user yang sudah login
        $myReports = null;

        if ($request->user()) {
            $myReports = Report::query()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->paginate(5);
        }

        return view('index', [
            'statistics' => $statistics,
            'updates' => $updates,
            'mapReports' => $mapReports,
            'myReports' => $myReports,
        ]);
    }
}
