@extends('layouts.panel-petugas')

@section('title', 'Ikhtisar')
@section('page-title', 'Ikhtisar laporan')
@section('eyebrow', now()->translatedFormat('l, d F Y'))
@section('page-actions')
    <a href="{{ route('admin.reports.index', ['verification_status' => 'pending']) }}" class="action action--primary action--sm">Tinjau antrean</a>
@endsection

@section('content')
    <p class="admin-intro">Pantau antrean masuk dan pekerjaan yang perlu didahulukan. Angka berikut berasal dari database aktif.</p>

    <dl class="admin-metrics">
        <div class="admin-metric"><dt>Semua laporan</dt><dd>{{ number_format($metrics['total']) }}</dd><small>Sejak layanan dibuka</small></div>
        <div class="admin-metric"><dt>Perlu verifikasi</dt><dd>{{ number_format($metrics['pending']) }}</dd><small>Belum ditinjau petugas</small></div>
        <div class="admin-metric"><dt>Belum ditangani</dt><dd>{{ number_format($metrics['not_started']) }}</dd><small>Sudah terverifikasi</small></div>
        <div class="admin-metric"><dt>Sedang diproses</dt><dd>{{ number_format($metrics['in_progress']) }}</dd><small>Penanganan berjalan</small></div>
        <div class="admin-metric"><dt>Selesai</dt><dd>{{ number_format($metrics['completed']) }}</dd><small>{{ $metrics['completion_rate'] }}% dari seluruh laporan</small></div>
    </dl>

    <div class="admin-dashboard-grid">
        <section class="admin-panel">
            <header class="admin-panel__head">
                <h2>Laporan terbaru</h2>
                <a href="{{ route('admin.reports.index') }}" class="admin-panel__link">Lihat semua</a>
            </header>
            @forelse($recentReports as $report)
                <a href="{{ route('admin.reports.show', $report) }}" class="admin-list-item">
                    <span>
                        <strong>{{ $report->report_code }} · {{ $report->title }}</strong>
                        <small>{{ $report->dusun->name }} · {{ $report->subcategory->category->name }}</small>
                    </span>
                    <span class="text-right">
                        <time datetime="{{ $report->submitted_at->toIso8601String() }}">{{ $report->submitted_at->diffForHumans() }}</time>
                        <span class="mt-1 block"><x-reports.verification-badge :status="$report->verification_status" /></span>
                    </span>
                </a>
            @empty
                <p class="admin-empty">Belum ada laporan masuk.</p>
            @endforelse
        </section>

        <div class="admin-stack">
            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Perlu didahulukan</h2></header>
                @forelse($priorityReports as $report)
                    <a href="{{ route('admin.reports.show', $report) }}" class="admin-list-item">
                        <span><strong>{{ $report->title }}</strong><small>{{ $report->dusun->name }}</small></span>
                        <x-reports.priority-badge :priority="$report->admin_priority" />
                    </a>
                @empty
                    <p class="admin-empty">Tidak ada laporan prioritas tinggi.</p>
                @endforelse
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Sebaran kategori</h2></header>
                <div class="admin-panel__body">
                    @php($largestCategory = max(1, (int) ($byCategory->max('aggregate') ?? 1)))
                    @forelse($byCategory as $item)
                        <div class="admin-category-row">
                            <span>{{ $item->name }}</span>
                            <span class="admin-category-track"><span class="admin-category-fill" style="width: {{ round(($item->aggregate / $largestCategory) * 100) }}%"></span></span>
                            <strong>{{ $item->aggregate }}</strong>
                        </div>
                    @empty
                        <p class="admin-empty">Belum ada data kategori.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
