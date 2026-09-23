<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaringUsulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'dusun_id' => ['nullable', 'integer', Rule::exists('dusuns', 'id')],
            'jenis' => ['nullable', Rule::enum(JenisUsulan::class)],
            'status' => ['nullable', Rule::enum(StatusUsulan::class)],
            'tampil_publik' => ['nullable', 'in:0,1'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
