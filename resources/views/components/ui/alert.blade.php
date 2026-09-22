@props(['variant' => 'info', 'title' => null])

@php
    [$classes, $role] = match ($variant) {
        'success' => ['border-success/25 bg-success-soft text-success', 'status'],
        'warning' => ['border-warning/25 bg-warning-soft text-warning', 'status'],
        'danger' => ['border-danger/25 bg-danger-soft text-danger', 'alert'],
        default => ['border-info/25 bg-info-soft text-info', 'status'],
    };
@endphp

<div role="{{ $role }}" {{ $attributes->class(['flex gap-3 border p-4', $classes]) }}>
    <svg aria-hidden="true" viewBox="0 0 24 24" class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8">
        @if ($variant === 'success')
            <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round" /><circle cx="12" cy="12" r="9" />
        @elseif ($variant === 'warning' || $variant === 'danger')
            <path d="M12 9v4m0 4h.01M10.3 4.2 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z" stroke-linecap="round" />
        @else
            <circle cx="12" cy="12" r="9" /><path d="M12 11v5m0-8h.01" stroke-linecap="round" />
        @endif
    </svg>
    <div class="min-w-0 text-foreground">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        <div @class(['text-sm leading-6', 'mt-1' => $title])>{{ $slot }}</div>
    </div>
</div>
