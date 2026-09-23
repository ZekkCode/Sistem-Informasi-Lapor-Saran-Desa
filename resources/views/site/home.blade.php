@extends('layouts.site')

@section('title', 'Beranda')

@section('content')
    <div class="home-page">
        <section class="home-hero">
            <div class="site-shell home-hero__grid">
                <div class="home-hero__copy" data-aos="fade-up">
                    <p class="home-kicker"><span aria-hidden="true"></span>Layanan pengaduan Desa Padelegan</p>
                    <h1 class="home-hero__title">Laporkan masalah desa ke petugas.</h1>
                    <p class="home-hero__lead">Tulis lokasi, lampirkan foto, simpan nomor untuk cek pembaruan.</p>
                    <div class="home-hero__actions">
                        <x-ui.action :href="route('reports.create')" size="lg">Buat laporan</x-ui.action>
                        <x-ui.action :href="route('reports.track')" variant="secondary" size="lg">Cek status</x-ui.action>
                    </div>
                    <dl class="home-hero__facts">
                        <div><dt>Akses</dt><dd>Tanpa akun</dd></div>
                        <div><dt>Privasi</dt><dd>Petugas saja yang lihat identitas</dd></div>
                    </dl>
                </div>

                <figure class="home-hero__media hero-depth" data-depth-scene data-aos="fade" data-aos-delay="80">
                    <div class="hero-depth__plane" data-depth-plane>
                        <img src="{{ asset('images/village/padelegan-dermaga-senja.webp') }}" alt="Perahu nelayan bersandar di dermaga Desa Padelegan saat senja" width="1600" height="1200" class="hero-depth__image" fetchpriority="high">
                        <span class="hero-depth__frame" aria-hidden="true"></span>
                        <figcaption class="home-photo-note hero-depth__caption">Dermaga nelayan Padelegan</figcaption>
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
                ] as [$title, $description, $href])
                    <a href="{{ $href }}" class="home-service__link">
                        <div>
                            <h3>{{ $title }}</h3>
                            <p>{{ $description }}</p>
                        </div>
                        <span aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </nav>

        <section id="alur" class="home-process">
            <div class="site-shell home-process__grid">
                <div class="home-process__copy" data-aos="fade-up">
                    <p class="home-kicker"><span aria-hidden="true"></span>Alur laporan</p>
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

                <figure class="home-process__media" data-aos="fade" data-aos-delay="80">
                    <img src="{{ asset('images/village/padelegan-jalan-desa.webp') }}" alt="Jalan desa di antara lahan warga Padelegan" width="1600" height="1200" loading="lazy">
                    <figcaption>Jalan lingkungan Desa Padelegan</figcaption>
                </figure>
            </div>
        </section>
    </div>
@endsection
