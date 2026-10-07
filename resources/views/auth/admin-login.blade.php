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

                @if ($googleAktif)
                    <a href="{{ route('admin.login.google') }}" class="google-action mt-7">
                        <svg aria-hidden="true" viewBox="0 0 48 48" class="google-action__logo">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        </svg>
                        Masuk dengan Google
                    </a>
                    <p class="mt-2 text-xs leading-5 text-padelegan-900/55">Khusus akun Google resmi admin desa.</p>
                    <div class="login-divider" role="separator"><span>atau pakai username</span></div>
                @endif

                @if (session('sso_error'))
                    <dialog class="sso-dialog" open data-dialog-otomatis role="alertdialog" aria-labelledby="sso-dialog-judul" aria-describedby="sso-dialog-pesan">
                        <div class="sso-dialog__isi">
                            <span class="sso-dialog__ikon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5m0 3h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <h2 id="sso-dialog-judul" class="sso-dialog__judul">{{ session('sso_error')['judul'] }}</h2>
                            <p id="sso-dialog-pesan" class="sso-dialog__pesan">{{ session('sso_error')['pesan'] }}</p>
                            <form method="dialog">
                                <button type="submit" class="action action--primary w-full" autofocus>Mengerti</button>
                            </form>
                        </div>
                    </dialog>
                @endif

                <form action="{{ route('admin.login.store') }}" method="POST" class="{{ $googleAktif ? '' : 'mt-7' }} grid gap-5" data-validate data-submit-once>
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
