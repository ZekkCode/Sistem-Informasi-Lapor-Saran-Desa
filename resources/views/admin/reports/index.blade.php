@extends('layouts.panel-petugas')

@section('title', 'Kelola Laporan')
@section('page-title', 'Kelola laporan')
@section('eyebrow', 'Antrean dan penanganan')
@section('page-actions')
    <a href="{{ route('admin.reports.export.csv', request()->query()) }}" class="action action--secondary action--sm">Unduh CSV</a>
    <a href="{{ route('admin.reports.print', request()->query()) }}" class="action action--quiet action--sm" target="_blank" rel="noopener">Cetak rekap</a>
@endsection

@section('content')
    <form action="{{ route('admin.reports.index') }}" method="GET" class="admin-filter" aria-label="Filter laporan">
        <label class="field"><span class="field__label">Cari</span><input name="q" class="control" value="{{ request('q') }}" placeholder="Nomor, judul, nama, atau WhatsApp"></label>
        <label class="field"><span class="field__label">Verifikasi</span><select name="verification_status" class="control"><option value="">Semua verifikasi</option>@foreach($verificationStatuses as $item)<option value="{{ $item->value }}" @selected(request('verification_status') === $item->value)>{{ $item->label() }}</option>@endforeach</select></label>
        <label class="field"><span class="field__label">Penanganan</span><select name="status" class="control"><option value="">Semua penanganan</option>@foreach($statuses as $item)<option value="{{ $item->value }}" @selected(request('status') === $item->value)>{{ $item->label() }}</option>@endforeach</select></label>
        <label class="field"><span class="field__label">Dusun</span><select name="dusun_id" class="control"><option value="">Semua dusun</option>@foreach($dusuns as $dusun)<option value="{{ $dusun->id }}" @selected((string) request('dusun_id') === (string) $dusun->id)>{{ $dusun->name }}</option>@endforeach</select></label>
        <button type="submit" class="action action--primary action--sm">Terapkan</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <caption class="sr-only">Daftar laporan warga sesuai filter aktif</caption>
            <thead><tr><th scope="col">Laporan</th><th scope="col">Lokasi</th><th scope="col">Verifikasi</th><th scope="col">Penanganan</th><th scope="col">Prioritas</th><th scope="col">Masuk</th></tr></thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td><a href="{{ route('admin.reports.show', $report) }}">{{ $report->report_code }}</a><span class="admin-table__title">{{ $report->title }}</span><span class="admin-table__muted">{{ $report->reporter_name }} · {{ $report->reporter_phone }}</span></td>
                        <td>{{ $report->dusun->name }}<span class="admin-table__muted block">{{ $report->subcategory->category->name }}</span></td>
                        <td><x-reports.verification-badge :status="$report->verification_status" /></td>
                        <td><x-reports.status :status="$report->status" /></td>
                        <td><x-reports.priority-badge :priority="$report->admin_priority ?? $report->citizen_priority" /></td>
                        <td><time datetime="{{ $report->submitted_at->toIso8601String() }}">{{ $report->submitted_at->translatedFormat('d M Y') }}</time><span class="admin-table__muted block">{{ $report->submitted_at->format('H:i') }} WIB</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-empty">Tidak ada laporan yang cocok dengan filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-pagination">{{ $reports->links() }}</div>
@endsection
