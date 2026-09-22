@props(['name' => null, 'message' => null])

@php
    $errorMessage = $message ?: ($name && isset($errors) ? $errors->first($name) : null);
@endphp

@if ($errorMessage)
    <p id="{{ $name }}-error" {{ $attributes->class('field__error') }}>{{ $errorMessage }}</p>
@endif
