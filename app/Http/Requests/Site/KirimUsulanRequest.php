<?php

namespace App\Http\Requests\Site;

use App\Enums\JenisUsulan;
use App\Services\Site\SiteDataService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class KirimUsulanRequest extends FormRequest
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
        $databaseSiap = app(SiteDataService::class)->bisaMenerimaUsulan();

        return [
            'token_kirim' => ['required', 'uuid'],
            'nama_pengusul' => ['nullable', 'string', 'max:120'],
            'telepon_pengusul' => ['nullable', 'string', 'max:30', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'dusun_id' => $databaseSiap
                ? ['required', 'integer', Rule::exists('dusuns', 'id')->where('is_active', true)]
                : ['required', 'integer'],
            'jenis' => ['required', Rule::enum(JenisUsulan::class)],
            'judul' => ['required', 'string', 'max:120'],
            'isi' => ['required', 'string', 'min:20', 'max:5000'],
            'consent' => ['accepted'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! app(SiteDataService::class)->bisaMenerimaUsulan()) {
                    $validator->errors()->add(
                        'submission',
                        'Pengiriman usulan belum aktif karena database belum tersambung atau data dusun belum diisi.',
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'telepon_pengusul.regex' => 'Gunakan nomor WhatsApp Indonesia yang valid, atau kosongkan bila ingin anonim.',
            'isi.min' => 'Jelaskan usulan sedikitnya 20 karakter agar petugas memahami maksudnya.',
            'judul.required' => 'Isi judul singkat usulan.',
            'dusun_id.required' => 'Pilih dusun yang berkaitan dengan usulan.',
            'jenis.required' => 'Pilih jenis usulan.',
            'consent.accepted' => 'Setujui pernyataan sebelum mengirim usulan.',
        ];
    }
}
