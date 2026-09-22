@extends('layouts.site')

@section('title', 'Cek Status Laporan')

@section('content')
    <section class="border-b border-padelegan-800/10 bg-white">
        <div class="site-shell py-9 sm:py-12 lg:py-14">
            <div class="tracking-intro">
                <div data-aos="fade-up">
                    <p class="public-kicker">Pelacakan laporan</p>
                    <h1 class="public-page-title mt-3 text-padelegan-900">Cek status laporan.</h1>
                    <p class="reading-measure mt-4 text-sm leading-6 text-padelegan-900/62 sm:text-base sm:leading-7">Masukkan nomor yang diterima saat pengiriman. Halaman ini bisa dibuka tanpa akun.</p>
                </div>

                <form action="{{ route('reports.track') }}" method="GET" class="tracking-search" data-validate data-aos="fade-up" data-aos-delay="100">
                    <div class="field">
                        <x-ui.label for="code" required>Nomor laporan</x-ui.label>
                        <p id="code-hint" class="field__hint">Contoh: PDL-2026-ABC123</p>
                        <input
                            id="code"
                            name="code"
                            value="{{ old('code', $code) }}"
                            class="control font-mono uppercase tracking-wide"
                            placeholder="PDL-2026-ABC123"
                            autocomplete="off"
                            autocapitalize="characters"
                            spellcheck="false"
                            aria-describedby="code-hint @error('code') code-error @enderror"
                            @error('code') aria-invalid="true" @enderror
                            required
                        >
                        @error('code')<p class="field__error" id="code-error">{{ $message }}</p>@enderror
                    </div>
                    <button class="action action--primary mt-3 w-full" type="submit">Tampilkan perkembangan</button>
                </form>
            </div>

            @if ($usingDemoData)
                <x-ui.alert variant="warning" title="Mode pratinjau" class="mt-7" data-aos="fade-up">
                    Belum ada laporan. Gunakan nomor
                    <a href="{{ route('reports.track', ['code' => $demoReportCode]) }}" class="font-semibold underline underline-offset-4">{{ $demoReportCode }}</a>
                    untuk contoh status dan riwayat.
                </x-ui.alert>
            @endif
        </div>
    </section>

    @if ($code !== '' && ! $report)
        <section class="site-shell py-8 sm:py-10" aria-live="polite">
            <div class="tracking-empty" role="status" data-aos="fade-up">
                <div>
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-padelegan-900">Nomor laporan tidak ditemukan.</h2>
                    <p class="mt-1 text-sm leading-6 text-padelegan-900/60">Periksa tiap karakter lalu coba lagi. Nomor tidak pakai huruf I atau O.</p>
                </div>
                <a href="{{ route('reports.create') }}" class="action action--secondary">Buat laporan baru</a>
            </div>
        </section>
    @endif

    @if ($report)
        <article class="site-shell py-9 sm:py-12 lg:py-14" aria-labelledby="report-title">
            <div class="tracking-result">
                <div class="tracking-summary" data-aos="fade-up">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-reports.status :status="$report->status" />
                        <span class="tracking-code text-padelegan-900/50">{{ $report->report_code }}</span>
                    </div>
                    <h2 id="report-title" class="mt-4 text-3xl font-semibold leading-tight tracking-[-0.04em] text-padelegan-900 sm:text-4xl">{{ $report->title }}</h2>

                    <dl class="tracking-meta">
                        @foreach ([
                            'Dusun' => $report->dusun->name,
                            'Kategori' => $report->subcategory->category->name,
                            'Jenis masalah' => $report->subcategory->name,
                            'Diterima' => $report->submitted_at->translatedFormat('d F Y, H:i'),
                            'Verifikasi' => $report->verification_status->label(),
                        ] as $term => $description)
                            <div><dt>{{ $term }}</dt><dd>{{ $description }}</dd></div>
                        @endforeach
                    </dl>

                    @if ($report->current_public_note)
                        <div class="tracking-note">
                            <h3 class="text-sm font-semibold text-padelegan-900">Catatan terbaru dari petugas</h3>
                            <p class="mt-1 text-sm leading-6 text-padelegan-900/65">{{ $report->current_public_note }}</p>
                        </div>
                    @endif
                </div>

                <section class="tracking-timeline" aria-labelledby="history-title" data-aos="fade-up" data-aos-delay="100">
                    <p class="public-kicker">Pembaruan</p>
                    <h2 id="history-title" class="mt-2 text-xl font-semibold tracking-[-0.025em] text-padelegan-900">Riwayat penanganan</h2>
                    <ol class="tracking-timeline__list">
                        @foreach ($report->histories as $history)
                            <li class="tracking-timeline__item">
                                <span class="tracking-timeline__dot" aria-hidden="true"></span>
                                <time class="text-xs font-medium text-padelegan-900/45" datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->translatedFormat('d M Y, H:i') }}</time>
                                <p class="mt-1 font-semibold text-padelegan-900">{{ $history->status->label() }}</p>
                                @if ($history->public_note)<p class="mt-1 text-sm leading-6 text-padelegan-900/60">{{ $history->public_note }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            </div>
        </article>
    @endif
@endsection
