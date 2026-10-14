<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Metadata Dasar & SEO --}}
    <title>@yield('title', 'Sistem Lapor Padelegan') | Desa Padelegan</title>
    <meta name="description" content="@yield('meta_description', 'Sistem Lapor Padelegan, layanan pengaduan dan aspirasi warga Pemerintah Desa Padelegan, Kecamatan Pademawu, Kabupaten Pamekasan.')">
    <meta name="keywords" content="lapor padelegan, desa padelegan, pengaduan desa, pademawu, pamekasan, aspirasi warga, lapor desa">
    <meta name="author" content="ZekkCode">
    <meta name="developer" content="ZekkCode (zekkcode webdev)">
    <meta name="publisher" content="Pemerintah Desa Padelegan">
    <meta name="theme-color" content="#176248">
    <meta name="color-scheme" content="light">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon & Web App Manifest --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    {{-- Open Graph / Facebook / WhatsApp / Telegram / LinkedIn --}}
    <meta property="og:site_name" content="Sistem Lapor Padelegan">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Sistem Lapor Padelegan') | Desa Padelegan">
    <meta property="og:description" content="@yield('meta_description', 'Layanan pengaduan dan aspirasi warga Pemerintah Desa Padelegan, Kecamatan Pademawu, Kabupaten Pamekasan.')">
    <meta property="og:image" content="{{ asset('images/brand/og-thumbnail-padelegan.jpg') }}">
    <meta property="og:image:secure_url" content="{{ asset('images/brand/og-thumbnail-padelegan.jpg') }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Sistem Lapor Padelegan - Layanan Pengaduan & Usulan Desa Padelegan">

    {{-- Twitter / X Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Sistem Lapor Padelegan') | Desa Padelegan">
    <meta name="twitter:description" content="@yield('meta_description', 'Layanan pengaduan dan aspirasi warga Pemerintah Desa Padelegan, Kecamatan Pademawu, Kabupaten Pamekasan.')">
    <meta name="twitter:image" content="{{ asset('images/brand/og-thumbnail-padelegan.jpg') }}">
    <meta name="twitter:image:alt" content="Sistem Lapor Padelegan">

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
