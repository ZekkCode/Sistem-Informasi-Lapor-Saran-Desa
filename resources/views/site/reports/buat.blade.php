@extends('layouts.site')

@section('title', 'Buat Laporan')

@section('content')
    @php
        $subcategoryMap = $categories->mapWithKeys(fn ($category) => [
            (string) $category->id => $category->subcategories->map(fn ($subcategory) => [
                'id' => (string) $subcategory->id,
                'name' => $subcategory->name,
            ])->values(),
        ]);

        $errorFields = collect($errors->keys());
        $initialStep = match (true) {
            $errorFields->intersect(['category_id', 'subcategory_id', 'title', 'description', 'citizen_priority'])->isNotEmpty() => 1,
            $errorFields->intersect(['dusun_id', 'location_text', 'photos', 'photos.0', 'photos.1', 'photos.2', 'photos.3', 'photos.4'])->isNotEmpty() => 2,
            $errorFields->isNotEmpty() => 3,
            default => 1,
        };
    @endphp

    <section class="border-b border-padelegan-800/10 bg-white">
        <div class="site-shell grid gap-6 py-8 md:grid-cols-[minmax(0,1fr)_auto] md:items-end sm:py-10">
            <div data-aos="fade-up">
                <p class="text-sm font-medium text-padelegan-600">Layanan warga</p>
                <h1 class="mt-2 max-w-2xl text-3xl font-semibold leading-tight tracking-[-0.035em] text-padelegan-900 sm:text-4xl">Jelaskan masalah ke petugas desa.</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-padelegan-900/62 sm:text-base">Satu laporan untuk satu masalah. Sertakan patokan dan foto lokasi.</p>
            </div>
            <ul class="report-assurances" role="list" aria-label="Informasi layanan" data-aos="fade" data-aos-delay="100">
                <li>Tanpa akun</li>
                <li>Identitas privat</li>
            </ul>
        </div>
    </section>

    <div class="site-shell py-8 sm:py-10 lg:py-12">
        <form
            action="{{ route('reports.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="report-form"
            data-validate
            data-aos="fade-up"
            x-data="reportForm({
                initialStep: {{ $initialStep }},
                selectedCategory: @js((string) old('category_id', '')),
                selectedSubcategory: @js((string) old('subcategory_id', '')),
                subcategoryMap: @js($subcategoryMap),
                directUpload: @js($directUpload),
                demoMode: @js($usingDemoData),
            })"
            @submit="handleSubmit($event)"
        >
            @csrf
            <input type="hidden" name="submission_token" value="{{ $submissionToken }}">
            <input type="hidden" name="qr_code" value="{{ $qrSource?->code }}">

            @if ($usingDemoData)
                <x-ui.alert variant="warning" title="Mode pratinjau" class="mb-6">
                    Halaman memakai data contoh. Pengiriman aktif setelah database tersambung.
                </x-ui.alert>
            @endif

            <div class="report-progress" aria-label="Tahapan laporan">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm font-medium text-padelegan-800">Isi laporan</p>
                    <p class="text-xs text-padelegan-900/50">Isi semua kolom</p>
                </div>
                <ol class="report-progress__list" role="list">
                    @foreach (['Masalah', 'Lokasi & foto', 'Pelapor'] as $stepLabel)
                        <li
                            class="report-progress__item"
                            :class="{ 'is-active': step === {{ $loop->iteration }}, 'is-done': step > {{ $loop->iteration }} }"
                            :aria-current="step === {{ $loop->iteration }} ? 'step' : null"
                        >{{ $stepLabel }}</li>
                    @endforeach
                </ol>
                <div class="report-progress__track" aria-hidden="true">
                    <span :style="`inline-size: ${(step / 3) * 100}%`"></span>
                </div>
            </div>

            @if ($errors->any())
                <x-ui.alert variant="danger" title="Periksa bagian yang ditandai" class="mt-6" role="alert" aria-live="polite">
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-padelegan-900/70">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            @if ($qrSource)
                <x-ui.alert class="mt-6">
                    Formulir dari <strong class="font-semibold">{{ $qrSource->name }}</strong>. Periksa dusun sebelum melanjutkan.
                </x-ui.alert>
            @endif

            <section data-form-step="1" x-show="step === 1" x-transition.opacity.duration.150ms aria-labelledby="step-problem-title" class="report-panel">
                <header class="report-panel__header">
                    <p>Masalah</p>
                    <h2 id="step-problem-title" tabindex="-1">Jelaskan kondisi</h2>
                    <span>Pilih jenis masalah, tulis kondisi yang Anda temukan.</span>
                </header>

                <div class="report-panel__body">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="field">
                            <x-ui.label for="category_id" required>Kategori</x-ui.label>
                            <x-ui.select id="category_id" name="category_id" x-model="selectedCategory" @change="changeCategory()" required>
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.field-error name="category_id" />
                        </div>

                        <div class="field">
                            <x-ui.label for="subcategory_id" required>Jenis masalah</x-ui.label>
                            <x-ui.select id="subcategory_id" name="subcategory_id" x-model="selectedSubcategory" x-bind:disabled="!selectedCategory" required>
                                <option value="">Pilih jenis masalah</option>
                                <template x-for="item in subcategories" :key="item.id">
                                    <option :value="item.id" x-text="item.name"></option>
                                </template>
                            </x-ui.select>
                            <x-ui.field-error name="subcategory_id" />
                        </div>
                    </div>

                    <div class="field">
                        <x-ui.label for="title" required>Judul singkat</x-ui.label>
                        <x-ui.input id="title" name="title" :value="old('title')" required maxlength="120" enterkeyhint="next" placeholder="Contoh: Lampu jalan mati di pertigaan" />
                        <x-ui.field-error name="title" />
                    </div>

                    <div class="field">
                        <x-ui.label for="description" required>Kondisi yang ditemukan</x-ui.label>
                        <p class="field__hint" id="description-hint">Sebutkan kondisi, sejak kapan, dan dampaknya bagi warga.</p>
                        <x-ui.textarea describedby="description-hint" class="min-h-32" id="description" name="description" required minlength="20" maxlength="5000" placeholder="Contoh: Lampu tidak menyala sejak tiga malam lalu sehingga jalan sangat gelap.">{{ old('description') }}</x-ui.textarea>
                        <x-ui.field-error name="description" />
                    </div>

                    <fieldset>
                        <legend class="field__label">Seberapa mendesak?<abbr class="required-mark" title="Wajib diisi" aria-label="wajib">*</abbr></legend>
                        <p class="field__hint mt-1" id="priority-hint">Petugas menilai kembali prioritas saat verifikasi.</p>
                        <div class="report-priority">
                            @foreach ($priorities as $priority)
                                @php
                                    $priorityHelp = match ($priority->value) {
                                        'emergency' => 'Mengancam keselamatan warga.',
                                        'important' => 'Mengganggu layanan atau kegiatan warga.',
                                        default => 'Tidak berisiko langsung.',
                                    };
                                @endphp
                                <label class="report-choice" for="priority-{{ $priority->value }}">
                                    <input id="priority-{{ $priority->value }}" type="radio" name="citizen_priority" value="{{ $priority->value }}" aria-describedby="priority-hint" @checked(old('citizen_priority', 'normal') === $priority->value) required>
                                    <span><strong>{{ $priority->label() }}</strong><small>{{ $priorityHelp }}</small></span>
                                </label>
                            @endforeach
                        </div>
                        @error('citizen_priority')<p class="field__error mt-2" id="citizen_priority-error">{{ $message }}</p>@enderror
                    </fieldset>
                </div>

                <footer class="report-panel__actions report-panel__actions--end">
                    <button type="button" class="action action--primary" @click="goTo(2)">Lanjut ke lokasi</button>
                </footer>
            </section>

            <section data-form-step="2" x-show="step === 2" x-transition.opacity.duration.150ms aria-labelledby="step-location-title" class="report-panel">
                <header class="report-panel__header">
                    <p>Lokasi dan bukti</p>
                    <h2 id="step-location-title" tabindex="-1">Tentukan lokasi</h2>
                    <span>Tulis patokan terdekat, lampirkan foto kondisi terbaru.</span>
                </header>

                <div class="report-panel__body report-evidence-layout">
                    <div class="grid content-start gap-5">
                        <div class="field">
                            <x-ui.label for="dusun_id" required>Dusun</x-ui.label>
                            <x-ui.select id="dusun_id" name="dusun_id" required>
                                <option value="">Pilih dusun</option>
                                @foreach ($dusuns as $dusun)
                                    <option value="{{ $dusun->id }}" @selected((string) old('dusun_id', $qrSource?->dusun_id) === (string) $dusun->id)>{{ $dusun->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.field-error name="dusun_id" />
                        </div>

                        <div class="field">
                            <x-ui.label for="location_text" required>Patokan lokasi</x-ui.label>
                            <p class="field__hint" id="location-hint">Fasilitas umum atau arah jalan. Jangan sebutkan nama pemilik rumah.</p>
                            <x-ui.textarea describedby="location-hint" class="min-h-28" id="location_text" name="location_text" required maxlength="255" placeholder="Contoh: sebelah timur balai dusun, dekat jembatan kecil">{{ old('location_text') }}</x-ui.textarea>
                            <x-ui.field-error name="location_text" />
                        </div>
                    </div>

                    <div>
                        <span class="field__label">Foto kondisi<abbr class="required-mark" title="Wajib diisi" aria-label="wajib">*</abbr></span>
                        <p class="field__hint mt-1" id="photos-hint">Pilih 1 sampai 5 foto JPG, PNG, atau WEBP. Maksimal 5 MB per foto.</p>
                        <div class="report-upload mt-3">
                            <input id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple required aria-describedby="photos-hint photos-status" @change="previewFiles">
                            <label for="photos">
                                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 13v5.5A1.5 1.5 0 0 0 6.5 20h11a1.5 1.5 0 0 0 1.5-1.5V13"/></svg>
                                <span><strong>Pilih foto</strong><small>Ambil dari galeri atau kamera</small></span>
                            </label>
                        </div>
                        <p id="photos-status" class="mt-2 text-xs text-padelegan-900/55" aria-live="polite" x-text="photoMessage">Belum ada foto dipilih</p>

                        <div x-show="previews.length" class="report-preview-list">
                            <template x-for="item in previews" :key="item.url">
                                <figure>
                                    <img :src="item.url" alt="" class="aspect-[4/3] w-full object-cover">
                                    <figcaption x-text="item.name"></figcaption>
                                </figure>
                            </template>
                        </div>
                        @error('photos')<p class="field__error mt-2" id="photos-error">{{ $message }}</p>@enderror
                        @error('photos.*')<p class="field__error mt-2">{{ $message }}</p>@enderror
                    </div>
                </div>

                <footer class="report-panel__actions">
                    <button type="button" class="action action--quiet" @click="goBack(1)">Kembali</button>
                    <button type="button" class="action action--primary" @click="goTo(3)">Lanjut ke pelapor</button>
                </footer>
            </section>

            <section data-form-step="3" x-show="step === 3" x-transition.opacity.duration.150ms aria-labelledby="step-reporter-title" class="report-panel">
                <header class="report-panel__header">
                    <p>Pelapor</p>
                    <h2 id="step-reporter-title" tabindex="-1">Isi kontak pelapor</h2>
                        <span>Petugas pakai data ini untuk verifikasi laporan.</span>
                </header>

                <div class="report-panel__body">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="field">
                            <x-ui.label for="reporter_name" required>Nama lengkap</x-ui.label>
                            <x-ui.input id="reporter_name" name="reporter_name" :value="old('reporter_name')" autocomplete="name" required maxlength="120" placeholder="Nama sesuai KTP" />
                            <x-ui.field-error name="reporter_name" />
                        </div>
                        <div class="field">
                            <x-ui.label for="reporter_phone" required>Nomor WhatsApp</x-ui.label>
                            <p class="field__hint" id="phone-hint">Contoh: 081234567890</p>
                            <x-ui.input describedby="phone-hint" id="reporter_phone" name="reporter_phone" :value="old('reporter_phone')" autocomplete="tel" inputmode="tel" enterkeyhint="done" required maxlength="30" placeholder="081234567890" />
                            <x-ui.field-error name="reporter_phone" />
                        </div>
                    </div>

                    <p class="report-privacy-note">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 21s7-3.5 7-9V5.5L12 3 5 5.5V12c0 5.5 7 9 7 9Z"/><path d="m9.5 12 1.7 1.7 3.7-4"/></svg>
                        Petugas saja yang lihat nama dan WhatsApp.
                    </p>

                    <div>
                        <label class="report-consent" for="consent">
                            <input id="consent" type="checkbox" name="consent" value="1" @checked(old('consent')) required>
                            <span>Saya menyatakan informasi dan foto ini benar. Pemerintah Desa Padelegan dapat memakai untuk verifikasi dan penanganan.</span>
                        </label>
                        @error('consent')<p class="field__error mt-2" id="consent-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <footer class="report-panel__actions">
                    <button type="button" class="action action--quiet" @click="goBack(2)">Kembali</button>
                    <div class="flex flex-col items-end gap-2">
                        <x-ui.action type="submit" x-bind:disabled="submitting || demoMode" x-text="demoMode ? 'Pengiriman belum aktif' : (submitting ? (uploadStatus || 'Mengirim laporan…') : 'Kirim laporan')">Kirim laporan</x-ui.action>
                        <p class="text-right text-xs leading-5 text-padelegan-900/50">Jangan tutup halaman sampai nomor laporan muncul.</p>
                        <p x-cloak x-show="submitError" x-text="submitError" class="max-w-sm text-right text-xs leading-5 text-danger" role="alert"></p>
                    </div>
                </footer>
            </section>
        </form>
    </div>
@endsection
