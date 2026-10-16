@extends('layouts.site')

@section('title', 'Situs Mengalami Kendala')

@section('content')
    <section class="site-shell py-16 sm:py-24">
        <div class="max-w-2xl">
            <p class="public-kicker">Kendala sistem</p>
            <h1 class="public-page-title mt-3 text-padelegan-900">Halaman belum dapat diproses.</h1>
            <p class="mt-5 text-base leading-7 text-padelegan-900/65">Sistem sedang mengalami kendala. Muat ulang halaman atau kembali ke beranda.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <x-ui.action :href="route('home')">Kembali ke beranda</x-ui.action>
                <x-ui.action :href="route('site-support')" variant="secondary">Hubungi bantuan</x-ui.action>
            </div>
        </div>
    </section>
@endsection
