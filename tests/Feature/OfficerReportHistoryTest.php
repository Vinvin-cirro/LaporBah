<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerReportHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function createReport(): Report
    {
        return Report::create([
            'category' => 'begal',
            'description' => 'Terjadi dugaan pembegalan di Jalan Sudirman.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'location_name' => 'Jalan Sudirman',
            'status' => 'resolved',
            'tracking_code' => 'A8K2M9P4Q7XZ',
        ]);
    }

    private function assignReport(
        Report $report,
        User $officer,
        User $assignedBy,
        ?string $endedAt = null
    ): void {
        ReportAssignment::create([
            'report_id' => $report->id,
            'officer_id' => $officer->id,
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
            'ended_at' => $endedAt,
        ]);
    }

    private function createUpdate(
        Report $report,
        User $updater,
        string $oldStatus,
        string $newStatus,
        ?string $note = null
    ): ReportUpdate {
        return ReportUpdate::create([
            'report_id' => $report->id,
            'updated_by' => $updater->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
        ]);
    }

    public function test_officer_can_view_assigned_report_history(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officer, $governance);

        $this->createUpdate(
            $report,
            $governance,
            'pending',
            'verified',
            'Laporan telah diverifikasi.'
        );

        $this->createUpdate(
            $report,
            $officer,
            'verified',
            'investigating',
            'Petugas mulai melakukan investigasi.'
        );

        $response = $this->actingAs($officer)
            ->getJson("/officer/reports/{$report->id}/history");

        $response->assertOk()
            ->assertJsonPath('report_id', $report->id)
            ->assertJsonCount(2, 'history')
            ->assertJsonPath('history.0.old_status', 'verified')
            ->assertJsonPath('history.0.new_status', 'investigating')
            ->assertJsonPath(
                'history.0.note',
                'Petugas mulai melakukan investigasi.'
            )
            ->assertJsonPath('history.0.updated_by', $officer->name)
            ->assertJsonPath('history.1.old_status', 'pending')
            ->assertJsonPath('history.1.new_status', 'verified')
            ->assertJsonPath(
                'history.1.updated_by',
                $governance->name
            )
            ->assertJsonStructure([
                'report_id',
                'history' => [
                    '*' => [
                        'id',
                        'old_status',
                        'new_status',
                        'note',
                        'updated_by',
                        'created_at',
                    ],
                ],
            ]);
    }

    public function test_officer_cannot_view_history_assigned_to_another_officer(): void
    {
        $officerA = $this->createUser('officer');
        $officerB = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officerB, $governance);

        $this->actingAs($officerA)
            ->getJson("/officer/reports/{$report->id}/history")
            ->assertForbidden();
    }

    public function test_officer_cannot_view_history_with_ended_assignment(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport(
            $report,
            $officer,
            $governance,
            now()->toDateTimeString()
        );

        $this->actingAs($officer)
            ->getJson("/officer/reports/{$report->id}/history")
            ->assertForbidden();
    }

    public function test_governance_cannot_access_officer_report_history(): void
    {
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->actingAs($governance)
            ->getJson("/officer/reports/{$report->id}/history")
            ->assertForbidden();
    }

    public function test_citizen_cannot_access_officer_report_history(): void
    {
        $citizen = $this->createUser('citizen');
        $report = $this->createReport();

        $this->actingAs($citizen)
            ->getJson("/officer/reports/{$report->id}/history")
            ->assertForbidden();
    }

    public function test_guest_cannot_access_officer_report_history(): void
    {
        $report = $this->createReport();

        $this->getJson("/officer/reports/{$report->id}/history")
            ->assertUnauthorized();
    }

    public function test_report_history_returns_404_when_report_does_not_exist(): void
    {
        $officer = $this->createUser('officer');

        $this->actingAs($officer)
            ->getJson('/officer/reports/99999/history')
            ->assertNotFound();
    }
}
