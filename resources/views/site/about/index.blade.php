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
