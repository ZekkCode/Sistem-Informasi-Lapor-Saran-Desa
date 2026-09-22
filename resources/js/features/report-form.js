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

        get subcategories() {
            return this.subcategoryMap[this.selectedCategory] || [];
        },

        changeCategory() {
            this.selectedSubcategory = '';
        },

        goTo(nextStep) {
            if (nextStep > this.step && !this.validateCurrentStep()) {
                return;
            }

            this.step = nextStep;
            this.focusCurrentStep();
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

            invalidField.reportValidity();
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
            const input = event.currentTarget;
            const files = Array.from(input.files || []);
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const oversized = files.some((file) => file.size > 5 * 1024 * 1024);
            const invalidType = files.some((file) => !allowedTypes.includes(file.type));

            this.previews.forEach((item) => URL.revokeObjectURL(item.url));
            input.setCustomValidity(
                files.length > 5
                    ? 'Pilih maksimal lima foto.'
                    : oversized
                        ? 'Ukuran setiap foto maksimal 5 MB.'
                        : invalidType
                            ? 'Format foto harus JPG, PNG, atau WEBP.'
                            : '',
            );
            this.previews = files.slice(0, 5).map((file) => ({
                name: file.name,
                url: URL.createObjectURL(file),
            }));
            this.photoMessage = files.length > 5
                ? `${files.length} foto dipilih. Kurangi menjadi maksimal lima.`
                : oversized
                    ? 'Ada foto berukuran lebih dari 5 MB.'
                    : invalidType
                        ? 'Gunakan foto JPG, PNG, atau WEBP.'
                : files.length > 0
                    ? `${files.length} foto dipilih.`
                    : 'Belum ada foto dipilih';
        },

        async handleSubmit(event) {
            this.submitError = '';

            if (this.demoMode) {
                event.preventDefault();
                this.submitError = 'Pengiriman belum aktif karena database belum siap.';
                return;
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
            this.uploadStatus = 'Menyiapkan foto…';

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
                        files: files.map((file) => ({
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
                    const file = files[index];
                    this.uploadStatus = `Mengunggah foto ${index + 1}/${files.length}…`;

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
                this.submitting = false;
                this.uploadStatus = '';
                this.submitError = error instanceof Error
                    ? error.message
                    : 'Foto gagal diunggah. Periksa koneksi lalu coba lagi.';
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
