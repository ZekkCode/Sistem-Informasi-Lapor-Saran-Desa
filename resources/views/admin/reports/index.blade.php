@extends('layouts.panel-petugas')

@section('title', 'Kelola Laporan')
@section('page-title', 'Kelola laporan')
@section('eyebrow', 'Antrean dan penanganan')
@section('page-actions')
    <div class="flex flex-wrap items-center gap-2">
        <details class="admin-export-dropdown" @click.outside="$el.removeAttribute('open')">
            <summary class="action action--secondary action--sm cursor-pointer list-none select-none">
                <svg class="w-4 h-4 inline-block mr-1 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh CSV</span>
                <svg class="w-3.5 h-3.5 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="admin-export-menu" @click="$el.closest('details').removeAttribute('open')">
                <div class="admin-export-menu__header">Pilih rentang waktu CSV</div>
                <a href="{{ route('admin.reports.export.csv', request()->query()) }}" class="admin-export-menu__item admin-export-menu__item--active">
                    <span>Sesuai Filter Saat Ini</span>
                    <span class="text-[11px] opacity-75">Aktif</span>
                </a>
                <div class="admin-export-menu__divider"></div>
                <a href="{{ route('admin.reports.export.csv', array_merge(request()->query(), ['periode' => 'minggu'])) }}" class="admin-export-menu__item">
                    <span>Mingguan (7 Hari Terakhir)</span>
                </a>
                <a href="{{ route('admin.reports.export.csv', array_merge(request()->query(), ['periode' => 'bulan'])) }}" class="admin-export-menu__item">
                    <span>Bulanan (30 Hari Terakhir)</span>
                </a>
                <a href="{{ route('admin.reports.export.csv', array_merge(request()->query(), ['periode' => 'tahun'])) }}" class="admin-export-menu__item">
                    <span>Tahunan (Tahun {{ now()->year }})</span>
                </a>
                <a href="{{ route('admin.reports.export.csv', array_merge(request()->query(), ['periode' => 'semua'])) }}" class="admin-export-menu__item">
                    <span>Semua Waktu (Tanpa Batas)</span>
                </a>
            </div>
        </details>

        <form action="{{ route('admin.reports.export.drive') }}" method="POST" class="inline" onsubmit="return confirm('Ekspor rekap laporan (format CSV) sesuai filter aktif langsung ke folder Google Drive desa?')">
            @csrf
            @foreach(request()->query() as $key => $val)
                @if(is_scalar($val))
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <button type="submit" class="action action--secondary action--sm" title="Simpan rekap laporan ke Google Drive">
                <svg class="w-4 h-4 inline-block mr-1 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM17 13l-5 5-5-5h3V9h4v4h3z"/></svg>
                <span>Ekspor ke Google Drive</span>
            </button>
        </form>

        <a href="{{ route('admin.reports.print', request()->query()) }}" class="action action--quiet action--sm" target="_blank" rel="noopener">Cetak rekap</a>
    </div>
@endsection

@section('content')
    <form action="{{ route('admin.reports.index') }}" method="GET" class="admin-filter" aria-label="Filter laporan">
        <label class="field field--search">
            <span class="field__label">Cari</span>
            <input name="q" class="control" value="{{ request('q') }}" placeholder="Nomor, judul, nama, atau WhatsApp">
        </label>
        <label class="field">
            <span class="field__label">Rentang waktu</span>
            <select name="periode" class="control">
                <option value="">Semua waktu</option>
                <option value="minggu" @selected(request('periode') === 'minggu')>7 Hari terakhir (Mingguan)</option>
                <option value="bulan" @selected(request('periode') === 'bulan')>30 Hari terakhir (Bulanan)</option>
                <option value="tahun" @selected(request('periode') === 'tahun')>Tahun ini (Tahunan)</option>
            </select>
        </label>
        <label class="field">
            <span class="field__label">Verifikasi</span>
            <select name="verification_status" class="control">
                <option value="">Semua verifikasi</option>
                @foreach($verificationStatuses as $item)
                    <option value="{{ $item->value }}" @selected(request('verification_status') === $item->value)>{{ $item->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span class="field__label">Penanganan</span>
            <select name="status" class="control">
                <option value="">Semua penanganan</option>
                @foreach($statuses as $item)
                    <option value="{{ $item->value }}" @selected(request('status') === $item->value)>{{ $item->label() }}</option>
                @endforeach
            </select>
        </label>
        <label class="field">
            <span class="field__label">Dusun</span>
            <select name="dusun_id" class="control">
                <option value="">Semua dusun</option>
                @foreach($dusuns as $dusun)
                    <option value="{{ $dusun->id }}" @selected((string) request('dusun_id') === (string) $dusun->id)>{{ $dusun->name }}</option>
                @endforeach
            </select>
        </label>
        <div class="admin-filter__actions">
            <button type="submit" class="action action--primary action--sm">
                <span class="text-white font-semibold">Terapkan</span>
            </button>
            @if(request()->hasAny(['q', 'periode', 'verification_status', 'status', 'dusun_id', 'category_id', 'date_from', 'date_to']))
                <a href="{{ route('admin.reports.index') }}" class="action action--quiet action--sm">Reset</a>
            @endif
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <caption class="sr-only">Daftar laporan warga sesuai filter aktif</caption>
            <thead>
                <tr>
                    <th scope="col" class="w-16">Foto</th>
                    <th scope="col">Laporan</th>
                    <th scope="col">Lokasi</th>
                    <th scope="col">Verifikasi</th>
                    <th scope="col">Penanganan</th>
                    <th scope="col">Prioritas</th>
                    <th scope="col">Masuk</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $photo = $report->media->firstWhere('media_type', \App\Enums\ReportMediaType::BeforePhoto) ?? $report->media->first();
                        $photoCount = $report->media->count();
                    @endphp
                    <tr>
                        <td>
                            @if($photo)
                                <a href="{{ route('admin.media.show', $photo) }}" target="_blank" rel="noopener" class="admin-table__photo-thumb" title="Buka foto kondisi ({{ $photoCount }} foto)">
                                    <img src="{{ route('admin.media.show', $photo) }}" alt="Foto laporan {{ $report->report_code }}" loading="lazy">
                                    @if($photoCount > 1)
                                        <span class="admin-table__photo-badge">+{{ $photoCount - 1 }}</span>
                                    @endif
                                </a>
                            @else
                                <div class="admin-table__photo-thumb admin-table__photo-thumb--empty" title="Tidak ada foto">
                                    <svg class="w-5 h-5 text-muted opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.reports.show', $report) }}" class="font-bold text-primary">{{ $report->report_code }}</a>
                            <span class="admin-table__title">{{ $report->title }}</span>
                            <span class="admin-table__muted">{{ $report->reporter_name }} · {{ $report->reporter_phone }}</span>
                        </td>
                        <td>{{ $report->dusun->name }}<span class="admin-table__muted block">{{ $report->subcategory->category->name }}</span></td>
                        <td><x-reports.verification-badge :status="$report->verification_status" /></td>
                        <td><x-reports.status :status="$report->status" /></td>
                        <td><x-reports.priority-badge :priority="$report->admin_priority ?? $report->citizen_priority" /></td>
                        <td><time datetime="{{ $report->submitted_at->toIso8601String() }}">{{ $report->submitted_at->translatedFormat('d M Y') }}</time><span class="admin-table__muted block">{{ $report->submitted_at->format('H:i') }} WIB</span></td>
                        <td>
                            <a href="{{ route('admin.reports.show', $report) }}" class="action action--primary action--sm whitespace-nowrap" style="color: #ffffff !important;" title="Tinjau dan tindaklanjuti laporan {{ $report->report_code }}">
                                <span class="text-white font-bold" style="color: #ffffff !important;">Tinjau</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="admin-empty">Tidak ada laporan yang cocok dengan filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="admin-pagination">{{ $reports->links() }}</div>
@endsection
