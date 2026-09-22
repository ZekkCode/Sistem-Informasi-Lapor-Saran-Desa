@props(['status'])

@php
    $value = $status instanceof \App\Enums\ReportStatus ? $status->value : $status;
    $label = $status instanceof \App\Enums\ReportStatus
        ? $status->label()
        : \App\Enums\ReportStatus::from($status)->label();
@endphp

<span {{ $attributes->class('status') }} data-status="{{ $value }}">{{ $label }}</span>
