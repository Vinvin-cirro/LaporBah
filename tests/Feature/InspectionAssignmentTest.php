<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectionAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();
        return $user;
    }

    private function report(string $category = 'pju_rusak'): Report
    {
        return Report::create([
            'category' => $category,
            'description' => 'Perlu pemeriksaan lapangan',
            'latitude' => 3.5952,
            'longitude' => 98.6722,
            'location_name' => 'Medan',
            'status' => 'pending',
        ]);
    }

    private function teamWithOfficer(): array
    {
        $officer = $this->user('officer');
        $team = Team::create(['name' => 'Tim Inspeksi', 'is_active' => true]);
        $team->members()->attach($officer->id);
        return [$team, $officer];
    }

    public function test_governance_can_assign_inspection_for_pending_pju_report_without_verifying_it(): void
    {
        $governance = $this->user('governance');
        [$team] = $this->teamWithOfficer();
        $report = $this->report('pju_rusak');

        $this->actingAs($governance)->post(route('governance.reports.assign', $report), [
            'assignment_type' => 'inspection', 'team_id' => $team->id,
            'priority' => 'normal', 'due_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'note' => 'Periksa kondisi lampu di lokasi.',
        ])->assertRedirect(route('governance.reports.show', $report));

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'pending']);
        $this->assertDatabaseHas('report_assignments', [
            'report_id' => $report->id, 'team_id' => $team->id, 'assignment_type' => 'inspection', 'status' => 'assigned',
        ]);
    }

    public function test_area_gelap_can_be_assigned_for_inspection(): void
    {
        $governance = $this->user('governance');
        [$team] = $this->teamWithOfficer();
        $report = $this->report('area_gelap');

        $this->actingAs($governance)->post(route('governance.reports.assign', $report), [
            'assignment_type' => 'inspection', 'team_id' => $team->id,
            'priority' => 'normal', 'due_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'note' => 'Periksa kondisi penerangan lokasi.',
        ])->assertRedirect();

        $this->assertDatabaseHas('report_assignments', ['report_id' => $report->id, 'assignment_type' => 'inspection']);
    }

    public function test_inspection_cannot_be_assigned_to_other_categories(): void
    {
        $governance = $this->user('governance');
        [$team] = $this->teamWithOfficer();
        $report = $this->report('begal_curas');

        $this->actingAs($governance)->from(route('governance.reports.show', $report))
            ->post(route('governance.reports.assign', $report), [
                'assignment_type' => 'inspection', 'team_id' => $team->id,
                'priority' => 'normal', 'due_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'note' => 'Pemeriksaan.',
            ])->assertRedirect(route('governance.reports.show', $report))
            ->assertSessionHasErrors('assignment_type');

        $this->assertDatabaseCount('report_assignments', 0);
    }
}
