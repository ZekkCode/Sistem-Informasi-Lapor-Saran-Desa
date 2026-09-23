<?php

namespace Tests\Feature\Usulan;

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

    public function test_beranda_menautkan_ke_usulan(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('usulan.create'))
            ->assertSee(route('public-usulan.index'));
    }
}
