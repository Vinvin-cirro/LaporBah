<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerReportStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function createReport(string $status = 'verified'): Report
    {
        return Report::create([
            'category' => 'begal',
            'description' => 'Laporan pengujian status.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'location_name' => 'Jalan Sudirman',
            'status' => $status,
            'tracking_code' => strtoupper(fake()->unique()->bothify('????????????')),
        ]);
    }

    private function assignReport(
        Report $report,
        User $officer,
        User $governance,
        ?string $endedAt = null
    ): void {
        ReportAssignment::create([
            'report_id' => $report->id,
            'officer_id' => $officer->id,
            'assigned_by' => $governance->id,
            'assigned_at' => now(),
            'ended_at' => $endedAt,
        ]);
    }

    public function test_officer_can_update_verified_report_to_investigating(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->assignReport($report, $officer, $governance);

        $response = $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            [
                'status' => 'investigating',
                'note' => 'Petugas mulai melakukan pemeriksaan.',
            ]
        );

        $response->assertOk()
            ->assertJson([
                'old_status' => 'verified',
                'new_status' => 'investigating',
            ]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'investigating',
        ]);
    }

    public function test_officer_can_update_investigating_report_to_resolved(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('investigating');

        $this->assignReport($report, $officer, $governance);

        $response = $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'resolved']
        );

        $response->assertOk()
            ->assertJsonPath('new_status', 'resolved');
    }

    public function test_status_update_is_recorded_in_report_updates(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->assignReport($report, $officer, $governance);

        $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            [
                'status' => 'investigating',
                'note' => 'Petugas mulai melakukan pemeriksaan.',
            ]
        )->assertOk();

        $this->assertDatabaseHas('report_updates', [
            'report_id' => $report->id,
            'updated_by' => $officer->id,
            'old_status' => 'verified',
            'new_status' => 'investigating',
            'note' => 'Petugas mulai melakukan pemeriksaan.',
        ]);
    }

    public function test_officer_cannot_update_pending_report(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('pending');

        $this->assignReport($report, $officer, $governance);

        $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertUnprocessable();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'pending',
        ]);
    }

    public function test_officer_cannot_reject_report(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->assignReport($report, $officer, $governance);

        $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'rejected']
        )->assertUnprocessable();
    }

    public function test_officer_cannot_update_resolved_report_again(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('resolved');

        $this->assignReport($report, $officer, $governance);

        $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertUnprocessable();
    }

    public function test_officer_cannot_update_report_assigned_to_another_officer(): void
    {
        $officerA = $this->createUser('officer');
        $officerB = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->assignReport($report, $officerB, $governance);

        $this->actingAs($officerA)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertForbidden();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'verified',
        ]);
    }

    public function test_officer_cannot_update_report_with_ended_assignment(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->assignReport(
            $report,
            $officer,
            $governance,
            now()->toDateTimeString()
        );

        $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertForbidden();
    }

    public function test_officer_cannot_use_invalid_status_value(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->assignReport($report, $officer, $governance);

        $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'completed']
        )->assertUnprocessable();
    }

    public function test_governance_cannot_access_officer_status_endpoint(): void
    {
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->actingAs($governance)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertForbidden();
    }

    public function test_citizen_cannot_update_report_status(): void
    {
        $citizen = $this->createUser('citizen');
        $report = $this->createReport('verified');

        $this->actingAs($citizen)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertForbidden();
    }

    public function test_guest_cannot_update_report_status(): void
    {
        $report = $this->createReport('verified');

        $this->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertUnauthorized();
    }

    public function test_report_update_history_is_preserved(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport('verified');

        $this->assignReport($report, $officer, $governance);

        $this->actingAs($officer)->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertOk();

        $this->patchJson(
            "/officer/reports/{$report->id}/status",
            ['status' => 'resolved']
        )->assertOk();

        $this->assertDatabaseCount('report_updates', 2);

        $this->assertDatabaseHas('report_updates', [
            'report_id' => $report->id,
            'old_status' => 'verified',
            'new_status' => 'investigating',
        ]);

        $this->assertDatabaseHas('report_updates', [
            'report_id' => $report->id,
            'old_status' => 'investigating',
            'new_status' => 'resolved',
        ]);
    }
}
