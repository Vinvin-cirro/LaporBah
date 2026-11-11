<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportSubmissionImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function validReportData(): array
    {
        return [
            'category' => 'begal',
            'description' => 'Terjadi dugaan pembegalan di jalan tersebut.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'location_name' => 'Jalan Sudirman',
        ];
    }

    public function test_guest_can_submit_report_with_photo(): void
    {
        $response = $this->postJson('/reports', array_merge(
            $this->validReportData(),
            [
                'photos' => [
                    UploadedFile::fake()->image('evidence.jpg'),
                ],
            ]
        ));

        $response->assertCreated();

        $report = Report::first();

        $this->assertDatabaseHas('report_images', [
            'report_id' => $report->id,
        ]);

        Storage::disk('public')->assertExists(
            $report->images()->first()->image_path
        );
    }

    public function test_citizen_can_submit_report_with_photo(): void
    {
        $citizen = User::factory()->create([
            'role' => 'citizen',
        ]);

        $response = $this->actingAs($citizen)->postJson('/reports', array_merge(
            $this->validReportData(),
            [
                'photos' => [
                    UploadedFile::fake()->image('evidence.jpg'),
                ],
            ]
        ));

        $response->assertCreated();

        $report = Report::first();

        $this->assertDatabaseHas('report_images', [
            'report_id' => $report->id,
        ]);
    }

    public function test_multiple_photos_can_be_uploaded(): void
    {
        $response = $this->postJson('/reports', array_merge(
            $this->validReportData(),
            [
                'photos' => [
                    UploadedFile::fake()->image('photo1.jpg'),
                    UploadedFile::fake()->image('photo2.png'),
                    UploadedFile::fake()->image('photo3.webp'),
                ],
            ]
        ));

        $response->assertCreated();

        $this->assertDatabaseCount('report_images', 3);
    }

    public function test_report_can_be_submitted_without_photos(): void
    {
        $response = $this->postJson('/reports', $this->validReportData());

        $response->assertCreated();

        $this->assertDatabaseCount('reports', 1);
        $this->assertDatabaseCount('report_images', 0);
    }

    public function test_invalid_photo_format_is_rejected(): void
    {
        $response = $this->postJson('/reports', array_merge(
            $this->validReportData(),
            [
                'photos' => [
                    UploadedFile::fake()->create(
                        'document.pdf',
                        100,
                        'application/pdf'
                    ),
                ],
            ]
        ));

        $response->assertUnprocessable();

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_more_than_five_photos_are_rejected(): void
    {
        $photos = [];

        for ($i = 1; $i <= 6; $i++) {
            $photos[] = UploadedFile::fake()->image("photo{$i}.jpg");
        }

        $response = $this->postJson('/reports', array_merge(
            $this->validReportData(),
            ['photos' => $photos]
        ));

        $response->assertUnprocessable();

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_photo_larger_than_five_mb_is_rejected(): void
    {
        $response = $this->postJson('/reports', array_merge(
            $this->validReportData(),
            [
                'photos' => [
                    UploadedFile::fake()->image('large.jpg')->size(5121),
                ],
            ]
        ));

        $response->assertUnprocessable();

        $this->assertDatabaseCount('reports', 0);
    }
}
