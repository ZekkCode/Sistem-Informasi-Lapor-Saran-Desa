@extends('layouts.panel-petugas')

@section('title', $report->report_code)
@section('page-title', $report->report_code)
@section('eyebrow', 'Detail laporan warga')
@section('page-actions')
    <a href="{{ route('admin.reports.index') }}" class="action action--quiet action--sm">Kembali ke daftar</a>
@endsection

@section('content')
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <x-reports.verification-badge :status="$report->verification_status" />
        <x-reports.status :status="$report->status" />
        <x-reports.priority-badge :priority="$report->admin_priority ?? $report->citizen_priority" />
        <span class="text-xs text-muted">Masuk {{ $report->submitted_at->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>

    <div class="admin-detail-grid">
        <div class="admin-stack">
            <section class="admin-panel">
                <header class="admin-panel__head"><h2>{{ $report->title }}</h2></header>
                <div class="admin-panel__body">
                    <h3 class="admin-section-title">Isi laporan</h3>
                    <p class="admin-description">{{ $report->description }}</p>
                    <dl class="admin-data-grid mt-6 border-t border-border pt-5">
                        <div><dt>Nama pelapor</dt><dd>{{ $report->reporter_name }}</dd></div>
                        <div><dt>WhatsApp</dt><dd><a href="https://wa.me/{{ preg_replace('/\D+/', '', $report->reporter_phone) }}" target="_blank" rel="noopener" class="text-primary underline underline-offset-2">{{ $report->reporter_phone }}</a></dd></div>
                        <div><dt>Dusun</dt><dd>{{ $report->dusun->name }}</dd></div>
                        <div><dt>Lokasi tertulis</dt><dd>{{ $report->location_text }}</dd></div>
                        <div><dt>Kategori</dt><dd>{{ $report->subcategory->category->name }}</dd></div>
                        <div><dt>Jenis masalah</dt><dd>{{ $report->subcategory->name }}</dd></div>
                        <div><dt>Urgensi warga</dt><dd>{{ $report->citizen_priority->label() }}</dd></div>
                        <div><dt>Sumber laporan</dt><dd>{{ $report->qrSource?->name ?? 'Tautan langsung' }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Dokumentasi</h2><span class="text-xs text-muted">{{ $report->media->count() }} foto</span></header>
                <div class="admin-panel__body">
                    @if($report->media->isEmpty())
                        <p class="admin-empty">Tidak ada foto pada laporan ini.</p>
                    @else
                        @foreach([\App\Enums\ReportMediaType::BeforePhoto->value => 'Foto warga', \App\Enums\ReportMediaType::AfterPhoto->value => 'Bukti penanganan'] as $type => $label)
                            @php($items = $report->media->filter(fn ($media) => $media->media_type->value === $type))
                            @if($items->isNotEmpty())
                                <h3 class="admin-section-title {{ $loop->first ? '' : 'mt-6' }}">{{ $label }}</h3>
                                <div class="admin-media-grid">
                                    @foreach($items as $media)
                                        <a href="{{ route('admin.media.show', $media) }}" target="_blank" rel="noopener"><img src="{{ route('admin.media.show', $media) }}" alt="{{ $label }} {{ $loop->iteration }}" loading="lazy"></a>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Riwayat perubahan</h2></header>
                <div class="admin-panel__body">
                    <ol class="admin-timeline">
                        @foreach($report->histories as $history)
                            <li>
                                <time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->translatedFormat('d M Y, H:i') }} · {{ $history->changedBy?->name ?? 'Sistem' }}</time>
                                <p><strong>{{ $history->verification_status->label() }}</strong> · {{ $history->status->label() }}</p>
                                @if($history->public_note)<p>Untuk warga: {{ $history->public_note }}</p>@endif
                                @if($history->internal_note)<p class="text-muted">Catatan internal: {{ $history->internal_note }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        </div>

        <aside class="admin-stack">
            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Perbarui penanganan</h2></header>
                <form
                    action="{{ route('admin.reports.workflow.update', $report) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="admin-panel__body admin-form-grid"
                    x-data="adminReportForm(@js($directUpload))"
                    @submit="handleSubmit"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="workflow_version" value="{{ $report->workflow_version }}">

                    <div class="field field--full">
                        <label for="verification_status" class="field__label">Hasil verifikasi</label>
                        <select id="verification_status" name="verification_status" class="control" required>
                            @foreach($verificationStatuses as $item)<option value="{{ $item->value }}" @selected(old('verification_status', $report->verification_status->value) === $item->value)>{{ $item->label() }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field field--full">
                        <label for="status" class="field__label">Tahap penanganan</label>
                        <select id="status" name="status" class="control" required>
                            @foreach($statuses as $item)<option value="{{ $item->value }}" @selected(old('status', $report->status->value) === $item->value)>{{ $item->label() }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field field--full">
                        <label for="admin_priority" class="field__label">Prioritas petugas</label>
                        <select id="admin_priority" name="admin_priority" class="control" required>
                            @foreach($priorities as $item)<option value="{{ $item->value }}" @selected(old('admin_priority', $report->admin_priority?->value ?? \App\Enums\AdminPriority::Medium->value) === $item->value)>{{ $item->label() }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field field--full">
                        <label for="public_note" class="field__label">Kabar untuk warga</label>
                        <textarea id="public_note" name="public_note" class="control min-h-28" maxlength="2000" placeholder="Contoh: Tim desa menjadwalkan pemeriksaan lokasi besok.">{{ old('public_note', $report->current_public_note) }}</textarea>
                        <p class="field__hint">Warga membaca catatan ini pada halaman cek status. Cantumkan tindakan atau jadwal berikutnya.</p>
                    </div>
                    <div class="field field--full">
                        <label for="internal_note" class="field__label">Catatan internal</label>
                        <textarea id="internal_note" name="internal_note" class="control min-h-24" maxlength="2000" placeholder="Hanya terlihat oleh petugas.">{{ old('internal_note') }}</textarea>
                    </div>
                    <div class="field field--full">
                        <label for="after_photos" class="field__label">Bukti penanganan <span class="font-normal text-muted">(opsional)</span></label>
                        <input id="after_photos" name="after_photos[]" type="file" class="control" accept="image/jpeg,image/png,image/webp" multiple @change="validateFiles">
                        <p class="field__hint">Maksimal 5 foto, masing-masing 5 MB.</p>
                    </div>
                    <p class="field--full text-sm text-danger" x-show="error" x-text="error" x-cloak role="alert"></p>
                    <p class="field--full text-sm text-muted" x-show="uploadStatus" x-text="uploadStatus" x-cloak aria-live="polite"></p>
                    <div class="admin-form-actions">
                        <button type="submit" class="action action--primary" :disabled="submitting" x-text="submitting ? 'Menyimpan…' : 'Simpan perubahan'">Simpan perubahan</button>
                    </div>
                </form>
            </section>

            <section class="admin-panel">
                <header class="admin-panel__head"><h2>Informasi verifikasi</h2></header>
                <div class="admin-panel__body">
                    <dl class="admin-data-grid">
                        <div><dt>Diverifikasi oleh</dt><dd>{{ $report->verifier?->name ?? 'Belum ada' }}</dd></div>
                        <div><dt>Waktu verifikasi</dt><dd>{{ $report->verified_at?->translatedFormat('d M Y, H:i') ?? 'Belum ada' }}</dd></div>
                        <div><dt>Selesai pada</dt><dd>{{ $report->completed_at?->translatedFormat('d M Y, H:i') ?? 'Belum selesai' }}</dd></div>
                    </dl>
                </div>
            </section>
        </aside>
    </div>
@endsection
