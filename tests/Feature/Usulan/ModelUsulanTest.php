<?php

namespace Tests\Feature\Usulan;

use App\Enums\StatusUsulan;
use App\Models\Usulan;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelUsulanTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_diubah_menjadi_enum(): void
    {
        $this->seed(DusunSeeder::class);
        $usulan = Usulan::factory()->create();

        $this->assertInstanceOf(StatusUsulan::class, $usulan->status);
        $this->assertSame(StatusUsulan::Baru, $usulan->status);
    }

    public function test_identitas_pengusul_disembunyikan_dari_serialisasi(): void
    {
        $this->seed(DusunSeeder::class);
        $usulan = Usulan::factory()->create([
            'nama_pengusul' => 'Warga Rahasia',
            'telepon_pengusul' => '081200000000',
        ]);

        $data = $usulan->toArray();

        $this->assertArrayNotHasKey('nama_pengusul', $data);
        $this->assertArrayNotHasKey('telepon_pengusul', $data);
        $this->assertArrayNotHasKey('token_kirim', $data);
    }

    public function test_scope_tampil_publik_hanya_mengembalikan_yang_dipublikasikan(): void
    {
        $this->seed(DusunSeeder::class);
        Usulan::factory()->create();
        Usulan::factory()->tampilPublik()->create();

        $this->assertSame(1, Usulan::query()->tampilPublik()->count());
    }
}
