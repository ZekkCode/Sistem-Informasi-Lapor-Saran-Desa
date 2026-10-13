<?php

namespace App\Http\Requests\Admin;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AturKunciAdminRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('ubahKunciAdmin', $this->route('formulir'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'terkunci' => ['required', 'boolean'],
            'catatan' => ['nullable', 'string', 'max:200'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'terkunci.required' => 'Pilih status kunci sebelum menyimpan.',
            'terkunci.boolean' => 'Pilih status kunci sebelum menyimpan.',
            'catatan.max' => 'Catatan untuk warga maksimal 200 karakter. Persingkat lalu simpan kembali.',
        ];
    }
}
