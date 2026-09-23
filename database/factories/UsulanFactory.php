<?php

namespace Database\Factories;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use App\Models\Dusun;
use App\Models\Usulan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usulan>
 */
class UsulanFactory extends Factory
{
    protected $model = Usulan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => 'USL-'.now()->format('Y').'-'.Str::upper(Str::random(6)),
            'token_kirim' => Str::uuid()->toString(),
            'versi_alur' => 0,
            'nama_pengusul' => fake()->name(),
            'telepon_pengusul' => '0812'.fake()->numerify('########'),
            'dusun_id' => fn () => Dusun::query()->value('id')
                ?? Dusun::query()->create(['code' => 'DMY', 'name' => 'Dusun Uji', 'is_active' => true])->id,
            'jenis' => fake()->randomElement(JenisUsulan::cases())->value,
            'judul' => fake()->sentence(4),
            'isi' => fake()->paragraph(),
            'status' => StatusUsulan::Baru->value,
            'tampil_publik' => false,
            'catatan_publik' => 'Usulan diterima dan menunggu tinjauan petugas.',
            'dikirim_pada' => now(),
        ];
    }

    public function anonim(): static
    {
        return $this->state(fn () => [
            'nama_pengusul' => null,
            'telepon_pengusul' => null,
        ]);
    }

    public function tampilPublik(): static
    {
        return $this->state(fn () => [
            'tampil_publik' => true,
            'dipublikasikan_pada' => now(),
        ]);
    }
}
