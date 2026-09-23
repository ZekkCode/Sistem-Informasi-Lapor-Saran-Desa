<?php

namespace Tests\Feature\Usulan;

use Tests\TestCase;

class HalamanMulaiTest extends TestCase
{
    public function test_halaman_mulai_menampilkan_dua_pilihan(): void
    {
        $this->get(route('mulai'))
            ->assertOk()
            ->assertSee('Mau menyampaikan apa?')
            ->assertSee('Laporan masalah')
            ->assertSee('Usulan &amp; saran', false)
            ->assertSee(route('reports.create'))
            ->assertSee(route('usulan.create'));
    }

    public function test_beranda_mengarahkan_cta_ke_halaman_mulai(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('mulai'));
    }
}
