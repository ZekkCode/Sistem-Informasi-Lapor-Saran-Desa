@props(['status'])

@php
    $enum = $status instanceof \App\Enums\StatusUsulan ? $status : \App\Enums\StatusUsulan::tryFrom($status);
    $value = $enum?->value ?? $status;
    $label = $enum?->label() ?? $status;
@endphp

<span {{ $attributes->class('status') }} data-status-usulan="{{ $value }}">
    @if ($value === 'baru')
        <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
    @elseif ($value === 'ditinjau')
        <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    @elseif ($value === 'ditindaklanjuti')
        <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
    @elseif ($value === 'selesai')
        <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    @endif
    <span>{{ $label }}</span>
</span>
