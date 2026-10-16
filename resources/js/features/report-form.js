export function reportForm(config) {
    return {
        step: Number(config.initialStep) || 1,
        submitting: false,
        selectedCategory: String(config.selectedCategory || ''),
        selectedSubcategory: String(config.selectedSubcategory || ''),
        subcategoryMap: config.subcategoryMap || {},
        directUpload: config.directUpload || { enabled: false, url: '' },
        demoMode: Boolean(config.demoMode),
        previews: [],
        photoMessage: 'Belum ada foto dipilih',
        uploadStatus: '',
        submitError: '',

        init() {
            this.updateSubcategories();
            this.$watch('selectedCategory', () => {
                this.updateSubcategories();
            });
            if (this.step === 3) {
                this.ensureTurnstileRendered();
            }
        },

        ensureTurnstileRendered() {
            if (typeof window.turnstile === 'undefined') return;
            this.$nextTick(() => {
                const el = this.$root.querySelector('.cf-turnstile');
                if (el && !el.querySelector('iframe')) {
                    try {
                        window.turnstile.render(el, {
                            sitekey: el.getAttribute('data-sitekey'),
                            theme: el.getAttribute('data-theme') || 'light',
                        });
                    } catch {
                        // Already initialized
                    }
                }
            });
        },

        updateSubcategories() {
            const select = this.$root.querySelector('#subcategory_id');
            if (!select) return;

            const currentVal = String(this.selectedSubcategory || '');
            select.innerHTML = '<option value="">Pilih jenis masalah</option>';

            if (!this.selectedCategory) {
                select.disabled = true;
                this.selectedSubcategory = '';
                return;
            }

            select.disabled = false;
            const items = this.subcategoryMap[String(this.selectedCategory)] || [];

            items.forEach((item) => {
                const opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = item.name;
                if (String(item.id) === currentVal) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            });

            if (items.some((item) => String(item.id) === currentVal)) {
                this.selectedSubcategory = currentVal;
                select.value = currentVal;
            } else {
                this.selectedSubcategory = '';
                select.value = '';
            }
        },

        changeCategory(event) {
            if (event?.target?.value !== undefined) {
                this.selectedCategory = event.target.value;
            }
            this.selectedSubcategory = '';
            this.updateSubcategories();
            this.$nextTick(() => {
                const subcategorySelect = this.$root.querySelector('#subcategory_id');
                if (subcategorySelect) {
                    subcategorySelect.removeAttribute('aria-invalid');
                    if (typeof window.clearFormFieldError === 'function') {
                        window.clearFormFieldError(subcategorySelect);
                    }
                }
            });
        },

        goTo(nextStep) {
            if (nextStep > this.step && !this.validateCurrentStep()) {
                return;
            }

            this.step = nextStep;
            this.focusCurrentStep();

            if (this.step === 3) {
                this.ensureTurnstileRendered();
            }
        },

        goBack(previousStep) {
            this.step = previousStep;
            this.focusCurrentStep();
        },

        validateCurrentStep() {
            const panel = this.$root.querySelector(`[data-form-step="${this.step}"]`);
            const fields = panel ? Array.from(panel.querySelectorAll('input, select, textarea')) : [];
            const invalidField = fields.find((field) => !field.disabled && !field.checkValidity());

            if (!invalidField) {
                return true;
            }

            if (typeof window.validateFormField === 'function') {
                window.validateFormField(invalidField);
            }
            invalidField.focus({ preventScroll: false });

            return false;
        },

        focusCurrentStep() {
            this.$nextTick(() => {
                const panel = this.$root.querySelector(`[data-form-step="${this.step}"]`);
                const heading = panel?.querySelector('h2');
                const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                panel?.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
                heading?.focus({ preventScroll: true });
            });
        },

        previewFiles(event) {
            const input = event?.currentTarget || event?.target || this.$root.querySelector('#photos');
            if (!input) return;

            const rawFiles = Array.from(input.files || []);
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const files = rawFiles.slice(0, 3);
            const oversized = files.some((file) => file.size > 5 * 1024 * 1024);
            const invalidType = files.some((file) => !allowedTypes.includes(file.type));
            const tooMany = rawFiles.length > 3;

            this.previews.forEach((item) => URL.revokeObjectURL(item.url));

            input.setCustomValidity(
                tooMany
                    ? 'Pilih maksimal 3 foto yang jelas.'
                    : oversized
                        ? 'Ukuran setiap foto maksimal 5 MB.'
                        : invalidType
                            ? 'Format foto harus JPG, PNG, atau WEBP.'
                            : '',
            );

            this.previews = files.map((file, idx) => ({
                id: `${idx}-${file.name}`,
                name: file.name,
                sizeText: (file.size / (1024 * 1024)).toFixed(1) + ' MB',
                url: URL.createObjectURL(file),
            }));

            if (tooMany) {
                this.photoMessage = `Maksimal 3 foto. Anda memilih ${rawFiles.length} foto, hanya 3 pertama yang digunakan.`;
            } else if (oversized) {
                this.photoMessage = 'Ada foto berukuran lebih dari 5 MB.';
            } else if (invalidType) {
                this.photoMessage = 'Gunakan format foto JPG, PNG, atau WEBP.';
            } else if (files.length > 0) {
                this.photoMessage = `${files.length} dari maksimal 3 foto dipilih.`;
            } else {
                this.photoMessage = 'Belum ada foto dipilih (Maksimal 3 foto)';
            }

            if (typeof window.validateFormField === 'function') {
                window.validateFormField(input);
            }
        },

        removePhoto(index) {
            const input = this.$root.querySelector('#photos');
            if (!input) return;

            try {
                const dataTransfer = new DataTransfer();
                const currentFiles = Array.from(input.files || []).slice(0, 3);
                currentFiles.forEach((file, idx) => {
                    if (idx !== index) {
                        dataTransfer.items.add(file);
                    }
                });
                input.files = dataTransfer.files;
            } catch {
                input.value = '';
            }

            this.previewFiles({ currentTarget: input, target: input });
        },

        async handleSubmit(event) {
            this.submitError = '';

            if (this.demoMode) {
                event.preventDefault();
                this.submitError = 'Pengiriman belum aktif karena database belum siap.';
                return;
            }

            const turnstileEl = this.$root.querySelector('.cf-turnstile');
            if (turnstileEl) {
                const token = this.$root.querySelector('[name="cf-turnstile-response"]')?.value?.trim();
                if (!token) {
                    event.preventDefault();
                    this.submitError = 'Selesaikan pemeriksaan keamanan sebelum mengirim formulir.';
                    const turnstileField = turnstileEl.closest('.field');
                    if (turnstileField) {
                        turnstileField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }
            }

            if (!this.directUpload.enabled) {
                this.submitting = true;
                return;
            }

            event.preventDefault();

            if (this.submitting || !this.$root.checkValidity()) {
                this.$root.reportValidity();
                return;
            }

            const photoInput = this.$root.querySelector('#photos');
            const files = Array.from(photoInput?.files || []);

            if (!files.length) {
                photoInput?.reportValidity();
                return;
            }

            this.submitting = true;
            this.uploadStatus = 'Mengoptimalkan foto…';

            let uploadFiles = files;
            try {
                uploadFiles = await Promise.all(files.map((file) => convertToWebpFile(file)));
            } catch {
                uploadFiles = files;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const authorization = await fetch(this.directUpload.url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        files: uploadFiles.map((file) => ({
                            name: file.name,
                            type: file.type,
                            size: file.size,
                        })),
                    }),
                });

                const payload = await authorization.json().catch(() => ({}));

                if (!authorization.ok || !Array.isArray(payload.uploads)) {
                    throw new Error(this.responseError(payload, 'Foto belum dapat disiapkan untuk diunggah.'));
                }

                for (let index = 0; index < payload.uploads.length; index += 1) {
                    const upload = payload.uploads[index];
                    const file = uploadFiles[index];
                    this.uploadStatus = `Mengunggah foto ${index + 1}/${uploadFiles.length}…`;

                    const uploaded = await fetch(upload.url, {
                        method: 'PUT',
                        headers: upload.headers || { 'Content-Type': file.type },
                        body: file,
                    });

                    if (!uploaded.ok) {
                        throw new Error(`Foto ${index + 1} gagal diunggah. Periksa koneksi lalu coba lagi.`);
                    }
                }

                this.$root.querySelectorAll('input[data-direct-upload-token]').forEach((input) => input.remove());
                payload.uploads.forEach((upload) => {
                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = 'uploaded_media[]';
                    token.value = upload.token;
                    token.dataset.directUploadToken = '';
                    this.$root.appendChild(token);
                });

                photoInput.disabled = true;
                this.uploadStatus = 'Menyimpan laporan…';
                HTMLFormElement.prototype.submit.call(this.$root);
            } catch (error) {
                console.warn('Unggah langsung gagal, beralih ke formulir standar:', error);
                if (photoInput) {
                    photoInput.disabled = false;
                }
                this.$root.querySelectorAll('input[data-direct-upload-token]').forEach((input) => input.remove());
                this.uploadStatus = 'Menyimpan laporan…';
                HTMLFormElement.prototype.submit.call(this.$root);
            }
        },

        responseError(payload, fallback) {
            const errors = payload?.errors;

            if (errors && typeof errors === 'object') {
                const first = Object.values(errors).flat().find(Boolean);

                if (first) {
                    return String(first);
                }
            }

            return typeof payload?.message === 'string' && payload.message !== ''
                ? payload.message
                : fallback;
        },

        destroy() {
            this.previews.forEach((item) => URL.revokeObjectURL(item.url));
        },
    };
}

function convertToWebpFile(file) {
    if (file.type === 'image/webp') {
        return Promise.resolve(file);
    }

    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = () => {
            URL.revokeObjectURL(url);
            let { width, height } = img;
            const maxDim = 1920;

            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = Math.round((height * maxDim) / width);
                    width = maxDim;
                } else {
                    width = Math.round((width * maxDim) / height);
                    height = maxDim;
                }
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');

            if (!ctx) {
                resolve(file);
                return;
            }

            ctx.drawImage(img, 0, 0, width, height);
            canvas.toBlob((blob) => {
                if (blob) {
                    const newName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
                    resolve(new File([blob], newName, { type: 'image/webp', lastModified: Date.now() }));
                } else {
                    resolve(file);
                }
            }, 'image/webp', 0.82);
        };

        img.onerror = () => {
            URL.revokeObjectURL(url);
            resolve(file);
        };

        img.src = url;
    });
}
