<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdminPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReportWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $directUploadRequired = (bool) config('report-media.direct_upload.required');

        return [
            'workflow_version' => ['required', 'integer', 'min:0'],
            'verification_status' => ['required', Rule::enum(VerificationStatus::class)],
            'status' => ['required', Rule::enum(ReportStatus::class)],
            'admin_priority' => ['required', Rule::enum(AdminPriority::class)],
            'public_note' => ['nullable', 'string', 'max:2000'],
            'internal_note' => ['nullable', 'string', 'max:2000'],
            'after_photos' => [$directUploadRequired ? 'prohibited' : 'nullable', 'array', 'max:5'],
            'after_photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'uploaded_media' => ['nullable', 'array', 'max:5'],
            'uploaded_media.*' => ['required', 'string', 'max:4096'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $this->string('verification_status')->toString() !== VerificationStatus::Verified->value
                    && $this->string('status')->toString() !== ReportStatus::NotStarted->value
                ) {
                    $validator->errors()->add(
                        'status',
                        'Laporan hanya dapat diproses atau diselesaikan setelah dinyatakan terverifikasi.',
                    );
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'after_photos.prohibited' => 'Bukti penanganan perlu diunggah melalui proses unggah aman.',
            'after_photos.max' => 'Maksimal lima foto penanganan.',
            'after_photos.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ];
    }
}
