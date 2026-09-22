<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('data-depth-scene', false)
            ->assertSee('data-aos="fade-up"', false)
            ->assertSee('Pilih yang ingin Anda lakukan.')
            ->assertDontSee('Ringkasan penanganan');
    }

    public function test_site_help_explains_technical_issue_reporting(): void
    {
        $this->get('/bantuan-situs')
            ->assertOk()
            ->assertSee('Ada kendala memakai Padelegan Lapor?')
            ->assertSee('masalah pada situs')
            ->assertSee('Buat Laporan');
    }
}
