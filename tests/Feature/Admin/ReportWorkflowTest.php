<?php

namespace Tests\Feature\Admin;

use App\Enums\CitizenPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_can_verify_and_update_a_report_with_audit_history(): void
    {
        $admin = User::factory()->create();
        $report = $this->createReport();

        $this->actingAs($admin)->patch(route('admin.reports.workflow.update', $report), [
            'workflow_version' => 0,
            'verification_status' => 'verified',
            'status' => 'in_progress',
            'admin_priority' => 'high',
            'public_note' => 'Lokasi sudah diperiksa dan masuk jadwal penanganan.',
            'internal_note' => 'Koordinasi dengan tim lapangan.',
        ])->assertRedirect();

        $report->refresh();
        $this->assertSame(VerificationStatus::Verified, $report->verification_status);
        $this->assertSame(ReportStatus::InProgress, $report->status);
        $this->assertSame(1, $report->workflow_version);
        $this->assertSame($admin->id, $report->verified_by);
        $this->assertDatabaseHas('report_status_histories', ['report_id' => $report->id, 'changed_by' => $admin->id]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $admin->id, 'action' => 'report.workflow_updated']);
    }

    public function test_stale_workflow_form_cannot_overwrite_another_petugas_change(): void
    {
        $firstAdmin = User::factory()->create();
        $secondAdmin = User::factory()->create();
        $report = $this->createReport();
        $payload = [
            'workflow_version' => 0,
            'verification_status' => 'verified',
            'status' => 'in_progress',
            'admin_priority' => 'medium',
            'public_note' => 'Sedang ditangani.',
            'internal_note' => null,
        ];

        $this->actingAs($firstAdmin)->patch(route('admin.reports.workflow.update', $report), $payload)->assertRedirect();
        $this->actingAs($secondAdmin)->from(route('admin.reports.show', $report))
            ->patch(route('admin.reports.workflow.update', $report), [...$payload, 'status' => 'completed'])
            ->assertRedirect(route('admin.reports.show', $report))
            ->assertSessionHasErrors('workflow');

        $report->refresh();
        $this->assertSame(ReportStatus::InProgress, $report->status);
        $this->assertSame(1, $report->workflow_version);
        $this->assertDatabaseCount('report_status_histories', 1);
    }

    public function test_unverified_report_cannot_be_marked_in_progress(): void
    {
        $admin = User::factory()->create();
        $report = $this->createReport();

        $this->actingAs($admin)->from(route('admin.reports.show', $report))
            ->patch(route('admin.reports.workflow.update', $report), [
                'workflow_version' => 0,
                'verification_status' => 'invalid',
                'status' => 'in_progress',
                'admin_priority' => 'low',
                'public_note' => 'Laporan tidak valid.',
                'internal_note' => null,
            ])
            ->assertRedirect(route('admin.reports.show', $report))
            ->assertSessionHasErrors('status');

        $this->assertSame(ReportStatus::NotStarted, $report->fresh()->status);
        $this->assertDatabaseCount('report_status_histories', 0);
    }

    private function createReport(): Report
    {
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $subcategory = Category::query()->with('subcategories')->firstOrFail()->subcategories->firstOrFail();

        return Report::query()->create([
            'report_code' => 'PDL-2026-'.Str::upper(Str::random(6)),
            'submission_token' => Str::uuid(),
            'reporter_name' => 'Warga Uji',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'subcategory_id' => $subcategory->id,
            'title' => 'Laporan yang perlu ditangani',
            'description' => 'Uraian laporan untuk pengujian alur kerja petugas desa.',
            'location_text' => 'Dekat balai dusun',
            'citizen_priority' => CitizenPriority::Important,
            'verification_status' => VerificationStatus::Pending,
            'status' => ReportStatus::NotStarted,
            'submitted_at' => now(),
        ]);
    }
}
