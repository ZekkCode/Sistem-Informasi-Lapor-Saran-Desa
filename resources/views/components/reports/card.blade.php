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
        <span>{{ $report->dusun->name }} · {{ $report->subcategory->category->name }}</span>
    </div>
    <time class="mt-2 text-[0.6875rem] text-padelegan-900/40" datetime="{{ $report->submitted_at->toDateString() }}">Diterima {{ $report->submitted_at->translatedFormat('d M Y') }}</time>
</article>
