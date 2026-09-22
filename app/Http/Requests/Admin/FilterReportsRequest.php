<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdminPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterReportsRequest extends FormRequest
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
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'verification_status' => ['nullable', Rule::enum(VerificationStatus::class)],
            'status' => ['nullable', Rule::enum(ReportStatus::class)],
            'admin_priority' => ['nullable', Rule::enum(AdminPriority::class)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
