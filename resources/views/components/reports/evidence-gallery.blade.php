@props(['report'])

@php
    $mediaItems = $report->relationLoaded('media') ? $report->media : $report->media()->get();
    $beforePhotos = $mediaItems->filter(fn ($m) => $m->media_type === \App\Enums\ReportMediaType::BeforePhoto);
    $afterPhotos = $mediaItems->filter(fn ($m) => $m->media_type === \App\Enums\ReportMediaType::AfterPhoto);
    $hasAnyPhoto = $beforePhotos->isNotEmpty() || $afterPhotos->isNotEmpty();
@endphp

@if ($hasAnyPhoto)
    <div
        x-data="{
            activePhoto: null,
            activeTitle: '',
            activeSubtitle: '',
            openModal(url, title, subtitle) {
                this.activePhoto = url;
                this.activeTitle = title;
                this.activeSubtitle = subtitle;
                document.body.style.overflow = 'hidden';
            },
            closeModal() {
                this.activePhoto = null;
                document.body.style.overflow = '';
            }
        }"
        @keydown.escape.window="closeModal()"
        class="report-evidence"
        aria-labelledby="evidence-heading"
    >
        <div class="report-evidence__header">
            <div>
                <h2 id="evidence-heading" class="report-evidence__title">Dokumentasi dan Bukti Penanganan</h2>
                <p class="mt-1 text-xs text-padelegan-900/60">Transparansi kondisi laporan warga dan hasil tindak lanjut petugas di lapangan.</p>
            </div>
            @if ($afterPhotos->isNotEmpty())
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200">
                    <svg class="size-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/></svg>
                    Sudah Dilampirkan Bukti
                </span>
            @endif
        </div>

        <div class="report-evidence__grid report-evidence__grid--comparison">
            {{-- Kolom Sebelum (Foto Warga) --}}
            <div class="report-evidence__card">
                <div class="report-evidence__card-head">
                    <span class="font-semibold text-sm text-padelegan-900">Kondisi Awal</span>
                    <span class="report-evidence__badge report-evidence__badge--before">
                        Foto Warga ({{ $beforePhotos->count() }})
                    </span>
                </div>
                @if ($beforePhotos->isNotEmpty())
                    <div class="report-evidence__thumbs">
                        @foreach ($beforePhotos as $photo)
                            @php($photoUrl = route('public-reports.media', $photo))
                            <a
                                href="{{ $photoUrl }}"
                                target="_blank"
                                rel="noopener"
                                class="report-evidence__thumb-link group cursor-zoom-in"
                                title="Klik untuk memperbesar foto kondisi awal"
                                @click.prevent="openModal('{{ $photoUrl }}', 'Foto Kondisi Awal (Warga)', 'Laporan {{ $report->report_code }} - Foto ke-{{ $loop->iteration }}')"
                            >
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="Foto kondisi awal laporan {{ $report->report_code }} ({{ $loop->iteration }})"
                                    loading="lazy"
                                >
                                <span class="sr-only">Perbesar foto kondisi awal</span>
                            </a>
                        @endforeach
                    </div>
                    <p class="report-evidence__caption">Dokumentasi kondisi masalah saat pertama kali dikirim oleh pelapor. Klik foto untuk memperbesar.</p>
                @else
                    <div class="report-evidence__pending-box">
                        Pelapor tidak menyertakan foto kondisi awal saat mengirim laporan.
                    </div>
                @endif
            </div>

            {{-- Kolom Sesudah (Bukti Penanganan Petugas) --}}
            <div class="report-evidence__card">
                <div class="report-evidence__card-head">
                    <span class="font-semibold text-sm text-padelegan-900">Hasil Penanganan</span>
                    <span class="report-evidence__badge report-evidence__badge--after">
                        Bukti Desa ({{ $afterPhotos->count() }})
                    </span>
                </div>
                @if ($afterPhotos->isNotEmpty())
                    <div class="report-evidence__thumbs">
                        @foreach ($afterPhotos as $photo)
                            @php($photoUrl = route('public-reports.media', $photo))
                            <a
                                href="{{ $photoUrl }}"
                                target="_blank"
                                rel="noopener"
                                class="report-evidence__thumb-link group cursor-zoom-in"
                                title="Klik untuk memperbesar bukti penanganan"
                                @click.prevent="openModal('{{ $photoUrl }}', 'Bukti Penanganan (Pemerintah Desa)', 'Tindak lanjut laporan {{ $report->report_code }} - Bukti ke-{{ $loop->iteration }}')"
                            >
                                <img
                                    src="{{ $photoUrl }}"
                                    alt="Bukti penanganan laporan {{ $report->report_code }} ({{ $loop->iteration }})"
                                    loading="lazy"
                                >
                                <span class="sr-only">Perbesar bukti penanganan</span>
                            </a>
                        @endforeach
                    </div>
                    <p class="report-evidence__caption">Dokumentasi bukti tindak lanjut nyata yang diunggah oleh petugas desa Padelegan. Klik foto untuk memperbesar.</p>
                @else
                    <div class="report-evidence__pending-box">
                        <svg class="mx-auto size-5 text-padelegan-900/40 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Petugas desa akan melampirkan foto bukti setelah penanganan di lapangan selesai.
                    </div>
                @endif
            </div>
        </div>

        {{-- Lightbox Modal Popup Pratinjau Foto --}}
        <template x-teleport="body">
            <div
                x-show="activePhoto !== null"
                x-cloak
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-label="Pratinjau foto dokumentasi laporan"
            >
                {{-- Backdrop Overlay --}}
                <div
                    x-show="activePhoto !== null"
                    x-transition:enter="transition-opacity ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/85 backdrop-blur-xs"
                    @click="closeModal()"
                    aria-hidden="true"
                ></div>

                {{-- Modal Container --}}
                <div
                    x-show="activePhoto !== null"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative z-10 flex flex-col max-w-4xl w-full max-h-[92vh] overflow-hidden rounded-xl bg-[#0f261f] border border-white/15 shadow-2xl text-white"
                    @click.stop
                >
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-white/10 px-4 py-3 sm:px-6">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-emerald-400 font-bold" x-text="activeTitle"></p>
                            <p class="text-sm font-medium text-white/80 mt-0.5" x-text="activeSubtitle"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                :href="activePhoto"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/20 transition"
                                title="Buka gambar asli di tab baru"
                            >
                                <svg class="size-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Tab Baru
                            </a>
                            <button
                                type="button"
                                @click="closeModal()"
                                class="inline-flex min-h-[44px] min-w-[44px] items-center justify-center rounded-lg border border-white/20 bg-white/10 text-white/80 hover:bg-white/20 hover:text-white transition"
                                aria-label="Tutup pratinjau foto"
                            >
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body Gambar --}}
                    <div class="flex-1 overflow-auto p-2 sm:p-4 flex items-center justify-center bg-black/60">
                        <img
                            :src="activePhoto"
                            alt="Foto dokumentasi laporan"
                            class="max-h-[75vh] w-auto max-w-full object-contain rounded-md"
                        >
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
