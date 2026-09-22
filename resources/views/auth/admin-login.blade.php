@extends('layouts.site')

@section('title', 'Masuk Petugas')

@section('content')
    <section class="site-shell py-12 sm:py-20">
        <div class="mx-auto grid max-w-4xl border border-border bg-white lg:grid-cols-[0.9fr_1.1fr]">
            <div class="bg-padelegan-900 p-7 text-white sm:p-10">
                <img src="{{ asset('images/brand/lambang-pamekasan.png') }}" alt="Lambang Kabupaten Pamekasan" width="418" height="387" class="mb-7 h-auto w-16" fetchpriority="high">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-padelegan-200">Area internal desa</p>
                <h1 class="mt-4 text-3xl font-semibold tracking-[-0.035em] sm:text-4xl">Kelola laporan warga dengan tertib.</h1>
                <p class="mt-5 text-sm leading-7 text-white/70">Masuk untuk memverifikasi laporan, memperbarui penanganan, dan menyiapkan rekap. Data pelapor hanya terlihat oleh petugas berwenang.</p>
                <a href="{{ route('home') }}" class="mt-8 inline-flex text-sm font-semibold text-padelegan-100 underline decoration-white/30 underline-offset-4">Kembali ke situs warga</a>
            </div>

            <div class="p-7 sm:p-10">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-padelegan-600">Layanan Pengaduan Desa</p>
                <h2 class="mt-2 text-2xl font-semibold tracking-[-0.025em]">Masuk sebagai petugas</h2>

                @if($errors->any())
                    <div class="mt-5 border-l-2 border-danger bg-danger-soft p-4 text-sm" role="alert" aria-live="assertive">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('admin.login.store') }}" method="POST" class="mt-7 grid gap-5" data-validate data-submit-once>
                    @csrf
                    <div class="field">
                        <x-ui.label for="username" required>Username</x-ui.label>
                        <input id="username" name="username" class="control" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus placeholder="Username petugas">
                    </div>
                    <div class="field">
                        <x-ui.label for="password" required>Password</x-ui.label>
                        <input id="password" name="password" type="password" class="control" autocomplete="current-password" required placeholder="Password akun">
                    </div>
                    <label class="flex min-h-12 items-center gap-3 text-sm">
                        <input type="checkbox" name="remember" value="1" class="size-4 accent-padelegan-600" @checked(old('remember'))>
                        Tetap masuk di perangkat ini
                    </label>
                    <button type="submit" class="action action--primary action--lg w-full" data-submit-label="Memeriksa akun…">Masuk ke panel</button>
                </form>
            </div>
        </div>
    </section>
@endsection
