@extends('layouts.panel-petugas')

@section('title', 'Usulan & Saran')
@section('page-title', 'Usulan & saran')
@section('eyebrow', 'Aspirasi warga')

@section('content')
    <form action="{{ route('admin.usulan.index') }}" method="GET" class="admin-filter" aria-label="Filter usulan">
        <label class="field"><span class="field__label">Cari</span><input name="q" class="control" value="{{ request('q') }}" placeholder="Nomor, judul, nama, atau WhatsApp"></label>
        <label class="field"><span class="field__label">Status</span><select name="status" class="control"><option value="">Semua status</option>@foreach($statuses as $item)<option value="{{ $item->value }}" @selected(request('status') === $item->value)>{{ $item->label() }}</option>@endforeach</select></label>
        <label class="field"><span class="field__label">Jenis</span><select name="jenis" class="control"><option value="">Semua jenis</option>@foreach($jenisUsulan as $item)<option value="{{ $item->value }}" @selected(request('jenis') === $item->value)>{{ $item->label() }}</option>@endforeach</select></label>
        <label class="field"><span class="field__label">Dusun</span><select name="dusun_id" class="control"><option value="">Semua dusun</option>@foreach($dusuns as $dusun)<option value="{{ $dusun->id }}" @selected((string) request('dusun_id') === (string) $dusun->id)>{{ $dusun->name }}</option>@endforeach</select></label>
        <label class="field"><span class="field__label">Publik</span><select name="tampil_publik" class="control"><option value="">Semua</option><option value="1" @selected(request('tampil_publik') === '1')>Ditampilkan</option><option value="0" @selected(request('tampil_publik') === '0')>Tersembunyi</option></select></label>
        <button type="submit" class="action action--primary action--sm">Terapkan</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <caption class="sr-only">Daftar usulan warga sesuai filter aktif</caption>
            <thead><tr><th scope="col">Usulan</th><th scope="col">Dusun</th><th scope="col">Jenis</th><th scope="col">Status</th><th scope="col">Publik</th><th scope="col">Masuk</th></tr></thead>
            <tbody>
                @forelse($daftarUsulan as $usulan)
                    <tr>
                        <td><a href="{{ route('admin.usulan.show', $usulan) }}">{{ $usulan->kode }}</a><span class="admin-table__title">{{ $usulan->judul }}</span><span class="admin-table__muted">{{ $usulan->nama_pengusul ?: 'Anonim' }}</span></td>
                        <td>{{ $usulan->dusun->name }}</td>
                        <td>{{ $usulan->jenis->label() }}</td>
                        <td><x-usulan.status :status="$usulan->status" /></td>
                        <td>{{ $usulan->tampil_publik ? 'Ditampilkan' : 'Tersembunyi' }}</td>
                        <td><time datetime="{{ $usulan->dikirim_pada->toIso8601String() }}">{{ $usulan->dikirim_pada->translatedFormat('d M Y') }}</time><span class="admin-table__muted block">{{ $usulan->dikirim_pada->format('H:i') }} WIB</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-empty">Tidak ada usulan yang cocok dengan filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-pagination">{{ $daftarUsulan->links() }}</div>
@endsection
