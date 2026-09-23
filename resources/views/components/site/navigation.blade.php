<header class="site-header">
    <div class="site-shell site-header__inner">
        <a href="{{ route('home') }}" class="site-brand" aria-label="Sistem Lapor Padelegan, kembali ke beranda">
            <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="Lambang Kabupaten Pamekasan" width="418" height="387" class="site-brand__crest">
            <span class="site-brand__copy">
                <strong class="site-brand__name">Sistem Lapor Padelegan</strong>
                <span class="site-brand__service">Layanan pengaduan desa</span>
            </span>
        </a>

        <nav class="site-nav" aria-label="Navigasi utama">
            <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif class="site-nav__link">Beranda</a>
            <a href="{{ route('public-reports.index') }}" @if (request()->routeIs('public-reports.*')) aria-current="page" @endif class="site-nav__link">Laporan Desa</a>
            <a href="{{ route('public-usulan.index') }}" @if (request()->routeIs('public-usulan.*')) aria-current="page" @endif class="site-nav__link">Usulan Warga</a>
            <a href="{{ route('about') }}" @if (request()->routeIs('about')) aria-current="page" @endif class="site-nav__link">Tentang</a>
        </nav>

        <div class="site-header__actions">
            <x-ui.action :href="route('reports.track')" variant="secondary">Cek laporan</x-ui.action>
            <x-ui.action :href="route('usulan.lacak')" variant="secondary">Cek usulan</x-ui.action>
            <x-ui.action :href="route('mulai')">Sampaikan</x-ui.action>
        </div>
    </div>
</header>

<nav class="app-tabbar" aria-label="Navigasi seluler">
    <a href="{{ route('home') }}" class="app-tabbar__link @if (request()->routeIs('home')) is-active @endif" @if (request()->routeIs('home')) aria-current="page" @endif>
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 10.5 12 4l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Beranda</span>
    </a>
    <a href="{{ route('public-reports.index') }}" class="app-tabbar__link @if (request()->routeIs('public-reports.*')) is-active @endif" @if (request()->routeIs('public-reports.*')) aria-current="page" @endif>
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 3h7l4 4v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v4h4M9 12h6M9 16h4" stroke-linecap="round"/></svg>
        <span>Laporan</span>
    </a>
    <a href="{{ route('mulai') }}" class="app-tabbar__link app-tabbar__link--cta @if (request()->routeIs('mulai')) is-active @endif" @if (request()->routeIs('mulai')) aria-current="page" @endif>
        <span class="app-tabbar__cta" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
        </span>
        <span>Sampaikan</span>
    </a>
    <a href="{{ route('public-usulan.index') }}" class="app-tabbar__link @if (request()->routeIs('public-usulan.*')) is-active @endif" @if (request()->routeIs('public-usulan.*')) aria-current="page" @endif>
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 5h14a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-4 4V6a1 1 0 0 1 1-1Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Usulan</span>
    </a>
    <a href="{{ route('about') }}" class="app-tabbar__link @if (request()->routeIs('about')) is-active @endif" @if (request()->routeIs('about')) aria-current="page" @endif>
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 11v5m0-8h.01" stroke-linecap="round"/></svg>
        <span>Tentang</span>
    </a>
</nav>
