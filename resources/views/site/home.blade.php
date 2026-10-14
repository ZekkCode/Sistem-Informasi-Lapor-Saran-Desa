@extends('layouts.site')

@section('title', 'Beranda')

@section('content')
    @php
        $heroSlides = [
            [
                'image' => asset('images/village/foto-bersama-pemdes-dan-kkn.webp'),
                'caption' => 'Pemerintah Desa Padelegan & Tim Mahasiswa KKN',
                'location' => 'Balai Desa Padelegan',
            ],
            [
                'image' => asset('images/village/padelegan-dermaga-senja.webp'),
                'caption' => 'Dermaga Nelayan saat Senja',
                'location' => 'Dusun Pesisir',
            ],
            [
                'image' => asset('images/village/padelegan-tambak-senja.webp'),
                'caption' => 'Kawasan Tambak Garam & Ikan',
                'location' => 'Dusun Tambak',
            ],
            [
                'image' => asset('images/village/padelegan-jalur-mangrove.webp'),
                'caption' => 'Jalur Bambu Vegetasi Pesisir',
                'location' => 'Kawasan Mangrove',
            ],
            [
                'image' => asset('images/village/padelegan-jalan-desa.webp'),
                'caption' => 'Jalan Lingkungan Pemukiman Warga',
                'location' => 'Desa Padelegan',
            ],
        ];
    @endphp

    <div class="home-page">
        <section
            class="home-hero home-hero--slider relative overflow-hidden bg-padelegan-950 text-white min-h-[540px] sm:min-h-[580px] lg:min-h-[620px] flex items-center"
            x-data="heroSlider(@js($heroSlides))"
            @mouseenter="pause()"
            @mouseleave="resume()"
            @focusin="pause()"
            @focusout="resume()"
            aria-label="Tampilan Utama Desa Padelegan"
        >
            {{-- Background Cover Slides --}}
            <div class="absolute inset-0 z-0 overflow-hidden select-none" aria-hidden="true">
                <template x-for="(slide, index) in slides" :key="index">
                    <img
                        :src="slide.image"
                        :alt="slide.caption"
                        class="absolute inset-0 h-full w-full object-cover transition-opacity duration-1000 ease-in-out"
                        :class="current === index ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'"
                        loading="lazy"
                    >
                </template>

                {{-- Sedikit transparan dark gradient overlay agar teks terbaca tajam dan elegan --}}
                <div class="absolute inset-0 bg-gradient-to-r from-padelegan-950/95 via-padelegan-950/80 to-padelegan-950/55 z-20"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-padelegan-950/80 via-transparent to-padelegan-950/40 z-20"></div>
            </div>

            {{-- Navigasi Kiri (Previous) --}}
            <button
                type="button"
                @click="prev()"
                aria-label="Foto desa sebelumnya"
                class="absolute left-2.5 sm:left-6 top-1/2 -translate-y-1/2 z-30 flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-full border border-white/20 bg-black/40 text-white/90 shadow-md backdrop-blur-md transition hover:bg-black/75 hover:border-white/40 hover:text-white hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400"
            >
                <svg class="h-5 w-5 sm:h-6 sm:w-6" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>

            {{-- Navigasi Kanan (Next) --}}
            <button
                type="button"
                @click="next()"
                aria-label="Foto desa berikutnya"
                class="absolute right-2.5 sm:right-6 top-1/2 -translate-y-1/2 z-30 flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-full border border-white/20 bg-black/40 text-white/90 shadow-md backdrop-blur-md transition hover:bg-black/75 hover:border-white/40 hover:text-white hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400"
            >
                <svg class="h-5 w-5 sm:h-6 sm:w-6" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            {{-- Konten Utama Hero --}}
            <div class="site-shell relative z-20 w-full py-12 sm:py-16 lg:py-20">
                <div class="max-w-2xl" data-aos="fade-up">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-950/60 px-3 py-1 text-xs font-medium text-emerald-300 backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Layanan pengaduan Desa Padelegan</span>
                    </div>

                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.08]">
                        Laporkan masalah desa ke petugas.
                    </h1>

                    <p class="mt-4 text-sm sm:text-base leading-relaxed text-white/85 max-w-xl">
                        Tulis lokasi dan lampirkan foto. Simpan nomornya untuk cek pembaruan.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="{{ route('mulai') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-semibold text-sm px-6 py-2.5 transition shadow-sm">
                            Sampaikan
                        </a>
                        <a href="{{ route('about') }}" class="inline-flex items-center justify-center rounded-lg border border-white/30 bg-white/10 hover:bg-white/20 text-white font-semibold text-sm px-5 py-2.5 transition backdrop-blur-sm">
                            Tentang
                        </a>
                    </div>

                    <dl class="mt-8 grid grid-cols-2 gap-4 border-t border-white/15 pt-5 max-w-lg text-xs sm:text-sm">
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-white/50 font-medium">Akses</dt>
                            <dd class="mt-1 font-medium text-white/95">Tanpa akun</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-white/50 font-medium">Privasi</dt>
                            <dd class="mt-1 font-medium text-white/95">Anonim hanya Admin yang Lihat</dd>
                        </div>
                    </dl>
                </div>

                {{-- Status Foto & Indikator Titik di Bawah --}}
                <div class="mt-8 sm:mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4 text-xs text-white/75">
                    <div class="inline-flex items-center gap-2 rounded-lg border border-white/10 bg-black/35 px-3 py-1.5 backdrop-blur-sm">
                        <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span class="font-medium text-white" x-text="slides[current].caption"></span>
                        <span class="text-white/40">&bull;</span>
                        <span class="text-white/60" x-text="slides[current].location"></span>
                    </div>

                    {{-- Titik Pagination --}}
                    <div class="flex items-center gap-1.5" role="tablist" aria-label="Pilih foto latar">
                        <template x-for="(slide, idx) in slides" :key="idx">
                            <button
                                type="button"
                                @click="goTo(idx)"
                                :aria-label="'Buka foto ' + (idx + 1) + ': ' + slide.caption"
                                class="h-2 rounded-full transition-all focus:outline-none focus:ring-1 focus:ring-emerald-400"
                                :class="current === idx ? 'w-6 bg-emerald-400' : 'w-2 bg-white/35 hover:bg-white/60'"
                            ></button>
                        </template>
                    </div>
                </div>
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


    </div>
@endsection
