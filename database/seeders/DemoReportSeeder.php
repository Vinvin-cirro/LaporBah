<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoReportSeeder extends Seeder
{
    public function run(): void
    {
        $governance = User::where('role', 'governance')->firstOrFail();

        $officers = User::where('role', 'officer')
            ->orderBy('id')
            ->get();

        if ($officers->count() < 2) {
            throw new \RuntimeException(
                'Minimal harus tersedia 2 akun officer.'
            );
        }

        $reports = [
            [
                'code' => 'DEMO00000001',
                'category' => 'begal',
                'description' => 'DATA DUMMY — Simulasi laporan dugaan aksi begal di sekitar jalan permukiman.',
                'latitude' => 3.5900,
                'longitude' => 98.6750,
                'location' => 'Area Simulasi 01, Medan',
                'days_ago' => 1,
                'status' => 'pending',
                'updates' => [],
            ],
            [
                'code' => 'DEMO00000002',
                'category' => 'dark',
                'description' => 'DATA DUMMY — Simulasi laporan kondisi penerangan jalan yang minim.',
                'latitude' => 3.6000,
                'longitude' => 98.6800,
                'location' => 'Area Simulasi 02, Medan',
                'days_ago' => 2,
                'status' => 'pending',
                'updates' => [],
            ],
            [
                'code' => 'DEMO00000003',
                'category' => 'begal',
                'description' => 'DATA DUMMY — Simulasi laporan dugaan tindak kejahatan di jalan umum.',
                'latitude' => 3.5850,
                'longitude' => 98.6650,
                'location' => 'Area Simulasi 03, Medan',
                'days_ago' => 3,
                'status' => 'verified',
                'updates' => [
                    [18, 'pending', 'verified', 'Laporan telah diperiksa oleh governance.'],
                ],
            ],
            [
                'code' => 'DEMO00000004',
                'category' => 'dark',
                'description' => 'DATA DUMMY — Simulasi laporan lampu penerangan jalan yang tidak berfungsi.',
                'latitude' => 3.6050,
                'longitude' => 98.6700,
                'location' => 'Area Simulasi 04, Medan',
                'days_ago' => 4,
                'status' => 'verified',
                'updates' => [
                    [24, 'pending', 'verified', 'Kondisi laporan telah diverifikasi.'],
                ],
            ],
            [
                'code' => 'DEMO00000005',
                'category' => 'begal',
                'description' => 'DATA DUMMY — Simulasi laporan dugaan aksi begal di jalur yang relatif sepi.',
                'latitude' => 3.5800,
                'longitude' => 98.6850,
                'location' => 'Area Simulasi 05, Medan',
                'days_ago' => 5,
                'status' => 'investigating',
                'updates' => [
                    [18, 'pending', 'verified', 'Laporan telah diperiksa oleh governance.'],
                    [36, 'verified', 'investigating', 'Petugas mulai melakukan pemeriksaan lapangan.'],
                ],
            ],
            [
                'code' => 'DEMO00000006',
                'category' => 'dark',
                'description' => 'DATA DUMMY — Simulasi laporan area jalan dengan pencahayaan yang kurang.',
                'latitude' => 3.6100,
                'longitude' => 98.6800,
                'location' => 'Area Simulasi 06, Medan',
                'days_ago' => 6,
                'status' => 'investigating',
                'updates' => [
                    [24, 'pending', 'verified', 'Laporan telah diverifikasi.'],
                    [48, 'verified', 'investigating', 'Petugas ditugaskan untuk memeriksa lokasi.'],
                ],
            ],
            [
                'code' => 'DEMO00000007',
                'category' => 'begal',
                'description' => 'DATA DUMMY — Simulasi laporan dugaan tindak kejahatan di sekitar jalan penghubung.',
                'latitude' => 3.5750,
                'longitude' => 98.6700,
                'location' => 'Area Simulasi 07, Medan',
                'days_ago' => 8,
                'status' => 'investigating',
                'updates' => [
                    [24, 'pending', 'verified', 'Laporan telah diverifikasi oleh governance.'],
                    [48, 'verified', 'investigating', 'Pemeriksaan lokasi sedang berlangsung.'],
                ],
            ],
            [
                'code' => 'DEMO00000008',
                'category' => 'begal',
                'description' => 'DATA DUMMY — Simulasi laporan dugaan tindak kejahatan yang telah selesai ditangani.',
                'latitude' => 3.5950,
                'longitude' => 98.6950,
                'location' => 'Area Simulasi 08, Medan',
                'days_ago' => 10,
                'status' => 'resolved',
                'updates' => [
                    [18, 'pending', 'verified', 'Laporan telah diverifikasi.'],
                    [36, 'verified', 'investigating', 'Petugas mulai melakukan penanganan.'],
                    [96, 'investigating', 'resolved', 'Penanganan laporan telah diselesaikan.'],
                ],
            ],
            [
                'code' => 'DEMO00000009',
                'category' => 'dark',
                'description' => 'DATA DUMMY — Simulasi laporan penerangan jalan yang telah diperbaiki.',
                'latitude' => 3.6150,
                'longitude' => 98.6600,
                'location' => 'Area Simulasi 09, Medan',
                'days_ago' => 12,
                'status' => 'resolved',
                'updates' => [
                    [24, 'pending', 'verified', 'Laporan telah diverifikasi.'],
                    [48, 'verified', 'investigating', 'Petugas melakukan pemeriksaan lokasi.'],
                    [120, 'investigating', 'resolved', 'Penanganan laporan telah diselesaikan.'],
                ],
            ],
            [
                'code' => 'DEMO00000010',
                'category' => 'dark',
                'description' => 'DATA DUMMY — Simulasi laporan yang ditolak setelah proses pemeriksaan.',
                'latitude' => 3.5850,
                'longitude' => 98.6500,
                'location' => 'Area Simulasi 10, Medan',
                'days_ago' => 14,
                'status' => 'rejected',
                'updates' => [
                    [12, 'pending', 'rejected', 'Laporan ditolak setelah proses pemeriksaan.'],
                ],
            ],
        ];

        DB::transaction(function () use (
            $reports,
            $governance,
            $officers
        ) {
            foreach ($reports as $index => $data) {
                $createdAt = now()->subDays($data['days_ago']);
                $incidentAt = $createdAt->copy()->subHours(2);

                $lastUpdatedAt = $data['updates']
                    ? $createdAt->copy()->addHours(
                        $data['updates'][count($data['updates']) - 1][0]
                    )
                    : $createdAt->copy();

                $report = Report::updateOrCreate(
                    ['tracking_code' => $data['code']],
                    [
                        'user_id' => null,
                        'category' => $data['category'],
                        'description' => $data['description'],
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                        'location_name' => $data['location'],
                        'incident_at' => $incidentAt,
                        'status' => $data['status'],
                        'area_id' => null,
                    ]
                );

                // Atur timestamp laporan sesuai tanggal simulasi.
                $report->timestamps = false;
                $report->forceFill([
                    'created_at' => $createdAt,
                    'updated_at' => $lastUpdatedAt,
                ])->save();
                $report->timestamps = true;

                // Bersihkan riwayat lama milik laporan demo ini saja.
                $report->updates()->delete();
                $report->assignments()->delete();

                $verifiedAt = null;
                $resolvedAt = null;

                foreach ($data['updates'] as $update) {
                    [$hours, $oldStatus, $newStatus, $note] = $update;

                    $updatedAt = $createdAt->copy()->addHours($hours);

                    $updatedBy = in_array(
                        $newStatus,
                        ['investigating', 'resolved']
                    )
                        ? $officers[$index % 2]->id
                        : $governance->id;

                    $history = ReportUpdate::create([
                        'report_id' => $report->id,
                        'updated_by' => $updatedBy,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'note' => $note,
                    ]);

                    // Simpan timestamp riwayat sesuai simulasi.
                    $history->timestamps = false;
                    $history->forceFill([
                        'created_at' => $updatedAt,
                        'updated_at' => $updatedAt,
                    ])->save();
                    $history->timestamps = true;

                    if ($newStatus === 'verified') {
                        $verifiedAt = $updatedAt;
                    }

                    if ($newStatus === 'resolved') {
                        $resolvedAt = $updatedAt;
                    }
                }

                // Buat penugasan untuk laporan yang sudah diverifikasi.
                if ($verifiedAt) {
                    $officer = $officers[$index % 2];

                    $assignment = ReportAssignment::create([
                        'report_id' => $report->id,
                        'officer_id' => $officer->id,
                        'assigned_by' => $governance->id,
                        'assigned_at' => $verifiedAt,
                        'ended_at' => $resolvedAt,
                        'note' => 'Penugasan demo untuk pemeriksaan laporan.',
                    ]);

                    // Simpan timestamp penugasan sesuai simulasi.
                    $assignment->timestamps = false;
                    $assignment->forceFill([
                        'created_at' => $verifiedAt,
                        'updated_at' => $resolvedAt ?? $verifiedAt,
                    ])->save();
                    $assignment->timestamps = true;
                }
            }
        });
    }
}
