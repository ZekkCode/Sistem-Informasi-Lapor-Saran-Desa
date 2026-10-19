@props(['report'])

@php
    $mediaItems = $report->relationLoaded('media') ? $report->media : collect();
    $beforePhotos = $mediaItems->filter(fn ($m) => $m->media_type === \App\Enums\ReportMediaType::BeforePhoto);
    $afterPhotos = $mediaItems->filter(fn ($m) => $m->media_type === \App\Enums\ReportMediaType::AfterPhoto);
    $hasAnyPhoto = $beforePhotos->isNotEmpty() || $afterPhotos->isNotEmpty();
@endphp

@if ($hasAnyPhoto)
    <section class="report-evidence" aria-labelledby="evidence-heading">
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
                            <a
                                href="{{ route('public-reports.media', $photo) }}"
                                target="_blank"
                                rel="noopener"
                                class="report-evidence__thumb-link group"
                                title="Buka foto kondisi awal ukuran penuh"
                            >
                                <img
                                    src="{{ route('public-reports.media', $photo) }}"
                                    alt="Foto kondisi awal laporan {{ $report->report_code }} ({{ $loop->iteration }})"
                                    loading="lazy"
                                >
                                <span class="sr-only">Buka foto ukuran penuh</span>
                            </a>
                        @endforeach
                    </div>
                    <p class="report-evidence__caption">Dokumentasi kondisi masalah saat pertama kali dikirim oleh pelapor.</p>
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
                            <a
                                href="{{ route('public-reports.media', $photo) }}"
                                target="_blank"
                                rel="noopener"
                                class="report-evidence__thumb-link group"
                                title="Buka bukti penanganan ukuran penuh"
                            >
                                <img
                                    src="{{ route('public-reports.media', $photo) }}"
                                    alt="Bukti penanganan laporan {{ $report->report_code }} ({{ $loop->iteration }})"
                                    loading="lazy"
                                >
                                <span class="sr-only">Buka foto ukuran penuh</span>
                            </a>
                        @endforeach
                    </div>
                    <p class="report-evidence__caption">Dokumentasi bukti tindak lanjut nyata yang diunggah oleh petugas desa Padelegan.</p>
                @else
                    <div class="report-evidence__pending-box">
                        <svg class="mx-auto size-5 text-padelegan-900/40 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Petugas desa akan melampirkan foto bukti setelah penanganan di lapangan selesai.
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
