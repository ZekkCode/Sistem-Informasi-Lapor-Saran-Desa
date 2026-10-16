<?php

namespace Tests\Feature\Admin;

use App\Enums\CitizenPriority;
use App\Enums\ReportMediaType;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportExportAndReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
    }

    public function test_admin_can_view_reports_table_with_photo_thumbnail_and_tinjau_action(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();

        $report = Report::query()->create([
            'report_code' => 'PDL-2026-FOT01',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '0881081772005',
            'dusun_id' => $dusun->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Sampah Menumpuk di Saluran Air',
            'description' => 'Saluran tersumbat sampah butuh pembersihan segera.',
            'location_text' => 'Dekat gardu dusun',
            'citizen_priority' => CitizenPriority::Important,
            'verification_status' => VerificationStatus::Pending,
            'status' => ReportStatus::NotStarted,
            'submitted_at' => now(),
        ]);

        $photoPath = 'reports/PDL-2026-FOT01/before/foto1.webp';
        Storage::disk('public')->put($photoPath, 'fake-image-bytes');
        $media = $report->media()->create([
            'media_type' => ReportMediaType::BeforePhoto,
            'storage_disk' => 'public',
            'file_path' => $photoPath,
            'mime_type' => 'image/webp',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('PDL-2026-FOT01');
        $response->assertSee('Sampah Menumpuk di Saluran Air');
        $response->assertSee('Tinjau');
        $response->assertSee(route('admin.reports.show', $report));
        $response->assertSee(route('admin.media.show', $media));
        $response->assertSee('Unduh CSV');
        $response->assertSee('Ekspor ke Google Drive');
        $response->assertSee('Rentang waktu');
    }

    public function test_admin_can_filter_reports_by_periode(): void
    {
        $admin = User::factory()->create();
        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();
        $subId = $category->subcategories->firstOrFail()->id;

        // Laporan baru (2 hari lalu)
        $recentReport = Report::query()->create([
            'report_code' => 'PDL-2026-RECENT',
            'reporter_name' => 'Warga Baru',
            'reporter_phone' => '0881081772005',
            'dusun_id' => $dusun->id,
            'subcategory_id' => $subId,
            'title' => 'Laporan Minggu Ini',
            'description' => 'Masalah baru saja terjadi.',
            'location_text' => 'Lokasi baru',
            'citizen_priority' => CitizenPriority::Normal,
            'verification_status' => VerificationStatus::Pending,
            'status' => ReportStatus::NotStarted,
            'submitted_at' => now()->subDays(2),
        ]);

        // Laporan lama (45 hari lalu)
        $oldReport = Report::query()->create([
            'report_code' => 'PDL-2026-OLD99',
            'reporter_name' => 'Warga Lama',
            'reporter_phone' => '0881081772005',
            'dusun_id' => $dusun->id,
            'subcategory_id' => $subId,
            'title' => 'Laporan Bulan Lalu',
            'description' => 'Masalah sudah lama.',
            'location_text' => 'Lokasi lama',
            'citizen_priority' => CitizenPriority::Normal,
            'verification_status' => VerificationStatus::Pending,
            'status' => ReportStatus::NotStarted,
            'submitted_at' => now()->subDays(45),
        ]);

        // Filter minggu: hanya recent yang tampil
        $responseMinggu = $this->actingAs($admin)->get(route('admin.reports.index', ['periode' => 'minggu']));
        $responseMinggu->assertOk();
        $responseMinggu->assertSee('PDL-2026-RECENT');
        $responseMinggu->assertDontSee('PDL-2026-OLD99');

        // Filter semua: keduanya tampil
        $responseSemua = $this->actingAs($admin)->get(route('admin.reports.index', ['periode' => 'semua']));
        $responseSemua->assertOk();
        $responseSemua->assertSee('PDL-2026-RECENT');
        $responseSemua->assertSee('PDL-2026-OLD99');
    }

    public function test_admin_can_download_csv_with_periode(): void
    {
        $admin = User::factory()->create();
        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();

        Report::query()->create([
            'report_code' => 'PDL-2026-CSV01',
            'reporter_name' => 'Budi Santoso',
            'reporter_phone' => '081234567890',
            'dusun_id' => $dusun->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Lampu PJU Padam',
            'description' => 'Lampu jalan mati sejak kemarin.',
            'location_text' => 'RT 02',
            'citizen_priority' => CitizenPriority::Important,
            'verification_status' => VerificationStatus::Pending,
            'status' => ReportStatus::NotStarted,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.export.csv', ['periode' => 'minggu']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('rekap-laporan-mingguan-7hari', $disposition);
        $this->assertStringContainsString('PDL-2026-CSV01', $response->streamedContent());
    }

    public function test_admin_can_export_reports_to_google_drive(): void
    {
        Cache::forget('google-drive:backup-access-token');
        config()->set('report-media.google_drive', [
            'enabled' => true,
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => 'refresh-token',
            'folder_id' => 'folder-padelegan-123',
            'queue' => 'backups',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'api_url' => 'https://www.googleapis.com/drive/v3',
            'upload_url' => 'https://www.googleapis.com/upload/drive/v3',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-token-123',
                'expires_in' => 3600,
            ]),
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'id' => 'drive-csv-file-999',
                'name' => 'rekap-laporan-mingguan-7hari.csv',
                'webViewLink' => 'https://drive.google.com/file/d/drive-csv-file-999/view',
            ]),
        ]);

        $admin = User::factory()->create();
        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();

        Report::query()->create([
            'report_code' => 'PDL-2026-DRIVE1',
            'reporter_name' => 'Warga Peduli',
            'reporter_phone' => '081234567890',
            'dusun_id' => $dusun->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Perbaikan Gorong-gorong',
            'description' => 'Gorong-gorong pecah.',
            'location_text' => 'Depan balai desa',
            'citizen_priority' => CitizenPriority::Emergency,
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::InProgress,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reports.export.drive', [
            'periode' => 'minggu',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertStringContainsString('https://drive.google.com/file/d/drive-csv-file-999/view', session('status'));

        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://www.googleapis.com/upload/drive/v3/files')
            && str_contains($request->body(), '"parents":["folder-padelegan-123"]')
            && str_contains($request->body(), 'PDL-2026-DRIVE1')
        );
    }

    public function test_export_drive_handles_disabled_drive_gracefully(): void
    {
        config()->set('report-media.google_drive.enabled', false);

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.reports.export.drive'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Ekspor ke Google Drive belum diaktifkan', session('error'));
    }

    public function test_admin_usulan_cannot_export_reports_to_drive(): void
    {
        $adminUsulan = User::factory()->adminUsulan()->create();

        $response = $this->actingAs($adminUsulan)->post(route('admin.reports.export.drive'));

        $response->assertForbidden();
    }
}
