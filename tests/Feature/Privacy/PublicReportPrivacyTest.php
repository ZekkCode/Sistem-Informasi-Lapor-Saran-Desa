<?php

namespace Tests\Feature\Privacy;

use App\Enums\CitizenPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicReportPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_page_never_renders_reporter_identity(): void
    {
        $report = $this->createReport(VerificationStatus::Pending, 'PDL-2026-ABC234');

        $response = $this->get(route('reports.track', ['code' => $report->report_code]));

        $response->assertOk();
        $response->assertSee($report->title);
        $response->assertDontSee('Nama Sangat Rahasia');
        $response->assertDontSee('081298765432');
    }

    public function test_only_verified_reports_appear_in_public_listing(): void
    {
        $verified = $this->createReport(VerificationStatus::Verified, 'PDL-2026-VER234', 'Laporan terverifikasi');
        $invalid = $this->createReport(VerificationStatus::Invalid, 'PDL-2026-INV234', 'Laporan tidak valid');

        $response = $this->get(route('public-reports.index'));

        $response->assertOk();
        $response->assertSee($verified->title);
        $response->assertDontSee($invalid->title);
    }

    private function createReport(
        VerificationStatus $verificationStatus,
        string $code,
        string $title = 'Lampu jalan perlu diperbaiki',
    ): Report {
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        return Report::query()->create([
            'report_code' => $code,
            'reporter_name' => 'Nama Sangat Rahasia',
            'reporter_phone' => '081298765432',
            'dusun_id' => Dusun::query()->value('id'),
            'subcategory_id' => Category::query()->with('subcategories')->firstOrFail()->subcategories->firstOrFail()->id,
            'title' => $title,
            'description' => 'Deskripsi laporan yang aman untuk ditampilkan kepada masyarakat.',
            'location_text' => 'Area umum dusun',
            'citizen_priority' => CitizenPriority::Normal,
            'verification_status' => $verificationStatus,
            'status' => ReportStatus::NotStarted,
            'submitted_at' => now(),
        ]);
    }
}
