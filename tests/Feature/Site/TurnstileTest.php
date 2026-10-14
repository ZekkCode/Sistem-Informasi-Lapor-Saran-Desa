<?php

namespace Tests\Feature\Site;

use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\Usulan;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Concerns\WithAntiBotFields;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    use RefreshDatabase;
    use WithAntiBotFields;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
    }

    public function test_turnstile_widget_renders_when_site_key_configured(): void
    {
        Config::set('services.turnstile.site_key', '1x00000000000000000000AA');

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('cf-turnstile')
            ->assertSee('data-sitekey="1x00000000000000000000AA"', false)
            ->assertSee('Pemeriksaan ini memastikan formulir dikirim oleh warga, bukan program bot pengirim spam.')
            ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js');

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertSee('cf-turnstile')
            ->assertSee('data-sitekey="1x00000000000000000000AA"', false)
            ->assertSee('Pemeriksaan ini memastikan formulir dikirim oleh warga, bukan program bot pengirim spam.')
            ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js');
    }

    public function test_turnstile_widget_hidden_when_site_key_empty(): void
    {
        Config::set('services.turnstile.site_key', null);

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertDontSee('cf-turnstile')
            ->assertDontSee('challenges.cloudflare.com/turnstile/v0/api.js');

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertDontSee('cf-turnstile')
            ->assertDontSee('challenges.cloudflare.com/turnstile/v0/api.js');
    }

    public function test_submission_fails_when_turnstile_response_missing_and_secret_active(): void
    {
        Config::set('services.turnstile.secret_key', '1x0000000000000000000000000000000AA');

        $category = Category::query()->with('subcategories')->firstOrFail();
        $payload = $this->withAntiBotFields([
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat pos kamling',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Saluran air tersumbat',
            'description' => 'Saluran air meluap ke jalan setelah hujan lebat kemarin sore.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('bukti.jpg')],
            'consent' => '1',
        ]);

        $response = $this->from(route('reports.create'))->post(route('reports.store'), $payload);

        $response->assertRedirect(route('reports.create'));
        $response->assertSessionHasErrors('cf-turnstile-response');
        $this->assertDatabaseCount(Report::class, 0);
    }

    public function test_submission_fails_when_cloudflare_verification_rejects_token(): void
    {
        Config::set('services.turnstile.secret_key', '1x0000000000000000000000000000000AA');

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $payload = $this->withAntiBotFields([
            'token_kirim' => Str::uuid()->toString(),
            'dusun_id' => Dusun::query()->value('id'),
            'jenis' => 'pembangunan',
            'judul' => 'Perbaikan tanggul dusun',
            'isi' => 'Mohon bantuan perbaikan tanggul dusun yang retak sebelum musim hujan tiba.',
            'consent' => '1',
            'cf-turnstile-response' => 'token-palsu-atau-kedaluwarsa',
        ]);

        $response = $this->from(route('usulan.create'))->post(route('usulan.store'), $payload);

        $response->assertRedirect(route('usulan.create'));
        $response->assertSessionHasErrors([
            'cf-turnstile-response' => 'Pemeriksaan keamanan gagal diverifikasi. Centang kembali kotak pemeriksaan lalu coba lagi.',
        ]);
        $this->assertDatabaseCount(Usulan::class, 0);
    }

    public function test_submission_succeeds_when_cloudflare_verification_passes(): void
    {
        Config::set('services.turnstile.secret_key', '1x0000000000000000000000000000000AA');

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $category = Category::query()->with('subcategories')->firstOrFail();
        $payload = $this->withAntiBotFields([
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat pos kamling',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Saluran air tersumbat',
            'description' => 'Saluran air meluap ke jalan setelah hujan lebat kemarin sore.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('bukti.jpg')],
            'consent' => '1',
            'cf-turnstile-response' => 'token-valid-cloudflare',
        ]);

        $response = $this->post(route('reports.store'), $payload);

        $report = Report::query()->sole();
        $response->assertRedirect(route('reports.success', $report->report_code));
        $this->assertDatabaseCount(Report::class, 1);
    }
}
