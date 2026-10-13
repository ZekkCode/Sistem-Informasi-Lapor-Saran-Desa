<?php

namespace Tests\Feature\Usulan;

use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PratinjauUsulanTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_usulan_terbuka_tanpa_data_awal(): void
    {
        // Tanpa seeding, situs berada dalam mode pratinjau.
        $this->get(route('usulan.create'))->assertOk()->assertSee('Mode pratinjau');
        $this->get(route('usulan.lacak'))->assertOk()->assertSee('Cek tindak lanjut usulan.');
        $this->get(route('public-usulan.index'))->assertOk()->assertSee('Usulan Warga');
    }

    public function test_database_tersambung_tanpa_usulan_terpublikasi_tidak_memakai_mode_pratinjau(): void
    {
        $this->seed(DusunSeeder::class);

        $this->get(route('public-usulan.index'))
            ->assertOk()
            ->assertDontSee('Mode pratinjau')
            ->assertDontSee('DEMO')
            ->assertSee('0 usulan ditampilkan')
            ->assertSee('Belum ada usulan yang dipublikasikan')
            ->assertSee(route('usulan.create'));

        $this->get(route('public-usulan.index', ['q' => 'taman']))
            ->assertOk()
            ->assertSee('Belum ada usulan yang sesuai')
            ->assertSee('Reset filter');

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertDontSee('Mode pratinjau');
    }

    public function test_beranda_menautkan_ke_usulan(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('usulan.create'))
            ->assertSee(route('public-usulan.index'));
    }
}
