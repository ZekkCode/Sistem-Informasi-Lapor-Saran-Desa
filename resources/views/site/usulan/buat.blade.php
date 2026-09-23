@extends('layouts.site')

@section('title', 'Kirim Usulan & Saran')

@section('content')
    <section class="border-b border-padelegan-800/10 bg-white">
        <div class="site-shell grid gap-6 py-8 md:grid-cols-[minmax(0,1fr)_auto] md:items-end sm:py-10">
            <div data-aos="fade-up">
                <p class="text-sm font-medium text-padelegan-600">Aspirasi warga</p>
                <h1 class="mt-2 max-w-2xl text-3xl font-semibold leading-tight tracking-[-0.035em] text-padelegan-900 sm:text-4xl">Sampaikan usulan dan saran untuk desa.</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-padelegan-900/62 sm:text-base">Usulkan pembangunan, pelayanan, atau kegiatan. Nama dan WhatsApp opsional.</p>
            </div>
            <ul class="report-assurances" role="list" aria-label="Informasi layanan" data-aos="fade" data-aos-delay="100">
                <li>Tanpa akun</li>
                <li>Boleh anonim</li>
            </ul>
        </div>
    </section>

    <div class="site-shell py-8 sm:py-10 lg:py-12">
        <form action="{{ route('usulan.store') }}" method="POST" class="report-form" data-validate data-aos="fade-up">
            @csrf
            <input type="hidden" name="token_kirim" value="{{ $tokenKirim }}">

            @if ($usingDemoData)
                <x-ui.alert variant="warning" title="Mode pratinjau" class="mb-6">
                    Halaman memakai data contoh. Pengiriman aktif setelah database tersambung.
                </x-ui.alert>
            @endif

            @if ($errors->any())
                <x-ui.alert variant="danger" title="Periksa bagian yang ditandai" class="mb-6" role="alert" aria-live="polite">
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-padelegan-900/70">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <section class="report-panel">
                <header class="report-panel__header">
                    <p>Usulan</p>
                    <h2>Jelaskan usulan Anda</h2>
                    <span>Pilih jenis dan dusun, lalu tulis usulan secara ringkas.</span>
                </header>

                <div class="report-panel__body">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="field">
                            <x-ui.label for="jenis" required>Jenis usulan</x-ui.label>
                            <x-ui.select id="jenis" name="jenis" required>
                                <option value="">Pilih jenis usulan</option>
                                @foreach ($jenisUsulan as $jenis)
                                    <option value="{{ $jenis->value }}" @selected(old('jenis') === $jenis->value)>{{ $jenis->label() }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.field-error name="jenis" />
                        </div>

                        <div class="field">
                            <x-ui.label for="dusun_id" required>Dusun</x-ui.label>
                            <x-ui.select id="dusun_id" name="dusun_id" required>
                                <option value="">Pilih dusun</option>
                                @foreach ($dusuns as $dusun)
                                    <option value="{{ $dusun->id }}" @selected((string) old('dusun_id') === (string) $dusun->id)>{{ $dusun->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.field-error name="dusun_id" />
                        </div>
                    </div>

                    <div class="field">
                        <x-ui.label for="judul" required>Judul singkat</x-ui.label>
                        <x-ui.input id="judul" name="judul" :value="old('judul')" required maxlength="120" enterkeyhint="next" placeholder="Contoh: Tambah lampu di jalan dusun" />
                        <x-ui.field-error name="judul" />
                    </div>

                    <div class="field">
                        <x-ui.label for="isi" required>Isi usulan</x-ui.label>
                        <p class="field__hint" id="isi-hint">Jelaskan usulan dan manfaatnya bagi warga.</p>
                        <x-ui.textarea describedby="isi-hint" class="min-h-32" id="isi" name="isi" required minlength="20" maxlength="5000" placeholder="Contoh: Usul menambah lampu jalan di jalur dusun agar warga aman saat malam.">{{ old('isi') }}</x-ui.textarea>
                        <x-ui.field-error name="isi" />
                    </div>
                </div>
            </section>

            <section class="report-panel">
                <header class="report-panel__header">
                    <p>Pengusul</p>
                    <h2>Identitas opsional</h2>
                    <span>Isi bila bersedia dihubungi petugas. Kosongkan untuk anonim.</span>
                </header>

                <div class="report-panel__body">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="field">
                            <label class="field__label" for="nama_pengusul">Nama <span class="font-normal text-muted">(opsional)</span></label>
                            <x-ui.input id="nama_pengusul" name="nama_pengusul" :value="old('nama_pengusul')" autocomplete="name" maxlength="120" placeholder="Nama Anda" />
                            <x-ui.field-error name="nama_pengusul" />
                        </div>
                        <div class="field">
                            <label class="field__label" for="telepon_pengusul">Nomor WhatsApp <span class="font-normal text-muted">(opsional)</span></label>
                            <p class="field__hint" id="telepon-hint">Contoh: 081234567890</p>
                            <x-ui.input describedby="telepon-hint" id="telepon_pengusul" name="telepon_pengusul" :value="old('telepon_pengusul')" autocomplete="tel" inputmode="tel" maxlength="30" placeholder="081234567890" />
                            <x-ui.field-error name="telepon_pengusul" />
                        </div>
                    </div>

                    <p class="report-privacy-note">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 21s7-3.5 7-9V5.5L12 3 5 5.5V12c0 5.5 7 9 7 9Z"/><path d="m9.5 12 1.7 1.7 3.7-4"/></svg>
                        Identitas tidak pernah tampil di halaman publik. Petugas saja yang melihatnya.
                    </p>

                    <div>
                        <label class="report-consent" for="consent">
                            <input id="consent" type="checkbox" name="consent" value="1" @checked(old('consent')) required>
                            <span>Saya menyatakan usulan ini benar dan sopan. Pemerintah Desa Padelegan dapat meninjau dan menampilkannya secara anonim.</span>
                        </label>
                        @error('consent')<p class="field__error mt-2" id="consent-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <footer class="report-panel__actions report-panel__actions--end">
                    <x-ui.action type="submit" :disabled="$usingDemoData">{{ $usingDemoData ? 'Pengiriman belum aktif' : 'Kirim usulan' }}</x-ui.action>
                </footer>
            </section>
        </form>
    </div>
@endsection
