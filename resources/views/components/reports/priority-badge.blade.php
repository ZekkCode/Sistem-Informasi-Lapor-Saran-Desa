@props(['priority'])

@php
    $value = $priority instanceof \BackedEnum ? $priority->value : $priority;

    [$label, $variant] = match ($value) {
        'emergency', 'urgent' => ['Darurat', 'danger'],
        'important', 'high' => ['Penting', 'warning'],
        'medium' => ['Sedang', 'info'],
        'low' => ['Rendah', 'neutral'],
        default => ['Biasa', 'neutral'],
    };
@endphp

<x-ui.badge :variant="$variant" {{ $attributes }}>{{ $label }}</x-ui.badge>
