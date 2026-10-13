@extends('layouts.panel-petugas')

@section('title', 'Kunci Formulir')
@section('page-title', 'Kunci formulir warga')
@section('eyebrow', 'Pengaturan layanan')

@section('content')
    <p class="admin-intro">Tutup formulir laporan atau usulan saat pengiriman perlu dihentikan. Cek status, laporan desa, dan usulan warga tetap terbuka untuk warga.</p>

    <div class="grid items-start gap-4 lg:grid-cols-2">
        @foreach ($statuses as $status)
            @php
                $formulir = $status->formulir;
                $kode = $formulir->value;
                $kata = $formulir->kataBenda();
                // Isi ulang input lama hanya pada panel yang dikirim.
                $pakaiInputLama = old('formulir') === $kode;
                $kunciAdminDicentang = $pakaiInputLama ? (bool) old('terkunci') : $status->kunciAdmin;
                $catatanTampil = $pakaiInputLama ? old('catatan') : $status->catatan;
                $catatanGagal = $pakaiInputLama && $errors->has('catatan');
            @endphp

            <section class="admin-panel" id="formulir-{{ $kode }}" aria-labelledby="formulir-{{ $kode }}-judul">
                <header class="admin-panel__head">
                    <h2 id="formulir-{{ $kode }}-judul">{{ $formulir->label() }}</h2>
                    <span class="status" data-status-kunci="{{ $status->kode() }}">{{ $status->label() }}</span>
                </header>

                <div class="admin-panel__body">
                    @can('ubahKunciAdmin', $formulir)
                        <form action="{{ route('admin.formulir.kunci-admin.update', $kode) }}" method="POST" class="admin-form-grid" data-submit-once>
                            @csrf
                            @method('PATCH')
                            <h3 class="admin-section-title field--full">Kunci admin</h3>
                            <input type="hidden" name="formulir" value="{{ $kode }}">

                            <div class="field field--full">
                                <label class="flex min-h-12 items-center gap-3 text-sm" for="kunci-admin-{{ $kode }}">
                                    <input type="hidden" name="terkunci" value="0">
                                    <input type="checkbox" role="switch" id="kunci-admin-{{ $kode }}" name="terkunci" value="1" aria-describedby="kunci-admin-{{ $kode }}-hint" class="size-4 accent-padelegan-600" @checked($kunciAdminDicentang)>
                                    Tutup formulir {{ $kata }}
                                </label>
                                <p id="kunci-admin-{{ $kode }}-hint" class="field__hint">Warga melihat pemberitahuan formulir ditutup beserta catatan di bawah.</p>
                            </div>

                            <div class="field field--full">
                                <label for="catatan-{{ $kode }}" class="field__label">Catatan untuk warga <span class="font-normal text-muted">(opsional)</span></label>
                                <textarea
                                    id="catatan-{{ $kode }}"
                                    name="catatan"
                                    maxlength="200"
                                    class="control min-h-24"
                                    aria-describedby="catatan-{{ $kode }}-hint"
                                    @if ($catatanGagal) aria-invalid="true" @endif
                                    placeholder="Contoh: Formulir dibuka kembali Senin pagi."
                                >{{ $catatanTampil }}</textarea>
                                <p id="catatan-{{ $kode }}-hint" class="field__hint">Maksimal 200 karakter, misalnya tanggal formulir dibuka kembali. Catatan terhapus saat formulir dibuka.</p>
                            </div>

                            @if ($status->kunciPengembang)
                                <p class="field--full field__hint">Kunci pengembang aktif. Formulir tetap tertutup walau kunci admin dimatikan.</p>
                            @endif

                            <div class="admin-form-actions">
                                <button type="submit" class="action action--primary">Simpan kunci admin</button>
                            </div>
                        </form>
                    @endcan

                    <div class="mt-6 border-t border-border pt-5">
                        <h3 class="admin-section-title">Kunci pengembang</h3>
                        @can('ubahKunciPengembang', $formulir)
                            <form action="{{ route('admin.formulir.kunci-pengembang.update', $kode) }}" method="POST" class="admin-form-grid" data-submit-once>
                                @csrf
                                @method('PATCH')
                                <div class="field field--full">
                                    <label class="flex min-h-12 items-center gap-3 text-sm" for="kunci-pengembang-{{ $kode }}">
                                        <input type="hidden" name="terkunci" value="0">
                                        <input type="checkbox" role="switch" id="kunci-pengembang-{{ $kode }}" name="terkunci" value="1" aria-describedby="kunci-pengembang-{{ $kode }}-hint" class="size-4 accent-padelegan-600" @checked($status->kunciPengembang)>
                                        Kunci untuk pemeliharaan
                                    </label>
                                    <p id="kunci-pengembang-{{ $kode }}-hint" class="field__hint">Hanya super admin yang dapat mengubah. Selama aktif, formulir tertutup walau kunci admin dimatikan.</p>
                                </div>

                                <div class="admin-form-actions">
                                    <button type="submit" class="action action--primary">Simpan kunci pengembang</button>
                                </div>
                            </form>
                        @else
                            <p class="text-sm leading-6 text-muted">
                                {{ $status->kunciPengembang
                                    ? 'Dikunci pengembang. Hubungi super admin untuk membukanya.'
                                    : 'Tidak aktif. Hanya super admin yang dapat mengubah kunci ini.' }}
                            </p>
                        @endcan
                    </div>
                </div>
            </section>
        @endforeach
    </div>
@endsection
