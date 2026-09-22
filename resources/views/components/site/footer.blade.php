@props(['compact' => false])

<footer class="{{ $compact ? 'mt-0' : 'mt-24' }} border-t border-padelegan-100 bg-padelegan-900 text-white">
    @if ($compact)
        <div class="site-shell flex flex-col gap-5 py-7 text-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="site-footer-brand">
                <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="site-footer-brand__crest" loading="lazy">
                <div>
                    <p class="site-footer-brand__name">Sistem Lapor Padelegan</p>
                    <p class="site-footer-brand__service">Layanan pengaduan Pemerintah Desa Padelegan</p>
                </div>
            </div>
            <nav class="flex flex-wrap gap-x-5 gap-y-2 font-medium text-white/75" aria-label="Tautan footer">
                <a class="hover:text-white" href="{{ route('reports.create') }}">Buat laporan</a>
                <a class="hover:text-white" href="{{ route('reports.track') }}">Cek status</a>
                <a class="hover:text-white" href="{{ route('public-reports.index') }}">Laporan desa</a>
                <a class="hover:text-white" href="{{ route('site-support') }}">Bantuan situs</a>
            </nav>
        </div>
    @else
    <div class="site-shell grid gap-10 py-12 md:grid-cols-[1.4fr_1fr_1fr]">
        <div>
            <div class="site-footer-brand">
                <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="site-footer-brand__crest" loading="lazy">
                <div>
                    <p class="site-footer-brand__name">Sistem Lapor Padelegan</p>
                    <p class="site-footer-brand__service">Layanan pengaduan Pemerintah Desa Padelegan</p>
                </div>
            </div>
            <p class="reading-measure mt-4 max-w-md text-sm leading-7 text-white/70">Sampaikan masalah desa dan ikuti pembaruan dari petugas.</p>
        </div>
        <div>
            <h2 class="text-sm font-semibold">Layanan</h2>
            <ul class="mt-4 space-y-3 text-sm text-white/70">
                <li><a class="hover:text-white" href="{{ route('reports.create') }}">Buat laporan</a></li>
                <li><a class="hover:text-white" href="{{ route('reports.track') }}">Cek laporan</a></li>
                <li><a class="hover:text-white" href="{{ route('public-reports.index') }}">Laporan desa</a></li>
                <li><a class="hover:text-white" href="{{ route('site-support') }}">Bantuan situs</a></li>
            </ul>
        </div>
        <div>
            <h2 class="text-sm font-semibold">Privasi warga</h2>
            <p class="mt-4 text-sm leading-7 text-white/70">Hanya petugas desa yang dapat melihat nama dan nomor WhatsApp pelapor. Halaman publik tidak memuat data tersebut.</p>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="site-shell flex flex-col gap-2 py-5 text-xs text-white/55 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} Pemerintah Desa Padelegan.</p>
            <a href="{{ route('admin.login') }}" class="hover:text-white">Akses petugas</a>
        </div>
    </div>
    @endif
</footer>
