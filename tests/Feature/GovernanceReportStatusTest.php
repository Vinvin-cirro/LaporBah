<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceReportStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createReport(string $status = 'pending'): Report
    {
        return Report::create([
            'category' => 'begal',
            'description' => 'Laporan pengujian status.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'location_name' => 'Jalan Sudirman',
            'status' => $status,
            'tracking_code' => strtoupper(
                fake()->unique()->bothify('????????????')
            ),
        ]);
    }

    private function createSuperadmin(): User
    {
        return User::factory()->create([
            'role' => 'superadmin',
        ]);
    }

    public function test_governance_cannot_update_report_status(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $report = $this->createReport();

        $this->actingAs($governance)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'verified']
        )->assertForbidden();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'pending',
        ]);
    }

    public function test_superadmin_can_update_report_status(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport();

        $response = $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            [
                'status' => 'verified',
                'note' => 'Laporan sudah diperiksa.',
            ]
        );

        $response->assertOk()
            ->assertJson([
                'old_status' => 'pending',
                'new_status' => 'verified',
            ]);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'verified',
        ]);
    }

    public function test_status_update_is_recorded_in_report_updates(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport();

        $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            [
                'status' => 'verified',
                'note' => 'Bukti awal telah diperiksa.',
            ]
        )->assertOk();

        $this->assertDatabaseHas('report_updates', [
            'report_id' => $report->id,
            'updated_by' => $superadmin->id,
            'old_status' => 'pending',
            'new_status' => 'verified',
            'note' => 'Bukti awal telah diperiksa.',
        ]);
    }

    public function test_status_can_move_from_verified_to_investigating(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport('verified');

        $response = $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'investigating']
        );

        $response->assertOk()
            ->assertJsonPath('new_status', 'investigating');
    }

    public function test_status_can_move_from_investigating_to_resolved(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport('investigating');

        $response = $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'resolved']
        );

        $response->assertOk()
            ->assertJsonPath('new_status', 'resolved');
    }

    public function test_pending_report_can_be_rejected(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport();

        $response = $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            [
                'status' => 'rejected',
                'note' => 'Laporan tidak dapat diverifikasi.',
            ]
        );

        $response->assertOk()
            ->assertJsonPath('new_status', 'rejected');
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport();

        $response = $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'resolved']
        );

        $response->assertUnprocessable();

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('report_updates', 0);
    }

    public function test_resolved_report_cannot_be_updated_again(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport('resolved');

        $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'pending']
        )->assertUnprocessable();
    }

    public function test_rejected_report_cannot_be_updated_again(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport('rejected');

        $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'verified']
        )->assertUnprocessable();
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport();

        $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'completed']
        )->assertUnprocessable();
    }

    public function test_citizen_cannot_update_report_status(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
        ]);

        $report = $this->createReport();

        $this->actingAs($citizen)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'verified']
        )->assertForbidden();
    }

    public function test_officer_cannot_update_report_status_through_superadmin_endpoint(): void
    {
        $officer = User::factory()->create([
            'role' => 'officer',
        ]);

        $report = $this->createReport();

        $this->actingAs($officer)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'verified']
        )->assertForbidden();
    }

    public function test_guest_cannot_update_report_status(): void
    {
        $report = $this->createReport();

        $this->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'verified']
        )->assertUnauthorized();
    }

    public function test_report_update_history_is_preserved(): void
    {
        $superadmin = $this->createSuperadmin();

        $report = $this->createReport();

        $this->actingAs($superadmin)->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'verified']
        )->assertOk();

        $this->patchJson(
            "/governance/reports/{$report->id}/status",
            ['status' => 'investigating']
        )->assertOk();

        $this->assertDatabaseCount('report_updates', 2);

        $this->assertDatabaseHas('report_updates', [
            'report_id' => $report->id,
            'old_status' => 'pending',
            'new_status' => 'verified',
        ]);

        $this->assertDatabaseHas('report_updates', [
            'report_id' => $report->id,
            'old_status' => 'verified',
            'new_status' => 'investigating',
        ]);
    }
}
