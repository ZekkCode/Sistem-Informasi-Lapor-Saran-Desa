@extends('layouts.site')

@section('title', 'Beranda')

@section('content')
    <div class="home-page">
        <section class="home-hero">
            <div class="site-shell home-hero__grid">
                <div class="home-hero__copy" data-aos="fade-up">
                    <p class="home-kicker">Layanan pengaduan Desa Padelegan</p>
                    <h1 class="home-hero__title">Laporkan masalah desa ke petugas.</h1>
                    <p class="home-hero__lead">Tulis lokasi dan lampirkan foto. Simpan nomornya untuk cek pembaruan.</p>
                    <div class="home-hero__actions">
                        <x-ui.action :href="route('mulai')">Sampaikan</x-ui.action>
                        <x-ui.action :href="route('about')" variant="secondary">Tentang</x-ui.action>
                    </div>
                    <dl class="home-hero__facts">
                        <div><dt>Akses</dt><dd>Tanpa akun</dd></div>
                        <div><dt>Privasi</dt><dd>Anonim hanya Admin yang Lihat</dd></div>
                    </dl>
                </div>

                <figure class="home-hero__media hero-depth hero-art-scene" data-depth-scene data-aos="fade" data-aos-delay="80" aria-hidden="true">
                    <div class="hero-depth__plane" data-depth-plane>
                        <img src="{{ asset('images/illustrations/ilustrasi-laporan.webp') }}" alt="" width="820" height="1025" class="hero-art" fetchpriority="high" onerror="this.closest('.home-hero__media').classList.add('is-empty')">
                    </div>
                </figure>
            </div>
        </section>

        <nav class="home-service" aria-label="Layanan utama Desa Padelegan">
            <div class="site-shell home-service__grid">
                @foreach ([
                    ['Buat laporan', 'Jelaskan kondisi dan kirim foto.', route('reports.create')],
                    ['Kirim usulan', 'Sampaikan usulan atau saran untuk desa.', route('usulan.create')],
                    ['Cek status', 'Gunakan nomor laporan yang Anda simpan.', route('reports.track')],
                    ['Laporan publik', 'Baca laporan yang sudah diverifikasi.', route('public-reports.index')],
                    ['Usulan warga', 'Baca usulan yang sudah ditinjau petugas.', route('public-usulan.index')],
                    ['Bantuan situs', 'Laporkan kendala teknis pada situs.', route('site-support')],
                ] as [$title, $description, $href])
                    <a href="{{ $href }}" class="home-service__link">
                        <h3>{{ $title }}</h3>
                        <p>{{ $description }}</p>
                    </a>
                @endforeach
            </div>
        </nav>

        <section class="border-b border-padelegan-800/10 bg-white py-10 sm:py-14" aria-labelledby="aktivitas-title">
            <div class="site-shell">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between" data-aos="fade-up">
                    <div>
                        <p class="home-kicker">Aktivitas desa</p>
                        <h2 id="aktivitas-title" class="home-section__title">Laporan dan usulan terkini.</h2>
                        <p class="mt-2 text-sm leading-6 text-padelegan-900/65 sm:text-base">
                            Pantau penanganan masalah dan usulan warga di tiap dusun Desa Padelegan.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.action :href="route('public-reports.index')" variant="secondary">Semua laporan</x-ui.action>
                        <x-ui.action :href="route('public-usulan.index')" variant="secondary">Semua usulan</x-ui.action>
                    </div>
                </div>

                <div class="mt-8 grid gap-8 lg:grid-cols-2" data-aos="fade-up" data-aos-delay="80">
                    {{-- Kolom Laporan --}}
                    <div class="flex flex-col">
                        <div class="mb-4 flex items-center justify-between border-b border-padelegan-800/10 pb-3">
                            <h3 class="text-base font-semibold text-padelegan-900">Laporan warga terbaru</h3>
                            <a href="{{ route('public-reports.index') }}" class="text-xs font-semibold text-padelegan-700 hover:underline">Lihat semua &rarr;</a>
                        </div>
                        @if ($recentReports->isNotEmpty())
                            <div class="grid gap-3.5">
                                @foreach ($recentReports as $report)
                                    <x-reports.card :report="$report" />
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-padelegan-800/20 p-6 text-center text-xs text-padelegan-900/60">
                                Belum ada laporan terverifikasi untuk ditampilkan.
                            </div>
                        @endif
                    </div>

                    {{-- Kolom Usulan --}}
                    <div class="flex flex-col">
                        <div class="mb-4 flex items-center justify-between border-b border-padelegan-800/10 pb-3">
                            <h3 class="text-base font-semibold text-padelegan-900">Usulan pembangunan terbaru</h3>
                            <a href="{{ route('public-usulan.index') }}" class="text-xs font-semibold text-padelegan-700 hover:underline">Lihat semua &rarr;</a>
                        </div>
                        @if ($recentUsulan->isNotEmpty())
                            <div class="grid gap-3.5">
                                @foreach ($recentUsulan as $usulan)
                                    <x-usulan.card :usulan="$usulan" />
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-padelegan-800/20 p-6 text-center text-xs text-padelegan-900/60">
                                Belum ada usulan warga untuk ditampilkan.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section id="alur" class="home-process">
            <div class="site-shell home-process__grid">
                <div class="home-process__copy" data-aos="fade-up">
                    <p class="home-kicker">Alur laporan</p>
                    <h2 class="home-section__title">Petugas periksa dan tanggapi.</h2>
                    <p class="home-process__lead">Kirim satu masalah per laporan. Petugas periksa lokasi dan bukti yang Anda kirim.</p>

                    <ol class="home-steps">
                        @foreach ([
                            ['Kirim bukti', 'Kategori, patokan lokasi, foto kondisi.'],
                            ['Simpan nomor', 'Muncul setelah pengiriman selesai.'],
                            ['Baca pembaruan', 'Status dan catatan dari petugas.'],
                        ] as [$title, $description])
                            <li>
                                <span class="home-steps__mark" aria-hidden="true"></span>
                                <div><h3>{{ $title }}</h3><p>{{ $description }}</p></div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <figure class="home-process__media home-process__media--art" data-aos="fade" data-aos-delay="80" aria-hidden="true">
                    <img src="{{ asset('images/illustrations/ilustrasi-proses.webp') }}" alt="" width="820" height="1025" loading="lazy">
                </figure>
            </div>
        </section>

        <section class="home-gallery" aria-labelledby="galeri-title">
            <div class="site-shell">
                <p class="home-kicker">Galeri desa</p>
                <h2 id="galeri-title" class="home-section__title">Desa Padelegan.</h2>
                <div class="home-gallery__grid" data-aos="fade-up">
                    <figure class="home-gallery__item home-gallery__item--wide">
                        <img src="{{ asset('images/village/padelegan-dermaga-senja.webp') }}" alt="Dermaga nelayan Desa Padelegan saat senja" width="1600" height="1200" loading="lazy">
                        <figcaption>Dermaga nelayan</figcaption>
                    </figure>
                    <figure class="home-gallery__item">
                        <img src="{{ asset('images/village/padelegan-tambak-senja.webp') }}" alt="Tambak Desa Padelegan memantulkan cahaya sore" width="1600" height="1200" loading="lazy">
                        <figcaption>Tambak warga</figcaption>
                    </figure>
                    <figure class="home-gallery__item">
                        <img src="{{ asset('images/village/padelegan-jalan-desa.webp') }}" alt="Jalan lingkungan di antara lahan warga Padelegan" width="1600" height="1200" loading="lazy">
                        <figcaption>Jalan lingkungan</figcaption>
                    </figure>
                    <figure class="home-gallery__item home-gallery__item--wide">
                        <img src="{{ asset('images/village/padelegan-jalur-mangrove.webp') }}" alt="Jalur bambu di antara vegetasi pesisir Desa Padelegan" width="1600" height="1200" loading="lazy">
                        <figcaption>Jalur pesisir</figcaption>
                    </figure>
                </div>
            </div>
        </section>
    </div>
@endsection
