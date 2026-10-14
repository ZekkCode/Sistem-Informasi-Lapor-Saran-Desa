<?php

namespace App\Http\Controllers\Site;

use App\Enums\JenisUsulan;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\Usulan;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SitemapController extends Controller
{
    /**
     * Menghasilkan dokumen XML Sitemap berlapis untuk mesin pencari (Google, Bing, dsb).
     */
    public function xml(): Response
    {
        $baseUrl = config('app.url') ?: url('/');
        $baseUrl = rtrim($baseUrl, '/');
        $now = Carbon::now()->toIso8601String();

        $urls = [];

        // 1. Level 1 - Beranda Utama
        $urls[] = [
            'loc' => $baseUrl . '/',
            'lastmod' => $now,
            'changefreq' => 'daily',
            'priority' => '1.0',
            'section' => 'Halaman Utama',
        ];

        // 2. Level 2 - Modul Layanan Pengaduan (Laporan)
        $urls[] = [
            'loc' => $baseUrl . '/lapor',
            'lastmod' => $now,
            'changefreq' => 'weekly',
            'priority' => '0.9',
            'section' => 'Layanan Pengaduan',
        ];
        $urls[] = [
            'loc' => $baseUrl . '/cek-laporan',
            'lastmod' => $now,
            'changefreq' => 'daily',
            'priority' => '0.8',
            'section' => 'Layanan Pengaduan',
        ];
        $urls[] = [
            'loc' => $baseUrl . '/laporan',
            'lastmod' => $now,
            'changefreq' => 'daily',
            'priority' => '0.9',
            'section' => 'Layanan Pengaduan',
        ];

        // 3. Level 2 - Modul Layanan Aspirasi (Usulan Warga)
        $urls[] = [
            'loc' => $baseUrl . '/mulai',
            'lastmod' => $now,
            'changefreq' => 'monthly',
            'priority' => '0.8',
            'section' => 'Layanan Aspirasi',
        ];
        $urls[] = [
            'loc' => $baseUrl . '/usulan',
            'lastmod' => $now,
            'changefreq' => 'weekly',
            'priority' => '0.9',
            'section' => 'Layanan Aspirasi',
        ];
        $urls[] = [
            'loc' => $baseUrl . '/cek-usulan',
            'lastmod' => $now,
            'changefreq' => 'daily',
            'priority' => '0.8',
            'section' => 'Layanan Aspirasi',
        ];
        $urls[] = [
            'loc' => $baseUrl . '/usulan-warga',
            'lastmod' => $now,
            'changefreq' => 'daily',
            'priority' => '0.9',
            'section' => 'Layanan Aspirasi',
        ];

        // 4. Level 2 - Informasi & Panduan Desa
        $urls[] = [
            'loc' => $baseUrl . '/tentang',
            'lastmod' => $now,
            'changefreq' => 'monthly',
            'priority' => '0.8',
            'section' => 'Informasi Desa',
        ];
        $urls[] = [
            'loc' => $baseUrl . '/bantuan-situs',
            'lastmod' => $now,
            'changefreq' => 'monthly',
            'priority' => '0.8',
            'section' => 'Informasi Desa',
        ];
        $urls[] = [
            'loc' => $baseUrl . '/peta-situs',
            'lastmod' => $now,
            'changefreq' => 'weekly',
            'priority' => '0.7',
            'section' => 'Peta Situs',
        ];

        // 5. Level 3 - Sub-Halaman Arsip Terfilter (Kategori & Dusun)
        try {
            if (Schema::hasTable('categories')) {
                $categories = Category::query()->orderBy('name')->get(['id', 'name']);
                foreach ($categories as $category) {
                    $urls[] = [
                        'loc' => $baseUrl . '/laporan?category=' . $category->id,
                        'lastmod' => $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                        'section' => 'Laporan per Kategori: ' . $category->name,
                    ];
                }
            }

            if (Schema::hasTable('dusuns')) {
                $dusuns = Dusun::query()->orderBy('name')->get(['id', 'name']);
                foreach ($dusuns as $dusun) {
                    $urls[] = [
                        'loc' => $baseUrl . '/laporan?dusun=' . $dusun->id,
                        'lastmod' => $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                        'section' => 'Laporan per Dusun: ' . $dusun->name,
                    ];
                    $urls[] = [
                        'loc' => $baseUrl . '/usulan-warga?dusun=' . $dusun->id,
                        'lastmod' => $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                        'section' => 'Usulan per Dusun: ' . $dusun->name,
                    ];
                }
            }

            foreach (JenisUsulan::cases() as $jenis) {
                $urls[] = [
                    'loc' => $baseUrl . '/usulan-warga?jenis=' . $jenis->value,
                    'lastmod' => $now,
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                    'section' => 'Usulan per Jenis: ' . $jenis->label(),
                ];
            }

            // 6. Level 4 - Detail Laporan Terverifikasi Publik
            if (Schema::hasTable('reports')) {
                $reports = Report::visibleToPublic()
                    ->latest('updated_at')
                    ->take(300)
                    ->get(['report_code', 'updated_at']);

                foreach ($reports as $report) {
                    $urls[] = [
                        'loc' => $baseUrl . '/laporan/' . $report->report_code,
                        'lastmod' => $report->updated_at ? $report->updated_at->toIso8601String() : $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                        'section' => 'Detail Laporan Terverifikasi',
                    ];
                }
            }

            // 7. Level 4 - Detail Usulan Dipublikasikan Publik
            if (Schema::hasTable('usulan')) {
                $usulans = Usulan::tampilPublik()
                    ->latest('updated_at')
                    ->take(300)
                    ->get(['kode', 'updated_at']);

                foreach ($usulans as $usulan) {
                    $urls[] = [
                        'loc' => $baseUrl . '/usulan-warga/' . $usulan->kode,
                        'lastmod' => $usulan->updated_at ? $usulan->updated_at->toIso8601String() : $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                        'section' => 'Detail Usulan Publik',
                    ];
                }
            }
        } catch (\Throwable) {
            // Fallback graceful jika database sedang dalam migrasi
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<!-- Sitemap Resmi Sistem Informasi Lapor & Aspirasi Warga Desa Padelegan -->' . "\n";
        $xml .= '<!-- Pemerintah Desa Padelegan, Kecamatan Pademawu, Kabupaten Pamekasan -->' . "\n";
        $xml .= '<!-- Arsitektur & Rekayasa Perangkat Lunak oleh Zakaria Mujur (Zekk Code) -->' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $item) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "        <lastmod>" . $item['lastmod'] . "</lastmod>\n";
            $xml .= "        <changefreq>" . $item['changefreq'] . "</changefreq>\n";
            $xml .= "        <priority>" . $item['priority'] . "</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    /**
     * Menampilkan halaman HTML Peta Situs publik berstruktur pohon sub-level ("ber-sub ber-sub tiap halaman").
     */
    public function htmlSitemap(): View
    {
        $categories = collect();
        $dusuns = collect();
        $totalLaporan = 0;
        $totalUsulan = 0;

        try {
            if (Schema::hasTable('categories')) {
                $categories = Category::query()->orderBy('name')->get();
            }
            if (Schema::hasTable('dusuns')) {
                $dusuns = Dusun::query()->orderBy('name')->get();
            }
            if (Schema::hasTable('reports')) {
                $totalLaporan = Report::visibleToPublic()->count();
            }
            if (Schema::hasTable('usulan')) {
                $totalUsulan = Usulan::tampilPublik()->count();
            }
        } catch (\Throwable) {
            // Gunakan fallback kosong jika DB bermasalah
        }

        return view('site.sitemap', [
            'categories' => $categories,
            'dusuns' => $dusuns,
            'jenisUsulan' => JenisUsulan::cases(),
            'totalLaporan' => $totalLaporan,
            'totalUsulan' => $totalUsulan,
        ]);
    }
}
