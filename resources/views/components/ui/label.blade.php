@props(['for' => null, 'required' => false])

<label {{ $attributes->merge(['class' => 'field__label'])->merge(['for' => $for]) }}>
    {{ $slot }}@if($required)<abbr class="required-mark" title="Wajib diisi" aria-label="wajib">*</abbr>@endif
</label>
