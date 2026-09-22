@props(['name' => null, 'invalid' => false, 'describedby' => null])

@php
    $hasError = $invalid || ($name && isset($errors) && $errors->has($name));
    $descriptionIds = collect([$describedby, $hasError && $name ? $name.'-error' : null])->filter()->implode(' ');
@endphp

<div class="relative">
    <select
        @if ($name) name="{{ $name }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif
        {{ $attributes->class(['control', 'appearance-none', 'pr-10']) }}
    >
        {{ $slot }}
    </select>
    <svg aria-hidden="true" viewBox="0 0 24 24" class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-muted" fill="none" stroke="currentColor" stroke-width="2">
        <path d="m7 10 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
</div>
