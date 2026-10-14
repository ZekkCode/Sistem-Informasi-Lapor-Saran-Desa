@props(['compact' => false])

<footer class="{{ $compact ? 'mt-0' : 'mt-10' }} border-t border-padelegan-800/20 bg-padelegan-900 text-white">
    @if ($compact)
        <div class="site-shell py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="site-footer-brand">
                    <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="site-footer-brand__crest" loading="lazy">
                    <div>
                        <p class="site-footer-brand__name text-xs sm:text-sm font-semibold">Sistem Lapor Padelegan</p>
                        <p class="site-footer-brand__service text-[11px] text-white/60">Pemerintah Desa Padelegan &bull; Pademawu, Pamekasan</p>
                    </div>
                </div>

                <nav class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-medium text-white/75" aria-label="Tautan footer">
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('reports.create') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        <span>Buat laporan</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('reports.track') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <span>Cek status</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('usulan.create') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
                        <span>Kirim usulan</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('public-reports.index') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Laporan desa</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('public-usulan.index') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span>Usulan warga</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('about') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Tentang</span>
                    </a>
                    <a class="inline-flex items-center gap-1.5 transition hover:text-white" href="{{ route('site-support') }}">
                        <svg class="h-3.5 w-3.5 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>Bantuan situs</span>
                    </a>
                </nav>
            </div>
        </div>

        <div class="border-t border-white/10 bg-padelegan-950/60">
            <div class="site-shell flex flex-col gap-2.5 py-3 text-[11px] text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span class="font-medium text-white/80">&copy; {{ now()->year }} Pemerintah Desa Padelegan</span>
                    <span class="text-white/30">&bull;</span>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-1 hover:text-white underline decoration-white/30 underline-offset-2">
                        <svg class="h-3 w-3 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <span>Tim KKN</span>
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    {{-- Zakaria Mujur / Zekk Code Developer Credit --}}
                    <a href="https://github.com/zekkcode" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[11px] text-emerald-400/80 hover:text-emerald-300 transition" title="Pengembang Web: Zakaria Mujur (Zekk Code)">
                        <svg class="h-3 w-3" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>Dev: <strong class="font-semibold text-white/90">Zakaria Mujur (Zekk Code)</strong></span>
                        <span class="sr-only">zekkcode webdev</span>
                    </a>

                    <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1 text-white/70 transition hover:text-white">
                        <svg class="h-3 w-3 text-white/50" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Akses petugas</span>
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="site-shell py-6 sm:py-8 grid gap-6 md:grid-cols-[1.2fr_1.3fr_1fr] lg:gap-8">
            {{-- Kolom 1: Identitas & Kontak Desa Padelegan --}}
            <div>
                <div class="site-footer-brand">
                    <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="" width="418" height="387" class="site-footer-brand__crest" loading="lazy">
                    <div>
                        <p class="site-footer-brand__name text-sm font-semibold">Sistem Lapor Padelegan</p>
                        <p class="site-footer-brand__service text-[11px] text-white/60">Layanan pengaduan Pemerintah Desa Padelegan</p>
                    </div>
                </div>
                <p class="mt-2.5 max-w-sm text-xs leading-5 text-white/70">
                    Sampaikan laporan masalah lingkungan dan usulan pembangunan untuk kemajuan Desa Padelegan.
                </p>

                <div class="mt-3.5 space-y-1.5 text-[11px] text-white/70">
                    <p class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span>Desa Padelegan, Kec. Pademawu, Pamekasan</span>
                    </p>
                    @if(config('padelegan.support_whatsapp'))
                        <p class="flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <a href="https://wa.me/{{ config('padelegan.support_whatsapp') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white underline decoration-white/30">
                                WhatsApp: {{ config('padelegan.support_contact_name') }} ({{ config('padelegan.support_whatsapp_label') }})
                            </a>
                        </p>
                    @endif
                </div>
            </div>

            {{-- Kolom 2: Layanan Warga (Struktur 4 4 Simetris) --}}
            <div>
                <h2 class="text-xs font-bold tracking-wider text-white uppercase">Layanan Warga</h2>
                <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-xs text-white/70">
                    {{-- 4 Item Pengaduan & Bantuan --}}
                    <ul class="space-y-2">
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('reports.create') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <span>Buat laporan</span>
                            </a>
                        </li>
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('reports.track') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <span>Cek laporan</span>
                            </a>
                        </li>
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('public-reports.index') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <span>Laporan desa</span>
                            </a>
                        </li>
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('site-support') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                <span>Bantuan situs</span>
                            </a>
                        </li>
                    </ul>

                    {{-- 4 Item Usulan & Informasi --}}
                    <ul class="space-y-2">
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('usulan.create') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg>
                                <span>Kirim usulan</span>
                            </a>
                        </li>
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('usulan.lacak') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <span>Lacak usulan</span>
                            </a>
                        </li>
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('public-usulan.index') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                <span>Usulan warga</span>
                            </a>
                        </li>
                        <li>
                            <a class="inline-flex items-center gap-1.5 hover:text-white transition" href="{{ route('about') }}">
                                <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span>Tentang & Tim KKN</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Kolom 3: Privasi & Keamanan (Ringkas & Padat) --}}
            <div>
                <h2 class="text-xs font-bold tracking-wider text-white uppercase">Privasi &amp; Keamanan</h2>
                <div class="mt-3 space-y-2">
                    <div class="rounded-lg border border-white/10 bg-white/5 p-2.5 text-[11px] text-white/70 leading-normal">
                        <div class="flex items-center gap-1.5 font-medium text-white mb-0.5">
                            <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Privasi Terjaga</span>
                        </div>
                        <p>Identitas pelapor privat dan hanya untuk verifikasi aparat desa berwenang.</p>
                    </div>

                    <div class="rounded-lg border border-white/10 bg-white/5 p-2.5 text-[11px] text-white/70 leading-normal">
                        <div class="flex items-center gap-1.5 font-medium text-white mb-0.5">
                            <svg class="h-3.5 w-3.5 text-emerald-400 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                            <span>Keamanan Sistem</span>
                        </div>
                        <p>Dilindungi anti-bot dan enkripsi data untuk memastikan keaslian kiriman.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 bg-padelegan-950/60">
            <div class="site-shell flex flex-col gap-2.5 py-3 text-[11px] text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span class="font-medium text-white/80">&copy; {{ now()->year }} Pemerintah Desa Padelegan</span>
                    <span class="text-white/30">&bull;</span>
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-1 hover:text-white underline decoration-white/30 underline-offset-2">
                        <svg class="h-3 w-3 text-emerald-400" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <span>Tim KKN</span>
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-3 sm:gap-4">
                    {{-- Zakaria Mujur / Zekk Code Developer Credit --}}
                    <a href="https://github.com/zekkcode" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-[11px] text-emerald-400/80 hover:text-emerald-300 transition" title="Pengembang Web: Zakaria Mujur (Zekk Code)">
                        <svg class="h-3 w-3" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>Dev: <strong class="font-semibold text-white/90">Zakaria Mujur (Zekk Code)</strong></span>
                        <span class="sr-only">zekkcode webdev</span>
                    </a>

                    <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1 text-white/70 transition hover:text-white">
                        <svg class="h-3 w-3 text-white/50" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
