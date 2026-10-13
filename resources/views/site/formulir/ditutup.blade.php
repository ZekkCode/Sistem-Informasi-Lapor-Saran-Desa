@extends('layouts.site')

@section('title', 'Formulir '.ucfirst($status->formulir->kataBenda()).' Ditutup')

@section('content')
    @php
        $kata = $status->formulir->kataBenda();
        $kataAwal = ucfirst($kata);
        $pemeliharaan = $status->kode() === 'pengembang';
        $catatan = $status->catatanPublik();
        $laporan = $status->formulir === \App\Enums\JenisFormulir::Laporan;
        $contactName = config('padelegan.support_contact_name');
        $contactWa = config('padelegan.support_whatsapp');
        $contactWaLabel = config('padelegan.support_whatsapp_label');
    @endphp

    {{-- Tanpa data-aos agar pemberitahuan tetap terbaca saat JavaScript gagal. --}}
    <section class="site-shell py-10 sm:py-14" aria-labelledby="formulir-ditutup-title">
        <div class="reading-measure">
            <p class="public-kicker">{{ $laporan ? 'Layanan warga' : 'Aspirasi warga' }}</p>
            <h1 id="formulir-ditutup-title" class="public-page-title mt-3 text-padelegan-900">
                {{ $pemeliharaan ? "Formulir {$kata} sedang dalam pemeliharaan." : "Formulir {$kata} ditutup sementara." }}
            </h1>
            <p class="mt-4 text-base leading-7 text-padelegan-900/65">
                @if ($pemeliharaan)
                    Pengiriman {{ $kata }} baru berhenti selama pemeliharaan sistem. Coba lagi nanti.
                @else
                    Petugas desa menutup formulir ini. {{ $kataAwal }} baru belum bisa dikirim sampai formulir dibuka kembali.
                @endif
            </p>

            @error('submission')
                <x-ui.alert variant="danger" class="mt-6">
                    {{ $kataAwal }} Anda belum terkirim karena formulir ditutup sebelum {{ $kata }} tersimpan.
                </x-ui.alert>
            @enderror

            @if ($catatan)
                <div class="tracking-note">
                    <h2 class="text-sm font-semibold text-padelegan-900">Catatan petugas</h2>
                    <p class="mt-1 text-sm leading-6 text-padelegan-900/65">{{ $catatan }}</p>
                </div>
            @endif

            <div class="mt-8 border-t border-padelegan-800/15 pt-6">
                <p class="text-sm leading-6 text-padelegan-900/65">{{ $kataAwal }} yang sudah terkirim tetap bisa dicek dengan nomor {{ $kata }}.</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    @if ($laporan)
                        <a class="action action--primary" href="{{ route('reports.track') }}">Cek status laporan</a>
                        <a class="action action--secondary" href="{{ route('public-reports.index') }}">Lihat laporan desa</a>
                    @else
                        <a class="action action--primary" href="{{ route('usulan.lacak') }}">Cek status usulan</a>
                        <a class="action action--secondary" href="{{ route('public-usulan.index') }}">Lihat usulan warga</a>
                    @endif
                </div>
            </div>

            <div class="mt-8 border-t border-padelegan-800/15 pt-6">
                <p class="text-sm leading-6 text-padelegan-900/65">Untuk keadaan mendesak, hubungi {{ $contactName }} lewat WhatsApp.</p>
                <a class="action action--secondary mt-3" href="https://wa.me/{{ $contactWa }}" target="_blank" rel="noopener">Chat WhatsApp {{ $contactWaLabel }}</a>
            </div>
        </div>
    </section>
@endsection
