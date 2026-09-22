<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Panel Petugas') | Sistem Lapor Padelegan</title>
    <link rel="icon" href="{{ asset('images/brand/lambang-pamekasan.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="admin-body antialiased" x-data="{ navigationOpen: false }">
    <a href="#admin-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-4 focus:py-3">
        Lewati ke konten utama
    </a>

    <header class="admin-mobile-bar">
        <a href="{{ route('admin.dashboard') }}" class="admin-mobile-brand" aria-label="Sistem Lapor Padelegan, Dasbor">
            <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="admin-brand__crest">
            <strong>Sistem Lapor Padelegan</strong>
        </a>
        <button type="button" class="admin-menu-button" @click="navigationOpen = !navigationOpen" :aria-expanded="navigationOpen.toString()" aria-controls="admin-navigation">
            <span class="sr-only">Buka navigasi</span>
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" /></svg>
        </button>
    </header>

    <div class="admin-shell">
        <aside id="admin-navigation" class="admin-sidebar" :class="navigationOpen ? 'is-open' : ''" @click.outside="navigationOpen = false">
            <div class="admin-brand">
                <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="admin-brand__crest">
                <span><strong>Sistem Lapor Padelegan</strong><small>Panel petugas desa</small></span>
            </div>

            <nav class="admin-nav" aria-label="Navigasi petugas">
                <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.dashboard')])>
                    <span>Ikhtisar</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" @class(['is-active' => request()->routeIs('admin.reports.*')])>
                    <span>Kelola laporan</span>
                </a>
                @if(auth()->user()->role === \App\Enums\UserRole::SuperAdmin)
                    <p class="admin-nav__label">Pengaturan sistem</p>
                    <a href="{{ route('admin.master.index') }}" @class(['is-active' => request()->routeIs('admin.master.*')])>
                        <span>Data master</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" @class(['is-active' => request()->routeIs('admin.users.*')])>
                        <span>Akun petugas</span>
                    </a>
                @endif
            </nav>

            <div class="admin-account">
                <span class="admin-account__avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="admin-account__copy">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->role->label() }}</small>
                </span>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-logout" title="Keluar">Keluar</button>
                </form>
            </div>
        </aside>

        <main id="admin-content" class="admin-main" tabindex="-1">
            <header class="admin-page-head">
                <div>
                    <p class="admin-eyebrow">@yield('eyebrow', 'Operasional desa')</p>
                    <h1>@yield('page-title', 'Ikhtisar')</h1>
                </div>
                <div class="admin-page-actions">@yield('page-actions')</div>
            </header>

            @if(session('status'))
                <div class="admin-notice admin-notice--success" role="status">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="admin-notice admin-notice--danger" role="alert" aria-live="assertive">
                    <strong>Periksa kembali data berikut:</strong>
                    <ul>
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
