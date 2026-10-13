<?php

namespace App\Services\Formulir;

use App\Enums\JenisFormulir;

final readonly class StatusKunciFormulir
{
    public function __construct(
        public JenisFormulir $formulir,
        public bool $kunciAdmin,
        public bool $kunciPengembang,
        public ?string $catatan,
    ) {}

    public function terbuka(): bool
    {
        return ! $this->kunciAdmin && ! $this->kunciPengembang;
    }

    /** Kunci pengembang selalu menimpa kunci admin. */
    public function kode(): string
    {
        return match (true) {
            $this->kunciPengembang => 'pengembang',
            $this->kunciAdmin => 'admin',
            default => 'terbuka',
        };
    }

    public function label(): string
    {
        return match ($this->kode()) {
            'pengembang' => 'Dikunci pengembang',
            'admin' => 'Ditutup admin',
            default => 'Dibuka',
        };
    }

    public function catatanPublik(): ?string
    {
        return $this->kode() === 'admin' ? $this->catatan : null;
    }

    public function pesanPublik(): string
    {
        $kata = $this->formulir->kataBenda();
        $kataAwal = ucfirst($kata);
        $kontak = config('padelegan.support_whatsapp_label');

        if ($this->kode() === 'pengembang') {
            return "Formulir {$kata} sedang dalam pemeliharaan sistem. {$kataAwal} Anda belum terkirim. "
                ."Coba lagi nanti atau hubungi petugas lewat WhatsApp {$kontak} bila mendesak.";
        }

        $pesan = "Formulir {$kata} sedang ditutup petugas desa. {$kataAwal} Anda belum terkirim. "
            ."Hubungi petugas lewat WhatsApp {$kontak} bila mendesak.";

        if ($this->catatanPublik() !== null) {
            $pesan .= " Catatan petugas: {$this->catatanPublik()}";
        }

        return $pesan;
    }
}
