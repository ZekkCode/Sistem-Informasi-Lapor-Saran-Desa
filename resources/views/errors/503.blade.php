@extends('layouts.site')

@section('title', 'Layanan Sedang Dirawat')

@section('content')
    <section class="site-shell py-16 sm:py-24">
        <div class="max-w-2xl">
            <p class="public-kicker">Pemeliharaan</p>
            <h1 class="public-page-title mt-3 text-padelegan-900">Sistem Lapor Padelegan sedang dirawat.</h1>
            <p class="mt-5 text-base leading-7 text-padelegan-900/65">Silakan coba kembali beberapa saat lagi. Laporan hanya tersimpan setelah nomor laporan muncul.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <x-ui.action :href="route('home')">Coba kembali</x-ui.action>
                <x-ui.action :href="route('site-support')" variant="secondary">Laporkan kendala situs</x-ui.action>
            </div>
        </div>
    </section>
@endsection
