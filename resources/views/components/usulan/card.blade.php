@props(['usulan'])

<article class="public-report-card">
    <div class="public-report-card__meta text-padelegan-900/50">
        <span class="public-report-card__code">{{ $usulan->kode }}</span>
        <x-usulan.status :status="$usulan->status" />
    </div>

    <h3 class="public-report-card__title">
        <a href="{{ route('public-usulan.show', $usulan->kode) }}">{{ $usulan->judul }}</a>
    </h3>
    <p class="public-report-card__body">{{ \Illuminate\Support\Str::limit(strip_tags($usulan->isi), 118) }}</p>

    <div class="public-report-card__footer">
        <span>{{ $usulan->dusun->name }} · {{ $usulan->jenis->label() }}</span>
        <span class="public-report-card__arrow" aria-hidden="true">→</span>
    </div>
    <time class="mt-2 text-[0.6875rem] text-padelegan-900/40" datetime="{{ $usulan->dikirim_pada->toDateString() }}">Dikirim {{ $usulan->dikirim_pada->translatedFormat('d M Y') }}</time>
</article>
