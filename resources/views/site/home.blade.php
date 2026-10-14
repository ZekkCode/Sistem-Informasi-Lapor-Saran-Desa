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
                'description' => 'Pusat sandar perahu dan aktivitas ekonomi nelayan pesisir Padelegan di kala senja.',
            ],
            [
                'image' => asset('images/village/padelegan-tambak-senja.webp'),
                'caption' => 'Kawasan Tambak Garam & Ikan',
                'location' => 'Dusun Tambak',
                'description' => 'Potensi tambak produktif penghasil garam dan perikanan air payau.',
            ],
            [
                'image' => asset('images/village/padelegan-jalur-mangrove.webp'),
                'caption' => 'Jalur Bambu Vegetasi Pesisir',
                'location' => 'Kawasan Mangrove',
                'description' => 'Zona konservasi mangrove yang melindungi garis pantai dari abrasi air laut.',
            ],
            [
                'image' => asset('images/village/padelegan-jalan-desa.webp'),
                'caption' => 'Jalan Lingkungan Pemukiman Warga',
                'location' => 'Desa Padelegan',
                'description' => 'Akses jalan lingkungan yang menghubungkan permukiman warga antar dusun.',
            ],
        ];
    @endphp

    <div class="home-page">
        <section
            class="home-hero home-hero--slider relative overflow-hidden bg-[#f4f7f5] text-padelegan-900 min-h-[480px] sm:min-h-[520px] lg:min-h-[560px] flex items-center border-b border-padelegan-800/10"
            aria-label="Tampilan Utama Desa Padelegan"
        >
            {{-- Background Cover: Satu foto pemandangan damai Padelegan dengan nuansa hijau lembut transparan (terang & bersih) --}}
            <div class="absolute inset-0 z-0 overflow-hidden select-none pointer-events-none" aria-hidden="true" data-hero-bg-slider>
                <img
                    src="{{ asset('images/village/hero-padelegan.webp') }}"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover opacity-35 object-center"
                    fetchpriority="high"
                >

                {{-- Lapisan hijau transparasi lembut agar foto berpadu dengan tema hijau Padelegan secara terang --}}
                <div class="absolute inset-0 bg-gradient-to-r from-[#f4f7f5] via-[#f4f7f5]/85 to-emerald-100/40 z-10"></div>
                <div class="absolute inset-0 bg-emerald-700/10 mix-blend-multiply z-10"></div>
            </div>

            {{-- Konten Utama Hero --}}
            <div class="site-shell relative z-20 w-full py-12 sm:py-16 lg:py-20">
                <div class="max-w-2xl" data-aos="fade-up">
                    <p class="home-kicker text-padelegan-600">
                        <span class="home-kicker__line bg-emerald-600"></span>
                        <span class="text-xs uppercase tracking-wider font-semibold text-emerald-800">Layanan pengaduan</span>
                    </p>

                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-padelegan-900 sm:text-5xl lg:text-6xl leading-[1.08]">
                        Laporkan masalah desa ke petugas.
                    </h1>

                    <p class="mt-4 text-sm sm:text-base leading-relaxed text-padelegan-800/80 max-w-xl">
                        Tulis lokasi dan lampirkan foto bukti. Simpan nomor laporan untuk mengecek status penanganan.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <a href="{{ route('reports.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 hover:bg-emerald-800 active:bg-emerald-900 text-white font-semibold text-sm px-6 py-2.5 transition shadow-sm">
                            Buat laporan
                        </a>
                        <a href="{{ route('reports.track') }}" class="inline-flex items-center justify-center rounded-lg border border-padelegan-800/20 bg-white/90 hover:bg-white text-padelegan-900 font-semibold text-sm px-5 py-2.5 transition backdrop-blur-sm shadow-xs">
                            Cek status
                        </a>
                    </div>

                    <dl class="mt-8 grid grid-cols-2 gap-4 border-t border-padelegan-800/15 pt-5 max-w-lg text-xs sm:text-sm">
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-padelegan-700/70 font-medium">Akses</dt>
                            <dd class="mt-1 font-semibold text-padelegan-900">Tanpa login</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] uppercase tracking-wider text-padelegan-700/70 font-medium">Identitas</dt>
                            <dd class="mt-1 font-semibold text-padelegan-900">Privat untuk petugas</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <nav class="home-service" aria-label="Layanan utama Desa Padelegan">
            <div class="site-shell home-service__grid">
                @foreach ([
                    ['Buat laporan', 'Kirim laporan kondisi fasilitas atau lingkungan desa.', route('reports.create')],
                    ['Kirim usulan', 'Ajukan saran pembangunan atau fasilitas desa.', route('usulan.create')],
                    ['Cek status', 'Lacak penanganan dengan nomor laporan Anda.', route('reports.track')],
                    ['Laporan desa', 'Daftar laporan warga yang sudah diverifikasi.', route('public-reports.index')],
                    ['Usulan warga', 'Daftar usulan yang sudah ditinjau perangkat desa.', route('public-usulan.index')],
                    ['Bantuan situs', 'Panduan dan kontak bantuan teknis sistem.', route('site-support')],
                ] as [$title, $description, $href])
                    <a href="{{ $href }}" class="home-service__link">
                        <h3>{{ $title }}</h3>
                        <p>{{ $description }}</p>
                    </a>
                @endforeach
            </div>
        </nav>

        <section id="alur" class="home-process">
            <div class="site-shell home-process__grid">
                <div class="home-process__copy" data-aos="fade-up">
                    <p class="home-kicker">Alur laporan</p>
                    <h2 class="home-section__title">Petugas periksa dan tanggapi.</h2>
                    <p class="home-process__lead">Kirim satu masalah per laporan. Petugas periksa lokasi dan bukti yang Anda kirim.</p>

                    <ol class="home-steps">
                        @foreach ([
                            ['Kirim laporan', 'Kategori, patokan lokasi, dan foto kondisi.'],
                            ['Simpan nomor', 'Gunakan nomor laporan untuk pelacakan.'],
                            ['Pantau status', 'Cek tanggapan dan perkembangan dari petugas.'],
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
        <section class="home-showcase border-t border-padelegan-800/10 bg-white py-12 text-padelegan-900 sm:py-16" aria-labelledby="showcase-title">
            <div class="site-shell">
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between mb-6 sm:mb-8" data-aos="fade-up">
                    <div>
                        <p class="home-kicker text-padelegan-600">
                            <span class="home-kicker__line bg-emerald-600"></span>
                            <span class="text-xs uppercase tracking-wider font-semibold text-emerald-800">Dokumentasi desa</span>
                        </p>
                        <h2 id="showcase-title" class="home-section__title text-padelegan-900">Suasana Desa Padelegan.</h2>
                        <p class="mt-2 text-sm leading-6 text-padelegan-800/70 sm:text-base max-w-2xl">
                            Dokumentasi balai desa bersama perangkat dan mahasiswa KKN, pesisir dermaga, tambak garam, hingga kawasan konservasi mangrove.
                        </p>
                    </div>

                    {{-- Counter info --}}
                    <div class="hidden sm:inline-flex items-center gap-2 rounded-full border border-padelegan-800/15 bg-padelegan-50 px-3 py-1 text-xs font-medium text-padelegan-800">
                        <svg class="h-3.5 w-3.5 text-emerald-700" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                        <span data-showcase-counter>1 / {{ count($heroSlides) }}</span>
                    </div>
                </div>

                {{-- Showcase Box dengan Navigasi Kiri & Kanan --}}
                <div
                    class="relative overflow-hidden rounded-2xl border border-padelegan-800/15 bg-black shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500"
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
