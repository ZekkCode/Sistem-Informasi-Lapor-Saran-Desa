@extends('layouts.site')

@section('title', 'Tentang')

@section('content')
    <section class="site-shell about-page">
        <div class="about-intro">
            <header>
                <p class="public-kicker">Tentang layanan</p>
                <h1 class="public-page-title mt-3 text-padelegan-900">Warga mengirim laporan, lalu petugas memperbarui statusnya.</h1>
            </header>

            <div class="about-copy">
                <p>Warga dapat melaporkan masalah fasilitas, lingkungan, infrastruktur, pelayanan desa, dan aspirasi tanpa membuat akun. Petugas memakai bukti serta patokan lokasi untuk memeriksa laporan.</p>

                <dl class="about-details">
                    <div>
                        <dt>Privasi pelapor</dt>
                        <dd>Halaman publik tidak memuat nama atau nomor WhatsApp. Petugas memakai kontak tersebut untuk verifikasi.</dd>
                    </div>
                    <div>
                        <dt>Wilayah layanan</dt>
                        <dd>Bangkal, Asam Batur, Laok Tambak, Modung, Dajah Tambak, dan Muarah.</dd>
                    </div>
                </dl>

                <x-ui.action :href="route('reports.create')">Buat laporan</x-ui.action>
            </div>
        </div>

        <section class="mt-16 pt-12 border-t border-padelegan-100" aria-labelledby="tim-kkn-title">
            <div class="max-w-3xl">
                <p class="public-kicker">Tim pelaksana</p>
                <h2 id="tim-kkn-title" class="text-2xl font-bold text-padelegan-900 mt-2">Tim KKN & Pengembang Sistem</h2>
                <p class="mt-3 text-sm text-padelegan-800/80 leading-relaxed">
                    Sistem Lapor Padelegan disusun bersama mahasiswa Kuliah Kerja Nyata (KKN) untuk mempermudah komunikasi warga dengan Pemerintah Desa Padelegan, didukung pengembangan website oleh ZekkCode.
                </p>

                <div class="mt-8 overflow-hidden rounded-xl border border-padelegan-100 bg-white">
                    <div class="px-5 py-3.5 bg-padelegan-50/70 border-b border-padelegan-100 font-semibold text-xs tracking-wider uppercase text-padelegan-900">
                        Susunan Tim KKN Desa Padelegan
                    </div>
                    <dl class="divide-y divide-padelegan-100/70 text-sm">
                        <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <dt class="font-medium text-padelegan-900">MOH ALFAN BAETONI</dt>
                            <dd class="text-padelegan-700 sm:text-right">Koordinator Desa (Kordes)</dd>
                        </div>
                        <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <dt class="font-medium text-padelegan-900">ABDUR RAHMAN JAINUR DANI</dt>
                            <dd class="text-padelegan-700 sm:text-right">Wakil Koordinator Desa (Wakordes)</dd>
                        </div>
                        <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <dt class="font-medium text-padelegan-900">SUAIDA TRI RAHAYU</dt>
                            <dd class="text-padelegan-700 sm:text-right">Bendahara</dd>
                        </div>
                        <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <dt class="font-medium text-padelegan-900">ENI WILDATUL UMMAH</dt>
                            <dd class="text-padelegan-700 sm:text-right">Sekretaris</dd>
                        </div>
                        <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                            <dt class="font-medium text-padelegan-900">FASYAROTUL JANNAH</dt>
                            <dd class="text-padelegan-700 sm:text-right">Kominfo</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl border border-padelegan-100 bg-padelegan-50/40 text-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-padelegan-900 text-white shadow-sm">
                            <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-padelegan-900">Pengembangan Website</p>
                            <p class="text-xs text-padelegan-700 mt-0.5">Dirancang dan dibangun oleh <strong>ZekkCode</strong> untuk Desa Padelegan.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 self-start sm:self-auto px-3 py-1.5 rounded-lg text-xs font-semibold bg-padelegan-900 text-white tracking-wide shadow-sm">
                        <svg class="h-3.5 w-3.5 text-padelegan-300" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>zekkcode by web</span>
                    </span>
                </div>
            </div>
        </section>

        <div class="about-gallery" aria-label="Lanskap Desa Padelegan">
            <figure class="about-gallery__wide">
                <img src="{{ asset('images/village/padelegan-tambak-senja.webp') }}" alt="Tambak Desa Padelegan memantulkan cahaya matahari sore" width="1600" height="1200" loading="lazy">
                <figcaption>Tambak pada sore hari</figcaption>
            </figure>
            <figure class="about-gallery__tall">
                <img src="{{ asset('images/village/padelegan-jalur-mangrove.webp') }}" alt="Jalur bambu di antara vegetasi pesisir Desa Padelegan" width="1600" height="1200" loading="lazy">
                <figcaption>Jalur pesisir warga</figcaption>
            </figure>
        </div>
    </section>
@endsection
