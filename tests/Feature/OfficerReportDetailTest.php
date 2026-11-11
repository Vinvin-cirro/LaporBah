<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfficerReportDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

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
            'status' => 'pending',
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

    public function test_officer_can_view_assigned_report_detail(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officer, $governance);

        $response = $this->actingAs($officer)
            ->getJson("/officer/reports/{$report->id}");

        $response->assertOk()
            ->assertJson([
                'id' => $report->id,
                'tracking_code' => 'A8K2M9P4Q7XZ',
                'category' => 'begal',
                'description' => 'Terjadi dugaan pembegalan di Jalan Sudirman.',
                'location_name' => 'Jalan Sudirman',
                'status' => 'pending',
            ]);
    }

    public function test_report_detail_contains_coordinates(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officer, $governance);

        $response = $this->actingAs($officer)
            ->getJson("/officer/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('latitude', '-6.2000000')
            ->assertJsonPath('longitude', '106.8166667');
    }

    public function test_report_detail_contains_uploaded_photos(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officer, $governance);

        $path = 'reports/evidence.jpg';

        Storage::disk('public')->put($path, 'fake-image-content');

        $report->images()->create([
            'image_path' => $path,
        ]);

        $response = $this->actingAs($officer)
            ->getJson("/officer/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('photos.0.image_path', $path)
            ->assertJsonPath(
                'photos.0.image_url',
                Storage::disk('public')->url($path)
            );
    }

    public function test_report_detail_returns_empty_photos_when_no_photo_exists(): void
    {
        $officer = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officer, $governance);

        $response = $this->actingAs($officer)
            ->getJson("/officer/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonCount(0, 'photos');
    }

    public function test_officer_cannot_view_report_assigned_to_another_officer(): void
    {
        $officerA = $this->createUser('officer');
        $officerB = $this->createUser('officer');
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->assignReport($report, $officerB, $governance);

        $this->actingAs($officerA)
            ->getJson("/officer/reports/{$report->id}")
            ->assertForbidden();
    }

    public function test_officer_cannot_view_report_with_ended_assignment(): void
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
            ->getJson("/officer/reports/{$report->id}")
            ->assertForbidden();
    }

    public function test_report_detail_returns_404_when_report_does_not_exist(): void
    {
        $officer = $this->createUser('officer');

        $this->actingAs($officer)
            ->getJson('/officer/reports/99999')
            ->assertNotFound();
    }

    public function test_governance_cannot_access_officer_report_detail(): void
    {
        $governance = $this->createUser('governance');
        $report = $this->createReport();

        $this->actingAs($governance)
            ->getJson("/officer/reports/{$report->id}")
            ->assertForbidden();
    }

    public function test_citizen_cannot_access_officer_report_detail(): void
    {
        $citizen = $this->createUser('citizen');
        $report = $this->createReport();

        $this->actingAs($citizen)
            ->getJson("/officer/reports/{$report->id}")
            ->assertForbidden();
    }

    public function test_guest_cannot_access_officer_report_detail(): void
    {
        $report = $this->createReport();

        $this->getJson("/officer/reports/{$report->id}")
            ->assertUnauthorized();
    }
}
