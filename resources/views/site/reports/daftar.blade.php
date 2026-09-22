@extends('layouts.site')

@section('title', 'Laporan Desa')

@section('content')
    <section class="border-b border-padelegan-800/10 bg-white">
        <div class="site-shell py-9 sm:py-12">
            <p class="public-kicker">Laporan terverifikasi</p>
            <div class="mt-3 grid gap-5 lg:grid-cols-[1fr_auto] lg:items-end">
                <div><h1 class="public-page-title text-padelegan-900">Laporan Desa</h1><p class="reading-measure mt-4 text-base leading-7 text-padelegan-900/65">Petugas sudah memeriksa laporan dalam daftar ini. Halaman publik tidak menampilkan nama dan WhatsApp pelapor.</p></div>
                <p class="text-sm font-semibold text-padelegan-900/55">{{ $reports->total() }} laporan terverifikasi</p>
            </div>
        </div>
    </section>

    <div class="site-shell py-9 lg:py-12">
        @if ($usingDemoData)
            <x-ui.alert variant="warning" title="Mode pratinjau" class="mb-8">
                Daftar ini memakai data contoh. Hubungkan database dan isi data awal untuk menampilkan laporan terverifikasi.
            </x-ui.alert>
        @endif

        <search aria-label="Saring laporan desa">
            <form action="{{ route('public-reports.index') }}" method="GET" class="public-filter">
                <div class="field public-filter__query"><label class="field__label" for="q">Cari laporan</label><input class="control" id="q" name="q" value="{{ request('q') }}" placeholder="Judul atau nomor laporan"></div>
                <div class="field"><label class="field__label" for="status">Status</label><select class="control" id="status" name="status"><option value="">Semua status</option>@foreach (\App\Enums\ReportStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
                <div class="field"><label class="field__label" for="dusun">Dusun</label><select class="control" id="dusun" name="dusun"><option value="">Semua dusun</option>@foreach ($dusuns as $dusun)<option value="{{ $dusun->id }}" @selected((string) request('dusun') === (string) $dusun->id)>{{ $dusun->name }}</option>@endforeach</select></div>
                <div class="field"><label class="field__label" for="category">Kategori</label><select class="control" id="category" name="category"><option value="">Semua kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="public-filter__action"><button class="action action--primary w-full" type="submit">Tampilkan hasil</button></div>
            </form>
        </search>

        <div class="public-report-grid mt-7">
            @forelse ($reports as $report)
                <x-reports.card :report="$report" />
            @empty
                <x-ui.empty-state title="Belum ada laporan yang sesuai" description="Ubah atau hapus beberapa filter untuk melihat hasil lain." class="my-10">
                    <x-slot:action><x-ui.action :href="route('public-reports.index')" variant="secondary">Reset filter</x-ui.action></x-slot:action>
                </x-ui.empty-state>
            @endforelse
        </div>

        @if ($reports->hasPages())<div class="mt-10">{{ $reports->links() }}</div>@endif
    </div>
@endsection
