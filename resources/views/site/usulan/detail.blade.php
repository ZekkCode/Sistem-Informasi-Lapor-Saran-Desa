@extends('layouts.site')

@section('title', $usulan->judul)

@section('content')
    <article class="site-shell py-9 lg:py-14">
        <a href="{{ route('public-usulan.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-padelegan-600 hover:text-padelegan-800">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Kembali ke Usulan Warga
        </a>
        @if ($usingDemoData)
            <x-ui.alert variant="warning" title="Usulan contoh" class="mt-6">
                Halaman ini memakai data contoh. Data tersebut tidak berasal dari usulan warga.
            </x-ui.alert>
        @endif
        <div class="mt-8 grid gap-12 lg:grid-cols-[1.2fr_0.8fr]">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-usulan.status :status="$usulan->status" />
                    <span class="font-mono text-xs tracking-wide text-padelegan-900/45">{{ $usulan->kode }}</span>
                </div>
                <h1 class="public-page-title mt-5 text-padelegan-900">{{ $usulan->judul }}</h1>
                <p class="mt-7 whitespace-pre-line text-base leading-8 text-padelegan-900/70">{{ $usulan->isi }}</p>
                @if ($usulan->catatan_publik)
                    <div class="mt-9 border-l-2 border-pond-500 bg-white p-6">
                        <h2 class="font-semibold">Catatan Pemerintah Desa</h2>
                        <p class="mt-2 text-sm leading-7 text-padelegan-900/65">{{ $usulan->catatan_publik }}</p>
                    </div>
                @endif
            </div>
            <aside>
                <dl class="border-t border-padelegan-800/15 text-sm">
                    @foreach (['Dusun' => $usulan->dusun->name, 'Jenis usulan' => $usulan->jenis->label(), 'Tanggal' => $usulan->dikirim_pada->translatedFormat('d F Y')] as $term => $description)
                        <div class="grid grid-cols-[7rem_1fr] gap-4 border-b border-padelegan-800/15 py-4">
                            <dt class="text-padelegan-900/50">{{ $term }}</dt>
                            <dd class="font-semibold">{{ $description }}</dd>
                        </div>
                    @endforeach
                </dl>
                <section class="mt-9" aria-labelledby="public-riwayat">
                    <h2 id="public-riwayat" class="text-lg font-semibold">Riwayat tindak lanjut</h2>
                    <ol class="mt-5 border-l border-padelegan-800/20 pl-6">
                        @foreach ($usulan->riwayat as $riwayat)
                            <li class="relative pb-7 last:pb-0">
                                <span class="absolute -left-[1.74rem] top-1 size-3 rounded-full bg-padelegan-600" aria-hidden="true"></span>
                                <time class="text-xs text-padelegan-900/45" datetime="{{ $riwayat->created_at->toIso8601String() }}">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }}</time>
                                <p class="mt-1 font-semibold">{{ $riwayat->status->label() }}</p>
                                @if ($riwayat->catatan_publik)<p class="mt-1 text-sm leading-6 text-padelegan-900/60">{{ $riwayat->catatan_publik }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            </aside>
        </div>
    </article>
@endsection
