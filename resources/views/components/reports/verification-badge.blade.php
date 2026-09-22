@props(['status'])

@php
    $value = $status instanceof \App\Enums\VerificationStatus ? $status->value : $status;
    $label = $status instanceof \App\Enums\VerificationStatus
        ? $status->label()
        : \App\Enums\VerificationStatus::from($status)->label();

    $variant = match ($value) {
        'verified' => 'info',
        'invalid' => 'danger',
        default => 'neutral',
    };
@endphp

<x-ui.badge :variant="$variant" {{ $attributes }}>{{ $label }}</x-ui.badge>
