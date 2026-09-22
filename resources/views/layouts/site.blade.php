<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Lapor Padelegan, layanan pengaduan warga untuk mengirim dan memantau laporan desa.">
    <title>@yield('title', 'Sistem Lapor Padelegan') | Desa Padelegan</title>
    <link rel="icon" href="{{ asset('images/brand/lambang-pamekasan.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-4 focus:py-3">
        Lewati ke konten utama
    </a>

    <x-site.navigation />

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <x-site.footer :compact="request()->routeIs('home', 'reports.*')" />
    @stack('scripts')
</body>
</html>
