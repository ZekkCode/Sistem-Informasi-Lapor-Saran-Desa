<?php

namespace Tests\Feature\Formulir;

use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Usulan;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class KunciFormulirPublikTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
    }

    /**
     * Layanan kunci memoize nilai per request lifecycle, jadi baris settings
     * disiapkan sebelum request pertama.
     *
     * @param  array<string, string|null>  $nilai
     */
    private function kunci(array $nilai): void
    {
        foreach ($nilai as $key => $value) {
            Setting::query()->create(['key' => $key, 'value' => $value]);
        }
    }

    /** @return array<string, mixed> */
    private function dataLaporan(): array
    {
        $category = Category::query()->with('subcategories')->firstOrFail();

        return [
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'dusun_id' => Dusun::query()->value('id'),
            'location_text' => 'Dekat balai dusun',
            'category_id' => $category->id,
            'subcategory_id' => $category->subcategories->firstOrFail()->id,
            'title' => 'Lampu jalan mati',
            'description' => 'Lampu di depan balai dusun tidak menyala sejak dua malam lalu.',
            'citizen_priority' => 'normal',
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
            'consent' => '1',
        ];
    }

    /** @return array<string, mixed> */
    private function dataUsulan(): array
    {
        return [
            'token_kirim' => Str::uuid()->toString(),
            'dusun_id' => Dusun::query()->value('id'),
            'jenis' => 'pembangunan',
            'judul' => 'Tambah lampu jalan dusun',
            'isi' => 'Usul menambah lampu penerangan di jalur dusun agar warga aman saat malam.',
            'consent' => '1',
        ];
    }

    public function test_kedua_formulir_terbuka_tanpa_baris_settings(): void
    {
        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('name="submission_token"', false);

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertSee('name="token_kirim"', false);
    }

    public function test_kunci_admin_laporan_menampilkan_pemberitahuan_tanpa_field(): void
    {
        $this->kunci(['kunci_admin_laporan' => '1', 'catatan_kunci_laporan' => 'Dibuka lagi Senin pagi.']);

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('Formulir laporan ditutup sementara.')
            ->assertSee('Catatan petugas')
            ->assertSee('Dibuka lagi Senin pagi.')
            ->assertSee(route('reports.track'))
            ->assertSee('Cek status laporan')
            ->assertSee('https://wa.me/'.config('padelegan.support_whatsapp'), false)
            ->assertDontSee('name="submission_token"', false)
            ->assertDontSee('name="reporter_name"', false)
            ->assertDontSee('data-aos', false);
    }

    public function test_kunci_pengembang_laporan_menampilkan_pemeliharaan_tanpa_catatan_admin(): void
    {
        $this->kunci([
            'kunci_admin_laporan' => '1',
            'catatan_kunci_laporan' => 'Catatan dari admin desa',
            'kunci_pengembang_laporan' => '1',
        ]);

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('Formulir laporan sedang dalam pemeliharaan.')
            ->assertDontSee('Catatan dari admin desa')
            ->assertDontSee('name="submission_token"', false);
    }

    public function test_kirim_laporan_ditolak_saat_kunci_admin_aktif(): void
    {
        $this->kunci(['kunci_admin_laporan' => '1']);

        $this->post(route('reports.store'), $this->dataLaporan())
            ->assertRedirect(route('reports.create'))
            ->assertSessionHasErrors('submission');

        $this->assertDatabaseCount(Report::class, 0);

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('Laporan Anda belum terkirim karena formulir ditutup sebelum laporan tersimpan.');
    }

    public function test_kirim_laporan_ditolak_saat_kunci_pengembang_aktif(): void
    {
        $this->kunci(['kunci_pengembang_laporan' => '1']);

        $this->post(route('reports.store'), $this->dataLaporan())
            ->assertRedirect(route('reports.create'))
            ->assertSessionHasErrors('submission');

        $this->assertDatabaseCount(Report::class, 0);
    }

    public function test_otorisasi_unggah_foto_ditolak_423_saat_terkunci(): void
    {
        foreach (['kunci_admin_laporan', 'kunci_pengembang_laporan'] as $key) {
            Setting::query()->delete();
            $this->kunci([$key => '1']);
            // Kosongkan memo layanan kunci agar baris baru terbaca.
            $this->app->forgetScopedInstances();

            $response = $this->postJson(route('reports.uploads.authorize'), [
                'files' => [['name' => 'bukti.jpg', 'type' => 'image/jpeg', 'size' => 1000]],
            ]);

            $response->assertStatus(423);
            $this->assertStringContainsString('belum terkirim', $response->json('message'));
        }
    }

    public function test_kunci_usulan_tidak_memengaruhi_formulir_laporan(): void
    {
        $this->kunci(['kunci_admin_usulan' => '1']);

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertSee('Formulir usulan ditutup sementara.')
            ->assertSee(route('usulan.lacak'))
            ->assertSee('Cek status usulan')
            ->assertDontSee('name="token_kirim"', false);

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('name="submission_token"', false);

        $response = $this->post(route('reports.store'), $this->dataLaporan());
        $response->assertRedirect(route('reports.success', Report::query()->sole()->report_code));

        $this->post(route('usulan.store'), $this->dataUsulan())
            ->assertRedirect(route('usulan.create'))
            ->assertSessionHasErrors('submission');
        $this->assertDatabaseCount(Usulan::class, 0);
    }

    public function test_kirim_usulan_ditolak_saat_kunci_pengembang_aktif(): void
    {
        $this->kunci(['kunci_pengembang_usulan' => '1']);

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertSee('Formulir usulan sedang dalam pemeliharaan.');

        $this->post(route('usulan.store'), $this->dataUsulan())
            ->assertRedirect(route('usulan.create'))
            ->assertSessionHasErrors('submission');
        $this->assertDatabaseCount(Usulan::class, 0);
    }

    public function test_kunci_laporan_tidak_memengaruhi_kiriman_usulan(): void
    {
        $this->kunci(['kunci_admin_laporan' => '1', 'kunci_pengembang_laporan' => '1']);

        $response = $this->post(route('usulan.store'), $this->dataUsulan());

        $response->assertRedirect(route('usulan.sukses', Usulan::query()->sole()->kode));
    }

    public function test_cek_status_dan_daftar_publik_tetap_terbuka_saat_terkunci(): void
    {
        $this->kunci([
            'kunci_admin_laporan' => '1',
            'kunci_pengembang_laporan' => '1',
            'kunci_admin_usulan' => '1',
            'kunci_pengembang_usulan' => '1',
        ]);

        $this->get(route('reports.track'))->assertOk();
        $this->get(route('usulan.lacak'))->assertOk();
        $this->get(route('public-reports.index'))->assertOk();
        $this->get(route('public-usulan.index'))->assertOk();
    }

    public function test_halaman_mulai_menandai_formulir_yang_ditutup(): void
    {
        $this->kunci(['kunci_admin_laporan' => '1']);

        $this->get(route('mulai'))
            ->assertOk()
            ->assertSee('Ditutup sementara')
            ->assertSee('Kirim usulan')
            ->assertSee(route('reports.create'));
    }

    public function test_tabel_settings_hilang_formulir_tetap_terbuka(): void
    {
        Schema::drop('settings');

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertSee('name="token_kirim"', false);

        $response = $this->post(route('usulan.store'), $this->dataUsulan());
        $response->assertRedirect(route('usulan.sukses', Usulan::query()->sole()->kode));
    }
}
