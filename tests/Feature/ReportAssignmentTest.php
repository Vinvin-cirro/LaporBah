<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\User;
use App\Services\ReportAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReportAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function createReport(): Report
    {
        return Report::create([
            'category' => 'begal',
            'description' => 'Contoh laporan pengujian.',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'status' => 'pending',
        ]);
    }

    protected function createUser(string $role): User
    {
        $user = User::factory()->create();

        $user->forceFill([
            'role' => $role,
        ])->save();

        return $user;
    }

    public function test_report_can_be_assigned_to_an_officer(): void
    {
        $report = $this->createReport();
        $officer = $this->createUser('officer');

        $assignment = app(ReportAssignmentService::class)->assign(
            $report->id,
            $officer->id
        );

        $this->assertDatabaseHas('report_assignments', [
            'id' => $assignment->id,
            'report_id' => $report->id,
            'officer_id' => $officer->id,
            'ended_at' => null,
        ]);
    }

    public function test_assignment_can_be_transferred_to_another_officer(): void
    {
        $report = $this->createReport();

        $officerA = $this->createUser('officer');
        $officerB = $this->createUser('officer');

        $service = app(ReportAssignmentService::class);

        $assignmentA = $service->assign(
            $report->id,
            $officerA->id
        );

        $assignmentB = $service->assign(
            $report->id,
            $officerB->id
        );

        // Riwayat Petugas A tetap tersimpan.
        $this->assertDatabaseHas('report_assignments', [
            'id' => $assignmentA->id,
            'officer_id' => $officerA->id,
        ]);

        $this->assertNotNull($assignmentA->fresh()->ended_at);

        // Petugas B menjadi petugas aktif.
        $this->assertDatabaseHas('report_assignments', [
            'id' => $assignmentB->id,
            'officer_id' => $officerB->id,
            'ended_at' => null,
        ]);

        // Hanya boleh ada satu penugasan aktif.
        $this->assertSame(
            1,
            ReportAssignment::where('report_id', $report->id)
                ->whereNull('ended_at')
                ->count()
        );
    }

    public function test_same_officer_is_not_assigned_twice(): void
    {
        $report = $this->createReport();
        $officer = $this->createUser('officer');

        $service = app(ReportAssignmentService::class);

        $first = $service->assign(
            $report->id,
            $officer->id
        );

        $second = $service->assign(
            $report->id,
            $officer->id
        );

        $this->assertSame($first->id, $second->id);

        $this->assertSame(
            1,
            ReportAssignment::where('report_id', $report->id)->count()
        );
    }

    public function test_citizen_cannot_be_assigned_as_officer(): void
    {
        $report = $this->createReport();
        $citizen = $this->createUser('citizen');

        try {
            app(ReportAssignmentService::class)->assign(
                $report->id,
                $citizen->id
            );

            $this->fail('Citizen seharusnya tidak bisa ditugaskan.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey(
                'officer_id',
                $exception->errors()
            );
        }

        $this->assertDatabaseCount('report_assignments', 0);
    }
}
