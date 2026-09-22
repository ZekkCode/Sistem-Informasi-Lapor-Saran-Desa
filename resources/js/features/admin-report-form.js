export function adminReportForm(config) {
    return {
        directUpload: config || { enabled: false, url: '' },
        submitting: false,
        uploadStatus: '',
        error: '',

        validateFiles(event) {
            const input = event.currentTarget;
            const files = Array.from(input.files || []);
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const message = files.length > 5
                ? 'Pilih maksimal lima foto.'
                : files.some((file) => file.size > 5 * 1024 * 1024)
                    ? 'Ukuran setiap foto maksimal 5 MB.'
                    : files.some((file) => !allowedTypes.includes(file.type))
                        ? 'Format foto harus JPG, PNG, atau WEBP.'
                        : '';

            input.setCustomValidity(message);
            this.error = message;
        },

        async handleSubmit(event) {
            if (this.submitting) {
                event.preventDefault();
                return;
            }

            if (!this.$root.checkValidity()) {
                return;
            }

            const photoInput = this.$root.querySelector('#after_photos');
            const files = Array.from(photoInput?.files || []);

            if (!this.directUpload.enabled || files.length === 0) {
                this.submitting = true;
                return;
            }

            event.preventDefault();
            this.submitting = true;
            this.error = '';
            this.uploadStatus = 'Menyiapkan foto…';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const response = await fetch(this.directUpload.url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        files: files.map((file) => ({ name: file.name, type: file.type, size: file.size })),
                    }),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok || !Array.isArray(payload.uploads)) {
                    throw new Error(this.responseError(payload, 'Foto belum dapat disiapkan.'));
                }

                for (let index = 0; index < payload.uploads.length; index += 1) {
                    const upload = payload.uploads[index];
                    this.uploadStatus = `Mengunggah foto ${index + 1}/${files.length}…`;
                    const uploaded = await fetch(upload.url, {
                        method: 'PUT',
                        headers: upload.headers || { 'Content-Type': files[index].type },
                        body: files[index],
                    });

                    if (!uploaded.ok) {
                        throw new Error(`Foto ${index + 1} gagal diunggah. Periksa koneksi lalu coba lagi.`);
                    }
                }

                payload.uploads.forEach((upload) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'uploaded_media[]';
                    input.value = upload.token;
                    this.$root.appendChild(input);
                });

                photoInput.disabled = true;
                this.uploadStatus = 'Menyimpan perubahan…';
                HTMLFormElement.prototype.submit.call(this.$root);
            } catch (error) {
                this.submitting = false;
                this.uploadStatus = '';
                this.error = error instanceof Error ? error.message : 'Unggah foto gagal. Coba kembali.';
            }
        },

        responseError(payload, fallback) {
            const firstError = payload?.errors && typeof payload.errors === 'object'
                ? Object.values(payload.errors).flat().find(Boolean)
                : null;

            return firstError ? String(firstError) : (payload?.message || fallback);
        },
    };
}

export function initSubmitOnce() {
    document.querySelectorAll('form[data-submit-once]').forEach((form) => {
        form.addEventListener('submit', () => {
            if (!form.checkValidity()) {
                return;
            }

            const button = form.querySelector('button[type="submit"]');

            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-disabled', 'true');

            if (button.dataset.submitLabel) {
                button.textContent = button.dataset.submitLabel;
            }
        });
    });
}
