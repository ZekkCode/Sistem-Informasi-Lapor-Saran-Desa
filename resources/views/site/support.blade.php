@extends('layouts.site')

@section('title', 'Bantuan Situs')

@section('content')
    @php
        $supportEmail = config('padelegan.support_email');
        $supportSubject = rawurlencode('Kendala pada Sistem Lapor Padelegan');
        $supportBody = rawurlencode("Halaman yang bermasalah:\nWaktu kejadian:\nPerangkat/browser:\nKendala yang terjadi:\n");
    @endphp

    <section class="site-shell py-10 sm:py-14">
        <div class="grid gap-9 lg:grid-cols-[0.82fr_1.18fr] lg:gap-16">
            <header>
                <p class="public-kicker">Bantuan teknis</p>
                <h1 class="public-page-title mt-3 text-padelegan-900">Ada kendala memakai Sistem Lapor Padelegan?</h1>
                <p class="reading-measure mt-4 text-sm leading-7 text-padelegan-900/65">Gunakan bantuan ini untuk masalah pada situs. Laporan jalan, lingkungan, fasilitas, atau pelayanan desa tetap dikirim melalui halaman Buat Laporan.</p>
            </header>

            <div class="border-t border-padelegan-800/20 pt-6 lg:border-l lg:border-t-0 lg:pl-10 lg:pt-0">
                <h2 class="text-xl font-semibold tracking-[-0.02em] text-padelegan-900">Sebelum melapor</h2>
                <ol class="mt-5 grid gap-4 text-sm leading-6 text-padelegan-900/68">
                    <li><strong class="font-semibold text-padelegan-900">1. Coba sekali lagi.</strong> Muat ulang halaman dan pastikan jaringan tetap tersambung.</li>
                    <li><strong class="font-semibold text-padelegan-900">2. Catat kejadiannya.</strong> Simpan alamat halaman, waktu, dan pesan error yang terlihat.</li>
                    <li><strong class="font-semibold text-padelegan-900">3. Jangan sertakan data rahasia.</strong> Hapus nomor WhatsApp, kata sandi, atau identitas warga dari tangkapan layar.</li>
                </ol>

                <div class="mt-7 flex flex-wrap gap-3">
                    @if ($supportEmail)
                        <a class="action action--primary" href="mailto:{{ $supportEmail }}?subject={{ $supportSubject }}&body={{ $supportBody }}">Laporkan kendala situs</a>
                    @else
                        <p class="border-l-2 border-warning bg-warning-soft px-4 py-3 text-sm leading-6 text-padelegan-900/70">Kontak bantuan belum dikonfigurasi. Sampaikan alamat halaman dan waktu kejadian kepada petugas desa.</p>
                    @endif
                    <x-ui.action :href="route('home')" variant="secondary">Kembali ke beranda</x-ui.action>
                </div>
            </div>
        </div>
    </section>
@endsection
