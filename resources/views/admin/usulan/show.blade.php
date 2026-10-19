@extends('layouts.panel-petugas')

@php
    $daftarMedia = $usulan->relationLoaded('media') ? $usulan->media : collect();
    $daftarRiwayat = $usulan->relationLoaded('riwayat') ? $usulan->riwayat : collect();
    $tanggalKirim = ($usulan->dikirim_pada ?? $usulan->created_at)?->translatedFormat('d F Y, H:i');
    $jenisLabel = $usulan->jenis instanceof \App\Enums\JenisUsulan ? $usulan->jenis->label() : ($usulan->jenis ?? '—');
    $wargaMedia = $daftarMedia->whereNull('uploaded_by');
    $petugasMedia = $daftarMedia->whereNotNull('uploaded_by');
@endphp

@section('title', $usulan->kode)
@section('page-title', $usulan->kode)
@section('eyebrow', 'Detail usulan warga')
@section('page-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.usulan.index') }}" class="action action--quiet action--sm">Kembali ke daftar</a>
        <form method="POST" action="{{ route('admin.usulan.destroy', $usulan) }}" onsubmit="return confirm('Hapus usulan {{ $usulan->kode }}? Tindakan ini tidak dapat dibatalkan dari panel.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="action action--danger action--sm">Hapus usulan</button>
        </form>
    </div>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <x-usulan.status :status="$usulan->status" />
        <span class="status" data-status-usulan="{{ $usulan->tampil_publik ? 'selesai' : 'baru' }}">{{ $usulan->tampil_publik ? 'Tampil publik' : 'Belum tampil' }}</span>
        @if($tanggalKirim)
            <span class="text-xs text-muted">Masuk {{ $tanggalKirim }} WIB</span>
        @endif
    </div>

    <div class="admin-detail-grid">
        <div class="admin-stack">
            <section class="admin-panel">
                <header class="admin-panel__head"><h2>{{ $usulan->judul }}</h2></header>
                <div class="admin-panel__body">
                    <h3 class="admin-section-title">Isi usulan</h3>
                    <p class="admin-description">{{ $usulan->isi }}</p>
                    <dl class="admin-data-grid mt-6 border-t border-border pt-5">
                        <div><dt>Nama pengusul</dt><dd>{{ $usulan->nama_pengusul ?: 'Anonim' }}</dd></div>
                        <div><dt>WhatsApp</dt><dd>
                            @if($usulan->telepon_pengusul)
                                <a href="https://wa.me/{{ preg_replace('/\D+/', '', $usulan->telepon_pengusul) }}" target="_blank" rel="noopener" class="text-primary underline underline-offset-2">{{ $usulan->telepon_pengusul }}</a>
                            @else
                                Tidak dicantumkan
                            @endif
                        </dd></div>
                        <div><dt>Dusun</dt><dd>{{ $usulan->dusun?->name ?? '—' }}</dd></div>
                        <div><dt>Jenis usulan</dt><dd>{{ $jenisLabel }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Dokumentasi foto</h2><span class="text-xs text-muted">{{ $daftarMedia->count() }} foto</span></header>
                <div class="admin-panel__body space-y-6">
                    <div>
                        <h3 class="admin-section-title">Foto dari Pengusul / Warga ({{ $wargaMedia->count() }})</h3>
                        @if($wargaMedia->isEmpty())
                            <p class="text-xs text-muted mt-2">Pengusul tidak melampirkan foto pendukung saat mengirim usulan.</p>
                        @else
                            <div class="admin-media-grid mt-2">
                                @foreach($wargaMedia as $media)
                                    <a href="{{ route('admin.usulan.media.show', $media) }}" target="_blank" rel="noopener"><img src="{{ route('admin.usulan.media.show', $media) }}" alt="Foto usulan {{ $loop->iteration }}" loading="lazy"></a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-border pt-4">
                        <h3 class="admin-section-title">Bukti Tindak Lanjut oleh Petugas Desa ({{ $petugasMedia->count() }})</h3>
                        @if($petugasMedia->isEmpty())
                            <p class="text-xs text-muted mt-2">Belum ada foto bukti tindak lanjut yang diunggah petugas desa.</p>
                        @else
                            <div class="admin-media-grid mt-2">
                                @foreach($petugasMedia as $media)
                                    <a href="{{ route('admin.usulan.media.show', $media) }}" target="_blank" rel="noopener"><img src="{{ route('admin.usulan.media.show', $media) }}" alt="Bukti usulan {{ $loop->iteration }}" loading="lazy"></a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Riwayat tindak lanjut</h2></header>
                <div class="admin-panel__body">
                    @if($daftarRiwayat->isEmpty())
                        <p class="admin-empty">Belum ada riwayat tercatat.</p>
                    @else
                        <ol class="admin-timeline">
                            @foreach($daftarRiwayat as $riwayat)
                                @php
                                    $waktuRiwayat = ($riwayat->created_at ?? now());
                                    $statusRiwayat = $riwayat->status instanceof \App\Enums\StatusUsulan ? $riwayat->status->label() : ($riwayat->status ?? '—');
                                @endphp
                                <li>
                                    <time datetime="{{ $waktuRiwayat->toIso8601String() }}">{{ $waktuRiwayat->translatedFormat('d M Y, H:i') }} · {{ $riwayat->pengubah?->name ?? 'Sistem' }}</time>
                                    <p><strong>{{ $statusRiwayat }}</strong></p>
                                    @if($riwayat->catatan_publik)<p>Untuk warga: {{ $riwayat->catatan_publik }}</p>@endif
                                    @if($riwayat->catatan_internal)<p class="text-muted">Catatan internal: {{ $riwayat->catatan_internal }}</p>@endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </section>
        </div>

        <aside class="admin-stack">
            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Perbarui tindak lanjut</h2></header>
                <form action="{{ route('admin.usulan.alur.update', $usulan) }}" method="POST" enctype="multipart/form-data" class="admin-panel__body admin-form-grid" data-submit-once>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="versi_alur" value="{{ (int) ($usulan->versi_alur ?? 0) }}">

                    <div class="field field--full">
                        <label for="status" class="field__label">Status tindak lanjut</label>
                        <select id="status" name="status" class="control" required>
                            @foreach($statuses as $item)
                                <option value="{{ $item->value }}" @selected(old('status', $usulan->status?->value ?? $usulan->status) === $item->value)>{{ $item->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field field--full">
                        <label for="catatan_publik" class="field__label">Kabar untuk warga</label>
                        <textarea id="catatan_publik" name="catatan_publik" class="control min-h-28" maxlength="2000" placeholder="Contoh: Usulan masuk pembahasan musyawarah dusun bulan depan.">{{ old('catatan_publik', $usulan->catatan_publik) }}</textarea>
                        <p class="field__hint">Warga membaca catatan ini pada halaman cek usulan.</p>
                        @error('catatan_publik')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field field--full">
                        <label for="catatan_internal" class="field__label">Catatan internal</label>
                        <textarea id="catatan_internal" name="catatan_internal" class="control min-h-24" maxlength="2000" placeholder="Catatan khusus petugas, tidak tampil ke publik.">{{ old('catatan_internal') }}</textarea>
                        @error('catatan_internal')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field field--full">
                        <label for="bukti_photos" class="field__label">Foto bukti tindak lanjut / realisasi <span class="font-normal text-muted">(opsional, maks 5 foto)</span></label>
                        <input id="bukti_photos" name="bukti_photos[]" type="file" class="control" accept="image/jpeg,image/png,image/webp" multiple>
                        <p class="field__hint">Unggah dokumentasi foto tindak lanjut nyata dari pihak desa (JPG, PNG, atau WEBP, maks 5 MB per foto).</p>
                        @error('bukti_photos')<p class="field__error">{{ $message }}</p>@enderror
                        @error('bukti_photos.*')<p class="field__error">{{ $message }}</p>@enderror
                    </div>
                    <label class="field--full flex min-h-12 items-center gap-3 text-sm">
                        <input type="hidden" name="tampil_publik" value="0">
                        <input type="checkbox" name="tampil_publik" value="1" class="size-4 accent-padelegan-600" @checked(old('tampil_publik', $usulan->tampil_publik))>
                        Tampilkan usulan ini di halaman publik
                    </label>
                    <p class="field--full field__hint">Centang dan isi kabar untuk menampilkan usulan ke publik. Identitas pengusul tetap dirahasiakan.</p>
                    <div class="admin-form-actions">
                        <button type="submit" class="action action--primary">Simpan perubahan</button>
                    </div>
                </form>
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Informasi tindak lanjut</h2></header>
                <div class="admin-panel__body">
                    <dl class="admin-data-grid">
                        <div><dt>Ditangani oleh</dt><dd>{{ $usulan->penindak?->name ?? 'Belum ada' }}</dd></div>
                        <div><dt>Dipublikasikan</dt><dd>{{ $usulan->dipublikasikan_pada?->translatedFormat('d M Y, H:i') ?? 'Belum ditampilkan' }}</dd></div>
                    </dl>
                </div>
            </section>
        </aside>
    </div>
@endsection

