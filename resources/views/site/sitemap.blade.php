@extends('layouts.site')

@section('title', 'Peta Situs')
@section('meta_description', 'Peta situs lengkap Sistem Lapor Padelegan. Akses terstruktur seluruh modul pelaporan masalah desa, usulan pembangunan warga, dan pusat bantuan.')

@section('content')
    <section class="border-b border-padelegan-800/10 bg-white">
        <div class="site-shell py-8 sm:py-10">
            <div class="max-w-3xl">
                <p class="public-kicker">Struktur Navigasi & Arsitektur SEO</p>
                <h1 class="public-page-title text-padelegan-900 mt-1">Peta Situs Desa Padelegan</h1>
                <p class="reading-measure mt-3 text-sm leading-6 text-padelegan-900/70 sm:text-base">
                    Daftar terstruktur seluruh modul layanan, formulir pengaduan, tabel usulan warga, dan direktori publik Pemerintah Desa Padelegan, Pademawu, Pamekasan. Dikembangkan oleh Zekk Code.
                </p>
                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs">
                    <a href="{{ route('sitemap.xml') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-padelegan-800/20 bg-padelegan-50 px-3 py-1.5 font-medium text-padelegan-900 transition hover:bg-padelegan-100">
                        <svg class="h-3.5 w-3.5 text-emerald-700" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>Dokumen XML Sitemap</span>
                    </a>
                    <a href="/robots.txt" target="_blank" class="inline-flex items-center gap-1.5 rounded-md border border-padelegan-800/20 bg-padelegan-50 px-3 py-1.5 font-medium text-padelegan-900 transition hover:bg-padelegan-100">
                        <svg class="h-3.5 w-3.5 text-emerald-700" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <path d="M9 17v-6h6v6"/>
                        </svg>
                        <span>robots.txt</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="site-shell py-10 lg:py-14">
        <div class="grid gap-10 lg:grid-cols-2">

            {{-- 1. Modul Layanan Pengaduan --}}
            <div class="rounded-xl border border-padelegan-800/15 bg-white p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 border-b border-padelegan-800/10 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 text-white">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-padelegan-900">1. Layanan Pengaduan Warga</h2>
                        <p class="text-xs text-padelegan-800/70">Kirim, pantau status, dan baca laporan terverifikasi</p>
                    </div>
                </div>

                <ul class="mt-5 space-y-4 text-sm text-padelegan-900">
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('reports.create') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>1.1 Formulir Buat Laporan</span>
                            <span class="text-xs font-normal text-padelegan-700">/lapor &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Pengiriman masalah lingkungan, jalan, atau fasilitas desa beserta bukti foto.</p>
                    </li>

                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('reports.track') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>1.2 Cek Status & Lacak Laporan</span>
                            <span class="text-xs font-normal text-padelegan-700">/cek-laporan &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Masukkan nomor laporan untuk melihat perkembangan dan catatan petugas.</p>
                    </li>

                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('public-reports.index') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>1.3 Arsip Publik Laporan Terverifikasi ({{ $totalLaporan }} Laporan)</span>
                            <span class="text-xs font-normal text-padelegan-700">/laporan &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Daftar laporan warga yang sudah diperiksa petugas secara transparan.</p>

                        {{-- Sub-Kategori Laporan --}}
                        <div class="mt-3 pl-3 border-l-2 border-emerald-600 space-y-2">
                            <div>
                                <p class="text-xs font-semibold text-padelegan-900">1.3.1 Sub-Halaman per Kategori:</p>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    @foreach ($categories as $cat)
                                        <a href="{{ route('public-reports.index', ['category' => $cat->id]) }}" class="rounded bg-white px-2 py-0.5 text-xs text-emerald-800 border border-padelegan-800/15 hover:bg-emerald-50">
                                            {{ $cat->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-padelegan-900 mt-2">1.3.2 Sub-Halaman per Dusun:</p>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    @foreach ($dusuns as $dusun)
                                        <a href="{{ route('public-reports.index', ['dusun' => $dusun->id]) }}" class="rounded bg-white px-2 py-0.5 text-xs text-emerald-800 border border-padelegan-800/15 hover:bg-emerald-50">
                                            {{ $dusun->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- 2. Modul Layanan Usulan & Aspirasi --}}
            <div class="rounded-xl border border-padelegan-800/15 bg-white p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 border-b border-padelegan-800/10 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 text-white">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
                            <path d="M9 18h6"/>
                            <path d="M10 22h4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-padelegan-900">2. Layanan Usulan &amp; Aspirasi Warga</h2>
                        <p class="text-xs text-padelegan-800/70">Kirim ide pembangunan dan pantau tinjauan aparatur</p>
                    </div>
                </div>

                <ul class="mt-5 space-y-4 text-sm text-padelegan-900">
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('mulai') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>2.1 Pilihan Mulai Pengajuan</span>
                            <span class="text-xs font-normal text-padelegan-700">/mulai &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Halaman awal penentuan jalur antara pengaduan masalah atau saran pembangunan.</p>
                    </li>

                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('usulan.create') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>2.2 Formulir Pengajuan Usulan</span>
                            <span class="text-xs font-normal text-padelegan-700">/usulan &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Form penyampaian saran perbaikan infrastruktur dan program desa.</p>
                    </li>

                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('usulan.lacak') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>2.3 Lacak Status Usulan</span>
                            <span class="text-xs font-normal text-padelegan-700">/cek-usulan &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Cek status verifikasi usulan menggunakan kode pelacakan unik.</p>
                    </li>

                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('public-usulan.index') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>2.4 Daftar Aspirasi Warga Publik ({{ $totalUsulan }} Usulan)</span>
                            <span class="text-xs font-normal text-padelegan-700">/usulan-warga &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Daftar usulan pembangunan yang dipublikasikan setelah ditinjau perangkat desa.</p>

                        {{-- Sub-Jenis Usulan --}}
                        <div class="mt-3 pl-3 border-l-2 border-emerald-600 space-y-2">
                            <div>
                                <p class="text-xs font-semibold text-padelegan-900">2.4.1 Sub-Halaman per Jenis Usulan:</p>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    @foreach ($jenisUsulan as $jenis)
                                        <a href="{{ route('public-usulan.index', ['jenis' => $jenis->value]) }}" class="rounded bg-white px-2 py-0.5 text-xs text-emerald-800 border border-padelegan-800/15 hover:bg-emerald-50">
                                            {{ $jenis->label() }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-padelegan-900 mt-2">2.4.2 Sub-Halaman per Dusun:</p>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    @foreach ($dusuns as $dusun)
                                        <a href="{{ route('public-usulan.index', ['dusun' => $dusun->id]) }}" class="rounded bg-white px-2 py-0.5 text-xs text-emerald-800 border border-padelegan-800/15 hover:bg-emerald-50">
                                            {{ $dusun->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- 3. Halaman Utama & Profil --}}
            <div class="rounded-xl border border-padelegan-800/15 bg-white p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 border-b border-padelegan-800/10 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 text-white">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-padelegan-900">3. Halaman Beranda & Navigasi Utama</h2>
                        <p class="text-xs text-padelegan-800/70">Pintu masuk sistem informasi desa</p>
                    </div>
                </div>

                <ul class="mt-5 space-y-3 text-sm text-padelegan-900">
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('home') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>3.1 Beranda Utama</span>
                            <span class="text-xs font-normal text-padelegan-700">/ &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Tampilan sambutan, panduan alur cepat, dan galeri suasana Desa Padelegan.</p>
                    </li>
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('about') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>3.2 Tentang Desa Padelegan</span>
                            <span class="text-xs font-normal text-padelegan-700">/tentang &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Profil desa pesisir, potensi tambak garam, mangrove, serta kolaborasi bersama mahasiswa KKN.</p>
                    </li>
                </ul>
            </div>

            {{-- 4. Bantuan & Dokumen Teknis --}}
            <div class="rounded-xl border border-padelegan-800/15 bg-white p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-3 border-b border-padelegan-800/10 pb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-700 text-white">
                        <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-padelegan-900">4. Bantuan, Legalitas & Pengembang Sistem</h2>
                        <p class="text-xs text-padelegan-800/70">Panduan penggunaan, dokumen teknis, dan informasi pengembang</p>
                    </div>
                </div>

                <ul class="mt-5 space-y-3 text-sm text-padelegan-900">
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('site-support') }}" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>4.1 Bantuan Penggunaan Situs</span>
                            <span class="text-xs font-normal text-padelegan-700">/bantuan-situs &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Solusi jika mengalami kendala foto gagal terunggah, nomor hilang, atau server offline.</p>
                    </li>
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="{{ route('sitemap.xml') }}" target="_blank" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>4.2 Dokumen Sitemap XML Standar W3C</span>
                            <span class="text-xs font-normal text-padelegan-700">/sitemap.xml &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Indeks seluruh tautan dinamis untuk Google Search Console, Bing Webmaster, dll.</p>
                    </li>
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="/robots.txt" target="_blank" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>4.3 Aturan Perayapan Mesin Pencari</span>
                            <span class="text-xs font-normal text-padelegan-700">/robots.txt &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Petunjuk izin akses perayap publik dan pembatasan direktori privat admin.</p>
                    </li>
                    <li class="rounded-lg bg-padelegan-50/60 p-3.5 border border-padelegan-800/10">
                        <a href="https://github.com/zekkcode" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-800 hover:underline flex items-center justify-between">
                            <span>4.4 Pengembang Sistem: Zekk Code</span>
                            <span class="text-xs font-normal text-padelegan-700">github.com/zekkcode &rarr;</span>
                        </a>
                        <p class="mt-1 text-xs text-padelegan-800/75">Arsitektur dan pengembangan website oleh Zekk Code (zekkcode webdev) untuk kemajuan Desa Padelegan.</p>
                    </li>
                </ul>
            </div>

        </div>
    </div>
@endsection
