<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerReportListingTest extends TestCase
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

    private function assignReport(
        Report $report,
        User $officer,
        User $assignedBy,
        ?string $endedAt = null
    ): ReportAssignment {
        return ReportAssignment::create([
            'report_id' => $report->id,
            'officer_id' => $officer->id,
            'assigned_by' => $assignedBy->id,
            'assigned_at' => now(),
            'ended_at' => $endedAt,
        ]);
    }

    public function test_officer_can_see_reports_assigned_to_them(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officer, $governance);

        $response = $this->actingAs($officer)
            ->getJson(route('officer.reports.index'));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $report->id,
                'description' => 'Laporan pengujian',
            ]);
    }

    public function test_officer_cannot_see_reports_assigned_to_another_officer(): void
    {
        $officerA = $this->createUser('officer');
        $officerB = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officerB, $governance);

        $response = $this->actingAs($officerA)
            ->getJson(route('officer.reports.index'));

        $response->assertOk()
            ->assertJsonMissing([
                'id' => $report->id,
            ]);
    }

    public function test_officer_cannot_see_reports_with_ended_assignments(): void
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

        $response = $this->actingAs($officer)
            ->getJson(route('officer.reports.index'));

        $response->assertOk()
            ->assertJsonMissing([
                'id' => $report->id,
            ]);
    }

    public function test_governance_cannot_access_officer_reports(): void
    {
        $governance = $this->createUser('governance');

        $this->actingAs($governance)
            ->getJson(route('officer.reports.index'))
            ->assertForbidden();
    }

    public function test_citizen_cannot_access_officer_reports(): void
    {
        $citizen = $this->createUser('citizen');

        $this->actingAs($citizen)
            ->getJson(route('officer.reports.index'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_officer_reports(): void
    {
        $this->getJson(route('officer.reports.index'))
            ->assertUnauthorized();
    }
}
