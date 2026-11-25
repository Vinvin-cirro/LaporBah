<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class GovernanceTeamController extends Controller
{
    public function index()
    {
        $teams = Team::query()->withCount('members')->with('members:id,name,email')
            ->withCount(['assignments as active_assignments_count' => fn ($q) => $q->whereNull('ended_at')->where('status', '!=', 'completed')])
            ->orderBy('name')->paginate(12);

        return view('governance.teams.index', compact('teams'));
    }

    public function create()
    {
        $team = new Team(['is_active' => true]);
        $officers = User::where('role', 'officer')->orderBy('name')->get(['id', 'name', 'email']);
        return view('governance.teams.form', compact('team', 'officers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $team = Team::create(collect($data)->except('member_ids')->all());
            $team->members()->sync($data['member_ids']);
        });
        return redirect()->route('governance.teams.index')->with('success', 'Tim berhasil dibuat.');
    }

    public function edit(Team $team)
    {
        $team->load('members:id');
        $officers = User::where('role', 'officer')->orderBy('name')->get(['id', 'name', 'email']);
        return view('governance.teams.form', compact('team', 'officers'));
    }

    public function update(Request $request, Team $team)
    {
        $data = $this->validated($request, $team);
        DB::transaction(function () use ($data, $team) {
            $team->update(collect($data)->except('member_ids')->all());
            $team->members()->sync($data['member_ids']);
        });
        return redirect()->route('governance.teams.index')->with('success', 'Data tim berhasil diperbarui.');
    }

    public function toggle(Team $team)
    {
        $team->update(['is_active' => !$team->is_active]);
        return back()->with('success', $team->is_active ? 'Tim diaktifkan.' : 'Tim dinonaktifkan.');
    }

    private function validated(Request $request, ?Team $team = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('teams', 'name')->ignore($team?->id)],
            'institution_name' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where('role', 'officer')],
        ], [
            'member_ids.required' => 'Pilih minimal satu Officer untuk tim ini.',
            'member_ids.min' => 'Satu tim harus memiliki minimal satu Officer.',
            'member_ids.*.exists' => 'Anggota tim harus merupakan akun Officer.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }
}
