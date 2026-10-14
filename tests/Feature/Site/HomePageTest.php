<?php

namespace Tests\Feature\Site;

use App\Enums\JenisUsulan;
use App\Enums\ReportStatus;
use App\Enums\StatusUsulan;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\Subcategory;
use App\Models\Usulan;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([DusunSeeder::class, CategorySeeder::class]);
    }

    public function test_home_page_displays_recent_reports_and_usulan(): void
    {
        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();
        $subcategory = $category->subcategories->firstOrFail();

        // Siapkan 1 laporan terverifikasi
        $report = Report::query()->create([
            'report_code' => 'PDL-2026-TEST01',
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $dusun->id,
            'location_text' => 'Sebelah balai desa',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'title' => 'Lampu jalan padam di pos kamling',
            'description' => 'Lampu padam sejak kemarin sore.',
            'citizen_priority' => 'normal',
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::InProgress,
            'submitted_at' => now()->subDay(),
            'verified_at' => now(),
        ]);

        // Siapkan 1 usulan terpublikasi
        $usulan = Usulan::query()->create([
            'kode' => 'USL-2026-TEST01',
            'token_kirim' => Str::uuid()->toString(),
            'dusun_id' => $dusun->id,
            'jenis' => JenisUsulan::Pembangunan,
            'judul' => 'Pengaspalan jalan gang dusun',
            'isi' => 'Mohon bantuan perbaikan aspal jalan gang agar tidak becek saat hujan.',
            'status' => StatusUsulan::Ditinjau,
            'tampil_publik' => true,
            'dipublikasikan_pada' => now()->subHours(5),
            'dikirim_pada' => now()->subDay(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Laporan dan usulan terkini.')
            ->assertSee('Laporan warga terbaru')
            ->assertSee('Usulan pembangunan terbaru')
            // Cek laporan: judul, kode, dusun, status label
            ->assertSee($report->title)
            ->assertSee($report->report_code)
            ->assertSee($dusun->name)
            ->assertSee('Proses Pelaksanaan')
            // Cek usulan: judul, kode, dusun, status label
            ->assertSee($usulan->judul)
            ->assertSee($usulan->kode)
            ->assertSee('Sedang Ditinjau');
    }

    public function test_home_page_status_component_renders_svg_icons(): void
    {
        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();

        Report::query()->create([
            'report_code' => 'PDL-2026-STAT01',
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $dusun->id,
            'location_text' => 'Dekat jembatan',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Jalan berlubang cukup dalam',
            'description' => 'Aspal jalan retak dan membahayakan warga.',
            'citizen_priority' => 'normal',
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::Completed,
            'submitted_at' => now(),
            'verified_at' => now(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Sudah Terlaksana')
            ->assertSee('data-status="completed"', false)
            ->assertSee('<svg', false);
    }
}
