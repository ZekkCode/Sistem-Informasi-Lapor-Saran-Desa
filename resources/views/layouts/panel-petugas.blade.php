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
        <a href="{{ auth()->user()->canAccessReports() ? route('admin.dashboard') : route('admin.usulan.index') }}" class="admin-mobile-brand" aria-label="SISTEM LAPOR PADELEGAN, Dasbor">
            <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="admin-brand__crest">
            <div class="admin-mobile-brand__text">
                <strong>SISTEM LAPOR PADELEGAN</strong>
                <small>PANEL PETUGAS DESA</small>
            </div>
        </a>
        <button
            type="button"
            class="admin-menu-button"
            @click.stop="navigationOpen = !navigationOpen"
            :aria-expanded="navigationOpen.toString()"
            aria-controls="admin-navigation"
            aria-label="Buka menu navigasi"
        >
            <span class="sr-only">Toggle navigasi</span>
            <svg x-show="!navigationOpen" class="w-5 h-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
            </svg>
            <svg x-cloak x-show="navigationOpen" class="w-5 h-5 text-emerald-300" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </header>

    {{-- Backdrop Overlay for Mobile Drawer --}}
    <div
        x-cloak
        x-show="navigationOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="admin-backdrop"
        @click="navigationOpen = false"
        aria-hidden="true"
    ></div>

    <div class="admin-shell">
        <aside
            id="admin-navigation"
            class="admin-sidebar"
            :class="{ 'is-open': navigationOpen }"
            @click.outside="if (navigationOpen) navigationOpen = false"
            @keydown.escape.window="navigationOpen = false"
        >
            {{-- Mobile Drawer Header with Close Button --}}
            <div class="admin-sidebar__mobile-head">
                <div class="admin-brand__mobile-inner">
                    <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="admin-brand__crest">
                    <div class="admin-brand__text">
                        <strong>SISTEM LAPOR PADELEGAN</strong>
                        <small>PANEL PETUGAS DESA</small>
                    </div>
                </div>
                <button
                    type="button"
                    class="admin-sidebar__close-btn"
                    @click="navigationOpen = false"
                    aria-label="Tutup navigasi"
                >
                    <svg class="w-4 h-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <a href="{{ auth()->user()->canAccessReports() ? route('admin.dashboard') : route('admin.usulan.index') }}" class="admin-brand" aria-label="SISTEM LAPOR PADELEGAN, Dasbor">
                <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="admin-brand__crest">
                <div class="admin-brand__text">
                    <strong>SISTEM LAPOR PADELEGAN</strong>
                    <small>PANEL PETUGAS DESA</small>
                </div>
            </a>

            <nav class="admin-nav" aria-label="Navigasi petugas">
                @if(auth()->user()->canAccessReports())
                    <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.dashboard')]) @click="navigationOpen = false">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Ikhtisar</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}" @class(['is-active' => request()->routeIs('admin.reports.*')]) @click="navigationOpen = false">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Kelola laporan</span>
                    </a>
                @endif
                @if(auth()->user()->canAccessUsulan())
                    <a href="{{ route('admin.usulan.index') }}" @class(['is-active' => request()->routeIs('admin.usulan.*')]) @click="navigationOpen = false">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Usulan &amp; saran</span>
                    </a>
                @endif
                <a href="{{ route('admin.formulir.index') }}" @class(['is-active' => request()->routeIs('admin.formulir.*')]) @click="navigationOpen = false">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Kunci formulir</span>
                </a>
                @if(auth()->user()->role === \App\Enums\UserRole::SuperAdmin)
                    <p class="admin-nav__label">Pengaturan sistem</p>
                    <a href="{{ route('admin.master.index') }}" @class(['is-active' => request()->routeIs('admin.master.*')]) @click="navigationOpen = false">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>Data master</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" @class(['is-active' => request()->routeIs('admin.users.*')]) @click="navigationOpen = false">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Akun petugas</span>
                    </a>
                @endif
            </nav>

            <div class="admin-account">
                <div class="admin-account__info">
                    <span class="admin-account__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <div class="admin-account__copy">
                        <strong title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</strong>
                        <small class="admin-account__role">
                            <span class="admin-account__dot"></span>
                            {{ auth()->user()->role->label() }}
                        </small>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-logout" title="Keluar dari sesi petugas">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <main id="admin-content" class="admin-main" tabindex="-1">
            <header class="admin-page-head">
                <div>
                    <p class="admin-eyebrow">@yield('eyebrow', 'Panel Operasional Desa')</p>
                    <h1>@yield('page-title', 'Ikhtisar')</h1>
                </div>
                <div class="admin-page-actions">@yield('page-actions')</div>
            </header>

            @if(session('status'))
                <div class="admin-notice admin-notice--success flex items-start gap-2.5" role="status">
                    <svg class="w-5 h-5 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>{!! session('status') !!}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="admin-notice admin-notice--danger flex items-start gap-2.5" role="alert">
                    <svg class="w-5 h-5 text-danger shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="admin-notice admin-notice--danger flex items-start gap-2.5" role="alert" aria-live="assertive">
                    <svg class="w-5 h-5 text-danger shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/></svg>
                    <div>
                        <strong>Periksa kembali data pada formulir berikut:</strong>
                        <ul class="mt-1 list-disc list-inside space-y-0.5 text-xs">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
