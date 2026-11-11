<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GovernanceReportDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
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

    public function test_governance_can_view_report_detail(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $report = $this->createReport();

        $response = $this->actingAs($governance)
            ->getJson("/governance/reports/{$report->id}");

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

    public function test_superadmin_can_view_report_detail(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $report = $this->createReport();

        $response = $this->actingAs($superadmin)
            ->getJson("/governance/reports/{$report->id}");

        $response->assertOk();
    }

    public function test_report_detail_contains_coordinates(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $report = $this->createReport();

        $response = $this->actingAs($governance)
            ->getJson("/governance/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('latitude', '-6.2000000')
            ->assertJsonPath('longitude', '106.8166667');
    }

    public function test_report_detail_contains_uploaded_photos(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $report = $this->createReport();

        $path = 'reports/evidence.jpg';

        Storage::disk('public')->put($path, 'fake-image-content');

        $report->images()->create([
            'image_path' => $path,
        ]);

        $response = $this->actingAs($governance)
            ->getJson("/governance/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonPath('photos.0.image_path', $path)
            ->assertJsonPath(
                'photos.0.image_url',
                Storage::disk('public')->url($path)
            );
    }

    public function test_report_detail_returns_empty_photos_when_no_photo_exists(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $report = $this->createReport();

        $response = $this->actingAs($governance)
            ->getJson("/governance/reports/{$report->id}");

        $response->assertOk()
            ->assertJsonCount(0, 'photos');
    }

    public function test_report_detail_returns_404_when_report_does_not_exist(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $response = $this->actingAs($governance)
            ->getJson('/governance/reports/99999');

        $response->assertNotFound();
    }

    public function test_citizen_cannot_view_report_detail(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
        ]);

        $report = $this->createReport();

        $response = $this->actingAs($citizen)
            ->getJson("/governance/reports/{$report->id}");

        $response->assertForbidden();
    }

    public function test_officer_cannot_view_report_detail(): void
    {
        $officer = User::factory()->create([
            'role' => 'officer',
        ]);

        $report = $this->createReport();

        $response = $this->actingAs($officer)
            ->getJson("/governance/reports/{$report->id}");

        $response->assertForbidden();
    }

    public function test_guest_cannot_view_report_detail(): void
    {
        $report = $this->createReport();

        $response = $this->getJson(
            "/governance/reports/{$report->id}"
        );

        $response->assertUnauthorized();
    }
}
