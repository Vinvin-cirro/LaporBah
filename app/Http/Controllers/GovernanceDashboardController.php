<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportUpdate;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GovernanceDashboardController extends Controller
{
    public function index()
    {
        // Ringkasan laporan berdasarkan status
        $statusCounts = Report::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statistics = [
            'total_reports' => Report::count(),
            'pending_reports' => $statusCounts->get('pending', 0),
            'verified_reports' => $statusCounts->get('verified', 0),
            'investigating_reports' => $statusCounts->get('investigating', 0),
            'resolved_reports' => $statusCounts->get('resolved', 0),
            'rejected_reports' => $statusCounts->get('rejected', 0),

            'total_officers' => User::where('role', 'officer')->count(),

            'active_assignments' => ReportAssignment::whereNull('ended_at')
                ->count(),

            'total_areas' => Area::count(),
        ];

        // Grafik laporan selama 7 hari terakhir
        $startDate = now()->startOfDay()->subDays(6);

        $dailyRows = Report::query()
            ->selectRaw(
                'DATE(created_at) as report_date, category, COUNT(*) as total'
            )
            ->where('created_at', '>=', $startDate)
            ->groupByRaw('DATE(created_at), category')
            ->get();

        $dailyCounts = $dailyRows->groupBy('report_date');

        $dailyReports = collect(range(0, 6))->map(function ($day) use (
            $startDate,
            $dailyCounts
        ) {
            $date = $startDate->copy()->addDays($day);
            $dateKey = $date->toDateString();

            $counts = $dailyCounts->get($dateKey, collect())
                ->keyBy('category');

            $pjuRusak = (int) ($counts->get('pju_rusak')->total ?? 0);
            $areaGelap = (int) ($counts->get('area_gelap')->total ?? 0);
            $begalCuras = (int) ($counts->get('begal_curas')->total ?? 0);
            $lainLain = (int) ($counts->get('lain_lain')->total ?? 0);

            return [
                'date' => $dateKey,
                'label' => $date->translatedFormat('D'),
                'pju_rusak' => $pjuRusak,
                'area_gelap' => $areaGelap,
                'begal_curas' => $begalCuras,
                'lain_lain' => $lainLain,
                'total' => $pjuRusak + $areaGelap + $begalCuras + $lainLain,
            ];
        });

        // Distribusi laporan berdasarkan kategori
        $categoryDistribution = collect(Report::CATEGORIES)->mapWithKeys(
            fn ($label, $category) => [$category => Report::where('category', $category)->count()]
        )->all();

        // Area dengan laporan yang masih aktif
        $priorityAreas = Area::query()
            ->withCount([
                'reports as active_reports_count' => function ($query) {
                    $query->whereIn('status', [
                        'pending',
                        'verified',
                        'investigating',
                    ]);
                },
            ])
            ->having('active_reports_count', '>', 0)
            ->orderByDesc('active_reports_count')
            ->take(5)
            ->get([
                'id',
                'name',
                'center_latitude',
                'center_longitude',
                'status',
            ]);

        // Laporan terbaru
        $recentReports = Report::query()
            ->with('user:id,name')
            ->latest()
            ->take(6)
            ->get([
                'id',
                'user_id',
                'tracking_code',
                'category',
                'location_name',
                'status',
                'created_at',
            ]);

        // Riwayat perubahan laporan terbaru
        $recentUpdates = ReportUpdate::query()
            ->with([
                'report:id,tracking_code,category',
                'updater:id,name',
            ])
            ->latest()
            ->take(5)
            ->get();

        return view('governance.dashboard', [
            'statistics' => $statistics,
            'dailyReports' => $dailyReports,
            'categoryDistribution' => $categoryDistribution,
            'priorityAreas' => $priorityAreas,
            'recentReports' => $recentReports,
            'recentUpdates' => $recentUpdates,
        ]);
    }
}
