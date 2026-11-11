<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function validReportData(): array
    {
        return [
            'category' => 'begal',
            'description' => 'Terjadi dugaan pembegalan di jalan tersebut.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'location_name' => 'Jalan Sudirman',
            'incident_at' => now()->toDateTimeString(),
        ];
    }

    // Guest dapat mengirim laporan
    public function test_guest_can_submit_report(): void
    {
        $response = $this->postJson(
            route('reports.store'),
            $this->validReportData()
        );

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'tracking_code',
                'report_id',
            ]);

        $this->assertDatabaseHas('reports', [
            'category' => 'begal',
            'location_name' => 'Jalan Sudirman',
            'user_id' => null,
            'status' => 'pending',
        ]);

        $this->assertNotEmpty($response->json('tracking_code'));
    }

    // Citizen dapat mengirim laporan
    public function test_citizen_can_submit_report(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
        ]);

        $response = $this->actingAs($citizen)->postJson(
            route('reports.store'),
            $this->validReportData()
        );

        $response->assertCreated();

        $this->assertDatabaseHas('reports', [
            'user_id' => $citizen->id,
            'category' => 'begal',
            'status' => 'pending',
        ]);
    }

    // Laporan kategori dark dapat dikirim
    public function test_dark_report_can_be_submitted(): void
    {
        $data = $this->validReportData();
        $data['category'] = 'dark';

        $response = $this->postJson(
            route('reports.store'),
            $data
        );

        $response->assertCreated();

        $this->assertDatabaseHas('reports', [
            'category' => 'dark',
        ]);
    }

    // Kategori yang tidak valid ditolak
    public function test_invalid_category_is_rejected(): void
    {
        $data = $this->validReportData();
        $data['category'] = 'theft';

        $response = $this->postJson(
            route('reports.store'),
            $data
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('category');

        $this->assertDatabaseCount('reports', 0);
    }

    // Koordinat latitude di luar rentang ditolak
    public function test_invalid_latitude_is_rejected(): void
    {
        $data = $this->validReportData();
        $data['latitude'] = 120;

        $response = $this->postJson(
            route('reports.store'),
            $data
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('latitude');

        $this->assertDatabaseCount('reports', 0);
    }

    // Setiap laporan mendapatkan tracking code berbeda
    public function test_each_report_gets_unique_tracking_code(): void
    {
        $firstResponse = $this->postJson(
            route('reports.store'),
            $this->validReportData()
        );

        $secondResponse = $this->postJson(
            route('reports.store'),
            $this->validReportData()
        );

        $firstResponse->assertCreated();
        $secondResponse->assertCreated();

        $this->assertNotEquals(
            $firstResponse->json('tracking_code'),
            $secondResponse->json('tracking_code')
        );

        $this->assertDatabaseCount('reports', 2);
    }
}
