<?php

namespace Tests\Feature\Usulan;

use App\Models\Usulan;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsulanPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_hanya_usulan_dipublikasikan_yang_tampil_di_daftar(): void
    {
        $this->seed(DusunSeeder::class);
        Usulan::factory()->create(['judul' => 'Usulan tersembunyi belum dipublikasikan']);
        Usulan::factory()->tampilPublik()->create([
            'judul' => 'Usulan tampil publik',
            'nama_pengusul' => 'Warga Rahasia',
            'telepon_pengusul' => '081200000000',
        ]);

        $this->get(route('public-usulan.index'))
            ->assertOk()
            ->assertSee('Usulan tampil publik')
            ->assertDontSee('Usulan tersembunyi belum dipublikasikan')
            ->assertDontSee('Warga Rahasia')
            ->assertDontSee('081200000000');
    }

    public function test_usulan_belum_dipublikasikan_mengembalikan_404_di_detail_publik(): void
    {
        $this->seed(DusunSeeder::class);
        $usulan = Usulan::factory()->create(['kode' => 'USL-2026-HID001']);

        $this->get(route('public-usulan.show', $usulan->kode))->assertNotFound();
    }

    public function test_detail_publik_menampilkan_usulan_yang_dipublikasikan_tanpa_identitas(): void
    {
        $this->seed(DusunSeeder::class);
        $usulan = Usulan::factory()->tampilPublik()->create([
            'kode' => 'USL-2026-PUB001',
            'judul' => 'Usulan taman baca',
            'nama_pengusul' => 'Warga Rahasia',
            'telepon_pengusul' => '081200000000',
        ]);

        $this->get(route('public-usulan.show', $usulan->kode))
            ->assertOk()
            ->assertSee('Usulan taman baca')
            ->assertDontSee('Warga Rahasia')
            ->assertDontSee('081200000000');
    }
}
