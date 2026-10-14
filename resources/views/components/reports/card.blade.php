@props(['report'])

<article class="public-report-card">
    <div class="public-report-card__meta text-padelegan-900/50">
        <span class="public-report-card__code">{{ $report->report_code }}</span>
        <x-reports.status :status="$report->status" />
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
