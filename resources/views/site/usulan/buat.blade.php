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
        <form
            action="{{ route('usulan.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="report-form"
            data-validate
            data-aos="fade-up"
            x-data="usulanForm({
                demoMode: @js($usingDemoData),
                directUpload: @js($directUpload ?? ['enabled' => false, 'url' => '']),
            })"
            @submit="handleSubmit($event)"
        >
            @csrf
            <input type="hidden" name="token_kirim" value="{{ $tokenKirim }}">
            <input type="hidden" name="_form_rendered_at" value="{{ time() }}">
            {{-- Honeypot --}}
            <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;"><label for="hp_website_u">Website</label><input type="text" name="_website" id="hp_website_u" tabindex="-1" autocomplete="off" value=""></div>

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
                    <span>Pilih jenis dan dusun, lalu tulis usulan Anda.</span>
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

                    <div class="field">
                        <label class="field__label" for="photos">Foto pendukung <span class="font-normal text-muted">(opsional, maks 3 foto)</span></label>
                        <p class="field__hint" id="photos-hint">Lampirkan hingga 3 foto yang jelas jika usulan terkait lokasi atau kondisi tertentu (JPG, PNG, atau WEBP, maks 5 MB per foto).</p>
                        <div class="report-upload mt-3">
                            <input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple aria-describedby="photos-hint photos-status" @change="previewFiles($event)">
                            <label for="photos">
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 13v5.5A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5V13"/></svg>
                                <span><strong>Pilih foto</strong><small>Ambil dari galeri atau kamera (Maksimal 3 foto)</small></span>
                            </label>
                        </div>
                        <p id="photos-status" class="mt-2 text-xs text-padelegan-900/55" aria-live="polite" x-text="photoMessage">Belum ada foto dipilih (Maksimal 3 foto)</p>

                        <div x-show="previews.length" class="report-preview-list">
                            <template x-for="(item, index) in previews" :key="item.id || index">
                                <figure class="relative group">
                                    <img :src="item.url" :alt="item.name" class="aspect-[4/3] w-full object-cover">
                                    <figcaption class="flex items-center justify-between gap-2">
                                        <span class="truncate font-medium" x-text="item.name"></span>
                                        <span class="text-[10px] text-muted shrink-0" x-text="item.sizeText"></span>
                                    </figcaption>
                                    <button
                                        type="button"
                                        class="preview-remove-btn"
                                        @click="removePhoto(index)"
                                        title="Hapus foto ini"
                                        aria-label="Hapus foto ini"
                                    >
                                        <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="size-3.5">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.74l.5 6a.75.75 0 1 1-1.498.12l-.5-6a.75.75 0 0 1 .748-.86Zm3.58.74a.75.75 0 0 0-1.498-.12l-.5 6a.75.75 0 0 0 1.498.12l.5-6Z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </figure>
                            </template>
                        </div>
                        @error('photos')<p class="field__error mt-2" id="photos-error">{{ $message }}</p>@enderror
                        @error('photos.*')<p class="field__error mt-2">{{ $message }}</p>@enderror
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

                    <x-site.turnstile />

                    <div>
                        <label class="report-consent" for="consent">
                            <input id="consent" type="checkbox" name="consent" value="1" @checked(old('consent')) required>
                            <span>Saya menyatakan usulan ini benar dan sopan. Pemerintah Desa Padelegan dapat meninjau dan menampilkannya secara anonim.</span>
                        </label>
                        @error('consent')<p class="field__error mt-2" id="consent-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <footer class="report-panel__actions report-panel__actions--end">
                    <div class="flex flex-col items-end gap-2">
                        <x-ui.action type="submit" x-bind:disabled="submitting || demoMode" x-text="demoMode ? 'Pengiriman belum aktif' : (submitting ? (uploadStatus || 'Mengirim usulan…') : 'Kirim usulan')">Kirim usulan</x-ui.action>
                        <p x-cloak x-show="submitError" x-text="submitError" class="max-w-sm text-right text-xs leading-5 text-danger" role="alert"></p>
                    </div>
                </footer>
            </section>
        </form>
    </div>
@endsection
