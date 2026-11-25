<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GovernanceMapController extends Controller
{
    private const DBSCAN_EPS_METERS = 300;
    private const DBSCAN_MIN_POINTS = 3;

    public function index(Request $request)
    {
        $request->validate([
            'category' => 'nullable|in:pju_rusak,area_gelap,begal_curas,lain_lain',
            'status' => 'nullable|in:pending,verified,investigating,resolved,rejected',
            'period' => 'nullable|in:7,30,90,all',
            'risk' => 'nullable|in:high,medium,low,noise',
            'search' => 'nullable|string|max:150',
            'kecamatan' => 'nullable|string|max:100',
        ]);

        $period = $request->input('period', '30');
        $category = $request->input('category');
        $status = $request->input('status');
        $risk = $request->input('risk');
        $search = trim((string) $request->input('search', ''));
        $kecamatan = $request->input('kecamatan');

        $query = Report::query()
            ->with('user:id,name')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($kecamatan, fn ($q) => $q->where('kecamatan', $kecamatan))
            ->when($period !== 'all', function ($q) use ($period) {
                $q->where('created_at', '>=', Carbon::now()->subDays((int) $period));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('location_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('tracking_code', 'like', "%{$search}%");
                });
            })
            ->latest();

        $reports = $query->get([
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
            'kecamatan',
        ]);

        $clusterResult = $this->runDbscan($reports);
        $clusterByReportId = $clusterResult['report_clusters'];
        $clusters = $clusterResult['clusters'];

        $mapReports = $reports->map(function ($report) use ($clusterByReportId, $clusters) {
            $clusterId = $clusterByReportId[$report->id] ?? null;
            $cluster = $clusterId !== null ? ($clusters[$clusterId] ?? null) : null;

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
                'cluster_id' => $clusterId,
                'cluster_count' => $cluster['count'] ?? 0,
                'risk' => $cluster['risk'] ?? 'noise',
                'kecamatan' => $report->kecamatan,
            ];
        })->values();

        if ($risk) {
            $mapReports = $mapReports->filter(fn ($report) => $report['risk'] === $risk)->values();

            $visibleClusterIds = $mapReports
                ->pluck('cluster_id')
                ->filter(fn ($id) => $id !== null)
                ->unique()
                ->values()
                ->all();

            $clusters = collect($clusters)
                ->filter(fn ($cluster, $id) => in_array($id, $visibleClusterIds, true))
                ->all();
        }

        $visibleReports = $mapReports;
        $total = $visibleReports->count();

        $statistics = [
            'total' => $total,
            'pending' => $visibleReports->where('status', 'pending')->count(),
            'verified' => $visibleReports->whereIn('status', ['verified', 'investigating', 'resolved'])->count(),
            'investigating' => $visibleReports->where('status', 'investigating')->count(),
            'resolved' => $visibleReports->where('status', 'resolved')->count(),
            'pju_rusak' => $visibleReports->where('category', 'pju_rusak')->count(),
            'area_gelap' => $visibleReports->where('category', 'area_gelap')->count(),
            'begal_curas' => $visibleReports->where('category', 'begal_curas')->count(),
            'lain_lain' => $visibleReports->where('category', 'lain_lain')->count(),
            'high' => $visibleReports->where('risk', 'high')->count(),
            'medium' => $visibleReports->where('risk', 'medium')->count(),
            'low' => $visibleReports->where('risk', 'low')->count(),
            'noise' => $visibleReports->where('risk', 'noise')->count(),
            'cluster_count' => count($clusters),
        ];

        $topCluster = collect($clusters)
            ->sortByDesc('count')
            ->first();

        return view('governance.map', [
            'reports' => $reports,
            'mapReports' => $visibleReports,
            'clusters' => array_values($clusters),
            'statistics' => $statistics,
            'topCluster' => $topCluster,
            'selectedCategory' => $category,
            'selectedStatus' => $status,
            'selectedPeriod' => $period,
            'selectedRisk' => $risk,
            'search' => $search,
            'epsMeters' => self::DBSCAN_EPS_METERS,
            'minPoints' => self::DBSCAN_MIN_POINTS,
            'selectedKecamatan' => $kecamatan,
        ]);
    }

    /**
     * DBSCAN menggunakan jarak Haversine (meter).
     * Titik tanpa koordinat valid tidak dimasukkan ke clustering.
     */
    private function runDbscan($reports): array
    {
        $points = [];

        foreach ($reports as $report) {
            if (
                $report->latitude === null ||
                $report->longitude === null ||
                !is_numeric($report->latitude) ||
                !is_numeric($report->longitude)
            ) {
                continue;
            }

            $lat = (float) $report->latitude;
            $lng = (float) $report->longitude;

            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                continue;
            }

            $points[] = [
                'id' => $report->id,
                'lat' => $lat,
                'lng' => $lng,
                'category' => $report->category,
                'location_name' => $report->location_name,
                'kecamatan' => $report->kecamatan,
            ];
        }

        $labels = array_fill(0, count($points), null);
        $visited = array_fill(0, count($points), false);
        $clusterId = 0;

        for ($i = 0; $i < count($points); $i++) {
            if ($visited[$i]) {
                continue;
            }

            $visited[$i] = true;
            $neighbors = $this->regionQuery($points, $i);

            if (count($neighbors) < self::DBSCAN_MIN_POINTS) {
                $labels[$i] = -1;
                continue;
            }

            $labels[$i] = $clusterId;
            $queue = $neighbors;
            $queued = array_fill(0, count($points), false);

            foreach ($queue as $neighborIndex) {
                $queued[$neighborIndex] = true;
            }

            for ($cursor = 0; $cursor < count($queue); $cursor++) {
                $neighborIndex = $queue[$cursor];

                if (!$visited[$neighborIndex]) {
                    $visited[$neighborIndex] = true;
                    $neighborNeighbors = $this->regionQuery($points, $neighborIndex);

                    if (count($neighborNeighbors) >= self::DBSCAN_MIN_POINTS) {
                        foreach ($neighborNeighbors as $nextIndex) {
                            if (!$queued[$nextIndex]) {
                                $queue[] = $nextIndex;
                                $queued[$nextIndex] = true;
                            }
                        }
                    }
                }

                if ($labels[$neighborIndex] === null || $labels[$neighborIndex] === -1) {
                    $labels[$neighborIndex] = $clusterId;
                }
            }

            $clusterId++;
        }

        $clusters = [];
        $reportClusters = [];

        foreach ($points as $index => $point) {
            $label = $labels[$index];

            if ($label === null || $label === -1) {
                continue;
            }

            $clusters[$label] ??= [
                'id' => $label,
                'count' => 0,
                'latitude_sum' => 0,
                'longitude_sum' => 0,
                'report_ids' => [],
                'categories' => ['pju_rusak' => 0, 'area_gelap' => 0, 'begal_curas' => 0, 'lain_lain' => 0],
                'statuses' => [],
                'locations' => [],
            ];

            $clusters[$label]['count']++;
            $clusters[$label]['latitude_sum'] += $point['lat'];
            $clusters[$label]['longitude_sum'] += $point['lng'];
            $clusters[$label]['report_ids'][] = $point['id'];
            $clusters[$label]['categories'][$point['category']] =
                ($clusters[$label]['categories'][$point['category']] ?? 0) + 1;

            if (!empty($point['kecamatan'])) {
                $clusters[$label]['locations'][] = $point['kecamatan'];
            }
            $reportClusters[$point['id']] = $label;
        }

        foreach ($clusters as $id => &$cluster) {
            $cluster['latitude'] = $cluster['latitude_sum'] / $cluster['count'];
            $cluster['longitude'] = $cluster['longitude_sum'] / $cluster['count'];
            unset($cluster['latitude_sum'], $cluster['longitude_sum']);

            $cluster['risk'] = match (true) {
                $cluster['count'] >= 10 => 'high',
                $cluster['count'] >= 5 => 'medium',
                default => 'low',
            };

            $locationCounts = array_count_values($cluster['locations']);
            arsort($locationCounts);
            $cluster['label'] = array_key_first($locationCounts) ?: 'Area laporan';
        }
        unset($cluster);

        return [
            'clusters' => $clusters,
            'report_clusters' => $reportClusters,
        ];
    }

    private function regionQuery(array $points, int $index): array
    {
        $neighbors = [];

        foreach ($points as $candidateIndex => $candidate) {
            $distance = $this->haversineMeters(
                $points[$index]['lat'],
                $points[$index]['lng'],
                $candidate['lat'],
                $candidate['lng']
            );

            if ($distance <= self::DBSCAN_EPS_METERS) {
                $neighbors[] = $candidateIndex;
            }
        }

        return $neighbors;
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
