<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\OfficerTaskUpdate;
use App\Models\ReportUpdate;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class GovernanceReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'category' => ['nullable', 'in:pju_rusak,area_gelap,begal_curas,lain_lain'],
            'status' => ['nullable', 'string', 'max:20'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $reports = Report::query()
            ->select([
                'id',
                'tracking_code',
                'category',
                'kecamatan',
                'location_name',
                'latitude',
                'longitude',
                'status',
                'created_at',
            ])
            ->when($validated['category'] ?? null, fn ($query, $category) =>
                $query->where('category', $category))
            ->when($validated['status'] ?? null, fn ($query, $status) =>
                $query->where('status', $status))
            ->when($validated['kecamatan'] ?? null, fn ($query, $kecamatan) =>
                $query->where('kecamatan', $kecamatan))
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('tracking_code', 'like', "%{$search}%")
                        ->orWhere('location_name', 'like', "%{$search}%")
                        ->orWhere('kecamatan', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($reports);
        }

        return view('governance.reports.index', [
            'reports' => $reports,
            'filters' => $validated,
        ]);
    }

    public function show(Request $request, Report $report)
    {
        $report->load([
            'images',
            'updates.updater',
            'activeAssignment.officer',
            'activeAssignment.team.members',
            'activeAssignment.documents',
            'activeAssignment.updates.user',
            'assignments.officer',
            'assignments.team.members',
            'assignments.assigner',
            'duplicateOf',
        ]);

        $officers = User::query()
            ->where('role', 'officer')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $teams = Team::query()->where('is_active', true)->whereHas('members', fn ($q) => $q->where('role', 'officer'))
            ->with('members:id,name,email')->orderBy('name')->get();

        $similarReports = collect();
        if ($report->latitude !== null && $report->longitude !== null) {
            // Ambil kandidat dalam radius koordinat kasar, lalu hitung jarak sebenarnya.
            $latitude = (float) $report->latitude;
            $longitude = (float) $report->longitude;
            $candidates = Report::query()
                ->where('id', '!=', $report->id)
                ->whereBetween('latitude', [$latitude - 0.02, $latitude + 0.02])
                ->whereBetween('longitude', [$longitude - 0.02, $longitude + 0.02])
                ->latest()
                ->limit(100)
                ->get(['id', 'tracking_code', 'category', 'kecamatan', 'location_name', 'latitude', 'longitude', 'status', 'created_at']);

            $similarReports = $candidates->map(function ($candidate) use ($latitude, $longitude) {
                $lat2 = (float) $candidate->latitude;
                $lon2 = (float) $candidate->longitude;
                $latDelta = deg2rad($lat2 - $latitude);
                $lonDelta = deg2rad($lon2 - $longitude);
                $a = sin($latDelta / 2) ** 2
                    + cos(deg2rad($latitude)) * cos(deg2rad($lat2))
                    * sin($lonDelta / 2) ** 2;
                $candidate->distance_meters = 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
                return $candidate;
            })
                ->filter(fn ($candidate) => $candidate->distance_meters <= 2000)
                ->sortBy('distance_meters')
                ->take(5)
                ->values();
        }

        if (!$request->expectsJson()) {
            return view('governance.reports.show', compact(
                'report', 'officers', 'teams', 'similarReports'
            ));
        }

        return response()->json([
            'id' => $report->id,
            'tracking_code' => $report->tracking_code,
            'category' => $report->category,
            'description' => $report->description,
            'location_name' => $report->location_name,
            'kecamatan' => $report->kecamatan,
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'status' => $report->status,
            'incident_at' => $report->incident_at,
            'submitted_at' => $report->created_at,
            'duplicate_of_report_id' => $report->duplicate_of_report_id,
            'photos' => $report->images->map(fn ($image) => [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'image_url' => Storage::disk('public')->url($image->image_path),
            ]),
        ]);
    }

    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'action' => [
                'nullable',
                'in:verify,request_info,mark_duplicate,reject,reinspect,status_change',
            ],
            'status' => [
                'required_without:action',
                'required_if:action,status_change',
                'nullable',
                'in:pending,verifying,awaiting_approval,verified,investigating,resolved,rejected',
            ],
            'note' => [
                'nullable',
                'required_if:action,verify,request_info,mark_duplicate,reject,reinspect',
                'string',
                'max:1000',
            ],
            'duplicate_of_report_id' => [
                'required_if:action,mark_duplicate',
                'nullable',
                'integer',
                'exists:reports,id',
            ],
        ]);

        $action = $validated['action'] ?? 'status_change';

        $result = DB::transaction(function () use ($report, $validated, $action, $request) {
            $currentReport = Report::query()
                ->whereKey($report->id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldStatus = $currentReport->status;
            $newStatus = $oldStatus;

            if ($action === 'verify') {
                $newStatus = 'verified';
            } elseif (in_array($action, ['reject', 'mark_duplicate'], true)) {
                $newStatus = 'rejected';
            } elseif ($action === 'reinspect') {
                if ($oldStatus !== 'awaiting_approval') {
                    throw ValidationException::withMessages([
                        'action' => 'Pemeriksaan ulang hanya dapat diminta setelah Officer mengirim hasil inspeksi.',
                    ]);
                }

                $newStatus = 'verifying';
            } elseif ($action === 'status_change') {
                $newStatus = $validated['status'];
            }

            if ($action === 'request_info' && $oldStatus !== 'pending') {
                throw ValidationException::withMessages([
                    'action' => 'Permintaan informasi tambahan hanya dapat dikirim saat laporan menunggu verifikasi.',
                ]);
            }

            if ($action === 'mark_duplicate') {
                $duplicateId = (int) $validated['duplicate_of_report_id'];

                if ($duplicateId === $currentReport->id) {
                    throw ValidationException::withMessages([
                        'duplicate_of_report_id' => 'Laporan tidak dapat ditandai sebagai duplikat dirinya sendiri.',
                    ]);
                }

                $currentReport->duplicate_of_report_id = $duplicateId;
            }

            $allowedTransitions = [
                'pending' => ['verifying', 'verified', 'rejected'],
                'verifying' => ['awaiting_approval', 'verified', 'rejected'],
                'awaiting_approval' => ['verified', 'verifying', 'rejected'],
                'verified' => ['investigating', 'rejected'],
                'investigating' => ['resolved', 'rejected'],
                'resolved' => [],
                'rejected' => [],
            ];

            if (
                $newStatus !== $oldStatus &&
                !in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)
            ) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status tidak diizinkan dari status saat ini.',
                ]);
            }

            // Pemeriksaan ulang: aktifkan kembali tugas inspeksi Officer.
            if ($action === 'reinspect') {
                $inspectionAssignment = ReportAssignment::query()
                    ->where('report_id', $currentReport->id)
                    ->where('assignment_type', 'inspection')
                    ->whereNull('ended_at')
                    ->lockForUpdate()
                    ->first();

                if (!$inspectionAssignment) {
                    throw ValidationException::withMessages([
                        'action' => 'Penugasan inspeksi aktif tidak ditemukan. Silakan tugaskan Officer kembali.',
                    ]);
                }

                $inspectionAssignment->update([
                    'status' => 'in_progress',
                    'completion_requested_at' => null,
                ]);

                OfficerTaskUpdate::create([
                    'report_assignment_id' => $inspectionAssignment->id,
                    'user_id' => $request->user()->id,
                    'event' => 'inspection_reopened',
                    'note' => $validated['note'],
                ]);
            }

            $currentReport->status = $newStatus;
            $currentReport->save();

            // Tutup penugasan inspeksi ketika Governance sudah mengambil keputusan.
            if (in_array($action, ['verify', 'reject', 'mark_duplicate'], true)) {
                $inspectionAssignment = ReportAssignment::query()
                    ->where('report_id', $currentReport->id)
                    ->where('assignment_type', 'inspection')
                    ->whereNull('ended_at')
                    ->lockForUpdate()
                    ->first();

                if ($inspectionAssignment) {
                    $wasAwaitingApproval = $oldStatus === 'awaiting_approval';

                    $inspectionAssignment->update([
                        'status' => $action === 'verify' && $wasAwaitingApproval
                            ? 'completed'
                            : 'cancelled',
                        'ended_at' => now(),
                    ]);

                    OfficerTaskUpdate::create([
                        'report_assignment_id' => $inspectionAssignment->id,
                        'user_id' => $request->user()->id,
                        'event' => $action === 'verify'
                            ? 'inspection_approved'
                            : 'inspection_cancelled',
                        'note' => $action === 'verify'
                            ? 'Hasil inspeksi disetujui oleh Governance.'
                            : 'Penugasan inspeksi ditutup oleh Governance.',
                    ]);
                }
            }

            ReportUpdate::create([
                'report_id' => $currentReport->id,
                'updated_by' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'action' => $action,
                'note' => $request->input('note'),
            ]);

            return [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ];
        });

        $message = match ($action) {
            'verify' => 'Laporan berhasil diverifikasi.',
            'request_info' => 'Permintaan informasi tambahan berhasil dicatat.',
            'mark_duplicate' => 'Laporan berhasil ditandai sebagai duplikat.',
            'reject' => 'Laporan berhasil ditolak.',
            'reinspect' => 'Pemeriksaan ulang berhasil diminta. Officer dapat melanjutkan tugas inspeksi.',
            default => 'Status laporan berhasil diperbarui.',
        };

        if (!$request->expectsJson()) {
            return redirect()
                ->route('governance.reports.show', $report)
                ->with('success', $message);
        }

        return response()->json([
            'message' => $message,
            'report_id' => $report->id,
            'old_status' => $result['old_status'],
            'new_status' => $result['new_status'],
            'action' => $action,
        ]);
    }
}
