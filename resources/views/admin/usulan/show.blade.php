@extends('layouts.panel-petugas')

@section('title', $usulan->kode)
@section('page-title', $usulan->kode)
@section('eyebrow', 'Detail usulan warga')
@section('page-actions')
    <a href="{{ route('admin.usulan.index') }}" class="action action--quiet action--sm">Kembali ke daftar</a>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <x-usulan.status :status="$usulan->status" />
        <span class="status" data-status-usulan="{{ $usulan->tampil_publik ? 'selesai' : 'baru' }}">{{ $usulan->tampil_publik ? 'Tampil publik' : 'Belum tampil' }}</span>
        <span class="text-xs text-muted">Masuk {{ $usulan->dikirim_pada->translatedFormat('d F Y, H:i') }} WIB</span>
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
                        <div><dt>Dusun</dt><dd>{{ $usulan->dusun->name }}</dd></div>
                        <div><dt>Jenis usulan</dt><dd>{{ $usulan->jenis->label() }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Riwayat tindak lanjut</h2></header>
                <div class="admin-panel__body">
                    <ol class="admin-timeline">
                        @foreach($usulan->riwayat as $riwayat)
                            <li>
                                <time datetime="{{ $riwayat->created_at->toIso8601String() }}">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }} · {{ $riwayat->pengubah?->name ?? 'Sistem' }}</time>
                                <p><strong>{{ $riwayat->status->label() }}</strong></p>
                                @if($riwayat->catatan_publik)<p>Untuk warga: {{ $riwayat->catatan_publik }}</p>@endif
                                @if($riwayat->catatan_internal)<p class="text-muted">Catatan internal: {{ $riwayat->catatan_internal }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        </div>

        <aside class="admin-stack">
            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Perbarui tindak lanjut</h2></header>
                <form action="{{ route('admin.usulan.alur.update', $usulan) }}" method="POST" class="admin-panel__body admin-form-grid" data-submit-once>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="versi_alur" value="{{ $usulan->versi_alur }}">

                    <div class="field field--full">
                        <label for="status" class="field__label">Status tindak lanjut</label>
                        <select id="status" name="status" class="control" required>
                            @foreach($statuses as $item)<option value="{{ $item->value }}" @selected(old('status', $usulan->status->value) === $item->value)>{{ $item->label() }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field field--full">
                        <label for="catatan_publik" class="field__label">Kabar untuk warga</label>
                        <textarea id="catatan_publik" name="catatan_publik" class="control min-h-28" maxlength="2000" placeholder="Contoh: Usulan masuk pembahasan musyawarah dusun bulan depan.">{{ old('catatan_publik', $usulan->catatan_publik) }}</textarea>
                        <p class="field__hint">Warga membaca catatan ini pada halaman cek usulan.</p>
                    </div>
                    <div class="field field--full">
                        <label for="catatan_internal" class="field__label">Catatan internal</label>
                        <textarea id="catatan_internal" name="catatan_internal" class="control min-h-24" maxlength="2000" placeholder="Hanya terlihat oleh petugas.">{{ old('catatan_internal') }}</textarea>
                    </div>
                    <label class="field--full flex min-h-12 items-center gap-3 text-sm">
                        <input type="hidden" name="tampil_publik" value="0">
                        <input type="checkbox" name="tampil_publik" value="1" class="size-4 accent-padelegan-600" @checked(old('tampil_publik', $usulan->tampil_publik))>
                        Tampilkan usulan ini di halaman publik
                    </label>
                    <p class="field--full field__hint">Usulan tampil ke publik hanya setelah Anda mencentang dan mengisi kabar untuk warga. Identitas pengusul tidak pernah ditampilkan.</p>
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
