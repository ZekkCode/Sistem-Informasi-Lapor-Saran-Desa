@extends('layouts.panel-petugas')

@section('title', 'Ikhtisar')
@section('page-title', 'Ikhtisar laporan')
@section('eyebrow', now()->translatedFormat('l, d F Y'))
@section('page-actions')
    <a href="{{ route('admin.reports.index', ['verification_status' => 'pending']) }}" class="action action--primary action--sm">
        <span class="text-white font-semibold">Tinjau antrean</span>
    </a>
@endsection

@section('content')
    <p class="admin-intro">Pantau antrean verifikasi, tren data, dan laporan prioritas desa.</p>

    <dl class="admin-metrics">
        <div class="admin-metric"><dt>Semua laporan</dt><dd>{{ number_format($metrics['total']) }}</dd><small>Total data</small></div>
        <div class="admin-metric"><dt>Perlu verifikasi</dt><dd>{{ number_format($metrics['pending']) }}</dd><small>Belum diverifikasi</small></div>
        <div class="admin-metric"><dt>Belum ditangani</dt><dd>{{ number_format($metrics['not_started']) }}</dd><small>Terverifikasi</small></div>
        <div class="admin-metric"><dt>Sedang diproses</dt><dd>{{ number_format($metrics['in_progress']) }}</dd><small>Dalam proses</small></div>
        <div class="admin-metric"><dt>Selesai</dt><dd>{{ number_format($metrics['completed']) }}</dd><small>{{ $metrics['completion_rate'] }}% tuntas</small></div>
    </dl>

    {{-- Grafik Visual Interaktif --}}
    <div class="admin-charts-grid">
        <section class="admin-panel admin-chart-panel">
            <header class="admin-panel__head">
                <div>
                    <h2>Tren Aktivitas Warga (7 Hari Terakhir)</h2>
                    <span class="text-xs text-muted block mt-0.5">Statistik harian laporan dan usulan yang masuk</span>
                </div>
            </header>
            <div class="admin-panel__body">
                <div class="admin-chart-wrap" style="height: 250px; position: relative;">
                    <canvas id="chart-trend" data-chart="{{ json_encode($trendChart) }}"></canvas>
                </div>
            </div>
        </section>

        <section class="admin-panel admin-chart-panel">
            <header class="admin-panel__head">
                <div>
                    <h2>Status Penanganan</h2>
                    <span class="text-xs text-muted block mt-0.5">Proporsi laporan berdasarkan tahapan</span>
                </div>
            </header>
            <div class="admin-panel__body">
                <div class="admin-chart-wrap" style="height: 250px; position: relative;">
                    <canvas id="chart-status" data-chart="{{ json_encode($statusChart) }}"></canvas>
                </div>
            </div>
        </section>
    </div>

    <section class="admin-panel admin-chart-panel mt-4">
        <header class="admin-panel__head">
            <div>
                <h2>Sebaran Laporan per Dusun</h2>
                <span class="text-xs text-muted block mt-0.5">Jumlah laporan masuk di 6 dusun Desa Padelegan</span>
            </div>
        </header>
        <div class="admin-panel__body">
            <div class="admin-chart-wrap" style="height: 180px; position: relative;">
                <canvas id="chart-dusun" data-chart="{{ json_encode($dusunChart) }}"></canvas>
            </div>
        </div>
    </section>

    <div class="admin-dashboard-grid mt-4">
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
