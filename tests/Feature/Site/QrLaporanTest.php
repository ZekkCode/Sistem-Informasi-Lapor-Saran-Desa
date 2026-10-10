<?php

namespace Tests\Feature\Site;

use App\Models\Category;
use App\Models\QrSource;
use App\Models\Report;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Database\Seeders\QrSourceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function siapkanData(): QrSource
    {
        $this->seed([DusunSeeder::class, CategorySeeder::class, QrSourceSeeder::class]);

        return QrSource::query()->with('dusun')->where('code', 'DSN01')->firstOrFail();
    }

    public function test_tautan_qr_langsung_membuka_form_dengan_dusun_terisi(): void
    {
        $qr = $this->siapkanData();

        // Huruf kecil tetap dikenali karena kode diubah ke huruf besar.
        $this->get('/lapor?qr=dsn01')
            ->assertOk()
            ->assertSee('Formulir dari')
            ->assertSee($qr->name)
            ->assertSee('name="qr_code" value="DSN01"', false)
            ->assertSee('value="'.$qr->dusun_id.'" selected', false);
    }

    public function test_laporan_dari_qr_menyimpan_sumber_qr(): void
    {
        Storage::fake('public');
        $qr = $this->siapkanData();
        $category = Category::query()->with('subcategories')->firstOrFail();

        $this->post(route('reports.store'), [
            'submission_token' => Str::uuid()->toString(),
            'qr_code' => 'dsn01',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => $qr->dusun_id,
            'location_text' => 'Dekat balai dusun',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Lampu jalan mati',
            'description' => 'Lampu di depan balai dusun tidak menyala sejak dua malam lalu.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
            'consent' => '1',
        ])->assertRedirect();

        $this->assertSame($qr->id, Report::query()->sole()->qr_source_id);
    }

    public function test_kode_qr_tidak_dikenal_tetap_membuka_form_biasa(): void
    {
        $this->siapkanData();

        $this->get('/lapor?qr=TIDAKADA')
            ->assertOk()
            ->assertDontSee('Formulir dari')
            ->assertSee('name="qr_code" value=""', false);
    }

    public function test_qr_nonaktif_diabaikan(): void
    {
        $qr = $this->siapkanData();
        $qr->update(['is_active' => false]);

        $this->get('/lapor?qr=DSN01')
            ->assertOk()
            ->assertDontSee('Formulir dari');
    }
}
