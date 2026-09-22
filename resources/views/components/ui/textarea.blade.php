@props(['name' => null, 'invalid' => false, 'describedby' => null])

@php
    $hasError = $invalid || ($name && isset($errors) && $errors->has($name));
    $descriptionIds = collect([$describedby, $hasError && $name ? $name.'-error' : null])->filter()->implode(' ');
@endphp

<textarea
    @if ($name) name="{{ $name }}" @endif
    @if ($hasError) aria-invalid="true" @endif
    @if ($descriptionIds) aria-describedby="{{ $descriptionIds }}" @endif
    {{ $attributes->class(['control', 'min-h-32']) }}
>{{ $slot }}</textarea>
