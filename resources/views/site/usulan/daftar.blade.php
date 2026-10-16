@extends('layouts.site')

@section('title', 'Usulan Warga')

@section('content')
    <section class="border-b border-padelegan-800/10 bg-white">
        <div class="site-shell py-8 sm:py-10">
            <div class="grid items-center gap-6 lg:grid-cols-[1fr_200px]">
                <div>
                    <p class="public-kicker">Aspirasi terpilih</p>
                    <h1 class="public-page-title text-padelegan-900 mt-1">Usulan Warga</h1>
                    <p class="reading-measure mt-3 text-sm leading-6 text-padelegan-900/70 sm:text-base">
                        Usulan yang sudah ditinjau petugas. Halaman ini tidak menampilkan nama dan WhatsApp pengusul.
                    </p>
                    <p class="mt-3 text-xs font-semibold text-padelegan-900/60">{{ $daftarUsulan->total() }} usulan ditampilkan</p>
                </div>
                <figure class="hidden lg:flex justify-end" aria-hidden="true">
                    <img src="{{ asset('images/illustrations/ilustrasi-aspirasi.webp') }}" alt="" width="180" height="225" class="h-36 w-auto object-contain" fetchpriority="high">
                </figure>
            </div>
        </div>
    </section>

    <div class="site-shell py-9 lg:py-12">
        @if ($usingDemoData)
            <x-ui.alert variant="warning" title="Mode pratinjau" class="mb-8">
                Daftar ini memakai data contoh. Hubungkan database dan publikasikan usulan untuk menampilkan aspirasi warga.
            </x-ui.alert>
        @endif

        <search aria-label="Saring usulan warga">
            <form action="{{ route('public-usulan.index') }}" method="GET" class="public-filter">
                <div class="field public-filter__query"><label class="field__label" for="q">Cari usulan</label><input class="control" id="q" name="q" value="{{ request('q') }}" placeholder="Judul atau nomor usulan"></div>
                <div class="field"><label class="field__label" for="jenis">Jenis</label><select class="control" id="jenis" name="jenis"><option value="">Semua jenis</option>@foreach ($jenisUsulan as $jenis)<option value="{{ $jenis->value }}" @selected(request('jenis') === $jenis->value)>{{ $jenis->label() }}</option>@endforeach</select></div>
                <div class="field"><label class="field__label" for="dusun">Dusun</label><select class="control" id="dusun" name="dusun"><option value="">Semua dusun</option>@foreach ($dusuns as $dusun)<option value="{{ $dusun->id }}" @selected((string) request('dusun') === (string) $dusun->id)>{{ $dusun->name }}</option>@endforeach</select></div>
                <div class="public-filter__action"><button class="action action--primary w-full" type="submit">Tampilkan hasil</button></div>
            </form>
        </search>

        <div class="public-report-grid mt-7">
            @forelse ($daftarUsulan as $usulan)
                <x-usulan.card :usulan="$usulan" />
            @empty
                @if (collect(request()->only(['q', 'jenis', 'dusun']))->filter()->isNotEmpty())
                    <x-ui.empty-state title="Belum ada usulan yang sesuai" description="Ubah atau hapus beberapa filter untuk melihat hasil lain." class="my-10">
                        <x-slot:action><x-ui.action :href="route('public-usulan.index')" variant="secondary">Reset filter</x-ui.action></x-slot:action>
                    </x-ui.empty-state>
                @else
                    <x-ui.empty-state title="Belum ada usulan yang dipublikasikan" description="Usulan muncul di sini setelah petugas meninjau dan mempublikasikannya." class="my-10">
                        <x-slot:action><x-ui.action :href="route('usulan.create')" variant="primary">Kirim usulan</x-ui.action></x-slot:action>
                    </x-ui.empty-state>
                @endif
            @endforelse
        </div>

        @if ($daftarUsulan->hasPages())<div class="mt-10">{{ $daftarUsulan->links() }}</div>@endif
    </div>
@endsection
