<?php

namespace Tests\Feature;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function createReport(): Report
    {
        return Report::create([
            'category' => 'begal',
            'description' => 'Laporan pengujian tracking.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'location_name' => 'Jalan Sudirman',
            'status' => 'pending',
            'tracking_code' => 'A8K2M9P4Q7XZ',
        ]);
    }

    public function test_guest_can_track_report_using_valid_tracking_code(): void
    {
        $report = $this->createReport();

        $response = $this->getJson(
            '/reports/track/' . $report->tracking_code
        );

        $response->assertOk()
            ->assertJson([
                'tracking_code' => 'A8K2M9P4Q7XZ',
                'category' => 'begal',
                'location_name' => 'Jalan Sudirman',
                'status' => 'pending',
            ]);
    }

    public function test_invalid_tracking_code_returns_404(): void
    {
        $response = $this->getJson('/reports/track/INVALIDCODE');

        $response->assertNotFound();
    }

    public function test_tracking_code_is_case_insensitive(): void
    {
        $report = $this->createReport();

        $response = $this->getJson(
            '/reports/track/' . strtolower($report->tracking_code)
        );

        $response->assertOk();
    }

    public function test_report_updates_are_displayed_in_chronological_order(): void
    {
        $report = $this->createReport();

        DB::table('report_updates')->insert([
            [
                'report_id' => $report->id,
                'old_status' => 'pending',
                'new_status' => 'verified',
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ],
            [
                'report_id' => $report->id,
                'old_status' => 'verified',
                'new_status' => 'investigating',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson(
            '/reports/track/' . $report->tracking_code
        );

        $response->assertOk()
            ->assertJsonPath('updates.0.new_status', 'verified')
            ->assertJsonPath('updates.1.new_status', 'investigating');
    }

    public function test_internal_information_is_not_exposed(): void
    {
        $report = $this->createReport();

        $response = $this->getJson(
            '/reports/track/' . $report->tracking_code
        );

        $response->assertOk()
            ->assertJsonMissingPath('description')
            ->assertJsonMissingPath('user_id')
            ->assertJsonMissingPath('officer_id')
            ->assertJsonMissingPath('assigned_by');
    }
}
