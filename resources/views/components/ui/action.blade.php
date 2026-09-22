@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
])

@php
    $variantClass = match ($variant) {
        'secondary', 'outline' => 'action--secondary',
        'quiet', 'ghost' => 'action--quiet',
        'danger' => 'action--danger',
        'inverse' => 'action--inverse',
        'inverse-outline' => 'action--inverse-outline',
        default => 'action--primary',
    };

    $sizeClass = match ($size) {
        'sm' => 'action--sm',
        'lg' => 'action--lg',
        'icon' => 'action--icon',
        default => null,
    };

    $isDisabled = $disabled || $loading;
@endphp

@if ($href)
    <a
        href="{{ $isDisabled ? '#' : $href }}"
        @if ($isDisabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->class(['action', $variantClass, $sizeClass]) }}
    >
        @if ($loading)<x-ui.spinner class="size-4" />@endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($isDisabled)
        {{ $attributes->class(['action', $variantClass, $sizeClass]) }}
    >
        @if ($loading)<x-ui.spinner class="size-4" />@endif
        {{ $slot }}
    </button>
@endif
