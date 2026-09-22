@props(['name' => null, 'type' => 'text', 'invalid' => false, 'describedby' => null])

@php
    $hasError = $invalid || ($name && isset($errors) && $errors->has($name));
    $descriptionIds = collect([$describedby, $hasError && $name ? $name.'-error' : null])->filter()->implode(' ');
@endphp

<input
    type="{{ $type }}"
    @if ($name) name="{{ $name }}" @endif
    @if ($hasError) aria-invalid="true" @endif
    @if ($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif
    {{ $attributes->class('control') }}
>
