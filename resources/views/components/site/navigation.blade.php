<header class="site-header" x-data="{ open: false }">
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

        <button type="button" class="site-menu-button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-navigation">
            <span class="sr-only">Buka navigasi</span>
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
            </svg>
        </button>
    </div>

    <x-ui.sheet id="mobile-navigation" open="open" close="open = false" title="Menu">
        <nav class="grid gap-1 p-4" aria-label="Navigasi seluler">
            <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif class="border-b border-border px-2 py-3 font-medium">Beranda</a>
            <a href="{{ route('public-reports.index') }}" @if (request()->routeIs('public-reports.*')) aria-current="page" @endif class="border-b border-border px-2 py-3 font-medium">Laporan Desa</a>
            <a href="{{ route('public-usulan.index') }}" @if (request()->routeIs('public-usulan.*')) aria-current="page" @endif class="border-b border-border px-2 py-3 font-medium">Usulan Warga</a>
            <a href="{{ route('about') }}" @if (request()->routeIs('about')) aria-current="page" @endif class="border-b border-border px-2 py-3 font-medium">Tentang</a>
            <div class="mt-5 grid gap-2">
                <x-ui.action :href="route('mulai')">Sampaikan</x-ui.action>
                <x-ui.action :href="route('reports.track')" variant="secondary">Cek laporan</x-ui.action>
                <x-ui.action :href="route('usulan.lacak')" variant="secondary">Cek usulan</x-ui.action>
            </div>
        </nav>
    </x-ui.sheet>
</header>
