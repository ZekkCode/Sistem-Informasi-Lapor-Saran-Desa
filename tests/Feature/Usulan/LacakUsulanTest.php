<?php

namespace Tests\Feature\Usulan;

use App\Models\Usulan;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LacakUsulanTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_lacak_menampilkan_status_tanpa_identitas(): void
    {
        $this->seed(DusunSeeder::class);
        $usulan = Usulan::factory()->create([
            'kode' => 'USL-2026-ABC234',
            'nama_pengusul' => 'Warga Rahasia',
            'telepon_pengusul' => '081200000000',
            'judul' => 'Usulan taman baca dusun',
        ]);

        $this->get(route('usulan.lacak', ['kode' => $usulan->kode]))
            ->assertOk()
            ->assertSee('Usulan taman baca dusun')
            ->assertSee('Baru')
            ->assertDontSee('Warga Rahasia')
            ->assertDontSee('081200000000');
    }

    public function test_nomor_tidak_dikenal_menampilkan_pesan_tidak_ditemukan(): void
    {
        $this->seed(DusunSeeder::class);

        $this->get(route('usulan.lacak', ['kode' => 'USL-2026-ZZZ999']))
            ->assertOk()
            ->assertSee('Nomor usulan tidak ditemukan.');
    }

    public function test_halaman_sukses_dapat_dirender(): void
    {
        $this->seed(DusunSeeder::class);
        $usulan = Usulan::factory()->create(['kode' => 'USL-2026-SUK123']);

        $this->get(route('usulan.sukses', $usulan->kode))
            ->assertOk()
            ->assertSee('USL-2026-SUK123')
            ->assertSee('Usulan sudah kami terima.');
    }
}
