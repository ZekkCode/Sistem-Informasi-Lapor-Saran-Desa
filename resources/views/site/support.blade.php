@extends('layouts.site')

@section('title', 'Bantuan Situs')

@section('content')
    @php
        $supportEmail = config('padelegan.support_email');
        $supportSubject = rawurlencode('Kendala pada Sistem Lapor Padelegan');
        $supportBody = rawurlencode("Halaman yang bermasalah:\nWaktu kejadian:\nPerangkat/browser:\nKendala yang terjadi:\n");
        $contactName = config('padelegan.support_contact_name');
        $contactWa = config('padelegan.support_whatsapp');
        $contactWaLabel = config('padelegan.support_whatsapp_label');
        $waText = rawurlencode('Halo, saya warga Desa Padelegan. Saya mengalami kendala pada situs Sistem Lapor Padelegan: ');
    @endphp

    <section class="site-shell py-10 sm:py-14">
        <div class="grid gap-9 lg:grid-cols-[0.82fr_1.18fr] lg:gap-16">
            <header>
                <p class="public-kicker">Bantuan teknis</p>
                <h1 class="public-page-title mt-3 text-padelegan-900">Ada kendala memakai Sistem Lapor Padelegan?</h1>
                <p class="reading-measure mt-4 text-sm leading-7 text-padelegan-900/65">Gunakan bantuan ini untuk masalah pada situs. Laporan jalan, lingkungan, fasilitas, atau pelayanan desa tetap dikirim melalui halaman Buat Laporan.</p>
                <img src="{{ asset('images/illustrations/ilustrasi-aspirasi.webp') }}" alt="" width="820" height="1025" class="mt-8 hidden w-40 lg:block" loading="lazy">
            </header>

            <div class="border-t border-padelegan-800/20 pt-6 lg:border-l lg:border-t-0 lg:pl-10 lg:pt-0">
                <h2 class="text-xl font-semibold tracking-[-0.02em] text-padelegan-900">Sebelum melapor</h2>
                <ol class="mt-5 grid gap-4 text-sm leading-6 text-padelegan-900/68">
                    <li><strong class="font-semibold text-padelegan-900">1. Coba sekali lagi.</strong> Muat ulang halaman dan pastikan jaringan tetap tersambung.</li>
                    <li><strong class="font-semibold text-padelegan-900">2. Catat kejadiannya.</strong> Simpan alamat halaman, waktu, dan pesan error yang terlihat.</li>
                    <li><strong class="font-semibold text-padelegan-900">3. Jangan sertakan data rahasia.</strong> Hapus nomor WhatsApp, kata sandi, atau identitas warga dari tangkapan layar.</li>
                </ol>

                <div class="support-contact">
                    <p class="public-kicker">Kontak bantuan situs</p>
                    <div class="support-contact__person">
                        <span class="support-contact__avatar" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6.5 3.8c.5 0 .9.3 1 .8l.8 3a1.2 1.2 0 0 1-.35 1.15L7 9.7a12 12 0 0 0 5 5l.95-.95a1.2 1.2 0 0 1 1.15-.35l3 .8c.5.15.8.55.8 1.05v2.25a2 2 0 0 1-2.15 2A15.2 15.2 0 0 1 4.5 6.05 2 2 0 0 1 6.5 3.8Z" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <p class="support-contact__name">{{ $contactName }}</p>
                            <p class="support-contact__role">Layanan warga Desa Padelegan</p>
                        </div>
                    </div>
                    <div class="support-contact__actions">
                        <a class="action action--primary action--sm" href="https://wa.me/{{ $contactWa }}?text={{ $waText }}" target="_blank" rel="noopener">Chat WhatsApp</a>
                        <a class="action action--secondary action--sm" href="tel:+{{ $contactWa }}">{{ $contactWaLabel }}</a>
                    </div>
                    <p class="support-contact__note">Untuk kendala teknis pada situs. Jangan kirim data pribadi lewat pesan ini.</p>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($supportEmail)
                        <a class="action action--secondary" href="mailto:{{ $supportEmail }}?subject={{ $supportSubject }}&body={{ $supportBody }}">Kirim lewat email</a>
                    @endif
                    <x-ui.action :href="route('home')" variant="quiet">Kembali ke beranda</x-ui.action>
                </div>
            </div>
        </div>
    </section>
@endsection
