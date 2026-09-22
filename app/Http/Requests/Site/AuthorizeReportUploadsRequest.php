<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorizeReportUploadsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'files' => [
                'required',
                'array',
                'min:1',
                'max:'.config('report-media.direct_upload.max_files', 5),
            ],
            'files.*.name' => ['required', 'string', 'max:255'],
            'files.*.type' => [
                'required',
                'string',
                Rule::in(config('report-media.direct_upload.allowed_mime_types', [])),
            ],
            'files.*.size' => [
                'required',
                'integer',
                'min:1',
                'max:'.config('report-media.direct_upload.max_file_size', 5 * 1024 * 1024),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'files.max' => 'Maksimal lima foto untuk satu laporan.',
            'files.*.type.in' => 'Format foto harus JPG, PNG, atau WEBP.',
            'files.*.size.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ];
    }
}
