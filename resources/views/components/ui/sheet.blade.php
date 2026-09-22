@props([
    'open' => 'open',
    'close' => 'open = false',
    'title' => null,
    'side' => 'right',
])

<div
    x-cloak
    x-show="{{ $open }}"
    x-effect="document.body.style.overflow = {{ $open }} ? 'hidden' : ''"
    @keydown.escape.window="{{ $close }}"
    {{ $attributes->class('fixed inset-0 z-50') }}
    role="dialog"
    aria-modal="true"
    @if ($title) aria-label="{{ $title }}" @endif
>
    <button type="button" aria-label="Tutup panel" class="absolute inset-0 bg-padelegan-900/45" @click="{{ $close }}" x-transition.opacity></button>
    <aside
        x-show="{{ $open }}"
        @click.stop
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="{{ $side === 'left' ? '-translate-x-full' : 'translate-x-full' }}"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="{{ $side === 'left' ? '-translate-x-full' : 'translate-x-full' }}"
        @class([
            'absolute top-0 flex h-full w-[88%] max-w-sm flex-col bg-surface shadow-[var(--shadow-raised)]',
            'left-0' => $side === 'left',
            'right-0' => $side !== 'left',
        ])
    >
        <div class="flex min-h-16 shrink-0 items-center border-b border-border px-4">
            @if ($title)<h2 class="font-semibold text-foreground">{{ $title }}</h2>@endif
            <button type="button" aria-label="Tutup" class="ml-auto grid size-11 place-items-center text-muted hover:bg-primary-soft hover:text-primary" @click="{{ $close }}">
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" /></svg>
            </button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto">{{ $slot }}</div>
    </aside>
</div>
