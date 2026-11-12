<?php

namespace App\Http\Controllers;

use App\Models\Report;

class ReportTrackingController extends Controller
{
    
    
    public function show(string $trackingCode)
    {
        $report = Report::query()
            ->select([
                'id',
                'tracking_code',
                'category',
                'description',
                'latitude',
                'longitude',
                'location_name',
                'status',
                'incident_at',
                'created_at',
            ])
            ->with([
                'images',
                'updates' => function ($query) {
                    $query->select([
                        'id',
                        'report_id',
                        'old_status',
                        'new_status',
                        'created_at',
                    ])
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
                },
            ])
            ->where('tracking_code', strtoupper(trim($trackingCode)))
            ->firstOrFail();

        $data = [
            'tracking_code' => $report->tracking_code,
            'category' => $report->category,
            'location_name' => $report->location_name,
            'status' => $report->status,
            'submitted_at' => $report->created_at,
            'updates' => $report->updates->map(function ($update) {
                return [
                    'old_status' => $update->old_status,
                    'new_status' => $update->new_status,
                    'updated_at' => $update->created_at,
                ];
            }),
        ];

        // Tetap mempertahankan respons JSON untuk API dan pengujian.
        if (request()->expectsJson()) {
            return response()->json($data);
        }

        // Halaman web menerima detail laporan beserta foto dan riwayat status.
        return view('reports.track', [
            'report' => $report,
        ]);
    }

}
