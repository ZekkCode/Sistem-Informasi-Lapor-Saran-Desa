<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusUsulan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PerbaruiAlurUsulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'versi_alur' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(StatusUsulan::class)],
            'tampil_publik' => ['nullable', 'boolean'],
            'catatan_publik' => ['nullable', 'string', 'max:2000'],
            'catatan_internal' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $this->boolean('tampil_publik')
                    && blank($this->input('catatan_publik'))
                ) {
                    $validator->errors()->add(
                        'catatan_publik',
                        'Isi catatan publik sebelum menampilkan usulan ke halaman warga.',
                    );
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.enum' => 'Pilih status tindak lanjut yang tersedia.',
        ];
    }
}
