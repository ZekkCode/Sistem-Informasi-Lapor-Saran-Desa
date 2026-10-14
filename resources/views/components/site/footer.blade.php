@props(['compact' => false])

<footer class="{{ $compact ? 'mt-0' : 'mt-20' }} border-t border-padelegan-800/20 bg-padelegan-900 text-white">
    @if ($compact)
        <div class="site-shell py-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="site-footer-brand">
                    <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="site-footer-brand__crest" loading="lazy">
                    <div>
                        <p class="site-footer-brand__name">Sistem Lapor Padelegan</p>
                        <p class="site-footer-brand__service">Layanan pengaduan Pemerintah Desa Padelegan</p>
                        <p class="mt-1 flex items-center gap-1.5 text-xs text-white/60">
                            <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            Kecamatan Pademawu, Kabupaten Pamekasan
                        </p>
                    </div>
                </div>

                <nav class="flex flex-wrap items-center gap-x-5 gap-y-2.5 text-xs sm:text-sm font-medium text-white/75" aria-label="Tautan footer">
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('reports.create') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400/80" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span>Buat laporan</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('reports.track') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400/80" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <span>Cek status</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('usulan.create') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400/80" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
                        <span>Kirim usulan</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('public-reports.index') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400/80" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Laporan desa</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('public-usulan.index') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400/80" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span>Usulan warga</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('about') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400/80" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Tentang</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('site-support') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400/80" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>Bantuan situs</span>
                    </a>
                </nav>
            </div>
        </div>

        <div class="border-t border-white/10 bg-padelegan-950/60">
            <div class="site-shell flex flex-col gap-4 py-4 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span>&copy; {{ now()->year }} Pemerintah Desa Padelegan</span>
                    <span class="text-white/30">&bull;</span>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-1 hover:text-white underline decoration-white/30 underline-offset-4">
                        <svg class="h-3 w-3 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <span>Tim KKN</span>
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    {{-- Rebranding ZekkCode --}}
                    <a href="https://github.com/zekkcode" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-2.5 py-1 text-xs font-semibold text-white/90 transition hover:border-emerald-400/50 hover:bg-white/10 hover:text-white" title="Kunjungi GitHub ZekkCode">
                        <span class="flex h-4 w-4 items-center justify-center rounded bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500/30">
                            <svg class="h-2.5 w-2.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                        </span>
                        <span>zekkcode by web</span>
                    </a>

                    <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-white/70 transition hover:text-white">
                        <svg class="h-3.5 w-3.5 text-white/50" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Akses petugas</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="site-shell grid gap-10 py-12 md:grid-cols-[1.4fr_1fr_1.1fr]">
            {{-- Kolom Identitas & Kontak --}}
            <div>
                <div class="site-footer-brand">
                    <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="site-footer-brand__crest" loading="lazy">
                    <div>
                        <p class="site-footer-brand__name">Sistem Lapor Padelegan</p>
                        <p class="site-footer-brand__service">Layanan pengaduan Pemerintah Desa Padelegan</p>
                    </div>
                </div>
                <p class="reading-measure mt-4 max-w-md text-sm leading-6 text-white/70">
                    Sampaikan laporan masalah dan usulan pembangunan untuk kemajuan Desa Padelegan.
                </p>

                <div class="mt-5 space-y-2 text-xs text-white/70">
                    <p class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>Desa Padelegan, Kec. Pademawu, Kab. Pamekasan, Jawa Timur</span>
                    </p>
                    @if(config('padelegan.support_whatsapp'))
                        <p class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <a href="https://wa.me/{{ config('padelegan.support_whatsapp') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white underline decoration-white/30">
                                WhatsApp: {{ config('padelegan.support_contact_name') }} ({{ config('padelegan.support_whatsapp_label') }})
                            </a>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Kolom Layanan --}}
            <div>
                <h2 class="text-sm font-semibold tracking-wider text-white uppercase">Layanan Warga</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-white/70">
                    <li>
                        <a class="inline-flex items-center gap-2 hover:text-white transition" href="{{ route('reports.create') }}">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Buat laporan</span>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-2 hover:text-white transition" href="{{ route('reports.track') }}">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Cek laporan</span>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-2 hover:text-white transition" href="{{ route('usulan.create') }}">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
                            <span>Kirim usulan</span>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-2 hover:text-white transition" href="{{ route('public-reports.index') }}">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>Laporan desa</span>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-2 hover:text-white transition" href="{{ route('public-usulan.index') }}">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span>Usulan warga</span>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-2 hover:text-white transition" href="{{ route('about') }}">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Tentang & Tim KKN</span>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-2 hover:text-white transition" href="{{ route('site-support') }}">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>Bantuan situs</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Kolom Privasi & Keamanan --}}
            <div>
                <h2 class="text-sm font-semibold tracking-wider text-white uppercase">Privasi & Keamanan</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 text-xs text-white/75 leading-relaxed">
                        <div class="flex items-center gap-2 font-medium text-white mb-1.5">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Privasi Terjaga</span>
                        </div>
                        <p>Hanya petugas desa berwenang yang dapat melihat nama dan nomor WhatsApp pelapor. Identitas tidak pernah ditampilkan ke publik.</p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-white/5 p-3.5 text-xs text-white/75 leading-relaxed">
                        <div class="flex items-center gap-2 font-medium text-white mb-1.5">
                            <svg class="h-4 w-4 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            <span>Keamanan Sistem</span>
                        </div>
                        <p>Pengiriman dilindungi sistem anti-bot dan enkripsi data untuk memastikan keaslian kiriman warga.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 bg-padelegan-950/60">
            <div class="site-shell flex flex-col gap-4 py-5 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span>&copy; {{ now()->year }} Pemerintah Desa Padelegan</span>
                    <span class="text-white/30">&bull;</span>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-1 hover:text-white underline decoration-white/30 underline-offset-4">
                        <svg class="h-3 w-3 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <span>Tim KKN</span>
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    {{-- Rebranding ZekkCode --}}
                    <a href="https://github.com/zekkcode" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-semibold text-white/90 transition hover:border-emerald-400/50 hover:bg-white/10 hover:text-white" title="Kunjungi GitHub ZekkCode">
                        <span class="flex h-4 w-4 items-center justify-center rounded bg-emerald-500/20 text-emerald-400 group-hover:bg-emerald-500/30">
                            <svg class="h-2.5 w-2.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                        </span>
                        <span>zekkcode by web</span>
                    </a>

                    <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-white/70 transition hover:text-white">
                        <svg class="h-3.5 w-3.5 text-white/50" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Akses petugas</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
</footer>
