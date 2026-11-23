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

    public function test_officer_accepts_and_starts_assignment_without_changing_citizen_report_status(): void
    {
        $officer = User::factory()->create(['role' => 'officer']);
        $governance = User::factory()->create(['role' => 'governance']);
        $report = Report::create([
            'category' => 'pju_rusak', 'description' => 'Lampu jalan mati',
            'latitude' => 3.5952, 'longitude' => 98.6722, 'status' => 'verified',
        ]);
        $assignment = ReportAssignment::create([
            'report_id' => $report->id, 'officer_id' => $officer->id,
            'assigned_by' => $governance->id, 'assigned_at' => now(),
        ]);

        $this->actingAs($officer)->post(route('officer.reports.accept', $report))->assertRedirect();
        $this->assertDatabaseHas('report_assignments', ['id' => $assignment->id, 'status' => 'accepted']);
        $this->actingAs($officer)->post(route('officer.reports.start', $report))->assertRedirect();
        $this->assertDatabaseHas('report_assignments', ['id' => $assignment->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'verified']);
    }

    public function test_officer_cannot_start_another_officers_assignment(): void
    {
        $officerA = User::factory()->create(['role' => 'officer']);
        $officerB = User::factory()->create(['role' => 'officer']);
        $governance = User::factory()->create(['role' => 'governance']);
        $report = Report::create([
            'category' => 'area_gelap', 'description' => 'Area minim penerangan',
            'latitude' => 3.5952, 'longitude' => 98.6722, 'status' => 'verified',
        ]);
        ReportAssignment::create([
            'report_id' => $report->id, 'officer_id' => $officerB->id,
            'assigned_by' => $governance->id, 'assigned_at' => now(),
        ]);

        $this->actingAs($officerA)->post(route('officer.reports.start', $report))->assertNotFound();
    }
}
