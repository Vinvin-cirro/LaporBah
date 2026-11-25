<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\OfficerTaskUpdate;
use App\Models\OfficerTaskDocument;
use App\Models\OfficerPatrolLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfficerReportController extends Controller
{
    private function tasks(Request $request)
    {
        return ReportAssignment::query()->with(['report.images','officer','assigner','team.members'])
            ->whereNull('ended_at')
            ->where(function ($query) use ($request) {
                $query->whereHas('team.members', fn ($members) => $members->where('users.id', $request->user()->id))
                    ->orWhere(fn ($legacy) => $legacy->whereNull('team_id')->where('officer_id', $request->user()->id));
            });
    }

    private function owned(
        Request $request,
        Report $report,
        int $statusCode = 404
    ): ReportAssignment {
        $assignment = ReportAssignment::query()
            ->with([
                'report.images',
                'team.members',
                'updates.user',
                'documents',
                'patrolLogs.images'
            ])
            ->where('report_id', $report->id)
            ->whereNull('ended_at')
            ->where(function ($query) use ($request) {
                $query->whereHas('team.members', fn ($members) => $members->where('users.id', $request->user()->id))
                    ->orWhere(fn ($legacy) => $legacy->whereNull('team_id')->where('officer_id', $request->user()->id));
            })
            ->first();

        abort_unless($assignment, $statusCode);

        return $assignment;
    }

    private function log(ReportAssignment $assignment, Request $request, string $event, ?string $note = null, array $metadata = []): void
    {
        OfficerTaskUpdate::create([
            'report_assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'event' => $event, 'note' => $note, 'metadata' => $metadata ?: null,
        ]);
    }

    public function dashboard(Request $request)
    {
        $base = $this->tasks($request);
        $tasks = (clone $base)->whereNotIn('status', ['completed'])->latest('assigned_at')->limit(5)->get();
        $recent = (clone $base)->with('updates.user')->get()->flatMap(fn ($a) => $a->updates->map(fn ($u) => [
            'event' => $u->event, 'note' => $u->note, 'created_at' => $u->created_at, 'report' => $a->report,
        ]))->sortByDesc('created_at')->take(8);
        $deadline = (clone $base)->whereNotNull('due_at')->whereNotIn('status', ['completed'])->orderBy('due_at')->first();
        return view('officer.dashboard', compact('tasks','recent','deadline'));
    }

    public function map(Request $request)
    {
        $assignments = $this->tasks($request)->get()->filter(fn ($a) => $a->report && $a->report->latitude && $a->report->longitude);
        $points = $assignments->map(fn ($a) => [
            'id' => $a->report_id, 'assignment_id' => $a->id, 'lat' => (float)$a->report->latitude,
            'lng' => (float)$a->report->longitude, 'category' => $a->report->category,
            'label' => $a->report->category_label, 'location' => $a->report->location_name,
            'status' => $a->status, 'url' => route('officer.reports.show',$a->report_id),
            'description' => $a->report->description, 'date' => $a->report->created_at?->format('d/m/Y H:i'),
            'team' => $a->team?->name ?? $a->team_name,
        ])->values();
        return view('officer.map', compact('points'));
    }

    public function index(Request $request)
    {
        $query = $this->tasks($request);
        if ($request->filled('status') && $request->status !== 'all') $query->where('status', $request->status);
        if ($request->filled('category') && $request->category !== 'all') $query->whereHas('report', fn($q) => $q->where('category',$request->category));
        $assignments = $query->latest('assigned_at')->paginate(12)->withQueryString();
        if ($request->expectsJson()) {
            return response()->json($assignments->through(fn($a) => [
                'id' => $a->report->id, 'assignment_id' => $a->id,
                'tracking_code' => $a->report->tracking_code, 'category' => $a->report->category,
                'description' => $a->report->description, 'location_name' => $a->report->location_name,
                'latitude' => $a->report->latitude, 'longitude' => $a->report->longitude,
                'status' => $a->report->status, 'assignment_status' => $a->status,
                'priority' => $a->priority, 'due_at' => $a->due_at,
            ]));
        }
        return view('officer.tasks.index', compact('assignments'));
    }

    public function show(Request $request, Report $report)
    {
        $assignment = $this->owned($request, $report, 403);
        if ($request->expectsJson()) {
            return response()->json([
                'id'=>$report->id, 'tracking_code'=>$report->tracking_code, 'category'=>$report->category,
                'description'=>$report->description, 'location_name'=>$report->location_name,
                'latitude'=>$report->latitude, 'longitude'=>$report->longitude, 'status'=>$report->status,
                'assignment_status'=>$assignment->status, 'due_at'=>$assignment->due_at,
                'photos'=>$report->images->map(fn($image)=>['id'=>$image->id,'image_path'=>$image->image_path,'image_url'=>Storage::disk('public')->url($image->image_path)]),
            ]);
        }
        return view('officer.tasks.show', compact('assignment','report'));
    }

    public function accept(Request $request, Report $report)
    {
        $assignment = $this->owned($request, $report, 404);
        abort_unless($assignment->status === 'assigned', 422, 'Tugas tidak berada pada status yang dapat diterima.');
        $assignment->update(['status'=>'accepted','accepted_at'=>now()]);
        $this->log($assignment,$request,'accepted','Tugas diterima oleh petugas.');
        return back()->with('success','Tugas berhasil diterima.');
    }

    public function start(Request $request, Report $report)
    {
        $assignment = $this->owned($request, $report, 404);
        abort_unless(in_array($assignment->status,['accepted','assigned'],true),422);
        $assignment->update(['status'=>'in_progress','started_at'=>now()]);
        $this->log($assignment,$request,'started','Pekerjaan lapangan dimulai.');
        return back()->with('success','Pekerjaan dimulai.');
    }

    public function progress(Request $request, Report $report)
    {
        $data = $request->validate([
            'note'=>['nullable','string','max:5000'],
            'inspection_result'=>['nullable','string','max:3000'], 'damage_type'=>['nullable','string','max:1000'], 'repair_action'=>['nullable','string','max:3000'],
            'survey_result'=>['nullable','string','max:3000'], 'lighting_need'=>['nullable','string','max:2000'], 'installation_action'=>['nullable','string','max:3000'],
        ]);
        $note = collect($data)->filter(fn($value) => filled($value))->map(fn($value,$key) => ucfirst(str_replace('_',' ',$key)).': '.$value)->implode("\n");
        if (blank($note)) throw ValidationException::withMessages(['note'=>'Isi minimal satu catatan progres.']);
        $assignment = $this->owned($request,$report);
        abort_unless(in_array($assignment->status,['accepted','in_progress'],true),422);
        if ($assignment->status === 'accepted') $assignment->update(['status'=>'in_progress','started_at'=>now()]);
        $this->log($assignment,$request,'progress',$note);
        return back()->with('success','Catatan progres berhasil disimpan.');
    }

    public function upload(Request $request, Report $report)
    {
        $data = $request->validate([
            'kind'=>['required','in:before,after,survey,installation,patrol,other'],
            'caption'=>['nullable','string','max:255'],
            'photos'=>['required','array','min:1','max:10'],
            'photos.*'=>['required','image','mimes:jpg,jpeg,png','max:5120'],
        ]);
        $assignment = $this->owned($request,$report);
        abort_unless(in_array($assignment->status,['accepted','in_progress'],true),422);
        DB::transaction(function () use ($request,$data,$assignment) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('officer/tasks/'.$assignment->id,'public');
                OfficerTaskDocument::create(['report_assignment_id'=>$assignment->id,'user_id'=>$request->user()->id,'kind'=>$data['kind'],'file_path'=>$path,'caption'=>$data['caption'] ?? null]);
            }
            $this->log($assignment,$request,'document_uploaded','Dokumentasi '.$data['kind'].' diunggah.');
        });
        return back()->with('success','Dokumentasi berhasil diunggah.');
    }

    public function patrol(Request $request, Report $report)
    {
        $data = $request->validate([
            'patrol_date'=>['required','date'], 'started_at'=>['required','date_format:H:i'],
            'ended_at'=>['nullable','date_format:H:i','after:started_at'], 'location'=>['required','string','max:255'],
            'monitoring_result'=>['required','string','max:5000'], 'findings'=>['nullable','string','max:3000'],
            'notes'=>['nullable','string','max:3000'], 'photos'=>['required','array','min:1','max:10'],
            'photos.*'=>['image','mimes:jpg,jpeg,png','max:5120'],
        ]);
        $assignment = $this->owned($request,$report);
        abort_unless(in_array($assignment->status,['accepted','in_progress'],true),422);
        DB::transaction(function () use ($request,$data,$assignment) {
            if ($assignment->status === 'accepted') $assignment->update(['status'=>'in_progress','started_at'=>now()]);
            $log = OfficerPatrolLog::create([
                'report_assignment_id'=>$assignment->id,'user_id'=>$request->user()->id,
                'patrol_date'=>$data['patrol_date'],'started_at'=>$data['started_at'],'ended_at'=>$data['ended_at'] ?? null,
                'location'=>$data['location'],'monitoring_result'=>$data['monitoring_result'],
                'findings'=>$data['findings'] ?? null,'notes'=>$data['notes'] ?? null,
            ]);
            foreach ($request->file('photos') as $photo) $log->images()->create(['file_path'=>$photo->store('officer/patrols/'.$assignment->id,'public')]);
            $this->log($assignment,$request,'patrol_logged','Laporan patroli '.$data['patrol_date'].' disimpan.',['patrol_log_id'=>$log->id]);
        });
        return back()->with('success','Laporan patroli harian berhasil disimpan.');
    }

    public function complete(Request $request, Report $report)
    {
        $data = $request->validate(['completion_note'=>['required','string','max:5000']]);
        $assignment = $this->owned($request,$report);
        abort_unless($assignment->status === 'in_progress',422,'Pekerjaan harus dimulai sebelum diajukan selesai.');
        if ($report->category === 'begal_curas' && $assignment->patrolLogs()->count() < 1) {
            throw ValidationException::withMessages(['completion_note'=>'Tambahkan minimal satu laporan patroli harian sebelum mengajukan penyelesaian.']);
        }
        $kinds = $assignment->documents()->pluck('kind')->unique()->all();
        if ($assignment->assignment_type === 'inspection') {
            if (!in_array($report->category, ['pju_rusak', 'area_gelap'], true)) {
                abort(422, 'Kategori laporan ini tidak mendukung inspeksi pra-verifikasi.');
            }
            if ($assignment->documents()->count() < 1) {
                throw ValidationException::withMessages(['completion_note' => 'Unggah minimal satu foto bukti pemeriksaan sebelum mengirim hasil inspeksi.']);
            }
            if (!$assignment->updates()->where('event', 'progress')->exists()) {
                throw ValidationException::withMessages(['completion_note' => 'Simpan hasil pemeriksaan pada Catatan & laporan progres terlebih dahulu.']);
            }
        } elseif ($report->category === 'pju_rusak') {
            if (!in_array('before',$kinds,true) || !in_array('after',$kinds,true)) {
                throw ValidationException::withMessages(['completion_note'=>'PJU Rusak memerlukan foto kondisi sebelum dan sesudah perbaikan.']);
            }
        } elseif ($assignment->assignment_type !== 'inspection' && $report->category === 'area_gelap') {
            if (!in_array('survey',$kinds,true) && !in_array('before',$kinds,true)) {
                throw ValidationException::withMessages(['completion_note'=>'Area Gelap memerlukan dokumentasi kondisi awal atau survei.']);
            }
            if (preg_match('/pasang|instal/i', $assignment->note ?? '') && !in_array('installation',$kinds,true) && !in_array('after',$kinds,true)) {
                throw ValidationException::withMessages(['completion_note'=>'Instruksi mencakup pemasangan; unggah dokumentasi pemasangan terlebih dahulu.']);
            }
        } elseif ($assignment->assignment_type !== 'inspection' && $report->category !== 'begal_curas' && !$kinds) {
            throw ValidationException::withMessages(['completion_note'=>'Unggah dokumentasi lapangan sebelum mengajukan penyelesaian.']);
        }
        DB::transaction(function () use ($assignment, $request, $data, $report) {
            $assignment->update([
                'status' => 'awaiting_confirmation',
                'completion_requested_at' => now(),
            ]);

            if ($assignment->assignment_type === 'inspection') {
                $report->update([
                    'status' => 'awaiting_approval',
                ]);
            }

            $this->log(
                $assignment,
                $request,
                'completion_requested',
                $data['completion_note']
            );
        });
        return back()->with('success','Konfirmasi penyelesaian dikirim kepada supervisor. Status laporan masyarakat tidak diubah.');
    }
    public function history(Request $request, Report $report)
    {
        $assignment = $this->owned($request, $report, 403);

        $history = $assignment->updates->map(fn($update) => [
            'id' => $update->id,
            'event' => $update->event,
            'note' => $update->note,
            'updated_by' => $update->user?->name,
            'created_at' => $update->created_at,
        ]);

        // Gunakan riwayat laporan lama jika belum ada
        // aktivitas pada workflow Officer.
        if ($history->isEmpty()) {
            $history = $report->updates()
                ->with('updater:id,name')
                ->orderByDesc('id')
                ->get()
                ->map(fn($update) => [
                    'id' => $update->id,
                    'old_status' => $update->old_status,
                    'new_status' => $update->new_status,
                    'note' => $update->note,
                    'updated_by' => $update->updater?->name,
                    'created_at' => $update->created_at,
                ]);
        }

        return response()->json([
            'report_id' => $report->id,
            'assignment_status' => $assignment->status,
            'history' => $history,
        ]);
    }

}
