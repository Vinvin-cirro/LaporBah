<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceReportListingTest extends TestCase
{
    use RefreshDatabase;

    private function createReport(array $attributes = []): Report
    {
        return Report::create(array_merge([
            'category' => 'begal',
            'description' => 'Laporan pengujian.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'location_name' => 'Jalan Sudirman',
            'status' => 'pending',
            'tracking_code' => strtoupper(fake()->unique()->bothify('????????????')),
        ], $attributes));
    }

    public function test_governance_can_view_report_list(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $this->createReport();

        $response = $this->actingAs($governance)
            ->getJson('/governance/reports');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_superadmin_can_view_report_list(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $this->createReport();

        $response = $this->actingAs($superadmin)
            ->getJson('/governance/reports');

        $response->assertOk();
    }

    public function test_citizen_cannot_view_report_list(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
        ]);

        $response = $this->actingAs($citizen)
            ->getJson('/governance/reports');

        $response->assertForbidden();
    }

    public function test_officer_cannot_view_report_list(): void
    {
        $officer = User::factory()->create([
            'role' => 'officer',
        ]);

        $response = $this->actingAs($officer)
            ->getJson('/governance/reports');

        $response->assertForbidden();
    }

    public function test_reports_can_be_filtered_by_category(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $this->createReport(['category' => 'begal']);
        $this->createReport(['category' => 'dark']);

        $response = $this->actingAs($governance)
            ->getJson('/governance/reports?category=dark');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category', 'dark');
    }

    public function test_reports_can_be_filtered_by_status(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $this->createReport(['status' => 'pending']);
        $this->createReport(['status' => 'verified']);

        $response = $this->actingAs($governance)
            ->getJson('/governance/reports?status=verified');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'verified');
    }

    public function test_reports_can_be_searched_by_location(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        $this->createReport([
            'location_name' => 'Jalan Sudirman',
        ]);

        $this->createReport([
            'location_name' => 'Jalan Merdeka',
        ]);

        $response = $this->actingAs($governance)
            ->getJson('/governance/reports?search=Sudirman');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.location_name', 'Jalan Sudirman');
    }

    public function test_reports_are_paginated(): void
    {
        $governance = User::factory()->create([
            'role' => 'governance',
        ]);

        for ($i = 1; $i <= 20; $i++) {
            $this->createReport();
        }

        $response = $this->actingAs($governance)
            ->getJson('/governance/reports?per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total', 20);
    }

    public function test_guest_cannot_view_report_list(): void
    {
        $response = $this->getJson('/governance/reports');

        $response->assertUnauthorized();
    }
}
