<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'in:begal,dark'],
            'description' => ['required', 'string', 'max:5000'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'incident_at' => ['nullable', 'date'],

            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $storedPaths = [];

        try {
            $report = DB::transaction(function () use (
                $request,
                $validated,
                &$storedPaths
            ) {
                $report = Report::create([
                    'user_id' => $request->user()?->id,
                    'category' => $validated['category'],
                    'description' => $validated['description'],
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'location_name' => $validated['location_name'] ?? null,
                    'incident_at' => $validated['incident_at'] ?? null,
                    'status' => 'pending',
                    'tracking_code' => $this->generateTrackingCode(),
                ]);

                foreach ($request->file('photos', []) as $photo) {
                    $path = $photo->store('reports', 'public');

                    if ($path === false) {
                        throw new RuntimeException(
                            'Gagal menyimpan foto laporan.'
                        );
                    }

                    $storedPaths[] = $path;

                    $report->images()->create([
                        'image_path' => $path,
                    ]);
                }

                return $report;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        return response()->json([
            'message' => 'Laporan berhasil dikirim.',
            'tracking_code' => $report->tracking_code,
            'report_id' => $report->id,
        ], 201);
    }

    private function generateTrackingCode(): string
    {
        do {
            $trackingCode = strtoupper(Str::random(12));
        } while (
            Report::where('tracking_code', $trackingCode)->exists()
        );

        return $trackingCode;
    }
}
