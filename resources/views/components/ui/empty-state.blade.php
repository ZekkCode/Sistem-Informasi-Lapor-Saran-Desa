@props(['title', 'description' => null])

<div {{ $attributes->class('flex flex-col items-center justify-center border border-dashed border-border bg-surface px-6 py-14 text-center') }}>
    <div class="mb-4 grid size-12 place-items-center rounded-full bg-primary-soft text-primary">
        @isset($icon)
            {{ $icon }}
        @else
            <svg aria-hidden="true" viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7.5 7 4h10l3 3.5V20H4V7.5Z"/><path d="M4 14h5l1.5 2h3L15 14h5"/></svg>
        @endisset
    </div>
    <h3 class="text-base font-semibold text-foreground">{{ $title }}</h3>
    @if ($description)<p class="mt-1 max-w-sm text-sm leading-6 text-muted">{{ $description }}</p>@endif
    @isset($action)<div class="mt-5">{{ $action }}</div>@endisset
</div>
