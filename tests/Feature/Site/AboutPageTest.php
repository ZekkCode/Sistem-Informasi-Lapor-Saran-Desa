<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_halaman_tentang_menampilkan_tim_kkn_dan_kredit_zekkcode(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk()
            ->assertSee('MOH ALFAN BAETONI')
            ->assertSee('Koordinator Desa (Kordes)')
            ->assertSee('ABDUR RAHMAN JAINUR DANI')
            ->assertSee('Wakil Koordinator Desa (Wakordes)')
            ->assertSee('SUAIDA TRI RAHAYU')
            ->assertSee('Bendahara')
            ->assertSee('ENI WILDATUL UMMAH')
            ->assertSee('Sekretaris')
            ->assertSee('FASYAROTUL JANNAH')
            ->assertSee('Kominfo')
            ->assertSee('zekkcode by web');
    }

    public function test_footer_menampilkan_kredit_zekkcode_dan_tautan_tim_kkn(): void
    {
        // Uji di halaman beranda (compact footer)
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tim KKN')
            ->assertSee('zekkcode by web');

        // Uji di halaman tentang (normal footer)
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Tentang & Tim KKN', false)
            ->assertSee('zekkcode by web');
    }
}
