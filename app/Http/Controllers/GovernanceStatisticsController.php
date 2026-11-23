<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Carbon\Carbon;

class GovernanceStatisticsController extends Controller
{
    public function index()
    {
        $totalReports = Report::count();

        $categoryTotals = Report::query()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // Urutan selalu mengikuti empat kategori resmi agar label dan nilai grafik cocok.
        $categoryStatistics = collect(Report::CATEGORIES)->map(
            fn ($label, $category) => (object) [
                'category' => $category,
                'total' => (int) $categoryTotals->get($category, 0),
            ]
        )->values();

        $statusStatistics = Report::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        $monthlyStatistics = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()
                ->startOfMonth()
                ->subMonths($i);

            $monthlyStatistics->push([
                'month' => $month->format('M Y'),
                'total' => Report::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ]);
        }

        return view('governance.statistics.index', compact(
            'totalReports',
            'categoryStatistics',
            'statusStatistics',
            'monthlyStatistics'
        ));
    }
}
