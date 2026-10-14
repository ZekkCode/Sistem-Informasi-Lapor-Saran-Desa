@extends('layouts.site')

@section('title', 'Beranda')

@section('content')
    @php
        $heroSlides = [
            [
                'image' => asset('images/village/foto-bersama-pemdes-dan-kkn.webp'),
                'caption' => 'Pemerintah Desa Padelegan & Tim Mahasiswa KKN',
                'location' => 'Balai Desa Padelegan',
                'description' => 'Sinergi perangkat desa bersama mahasiswa KKN dalam pengembangan layanan digital desa.',
            ],
            [
                'image' => asset('images/village/padelegan-dermaga-senja.webp'),
                'caption' => 'Dermaga Nelayan saat Senja',
                'location' => 'Dusun Pesisir',
                'description' => 'Pusat sandar kapal dan kegiatan ekonomi nelayan pesisir Padelegan di kala senja.',
            ],
            [
                'image' => asset('images/village/padelegan-tambak-senja.webp'),
                'caption' => 'Kawasan Tambak Garam & Ikan',
                'location' => 'Dusun Tambak',
                'description' => 'Potensi komoditas garam dan perikanan tambak yang menjadi tumpuan warga pesisir.',
            ],
            [
                'image' => asset('images/village/padelegan-jalur-mangrove.webp'),
                'caption' => 'Jalur Bambu Vegetasi Pesisir',
                'location' => 'Kawasan Mangrove',
                'description' => 'Zona konservasi mangrove yang menjaga ketahanan ekosistem pesisir dari abrasi laut.',
            ],
            [
                'image' => asset('images/village/padelegan-jalan-desa.webp'),
                'caption' => 'Jalan Lingkungan Pemukiman Warga',
                'location' => 'Desa Padelegan',
                'description' => 'Infrastruktur jalan lingkungan yang menghubungkan pemukiman antar dusun.',
            ],
        ];
    @endphp

    <div class="home-page">
        <section
            class="home-hero home-hero--slider relative overflow-hidden bg-padelegan-950 text-white min-h-[500px] sm:min-h-[540px] lg:min-h-[580px] flex items-center"
            aria-label="Tampilan Utama Desa Padelegan"
        >
            {{-- Background Cover Slides (Atmosferik dengan ijo transparasi halus) --}}
            <div class="home-hero__bg-slider absolute inset-0 z-0 overflow-hidden select-none pointer-events-none" aria-hidden="true" data-hero-bg-slider>
                @foreach ($heroSlides as $idx => $slide)
                    <img
                        src="{{ $slide['image'] }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover transition-opacity duration-1000 ease-in-out opacity-25 mix-blend-luminosity {{ $idx === 0 ? 'opacity-25 z-10' : 'opacity-0 z-0' }}"
                        data-hero-bg-slide="{{ $idx }}"
                        {{ $idx === 0 ? 'fetchpriority=high' : 'loading=lazy' }}
                    >
                @endforeach

                {{-- Lapisan hijau transparasi agar foto tidak terlalu tajam/mencolok dan menyatu dengan palet Padelegan --}}
                <div class="absolute inset-0 bg-gradient-to-r from-padelegan-950/95 via-padelegan-950/90 to-emerald-950/80 z-20"></div>
                <div class="absolute inset-0 bg-emerald-950/40 mix-blend-multiply z-20"></div>
            </div>

            {{-- Konten Utama Hero (Hanya hero utama tanpa navigasi sesuai instruksi) --}}
            <div class="site-shell relative z-30 w-full py-12 sm:py-16 lg:py-20">
                <div class="max-w-2xl" data-aos="fade-up">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-950/60 px-3 py-1 text-xs font-medium text-emerald-300 backdrop-blur-sm">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Layanan pengaduan Desa Padelegan</span>
                    </div>

                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.08]">
                        Laporkan masalah desa ke petugas.
                    </h1>

                    <p class="mt-4 text-sm sm:text-base leading-relaxed text-white/90 max-w-xl">
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
                            <dt class="text-[11px] uppercase tracking-wider text-white/60 font-medium">Akses</dt>
                            <dd class="mt-1 font-medium text-white/95">Tanpa akun</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-white/60 font-medium">Privasi</dt>
                            <dd class="mt-1 font-medium text-white/95">Anonim hanya Admin yang Lihat</dd>
                        </div>
                    </dl>
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

        {{-- Slider Foto Desa Sebelum Footer Lengkap dengan Navigasi Kanan & Kiri --}}
        <section class="border-t border-padelegan-800/10 bg-padelegan-950 py-12 text-white sm:py-16" aria-labelledby="showcase-title">
            <div class="site-shell">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-6 sm:mb-8" data-aos="fade-up">
                    <div>
                        <p class="home-kicker text-emerald-400">
                            <span class="bg-emerald-400"></span>
                            <span>Dokumentasi desa</span>
                        </p>
                        <h2 id="showcase-title" class="home-section__title text-white">Lingkungan dan suasana Desa Padelegan.</h2>
                        <p class="mt-2 text-sm leading-6 text-white/70 sm:text-base max-w-2xl">
                            Dokumentasi balai desa bersama perangkat dan mahasiswa KKN, pesisir dermaga, tambak garam, hingga kawasan konservasi mangrove.
                        </p>
                    </div>

                    {{-- Counter info --}}
                    <div class="hidden sm:inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium text-white/80">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <span data-showcase-counter>1 / {{ count($heroSlides) }}</span>
                    </div>
                </div>

                {{-- Showcase Box dengan Navigasi Kiri & Kanan --}}
                <div
                    class="relative overflow-hidden rounded-2xl border border-white/15 bg-padelegan-900 shadow-2xl focus:outline-none focus:ring-2 focus:ring-emerald-400"
                    tabindex="0"
                    data-village-showcase
                    data-aos="fade-up"
                    aria-label="Galeri foto interaktif Desa Padelegan. Gunakan panah kiri dan kanan untuk berpindah foto."
                >
                    {{-- Container Slides Gambar --}}
                    <div class="relative h-[320px] sm:h-[420px] md:h-[480px] lg:h-[520px] w-full overflow-hidden select-none bg-black">
                        @foreach ($heroSlides as $idx => $photo)
                            <div
                                class="absolute inset-0 h-full w-full transition-opacity duration-700 ease-in-out {{ $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none' }}"
                                data-showcase-slide
                                data-caption="{{ $photo['caption'] }}"
                                data-location="{{ $photo['location'] }}"
                                data-description="{{ $photo['description'] }}"
                            >
                                <img
                                    src="{{ $photo['image'] }}"
                                    alt="{{ $photo['caption'] }}"
                                    class="h-full w-full object-cover"
                                    {{ $idx === 0 ? 'fetchpriority=high' : 'loading=lazy' }}
                                >
                                {{-- Dark gradient di bagian bawah agar teks caption terbaca jelas --}}
                                <div class="absolute inset-x-0 bottom-0 h-48 bg-gradient-to-t from-black/90 via-black/55 to-transparent"></div>
                            </div>
                        @endforeach

                        {{-- Navigasi Tombol Kiri (Prev) --}}
                        <button
                            type="button"
                            data-showcase-prev
                            aria-label="Foto sebelumnya"
                            class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 z-20 flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full border border-white/25 bg-black/60 text-white/90 shadow-lg backdrop-blur-md transition hover:bg-black/90 hover:border-emerald-400 hover:text-white hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                        >
                            <svg class="h-6 w-6" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>

                        {{-- Navigasi Tombol Kanan (Next) --}}
                        <button
                            type="button"
                            data-showcase-next
                            aria-label="Foto berikutnya"
                            class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 z-20 flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-full border border-white/25 bg-black/60 text-white/90 shadow-lg backdrop-blur-md transition hover:bg-black/90 hover:border-emerald-400 hover:text-white hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                        >
                            <svg class="h-6 w-6" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>

                        {{-- Floating Caption & Deskripsi di Bawah Gambar --}}
                        <div class="absolute inset-x-0 bottom-0 z-20 p-5 sm:p-7 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                            <div class="max-w-xl">
                                <div class="inline-flex items-center gap-2 rounded-md border border-white/20 bg-emerald-950/80 px-2.5 py-1 text-xs font-medium text-emerald-300 backdrop-blur-md mb-2">
                                    <svg class="h-3 w-3 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span data-showcase-location>{{ $heroSlides[0]['location'] }}</span>
                                </div>
                                <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-white tracking-tight" data-showcase-caption>
                                    {{ $heroSlides[0]['caption'] }}
                                </h3>
                                <p class="mt-1 text-xs sm:text-sm text-white/75 line-clamp-2" data-showcase-description>
                                    {{ $heroSlides[0]['description'] }}
                                </p>
                            </div>

                            {{-- Titik Indikator Dots --}}
                            <div class="flex items-center gap-2 shrink-0" role="tablist" aria-label="Pilih foto dokumentasi">
                                @foreach ($heroSlides as $idx => $photo)
                                    <button
                                        type="button"
                                        data-showcase-dot="{{ $idx }}"
                                        aria-label="Pilih foto {{ $idx + 1 }}: {{ $photo['caption'] }}"
                                        class="h-2.5 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-emerald-400 {{ $idx === 0 ? 'w-7 bg-emerald-400' : 'w-2 bg-white/40 hover:bg-white/70' }}"
                                    ></button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
@endsection
