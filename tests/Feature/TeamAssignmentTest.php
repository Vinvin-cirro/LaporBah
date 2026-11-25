<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();
        return $user;
    }

    public function test_governance_can_create_multiple_teams_with_shared_officer_membership(): void
    {
        $governance = $this->user('governance');
        $officerA = $this->user('officer');
        $officerB = $this->user('officer');

        $first = $this->actingAs($governance)->post(route('governance.teams.store'), [
            'name' => 'Tim PJU 01',
            'institution_name' => 'Dinas Perhubungan',
            'member_ids' => [$officerA->id, $officerB->id],
            'is_active' => '1',
        ]);
        $first->assertRedirect(route('governance.teams.index'));

        $second = $this->actingAs($governance)->post(route('governance.teams.store'), [
            'name' => 'Tim Keamanan 01',
            'member_ids' => [$officerA->id],
            'is_active' => '1',
        ]);
        $second->assertRedirect(route('governance.teams.index'));

        $this->assertDatabaseCount('teams', 2);
        $this->assertDatabaseHas('team_user', ['team_id' => Team::where('name', 'Tim PJU 01')->value('id'), 'user_id' => $officerA->id]);
        $this->assertDatabaseHas('team_user', ['team_id' => Team::where('name', 'Tim Keamanan 01')->value('id'), 'user_id' => $officerA->id]);
    }

    public function test_every_member_of_assigned_team_can_open_the_team_task(): void
    {
        $governance = $this->user('governance');
        $officerA = $this->user('officer');
        $officerB = $this->user('officer');
        $team = Team::create(['name' => 'Tim PJU 01', 'is_active' => true]);
        $team->members()->sync([$officerA->id, $officerB->id]);
        $report = Report::create([
            'category' => 'pju_rusak',
            'description' => 'Lampu PJU mati.',
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'location_name' => 'Medan',
            'status' => 'verified',
        ]);

        $this->actingAs($governance)->post(route('governance.reports.assign', $report), [
            'team_id' => $team->id,
            'institution_name' => 'Dinas Perhubungan',
            'priority' => 'normal',
            'due_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'note' => 'Periksa dan perbaiki lampu PJU.',
        ])->assertRedirect();

        $this->assertDatabaseHas('report_assignments', [
            'report_id' => $report->id,
            'team_id' => $team->id,
            'officer_id' => $officerA->id,
        ]);

        $this->actingAs($officerB)
            ->get(route('officer.reports.show', $report))
            ->assertOk();
    }
}
