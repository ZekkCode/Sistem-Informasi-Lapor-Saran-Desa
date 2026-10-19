@props(['report'])

@php
    $mediaItems = $report->relationLoaded('media') ? $report->media : collect();
    $photo = $mediaItems->firstWhere('media_type', \App\Enums\ReportMediaType::AfterPhoto)
        ?? $mediaItems->firstWhere('media_type', \App\Enums\ReportMediaType::BeforePhoto);
@endphp

<article class="public-report-card">
    @if ($photo)
        <a href="{{ route('public-reports.show', $report->report_code) }}" class="relative -mx-5 -mt-5 mb-3.5 block aspect-[16/9] overflow-hidden rounded-t-xl bg-emerald-950/10 border-b border-padelegan-800/10 group" aria-hidden="true" tabindex="-1">
            <img
                src="{{ route('public-reports.media', $photo) }}"
                alt="Foto dokumentasi laporan {{ $report->report_code }}"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            >
            @if ($photo->media_type === \App\Enums\ReportMediaType::AfterPhoto)
                <span class="absolute bottom-2 right-2 inline-flex items-center gap-1 rounded-md bg-emerald-900/90 text-white text-[10px] font-bold px-2 py-0.5 backdrop-blur-xs shadow-xs">
                    <svg class="size-3 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                    Bukti Penanganan
                </span>
            @endif
        </a>
    @endif

    <div class="public-report-card__meta text-padelegan-900/50">
        <span class="public-report-card__code">{{ $report->report_code }}</span>
        <div class="flex items-center gap-1.5 flex-wrap">
            <x-reports.verification-badge :status="$report->verification_status" />
            <x-reports.status :status="$report->status" />
        </div>
    </div>

    <h3 class="public-report-card__title">
        <a href="{{ route('public-reports.show', $report->report_code) }}">{{ $report->title }}</a>
    </h3>
    <p class="public-report-card__body">{{ \Illuminate\Support\Str::limit(strip_tags($report->description), 118) }}</p>

    <div class="public-report-card__footer">
        <span class="inline-flex items-center gap-1.5 font-medium text-padelegan-900/80">
            <svg class="h-3.5 w-3.5 text-padelegan-600 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            {{ $report->dusun->name }}
        </span>
        <span class="text-xs text-padelegan-900/50">{{ $report->subcategory->category->name }}</span>
    </div>
    <div class="mt-2.5 flex items-center justify-between border-t border-padelegan-800/10 pt-2 text-[0.6875rem] text-padelegan-900/50">
        <time class="inline-flex items-center gap-1.5" datetime="{{ $report->submitted_at->toDateString() }}">
            <svg class="h-3 w-3 text-padelegan-600/70 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                <line x1="16" x2="16" y1="2" y2="6"/>
                <line x1="8" x2="8" y1="2" y2="6"/>
                <line x1="3" x2="21" y1="10" y2="10"/>
            </svg>
            Laporan {{ $report->submitted_at->translatedFormat('d M Y') }}
        </time>
        <span class="text-padelegan-600 font-medium">Buka detail &rarr;</span>
    </div>
</article>
