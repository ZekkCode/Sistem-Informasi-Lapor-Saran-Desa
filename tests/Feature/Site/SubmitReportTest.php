<?php

namespace Tests\Feature\Site;

use App\Enums\MediaBackupStatus;
use App\Jobs\MirrorReportMediaToGoogleDrive;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmitReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_citizen_can_submit_a_report_and_receive_a_public_code(): void
    {
        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();
        $subcategory = $category->subcategories->firstOrFail();

        $response = $this->post(route('reports.store'), [
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $dusun->id,
            'location_text' => 'Dekat pertigaan utama dusun',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'title' => 'Lampu penerangan jalan mati',
            'description' => 'Lampu penerangan sudah tidak menyala selama beberapa malam.',
            'citizen_priority' => 'important',
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
            'consent' => '1',
        ]);

        $report = Report::query()->firstOrFail();

        $response->assertRedirect(route('reports.success', $report->report_code));
        $this->assertMatchesRegularExpression('/^PDL-\d{4}-[A-Z0-9]{6}$/', $report->report_code);
        $this->assertSame('pending', $report->verification_status->value);
        $this->assertSame('not_started', $report->status->value);
        $this->assertCount(1, $report->histories);
        $this->assertCount(1, $report->media);
        $this->assertSame('image/webp', $report->media->first()->mime_type);
        $this->assertStringEndsWith('.webp', $report->media->first()->file_path);
        Storage::disk('public')->assertExists($report->media->first()->file_path);
    }

    public function test_submit_rejects_a_subcategory_from_another_category(): void
    {
        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $categories = Category::query()->with('subcategories')->take(2)->get();

        $response = $this->from(route('reports.create'))->post(route('reports.store'), [
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat balai dusun',
            'category_id' => $categories[0]->id,
            'subcategory_id' => $categories[1]->subcategories->first()->id,
            'title' => 'Masalah fasilitas umum',
            'description' => 'Uraian masalah cukup panjang untuk dapat diverifikasi petugas.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('bukti.jpg')],
            'consent' => '1',
        ]);

        $response->assertRedirect(route('reports.create'));
        $response->assertSessionHasErrors('subcategory_id');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_retrying_the_same_submission_does_not_duplicate_report_or_media(): void
    {
        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $category = Category::query()->with('subcategories')->firstOrFail();
        $token = Str::uuid()->toString();
        $payload = [
            'submission_token' => $token,
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat pertigaan utama dusun',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Jalan berlubang di depan sekolah',
            'description' => 'Lubang cukup dalam dan membahayakan pengendara ketika malam hari.',
            'citizen_priority' => 'important',
            'consent' => '1',
        ];

        $first = $this->post(route('reports.store'), [
            ...$payload,
            'photos' => [UploadedFile::fake()->image('bukti-pertama.jpg')],
        ]);
        $second = $this->post(route('reports.store'), [
            ...$payload,
            'photos' => [UploadedFile::fake()->image('bukti-ulangan.jpg')],
        ]);

        $report = Report::query()->sole();

        $first->assertRedirect(route('reports.success', $report->report_code));
        $second->assertRedirect(route('reports.success', $report->report_code));
        $this->assertDatabaseCount('reports', 1);
        $this->assertDatabaseCount('report_media', 1);
        $this->assertDatabaseCount('report_status_histories', 1);
    }

    public function test_citizen_can_submit_with_a_direct_r2_upload_token(): void
    {
        Storage::fake('s3');
        config()->set('report-media.disk', 's3');
        config()->set('report-media.direct_upload.enabled', true);
        config()->set('report-media.direct_upload.disk', 's3');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $category = Category::query()->with('subcategories')->firstOrFail();
        $temporaryPath = 'temporary/report-media/2026/09/20/test-photo.jpg';
        $contents = "\xFF\xD8\xFF".str_repeat('A', 64);
        Storage::disk('s3')->put($temporaryPath, $contents);

        $token = Crypt::encryptString(json_encode([
            'version' => 1,
            'disk' => 's3',
            'path' => $temporaryPath,
            'mime_type' => 'image/jpeg',
            'file_size' => strlen($contents),
            'expires_at' => now()->addMinutes(15)->getTimestamp(),
        ], JSON_THROW_ON_ERROR));

        $response = $this->post(route('reports.store'), [
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat pertigaan utama dusun',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Saluran air tersumbat',
            'description' => 'Saluran air tersumbat dan meluap setelah hujan deras.',
            'citizen_priority' => 'important',
            'uploaded_media' => [$token],
            'consent' => '1',
        ]);

        $report = Report::query()->with('media')->firstOrFail();
        $media = $report->media->sole();

        $response->assertRedirect(route('reports.success', $report->report_code));
        $this->assertSame('s3', $media->storage_disk);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertStringStartsWith("reports/{$report->report_code}/before/", $media->file_path);
        Storage::disk('s3')->assertExists($media->file_path);
        Storage::disk('s3')->assertMissing($temporaryPath);
    }

    public function test_direct_upload_authorization_rejects_a_photo_over_five_megabytes(): void
    {
        config()->set('report-media.direct_upload.enabled', true);

        $response = $this->postJson(route('reports.uploads.authorize'), [
            'files' => [[
                'name' => 'terlalu-besar.jpg',
                'type' => 'image/jpeg',
                'size' => (5 * 1024 * 1024) + 1,
            ]],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('files.0.size');
    }

    public function test_vercel_mode_rejects_multipart_uploads_that_bypass_direct_upload(): void
    {
        Storage::fake('s3');
        config()->set('report-media.direct_upload.enabled', true);
        config()->set('report-media.direct_upload.required', true);
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $category = Category::query()->with('subcategories')->firstOrFail();

        $response = $this->from(route('reports.create'))->post(route('reports.store'), [
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat balai dusun',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Lampu jalan tidak menyala',
            'description' => 'Lampu tidak menyala selama beberapa malam dan jalan menjadi gelap.',
            'citizen_priority' => 'important',
            'photos' => [UploadedFile::fake()->image('bukti.jpg')],
            'consent' => '1',
        ]);

        $response
            ->assertRedirect(route('reports.create'))
            ->assertSessionHasErrors(['photos', 'uploaded_media']);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_google_drive_backup_is_queued_without_delaying_submission(): void
    {
        Storage::fake('public');
        Queue::fake();
        config()->set('report-media.google_drive.enabled', true);
        config()->set('report-media.google_drive.queue', 'backups');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $category = Category::query()->with('subcategories')->firstOrFail();

        $response = $this->post(route('reports.store'), [
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat pertigaan utama dusun',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Jalan desa perlu diperbaiki',
            'description' => 'Permukaan jalan rusak dan mengganggu warga yang melintas.',
            'citizen_priority' => 'important',
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
            'consent' => '1',
        ]);

        $media = Report::query()->firstOrFail()->media()->firstOrFail();

        $response->assertRedirect();
        $this->assertSame(MediaBackupStatus::Pending, $media->google_drive_status);
        Queue::assertPushedOn('backups', MirrorReportMediaToGoogleDrive::class);
    }
}
