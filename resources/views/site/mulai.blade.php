@extends('layouts.site')

@section('title', 'Mulai Sampaikan')

@section('content')
    <section class="site-shell py-10 sm:py-14 lg:py-16">
        <div class="max-w-2xl" data-aos="fade-up">
            <p class="public-kicker">Layanan warga</p>
            <h1 class="public-page-title mt-3 text-padelegan-900">Mau menyampaikan apa?</h1>
            <p class="reading-measure mt-4 text-base leading-7 text-padelegan-900/65">Laporan untuk masalah atau kerusakan. Usulan untuk ide dan saran. Keduanya tanpa akun.</p>
        </div>

        <div class="choice-grid mt-9" data-aos="fade-up" data-aos-delay="80">
            <a href="{{ route('reports.create') }}" class="choice-card">
                <span class="choice-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h2 class="choice-card__title">Laporan masalah</h2>
                <p class="choice-card__desc">Kerusakan fasilitas, lingkungan, atau pelayanan. Sertakan foto dan patokan lokasi.</p>
                <span class="choice-card__cta">Buat laporan <span aria-hidden="true">→</span></span>
            </a>

            <a href="{{ route('usulan.create') }}" class="choice-card">
                <span class="choice-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 18h6m-5 3h4M12 3a6 6 0 0 0-4 10.5c.6.6 1 1.4 1 2.2V16h6v-.3c0-.8.4-1.6 1-2.2A6 6 0 0 0 12 3Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <h2 class="choice-card__title">Usulan &amp; saran</h2>
                <p class="choice-card__desc">Ide untuk pembangunan, pelayanan, atau kegiatan desa. Boleh dikirim anonim.</p>
                <span class="choice-card__cta">Kirim usulan <span aria-hidden="true">→</span></span>
            </a>
        </div>

        <p class="mt-8 text-sm leading-6 text-padelegan-900/60" data-aos="fade-up" data-aos-delay="120">
            Sudah pernah mengirim?
            <a href="{{ route('reports.track') }}" class="font-semibold text-padelegan-700 underline underline-offset-4">Cek status laporan</a>
            atau
            <a href="{{ route('usulan.lacak') }}" class="font-semibold text-padelegan-700 underline underline-offset-4">cek tindak lanjut usulan</a>.
        </p>
    </section>
@endsection
