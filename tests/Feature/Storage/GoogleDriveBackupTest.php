<?php

namespace Tests\Feature\Storage;

use App\Enums\CitizenPriority;
use App\Enums\ReportMediaType;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Services\Storage\GoogleDriveBackup;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoogleDriveBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_is_uploaded_privately_to_the_configured_drive_folder(): void
    {
        Storage::fake('public');
        Cache::forget('google-drive:backup-access-token');
        config()->set('report-media.google_drive', [
            'enabled' => true,
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => 'refresh-token',
            'folder_id' => 'folder-123',
            'queue' => 'backups',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'api_url' => 'https://www.googleapis.com/drive/v3',
            'upload_url' => 'https://www.googleapis.com/upload/drive/v3',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-token',
                'expires_in' => 3600,
            ]),
            'https://www.googleapis.com/drive/v3/files*' => Http::response(['files' => []]),
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'id' => 'drive-file-123',
                'name' => 'pdl-2026-tes123-media-1.webp',
                'webViewLink' => 'https://drive.google.com/file/d/drive-file-123/view',
            ]),
        ]);

        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $category = Category::query()->with('subcategories')->firstOrFail();
        $report = Report::query()->create([
            'report_code' => 'PDL-2026-TES123',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Jalan desa perlu diperbaiki',
            'description' => 'Permukaan jalan rusak dan mengganggu warga yang melintas.',
            'location_text' => 'Dekat pertigaan utama dusun',
            'citizen_priority' => CitizenPriority::Important,
            'verification_status' => VerificationStatus::Pending,
            'status' => ReportStatus::NotStarted,
            'submitted_at' => now(),
        ]);
        $path = 'reports/PDL-2026-TES123/before/evidence.webp';
        Storage::disk('public')->put($path, 'webp-contents');
        $media = $report->media()->create([
            'media_type' => ReportMediaType::BeforePhoto,
            'storage_disk' => 'public',
            'file_path' => $path,
            'mime_type' => 'image/webp',
            'file_size' => 13,
        ]);

        $file = app(GoogleDriveBackup::class)->upload($media);

        $this->assertSame('drive-file-123', $file['id']);
        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://www.googleapis.com/upload/drive/v3/files')
            && str_contains($request->body(), '"parents":["folder-123"]')
            && str_contains($request->body(), '"padelegan_media_id":"'.$media->id.'"')
            && str_contains($request->body(), 'webp-contents')
        );
    }
}
