@extends('layouts.site')

@section('title', $report->title)

@section('content')
    <article class="site-shell py-9 lg:py-14">
        <a href="{{ route('public-reports.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-padelegan-600 hover:text-padelegan-800">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m15 18-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Kembali ke Laporan Desa
        </a>
        @if ($usingDemoData)
            <x-ui.alert variant="warning" title="Laporan contoh" class="mt-6">
                Halaman ini memakai data contoh. Data tersebut tidak berasal dari laporan warga.
            </x-ui.alert>
        @endif
        <div class="mt-8 grid gap-12 lg:grid-cols-[1.2fr_0.8fr]">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-reports.status :status="$report->status" />
                    <span class="font-mono text-xs tracking-wide text-padelegan-900/45">{{ $report->report_code }}</span>
                </div>
                <h1 class="public-page-title mt-5 text-padelegan-900">{{ $report->title }}</h1>
                <p class="mt-7 whitespace-pre-line text-base leading-8 text-padelegan-900/70">{{ $report->description }}</p>
                @if ($report->current_public_note)
                    <div class="mt-9 border-l-2 border-pond-500 bg-white p-6">
                        <h2 class="font-semibold">Catatan Pemerintah Desa</h2>
                        <p class="mt-2 text-sm leading-7 text-padelegan-900/65">{{ $report->current_public_note }}</p>
                    </div>
                @endif
            </div>
            <aside>
                <dl class="border-t border-padelegan-800/15 text-sm">
                    @foreach (['Dusun' => $report->dusun->name, 'Kategori' => $report->subcategory->category->name, 'Jenis masalah' => $report->subcategory->name, 'Tanggal' => $report->submitted_at->translatedFormat('d F Y')] as $term => $description)
                        <div class="grid grid-cols-[7rem_1fr] gap-4 border-b border-padelegan-800/15 py-4">
                            <dt class="text-padelegan-900/50">{{ $term }}</dt>
                            <dd class="font-semibold">{{ $description }}</dd>
                        </div>
                    @endforeach
                </dl>
                <section class="mt-9" aria-labelledby="public-history">
                    <h2 id="public-history" class="text-lg font-semibold">Riwayat status</h2>
                    <ol class="mt-5 border-l border-padelegan-800/20 pl-6">
                        @foreach ($report->histories as $history)
                            <li class="relative pb-7 last:pb-0">
                                <span class="absolute -left-[1.74rem] top-1 size-3 rounded-full bg-padelegan-600" aria-hidden="true"></span>
                                <time class="text-xs text-padelegan-900/45" datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->translatedFormat('d M Y, H:i') }}</time>
                                <p class="mt-1 font-semibold">{{ $history->status->label() }}</p>
                                @if ($history->public_note)<p class="mt-1 text-sm leading-6 text-padelegan-900/60">{{ $history->public_note }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            </aside>
        </div>
    </article>
@endsection
