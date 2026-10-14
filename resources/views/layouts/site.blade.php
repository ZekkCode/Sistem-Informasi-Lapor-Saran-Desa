<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Metadata Dasar & SEO --}}
    <title>@yield('title', 'Sistem Lapor Padelegan') | Desa Padelegan, Pamekasan</title>
    <meta name="description" content="@yield('meta_description', 'Sistem Lapor Padelegan: Layanan resmi pengaduan masalah dan aspirasi usulan pembangunan Pemerintah Desa Padelegan, Kecamatan Pademawu, Kabupaten Pamekasan. Dikembangkan oleh Zakaria Mujur (Zekk Code).')">
    <meta name="keywords" content="Desa Padelegan, Padelegan Pamekasan, Lapor Padelegan, Sistem Lapor Padelegan, Pengaduan Desa Padelegan, Aspirasi Warga Padelegan, Pademawu Pamekasan, Zakaria Mujur, Zekk Code, ZekkCode, zekkcode webdev, Pemerintah Desa Padelegan">
    <meta name="author" content="ZekkCode">
    <meta name="creator" content="Zakaria Mujur (Zekk Code)">
    <meta name="developer" content="Zakaria Mujur / Zekk Code (zekkcode webdev)">
    <meta name="publisher" content="Pemerintah Desa Padelegan">
    <meta name="geo.region" content="ID-JI">
    <meta name="geo.placename" content="Desa Padelegan, Pademawu, Pamekasan">
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
    <meta property="og:title" content="@yield('title', 'Sistem Lapor Padelegan') | Desa Padelegan, Pamekasan">
    <meta property="og:description" content="@yield('meta_description', 'Layanan pengaduan dan aspirasi warga Pemerintah Desa Padelegan, Kecamatan Pademawu, Kabupaten Pamekasan. Karya Zakaria Mujur (Zekk Code).')">
    <meta property="og:image" content="{{ asset('images/brand/og-thumbnail-padelegan.jpg') }}">
    <meta property="og:image:secure_url" content="{{ asset('images/brand/og-thumbnail-padelegan.jpg') }}">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Sistem Lapor Desa Padelegan - Karya Zakaria Mujur (Zekk Code)">

    {{-- Twitter / X Cards --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Sistem Lapor Padelegan') | Desa Padelegan, Pamekasan">
    <meta name="twitter:description" content="@yield('meta_description', 'Layanan pengaduan dan aspirasi warga Pemerintah Desa Padelegan, Kecamatan Pademawu, Kabupaten Pamekasan. Karya Zakaria Mujur (Zekk Code).')">
    <meta name="twitter:image" content="{{ asset('images/brand/og-thumbnail-padelegan.jpg') }}">
    <meta name="twitter:image:alt" content="Sistem Lapor Desa Padelegan oleh Zakaria Mujur (Zekk Code)">

    {{-- Schema.org JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'GovernmentOrganization',
        'name' => 'Pemerintah Desa Padelegan',
        'alternateName' => 'Sistem Lapor Padelegan',
        'url' => url('/'),
        'logo' => asset('images/brand/lambang-pamekasan.png'),
        'image' => asset('images/brand/og-thumbnail-padelegan.jpg'),
        'description' => 'Layanan resmi pengaduan masalah desa dan usulan pembangunan Pemerintah Desa Padelegan, Pademawu, Pamekasan.',
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Pamekasan',
            'addressRegion' => 'Jawa Timur',
            'addressCountry' => 'ID',
        ],
        'creator' => [
            '@type' => 'Person',
            'name' => 'Zakaria Mujur',
            'alternateName' => 'Zekk Code',
            'url' => 'https://github.com/zekkcode',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

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
