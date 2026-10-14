<?php

namespace Tests\Feature\Site;

use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\Subcategory;
use App\Models\Usulan;
use App\Enums\ReportStatus;
use App\Enums\StatusUsulan;
use App\Enums\VerificationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapAndMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_xml_sitemap_returns_valid_xml_with_all_core_routes(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString('/lapor</loc>', $content);
        $this->assertStringContainsString('/laporan</loc>', $content);
        $this->assertStringContainsString('/cek-laporan</loc>', $content);
        $this->assertStringContainsString('/mulai</loc>', $content);
        $this->assertStringContainsString('/usulan</loc>', $content);
        $this->assertStringContainsString('/cek-usulan</loc>', $content);
        $this->assertStringContainsString('/usulan-warga</loc>', $content);
        $this->assertStringContainsString('/tentang</loc>', $content);
        $this->assertStringContainsString('/bantuan-situs</loc>', $content);
        $this->assertStringContainsString('/peta-situs</loc>', $content);
    }

    public function test_xml_sitemap_includes_dynamic_verified_reports_and_published_usulan(): void
    {
        $dusun = Dusun::create(['code' => 'pesisir', 'name' => 'Dusun Pesisir']);
        $category = Category::create([
            'name' => 'Infrastruktur',
            'slug' => 'infrastruktur',
            'icon' => 'tool',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Jalan Rusak',
            'slug' => 'jalan-rusak',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $report = Report::create([
            'report_code' => 'LAP-TEST-XML-01',
            'submission_token' => 'token-test-1',
            'reporter_name' => 'Warga Padelegan',
            'reporter_phone' => '081234567890',
            'title' => 'Jalan Berlubang di Pesisir',
            'description' => 'Aspal mengelupas parah',
            'location_text' => 'Dekat gardu',
            'dusun_id' => $dusun->id,
            'subcategory_id' => $subcategory->id,
            'citizen_priority' => \App\Enums\CitizenPriority::Normal,
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::NotStarted,
        ]);

        $usulan = Usulan::create([
            'kode' => 'USL-TEST-XML-01',
            'token_kirim' => 'token-usl-1',
            'dusun_id' => $dusun->id,
            'jenis' => \App\Enums\JenisUsulan::Pembangunan,
            'judul' => 'Pembangunan Lampu Jalan Nelayan',
            'isi' => 'Mohon dipasang tiang lampu di sepanjang dermaga',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => true,
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('/laporan/LAP-TEST-XML-01</loc>', $content);
        $this->assertStringContainsString('/usulan-warga/USL-TEST-XML-01</loc>', $content);
    }

    public function test_html_sitemap_page_renders_hierarchical_sections_and_links(): void
    {
        $response = $this->get('/peta-situs');

        $response->assertOk();
        $response->assertSee('Peta Situs Desa Padelegan');
        $response->assertSee('Layanan Pengaduan Warga');
        $response->assertSee('Layanan Usulan &amp; Aspirasi Warga', false);
        $response->assertSee('Dokumen XML Sitemap');
        $response->assertSee('robots.txt');
        $response->assertSee(route('sitemap.xml'));
    }

    public function test_site_layout_renders_comprehensive_seo_and_social_media_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // SEO meta
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertSee('<meta name="theme-color" content="#176248">', false);
        $response->assertSee('rel="canonical"', false);

        // Favicons & Webmanifest
        $response->assertSee('href="http://localhost/favicon.ico"', false);
        $response->assertSee('href="http://localhost/favicon-32x32.png"', false);
        $response->assertSee('href="http://localhost/apple-touch-icon.png"', false);
        $response->assertSee('href="http://localhost/site.webmanifest"', false);

        // Open Graph
        $response->assertSee('property="og:site_name" content="Sistem Lapor Padelegan"', false);
        $response->assertSee('property="og:type" content="website"', false);
        $response->assertSee('property="og:locale" content="id_ID"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('og-thumbnail-padelegan.jpg', false);
        $response->assertSee('property="og:image:width" content="1200"', false);
        $response->assertSee('property="og:image:height" content="630"', false);

        // Twitter card
        $response->assertSee('name="twitter:card" content="summary_large_image"', false);
        $response->assertSee('name="twitter:title"', false);
        $response->assertSee('name="twitter:image"', false);
    }

    public function test_all_generated_assets_and_robots_txt_exist(): void
    {
        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));

        $this->assertFileExists(public_path('favicon-32x32.png'));
        $this->assertFileExists(public_path('favicon-16x16.png'));
        $this->assertFileExists(public_path('apple-touch-icon.png'));
        $this->assertFileExists(public_path('site.webmanifest'));
        $this->assertFileExists(public_path('images/brand/og-thumbnail-padelegan.jpg'));

        $this->assertFileExists(public_path('robots.txt'));
        $robotsContent = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Sitemap: https://sistem-lapor-padelegan.vercel.app/sitemap.xml', $robotsContent);
        $this->assertStringContainsString('Disallow: /admin', $robotsContent);
        $this->assertStringContainsString('User-agent: WhatsApp', $robotsContent);
    }
}
