<?php

namespace Tests\Feature\Site;

use App\Enums\CitizenPriority;
use App\Enums\ReportMediaType;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\ReportMedia;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicReportMediaTest extends TestCase
{
    use RefreshDatabase;

    private Dusun $dusun;
    private int $subcategoryId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $this->dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();
        $this->subcategoryId = $category->subcategories->firstOrFail()->id;
    }

    public function test_public_user_can_view_media_stream(): void
    {
        Storage::fake('public');
        $filePath = 'reports/PDL-2026-MED001/after/test.webp';
        Storage::disk('public')->put($filePath, 'fake-image-content');

        $report = Report::query()->create([
            'report_code' => 'PDL-2026-MED001',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $this->dusun->id,
            'subcategory_id' => $this->subcategoryId,
            'title' => 'Jalan Rusak Selesai Diperbaiki',
            'description' => 'Jalan sudah diperbaiki oleh aparat desa.',
            'location_text' => 'Dekat Balai Desa',
            'citizen_priority' => CitizenPriority::Normal,
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::Completed,
            'submitted_at' => now(),
        ]);

        $media = ReportMedia::query()->create([
            'report_id' => $report->id,
            'media_type' => ReportMediaType::AfterPhoto,
            'storage_disk' => 'public',
            'file_path' => $filePath,
            'mime_type' => 'image/webp',
            'file_size' => 1024,
        ]);

        $response = $this->get(route('public-reports.media', $media));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
    }

    public function test_public_report_detail_displays_before_and_after_evidence(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('reports/PDL-2026-MED002/before/awal.webp', 'image-awal');
        Storage::disk('public')->put('reports/PDL-2026-MED002/after/hasil.webp', 'image-hasil');

        $report = Report::query()->create([
            'report_code' => 'PDL-2026-MED002',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $this->dusun->id,
            'subcategory_id' => $this->subcategoryId,
            'title' => 'Saluran Air Mampet',
            'description' => 'Saluran air tersumbat sampah.',
            'location_text' => 'RT 02 RW 01',
            'citizen_priority' => CitizenPriority::Important,
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::Completed,
            'submitted_at' => now(),
        ]);

        $beforeMedia = ReportMedia::query()->create([
            'report_id' => $report->id,
            'media_type' => ReportMediaType::BeforePhoto,
            'storage_disk' => 'public',
            'file_path' => 'reports/PDL-2026-MED002/before/awal.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
        ]);

        $afterMedia = ReportMedia::query()->create([
            'report_id' => $report->id,
            'media_type' => ReportMediaType::AfterPhoto,
            'storage_disk' => 'public',
            'file_path' => 'reports/PDL-2026-MED002/after/hasil.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
        ]);

        $response = $this->get(route('public-reports.show', $report->report_code));

        $response->assertOk();
        $response->assertSee('Dokumentasi dan Bukti Penanganan');
        $response->assertSee('Kondisi Awal');
        $response->assertSee('Hasil Penanganan');
        $response->assertSee('Sudah Dilampirkan Bukti');
        $response->assertSee(route('public-reports.media', $beforeMedia));
        $response->assertSee(route('public-reports.media', $afterMedia));
    }

    public function test_tracking_page_displays_before_and_after_evidence(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('reports/PDL-2026-MED003/after/bukti.webp', 'image-bukti');

        $report = Report::query()->create([
            'report_code' => 'PDL-2026-MED003',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $this->dusun->id,
            'subcategory_id' => $this->subcategoryId,
            'title' => 'Lampu Jalan Padam',
            'description' => 'Lampu jalan mati di perempatan.',
            'location_text' => 'Perempatan Dusun Barat',
            'citizen_priority' => CitizenPriority::Normal,
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::Completed,
            'submitted_at' => now(),
        ]);

        $afterMedia = ReportMedia::query()->create([
            'report_id' => $report->id,
            'media_type' => ReportMediaType::AfterPhoto,
            'storage_disk' => 'public',
            'file_path' => 'reports/PDL-2026-MED003/after/bukti.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
        ]);

        $response = $this->get(route('reports.track', ['code' => $report->report_code]));

        $response->assertOk();
        $response->assertSee('Dokumentasi dan Bukti Penanganan');
        $response->assertSee('Hasil Penanganan');
        $response->assertSee(route('public-reports.media', $afterMedia));
    }

    public function test_missing_media_file_returns_404(): void
    {
        Storage::fake('public');

        $report = Report::query()->create([
            'report_code' => 'PDL-2026-MED004',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $this->dusun->id,
            'subcategory_id' => $this->subcategoryId,
            'title' => 'Kasus Uji File Hilang',
            'description' => 'Uji penanganan file tidak ada.',
            'location_text' => 'Lokasi Uji',
            'citizen_priority' => CitizenPriority::Normal,
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::Completed,
            'submitted_at' => now(),
        ]);

        $media = ReportMedia::query()->create([
            'report_id' => $report->id,
            'media_type' => ReportMediaType::AfterPhoto,
            'storage_disk' => 'public',
            'file_path' => 'reports/PDL-2026-MED004/after/non-existent.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
        ]);

        $response = $this->get(route('public-reports.media', $media));

        $response->assertNotFound();
    }
}
