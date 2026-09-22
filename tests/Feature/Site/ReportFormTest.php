<?php

namespace Tests\Feature\Site;

use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_form_uses_compact_labeled_steps_and_accessible_hints(): void
    {
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $response = $this->get(route('reports.create'));

        $response
            ->assertOk()
            ->assertSee('Jelaskan masalah ke petugas desa.')
            ->assertSee('Lokasi &amp; foto', false)
            ->assertSee('x-data="reportForm(', false)
            ->assertSee('directUpload:', false)
            ->assertSee('@submit="handleSubmit($event)"', false)
            ->assertSee('aria-describedby="description-hint"', false)
            ->assertSee('aria-describedby="priority-hint"', false)
            ->assertDontSee('Isi sekitar 5–7 menit')
            ->assertDontSee('01 Data pelapor');
    }
}
