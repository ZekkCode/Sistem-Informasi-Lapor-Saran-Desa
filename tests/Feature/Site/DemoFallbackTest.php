<?php

namespace Tests\Feature\Site;

use App\Models\Report;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DemoFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_form_uses_read_only_demo_references_until_seeded(): void
    {
        $response = $this->get(route('reports.create'));

        $response
            ->assertOk()
            ->assertSee('Mode pratinjau')
            ->assertSee('Fasilitas Desa')
            ->assertSee('Bangkal')
            ->assertSee('demoMode: true', false)
            ->assertSee('Pengiriman belum aktif');
    }

    public function test_demo_references_disappear_when_real_reference_data_exists(): void
    {
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertDontSee('Mode pratinjau')
            ->assertSee('demoMode: false', false);
    }

    public function test_public_reports_and_tracking_have_a_demo_dataset(): void
    {
        $demoCode = 'PDL-'.now()->format('Y').'-DEMO02';

        $this->get(route('public-reports.index'))
            ->assertOk()
            ->assertSee('Mode pratinjau')
            ->assertSee($demoCode)
            ->assertSee('3 laporan terverifikasi');

        $this->get(route('reports.track'))
            ->assertOk()
            ->assertSee($demoCode)
            ->assertSee('Cek status laporan.');

        $this->get(route('reports.track', ['code' => $demoCode]))
            ->assertOk()
            ->assertSee('Lampu penerangan jalan tidak menyala')
            ->assertSee('Riwayat penanganan');

        $this->get(route('public-reports.show', $demoCode))
            ->assertOk()
            ->assertSee('Laporan contoh')
            ->assertSee('Lampu penerangan jalan tidak menyala');
    }

    public function test_demo_form_cannot_create_a_real_report(): void
    {
        Storage::fake('public');

        $response = $this->from(route('reports.create'))->post(route('reports.store'), [
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Pratinjau',
            'reporter_phone' => '081234567890',
            'dusun_id' => 1001,
            'location_text' => 'Dekat balai dusun',
            'category_id' => 2001,
            'subcategory_id' => 3001,
            'title' => 'Lampu jalan tidak menyala',
            'description' => 'Lampu tidak menyala selama beberapa malam dan jalan menjadi gelap.',
            'citizen_priority' => 'important',
            'photos' => [UploadedFile::fake()->image('bukti.jpg')],
            'consent' => '1',
        ]);

        $response
            ->assertRedirect(route('reports.create'))
            ->assertSessionHasErrors('submission');
        $this->assertDatabaseCount(Report::class, 0);
    }
}
