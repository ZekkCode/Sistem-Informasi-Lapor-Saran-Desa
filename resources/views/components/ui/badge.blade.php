@props(['variant' => 'neutral'])

@php
    $classes = match ($variant) {
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-info',
        default => 'bg-padelegan-100 text-padelegan-800',
    };
@endphp

<span {{ $attributes->class(['inline-flex w-fit items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold', $classes]) }}>
    {{ $slot }}
</span>
