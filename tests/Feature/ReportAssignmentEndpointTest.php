<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAssignmentEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        $user = User::factory()->create();

        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function createReport(): Report
    {
        return Report::create([
            'category' => 'begal',
            'description' => 'Laporan pengujian',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'status' => 'pending',
        ]);
    }

    public function test_governance_can_assign_officer(): void
    {
        $governance = $this->createUser('governance');
        $officer = $this->createUser('officer');
        $report = $this->createReport();

        $response = $this->actingAs($governance)
            ->post(route('governance.reports.assign', $report), [
                'officer_id' => $officer->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('report_assignments', [
            'report_id' => $report->id,
            'officer_id' => $officer->id,
            'assigned_by' => $governance->id,
            'ended_at' => null,
        ]);
    }

    public function test_superadmin_can_assign_officer(): void
    {
        $admin = $this->createUser('superadmin');
        $officer = $this->createUser('officer');
        $report = $this->createReport();

        $response = $this->actingAs($admin)
            ->post(route('governance.reports.assign', $report), [
                'officer_id' => $officer->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('report_assignments', [
            'report_id' => $report->id,
            'officer_id' => $officer->id,
        ]);
    }

    public function test_citizen_cannot_assign_officer(): void
    {
        $citizen = $this->createUser('citizen');
        $officer = $this->createUser('officer');
        $report = $this->createReport();

        $this->actingAs($citizen)
            ->post(route('governance.reports.assign', $report), [
                'officer_id' => $officer->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('report_assignments', 0);
    }

    public function test_officer_cannot_assign_another_officer(): void
    {
        $officerA = $this->createUser('officer');
        $officerB = $this->createUser('officer');
        $report = $this->createReport();

        $this->actingAs($officerA)
            ->post(route('governance.reports.assign', $report), [
                'officer_id' => $officerB->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('report_assignments', 0);
    }

    
    public function test_guest_cannot_assign_officer(): void
    {
        $officer = $this->createUser('officer');
        $report = $this->createReport();

        $response = $this->postJson(
            route('governance.reports.assign', $report),
            [
                'officer_id' => $officer->id,
            ]
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount('report_assignments', 0);
    }

    public function test_citizen_cannot_be_selected_as_officer(): void
    {
        $governance = $this->createUser('governance');
        $citizen = $this->createUser('citizen');
        $report = $this->createReport();

        $this->actingAs($governance)
            ->post(route('governance.reports.assign', $report), [
                'officer_id' => $citizen->id,
            ])
            ->assertSessionHasErrors('officer_id');

        $this->assertDatabaseCount('report_assignments', 0);
    }
}
