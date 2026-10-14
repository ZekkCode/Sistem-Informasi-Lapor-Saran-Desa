<?php

namespace Tests\Feature\Site;

use App\Models\Category;
use App\Models\Dusun;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AntiBotTest extends TestCase
{
    use RefreshDatabase;

    // ─── Laporan ───

    public function test_report_honeypot_filled_is_rejected(): void
    {
        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $category = Category::query()->with('subcategories')->firstOrFail();

        $response = $this->from(route('reports.create'))->post(route('reports.store'), [
            '_website' => 'http://spam.example.com',
            '_form_rendered_at' => (string) (time() - 10),
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Bot Spammer',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Lokasi palsu',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Spam spam spam spam',
            'description' => 'Ini adalah pengiriman spam dari bot otomatis yang mengisi honeypot.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('spam.jpg')],
            'consent' => '1',
        ]);

        $response->assertRedirect(route('reports.create'));
        $response->assertSessionHasErrors('_website');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_submitted_too_fast_is_rejected(): void
    {
        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $category = Category::query()->with('subcategories')->firstOrFail();

        $response = $this->from(route('reports.create'))->post(route('reports.store'), [
            '_website' => '',
            '_form_rendered_at' => (string) time(), // 0 detik lalu — terlalu cepat
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Fast Bot',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Lokasi palsu',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Pengiriman kilat otomatis',
            'description' => 'Bot mengirim formulir dalam waktu kurang dari satu detik.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('fast.jpg')],
            'consent' => '1',
        ]);

        $response->assertRedirect(route('reports.create'));
        $response->assertSessionHasErrors('_form_rendered_at');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_without_timing_field_is_rejected(): void
    {
        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $category = Category::query()->with('subcategories')->firstOrFail();

        $response = $this->from(route('reports.create'))->post(route('reports.store'), [
            '_website' => '',
            // _form_rendered_at sengaja tidak disertakan
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat balai dusun',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Laporan tanpa field waktu',
            'description' => 'Pengiriman dari klien yang tidak menyertakan field waktu render.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('bukti.jpg')],
            'consent' => '1',
        ]);

        $response->assertRedirect(route('reports.create'));
        $response->assertSessionHasErrors('_form_rendered_at');
        $this->assertDatabaseCount('reports', 0);
    }

    // ─── Usulan ───

    public function test_usulan_honeypot_filled_is_rejected(): void
    {
        $this->seed(DusunSeeder::class);

        $response = $this->from(route('usulan.create'))->post(route('usulan.store'), [
            '_website' => 'http://spam.example.com',
            '_form_rendered_at' => (string) (time() - 10),
            'token_kirim' => Str::uuid()->toString(),
            'dusun_id' => Dusun::query()->value('id'),
            'jenis' => 'pembangunan',
            'judul' => 'Usulan bot',
            'isi' => 'Bot mengirim usulan dengan field honeypot terisi, seharusnya ditolak.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('usulan.create'));
        $response->assertSessionHasErrors('_website');
        $this->assertDatabaseCount('usulan', 0);
    }

    public function test_usulan_submitted_too_fast_is_rejected(): void
    {
        $this->seed(DusunSeeder::class);

        $response = $this->from(route('usulan.create'))->post(route('usulan.store'), [
            '_website' => '',
            '_form_rendered_at' => (string) time(),
            'token_kirim' => Str::uuid()->toString(),
            'dusun_id' => Dusun::query()->value('id'),
            'jenis' => 'pelayanan',
            'judul' => 'Usulan cepat kilat',
            'isi' => 'Bot mengirim usulan dalam waktu kurang dari satu detik, terlalu cepat.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('usulan.create'));
        $response->assertSessionHasErrors('_form_rendered_at');
        $this->assertDatabaseCount('usulan', 0);
    }

    // ─── Security Headers ───

    public function test_security_headers_are_present_on_public_pages(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Content-Security-Policy');
    }
}
