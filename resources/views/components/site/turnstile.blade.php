@props([
    'label' => 'Pemeriksaan keamanan',
])

@php
    $siteKey = (string) config('services.turnstile.site_key');
@endphp

@if($siteKey !== '')
    <div class="field">
        <span class="field__label" id="turnstile-label">{{ $label }}<abbr class="required-mark" title="Wajib diisi" aria-label="wajib">*</abbr></span>
        <p class="field__hint" id="turnstile-hint">Pemeriksaan ini memastikan formulir dikirim oleh warga, bukan program bot pengirim spam.</p>
        <div class="mt-2" aria-labelledby="turnstile-label" aria-describedby="turnstile-hint">
            <div class="cf-turnstile" data-sitekey="{{ $siteKey }}" data-theme="light"></div>
        </div>
        <x-ui.field-error name="cf-turnstile-response" />
    </div>

    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        @endpush
    @endonce
@endif
