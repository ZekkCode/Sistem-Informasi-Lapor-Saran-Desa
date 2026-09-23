@extends('layouts.site')

@section('title', 'Cek Tindak Lanjut Usulan')

@section('content')
    <section class="border-b border-padelegan-800/10 bg-white">
        <div class="site-shell py-9 sm:py-12 lg:py-14">
            <div class="tracking-intro">
                <div data-aos="fade-up">
                    <p class="public-kicker">Pelacakan usulan</p>
                    <h1 class="public-page-title mt-3 text-padelegan-900">Cek tindak lanjut usulan.</h1>
                    <p class="reading-measure mt-4 text-sm leading-6 text-padelegan-900/62 sm:text-base sm:leading-7">Masukkan nomor yang diterima saat pengiriman. Halaman ini bisa dibuka tanpa akun.</p>
                </div>

                <form action="{{ route('usulan.lacak') }}" method="GET" class="tracking-search" data-validate data-aos="fade-up" data-aos-delay="100">
                    <div class="field">
                        <x-ui.label for="kode" required>Nomor usulan</x-ui.label>
                        <p id="kode-hint" class="field__hint">Contoh: USL-2026-ABC123</p>
                        <input
                            id="kode"
                            name="kode"
                            value="{{ old('kode', $kode) }}"
                            class="control font-mono uppercase tracking-wide"
                            placeholder="USL-2026-ABC123"
                            autocomplete="off"
                            autocapitalize="characters"
                            spellcheck="false"
                            aria-describedby="kode-hint @error('kode') kode-error @enderror"
                            @error('kode') aria-invalid="true" @enderror
                            required
                        >
                        @error('kode')<p class="field__error" id="kode-error">{{ $message }}</p>@enderror
                    </div>
                    <button class="action action--primary mt-3 w-full" type="submit">Tampilkan tindak lanjut</button>
                </form>
            </div>
        </div>
    </section>

    @if ($kode !== '' && ! $usulan)
        <section class="site-shell py-8 sm:py-10" aria-live="polite">
            <div class="tracking-empty" role="status" data-aos="fade-up">
                <div>
                    <h2 class="text-lg font-semibold tracking-[-0.02em] text-padelegan-900">Nomor usulan tidak ditemukan.</h2>
                    <p class="mt-1 text-sm leading-6 text-padelegan-900/60">Periksa tiap karakter lalu coba lagi. Nomor tidak pakai huruf I atau O.</p>
                </div>
                <a href="{{ route('usulan.create') }}" class="action action--secondary">Kirim usulan baru</a>
            </div>
        </section>
    @endif

    @if ($usulan)
        <article class="site-shell py-9 sm:py-12 lg:py-14" aria-labelledby="usulan-title">
            <div class="tracking-result">
                <div class="tracking-summary" data-aos="fade-up">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-usulan.status :status="$usulan->status" />
                        <span class="tracking-code text-padelegan-900/50">{{ $usulan->kode }}</span>
                    </div>
                    <h2 id="usulan-title" class="mt-4 text-3xl font-semibold leading-tight tracking-[-0.04em] text-padelegan-900 sm:text-4xl">{{ $usulan->judul }}</h2>

                    <dl class="tracking-meta">
                        @foreach ([
                            'Dusun' => $usulan->dusun->name,
                            'Jenis usulan' => $usulan->jenis->label(),
                            'Dikirim' => $usulan->dikirim_pada->translatedFormat('d F Y, H:i'),
                        ] as $term => $description)
                            <div><dt>{{ $term }}</dt><dd>{{ $description }}</dd></div>
                        @endforeach
                    </dl>

                    @if ($usulan->catatan_publik)
                        <div class="tracking-note">
                            <h3 class="text-sm font-semibold text-padelegan-900">Catatan terbaru dari petugas</h3>
                            <p class="mt-1 text-sm leading-6 text-padelegan-900/65">{{ $usulan->catatan_publik }}</p>
                        </div>
                    @endif
                </div>

                <section class="tracking-timeline" aria-labelledby="riwayat-title" data-aos="fade-up" data-aos-delay="100">
                    <p class="public-kicker">Perkembangan</p>
                    <h2 id="riwayat-title" class="mt-2 text-xl font-semibold tracking-[-0.025em] text-padelegan-900">Riwayat tindak lanjut</h2>
                    <ol class="tracking-timeline__list">
                        @foreach ($usulan->riwayat as $riwayat)
                            <li class="tracking-timeline__item">
                                <span class="tracking-timeline__dot" aria-hidden="true"></span>
                                <time class="text-xs font-medium text-padelegan-900/45" datetime="{{ $riwayat->created_at->toIso8601String() }}">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }}</time>
                                <p class="mt-1 font-semibold text-padelegan-900">{{ $riwayat->status->label() }}</p>
                                @if ($riwayat->catatan_publik)<p class="mt-1 text-sm leading-6 text-padelegan-900/60">{{ $riwayat->catatan_publik }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            </div>
        </article>
    @endif
@endsection
