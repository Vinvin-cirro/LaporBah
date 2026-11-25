<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportUpdate;
use App\Models\OfficerTaskUpdate;
use App\Models\Team;
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
                'category' => 'begal_curas',
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
                'category' => 'area_gelap',
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
                'category' => 'begal_curas',
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
                'category' => 'pju_rusak',
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
                'category' => 'begal_curas',
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
                'category' => 'area_gelap',
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
                'category' => 'begal_curas',
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
                'category' => 'begal_curas',
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
                'category' => 'area_gelap',
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
                'category' => 'lain_lain',
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
            // Tim demo digunakan agar penugasan mengikuti struktur terbaru
            // (satu penugasan aktif dimiliki oleh satu tim).
            $team = Team::updateOrCreate(
                ['name' => 'Tim Demo LaporBah'],
                [
                    'institution_name' => 'Dinas Perhubungan',
                    'description' => 'Tim simulasi untuk data demo LaporBah.',
                    'is_active' => true,
                ]
            );

            $team->members()->syncWithoutDetaching($officers->pluck('id')->all());
            $representativeOfficer = $officers->first();
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

                // Laporan PJU Rusak / Area Gelap yang masih pending dapat
                // memiliki penugasan INSPEKSI sebelum laporan diverifikasi.
                $isPreVerificationInspection =
                    $data['status'] === 'pending'
                    && in_array($data['category'], ['pju_rusak', 'area_gelap'], true);

                // Penugasan HANDLING hanya dibuat setelah laporan diverifikasi.
                $isHandlingAssignment = $verifiedAt !== null;

                if ($isPreVerificationInspection || $isHandlingAssignment) {
                    $assignmentType = $isPreVerificationInspection ? 'inspection' : 'handling';
                    $assignedAt = $isPreVerificationInspection
                        ? $createdAt->copy()->addHours(2)
                        : $verifiedAt;

                    $assignmentStatus = match ($data['status']) {
                        'investigating' => 'in_progress',
                        'resolved' => 'completed',
                        default => 'assigned',
                    };

                    $acceptedAt = in_array($assignmentStatus, ['in_progress', 'completed'], true)
                        ? $assignedAt->copy()->addHour()
                        : null;
                    $startedAt = in_array($assignmentStatus, ['in_progress', 'completed'], true)
                        ? $assignedAt->copy()->addHours(2)
                        : null;
                    $completionRequestedAt = $assignmentStatus === 'completed'
                        ? $resolvedAt->copy()->subHour()
                        : null;

                    $assignment = ReportAssignment::create([
                        'report_id' => $report->id,
                        'officer_id' => $representativeOfficer->id,
                        'team_id' => $team->id,
                        'assigned_by' => $governance->id,
                        'institution_name' => $team->institution_name,
                        'team_name' => $team->name,
                        'priority' => 'normal',
                        'status' => $assignmentStatus,
                        'accepted_at' => $acceptedAt,
                        'started_at' => $startedAt,
                        'completion_requested_at' => $completionRequestedAt,
                        'completed_at' => $resolvedAt,
                        'assigned_at' => $assignedAt,
                        'due_at' => $assignedAt->copy()->addDay(),
                        'ended_at' => $resolvedAt,
                        'note' => $isPreVerificationInspection
                            ? 'INSPEKSI DEMO — Periksa kondisi penerangan di lokasi sebelum laporan diverifikasi.'
                            : 'PENANGANAN DEMO — Tugas operasional setelah laporan diverifikasi.',
                    ]);

                    // assignment_type ditambahkan melalui migration terbaru.
                    // forceFill dipakai agar tetap tersimpan meskipun model lama
                    // belum memasukkan kolom ini ke daftar fillable.
                    $assignment->forceFill([
                        'assignment_type' => $assignmentType,
                        'created_at' => $assignedAt,
                        'updated_at' => $resolvedAt ?? $assignedAt,
                    ])->save();

                    $taskEvents = [
                        [0, 'assigned', $isPreVerificationInspection
                            ? 'Petugas ditugaskan untuk memeriksa laporan sebelum verifikasi.'
                            : 'Penugasan operasional diberikan kepada tim.'],
                    ];

                    if ($acceptedAt) {
                        $taskEvents[] = [1, 'accepted', 'Tugas diterima oleh petugas.'];
                    }

                    if ($startedAt) {
                        $taskEvents[] = [2, 'started', $isPreVerificationInspection
                            ? 'Pemeriksaan lapangan dimulai.'
                            : 'Penanganan lapangan dimulai.'];
                    }

                    if ($resolvedAt) {
                        $taskEvents[] = [
                            max(3, $assignedAt->diffInHours($resolvedAt)),
                            'supervisor_verified',
                            'Penyelesaian tugas telah diverifikasi supervisor.',
                        ];
                    }

                    foreach ($taskEvents as [$hoursAfterAssignment, $event, $note]) {
                        $eventAt = $assignedAt->copy()->addHours($hoursAfterAssignment);
                        $taskUpdate = OfficerTaskUpdate::create([
                            'report_assignment_id' => $assignment->id,
                            'user_id' => $event === 'assigned'
                                ? $governance->id
                                : $representativeOfficer->id,
                            'event' => $event,
                            'note' => $note,
                        ]);

                        $taskUpdate->timestamps = false;
                        $taskUpdate->forceFill([
                            'created_at' => $eventAt,
                            'updated_at' => $eventAt,
                        ])->save();
                        $taskUpdate->timestamps = true;
                    }
                }
            }
        });
    }
}
