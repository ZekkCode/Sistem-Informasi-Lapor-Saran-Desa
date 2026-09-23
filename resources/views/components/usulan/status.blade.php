@props(['status'])

@php
    $enum = $status instanceof \App\Enums\StatusUsulan ? $status : \App\Enums\StatusUsulan::from($status);
@endphp

<span {{ $attributes->class('status') }} data-status-usulan="{{ $enum->value }}">{{ $enum->label() }}</span>
