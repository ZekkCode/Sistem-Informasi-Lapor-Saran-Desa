<?php

namespace App\Http\Requests\Site;

use App\Enums\CitizenPriority;
use App\Models\Subcategory;
use App\Services\Site\SiteDataService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReportRequest extends FormRequest
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
        $databaseReady = app(SiteDataService::class)->canAcceptReports();
        $directUploadRequired = (bool) config('report-media.direct_upload.required');

        return [
            'submission_token' => ['required', 'uuid'],
            'reporter_name' => ['required', 'string', 'max:120'],
            'reporter_phone' => ['required', 'string', 'max:30', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'dusun_id' => $databaseReady
                ? ['required', 'integer', Rule::exists('dusuns', 'id')->where('is_active', true)]
                : ['required', 'integer'],
            'location_text' => ['required', 'string', 'max:255'],
            'category_id' => $databaseReady
                ? ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)]
                : ['required', 'integer'],
            'subcategory_id' => $databaseReady
                ? ['required', 'integer', Rule::exists('subcategories', 'id')->where('is_active', true)]
                : ['required', 'integer'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'citizen_priority' => ['required', Rule::enum(CitizenPriority::class)],
            'photos' => [
                $directUploadRequired ? 'prohibited' : 'required_without:uploaded_media',
                'array',
                'min:1',
                'max:5',
            ],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'uploaded_media' => [
                $directUploadRequired ? 'required' : 'required_without:photos',
                'array',
                'min:1',
                'max:5',
            ],
            'uploaded_media.*' => ['required', 'string', 'max:4096'],
            'consent' => ['accepted'],
            'qr_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! app(SiteDataService::class)->canAcceptReports()) {
                    $validator->errors()->add(
                        'submission',
                        'Pengiriman belum aktif karena database belum tersambung atau data awal belum diisi.',
                    );

                    return;
                }

                if ($this->filled('uploaded_media') && ! config('report-media.direct_upload.enabled')) {
                    $validator->errors()->add('photos', 'Unggah langsung sedang tidak tersedia. Pilih ulang foto.');
                }

                if ($this->filled('uploaded_media') && $this->hasFile('photos')) {
                    $validator->errors()->add('photos', 'Gunakan satu metode unggah foto saja.');
                }

                if ($validator->errors()->hasAny(['category_id', 'subcategory_id'])) {
                    return;
                }

                $matchesCategory = Subcategory::query()
                    ->whereKey($this->integer('subcategory_id'))
                    ->where('category_id', $this->integer('category_id'))
                    ->where('is_active', true)
                    ->exists();

                if (! $matchesCategory) {
                    $validator->errors()->add('subcategory_id', 'Subkategori tidak sesuai dengan kategori yang dipilih.');
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
            'reporter_phone.regex' => 'Gunakan nomor WhatsApp Indonesia yang valid.',
            'description.min' => 'Jelaskan masalah sedikitnya 20 karakter agar petugas dapat memverifikasinya.',
            'photos.required' => 'Unggah minimal satu foto bukti.',
            'photos.required_without' => 'Unggah minimal satu foto bukti.',
            'photos.prohibited' => 'Foto perlu diunggah melalui proses unggah aman. Aktifkan JavaScript lalu pilih ulang foto.',
            'photos.max' => 'Maksimal lima foto untuk satu laporan.',
            'photos.*.image' => 'Setiap bukti harus berupa gambar.',
            'photos.*.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
            'uploaded_media.required_without' => 'Unggah minimal satu foto bukti.',
            'uploaded_media.required' => 'Foto belum selesai diunggah. Aktifkan JavaScript lalu pilih ulang foto.',
            'consent.accepted' => 'Setujui pernyataan data sebelum mengirim laporan.',
        ];
    }
}
