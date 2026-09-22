<?php

namespace Tests\Feature\Site;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DesignSystemComponentsTest extends TestCase
{
    public function test_ui_primitives_render_with_expected_semantics(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.action variant="danger" type="submit">Hapus</x-ui.action>
            <x-ui.input name="title" required />
            <x-ui.alert variant="warning" title="Perhatian">Periksa data.</x-ui.alert>
            <x-ui.badge variant="success">Selesai</x-ui.badge>
            <x-ui.empty-state title="Belum ada laporan" />
        BLADE);

        $this->assertStringContainsString('type="submit"', $html);
        $this->assertStringContainsString('action--danger', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('Belum ada laporan', $html);
    }

    public function test_report_badges_keep_internal_and_public_status_separate(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-reports.status status="in_progress" />
            <x-reports.verification-badge status="verified" />
            <x-reports.priority-badge priority="emergency" />
        BLADE);

        $this->assertStringContainsString('Proses Pelaksanaan', $html);
        $this->assertStringContainsString('Terverifikasi', $html);
        $this->assertStringContainsString('Darurat', $html);
    }
}
