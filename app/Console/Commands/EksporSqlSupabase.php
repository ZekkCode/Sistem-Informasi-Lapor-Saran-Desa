<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Menyusun berkas SQL untuk SQL Editor Supabase dari migrasi dan seeder Laravel,
 * sehingga skema di Supabase sama persis dengan hasil `php artisan migrate`.
 *
 * Skema dibentuk lewat mode pretend koneksi pgsql tanpa membuka koneksi ke
 * server. Data awal diambil dari hasil seeder pada SQLite sementara.
 */
class EksporSqlSupabase extends Command
{
    protected $signature = 'supabase:sql {--keluaran= : Folder tujuan berkas SQL (bawaan database/supabase)}';

    protected $description = 'Susun berkas SQL skema, data awal, dan akun admin untuk SQL Editor Supabase';

    private const KONEKSI_PG = 'ekspor_pgsql';

    private const KONEKSI_SQLITE = 'ekspor_sqlite';

    /** Urutan mengikuti foreign key. */
    private const TABEL_DATA_AWAL = ['dusuns', 'categories', 'subcategories', 'qr_sources', 'settings'];

    private const TABEL_CONTOH_USULAN = ['usulan', 'riwayat_usulan'];

    public function handle(Migrator $migrator): int
    {
        $folder = $this->option('keluaran') ?: database_path('supabase');
        File::ensureDirectoryExists($folder);

        $koneksiAwal = DB::getDefaultConnection();

        try {
            File::put($folder.'/01_skema.sql', $this->susunSkema($migrator));
            [$dataAwal, $contohUsulan] = $this->susunData();
            File::put($folder.'/02_data_awal.sql', $dataAwal);
            File::put($folder.'/03_contoh_usulan.sql', $contohUsulan);
        } finally {
            DB::setDefaultConnection($koneksiAwal);
            DB::purge(self::KONEKSI_PG);
        }

        $akunAdmin = $this->susunAkunAdmin();

        if ($akunAdmin !== null) {
            File::put($folder.'/akun-admin.local.sql', $akunAdmin);
        }

        $this->components->info('Berkas SQL tersimpan di '.$folder);
        $this->components->bulletList(array_filter([
            '01_skema.sql: tabel, indeks, riwayat migrasi, dan kunci akses REST API',
            '02_data_awal.sql: dusun, kategori, jenis masalah, sumber QR, pengaturan',
            '03_contoh_usulan.sql: usulan contoh untuk demo (opsional)',
            $akunAdmin !== null ? 'akun-admin.local.sql: akun petugas berisi hash password (jangan di-commit)' : null,
        ]));

        if ($akunAdmin === null) {
            $this->components->warn('akun-admin.local.sql tidak dibuat karena INITIAL_ADMIN_PASSWORD dan INITIAL_ADMIN_DESA_PASSWORD kosong.');
        }

        return self::SUCCESS;
    }

    private function susunSkema(Migrator $migrator): string
    {
        // Koneksi pgsql tiruan. Mode pretend tidak mengeksekusi query, jadi tidak
        // perlu server PostgreSQL maupun ekstensi pdo_pgsql. PDO diganti closure
        // yang langsung gagal agar tidak ada koneksi keluar secara tidak sengaja.
        config(['database.connections.'.self::KONEKSI_PG => [
            'driver' => 'pgsql',
            'database' => 'postgres',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
        ]]);
        DB::extend(self::KONEKSI_PG, fn (array $config) => new class(
            fn () => throw new RuntimeException('Ekspor SQL tidak boleh membuka koneksi PostgreSQL.'),
            $config['database'],
            '',
            $config,
        ) extends PostgresConnection {
            // Schema::hasColumn menanyakan versi server. Supabase memakai PostgreSQL 17.
            public function getServerVersion(): string
            {
                return '17.0';
            }
        });
        DB::purge(self::KONEKSI_PG);
        DB::setDefaultConnection(self::KONEKSI_PG);
        $pg = DB::connection(self::KONEKSI_PG);

        $berkas = $migrator->getMigrationFiles([database_path('migrations')]);
        $bagian = [];

        foreach ($berkas as $nama => $path) {
            $migrasi = require $path;
            $perintah = [];

            foreach ($pg->pretend(fn () => $migrasi->up()) as $kueri) {
                // Pemeriksaan seperti Schema::hasColumn ikut tercatat sebagai select.
                if (str_starts_with(strtolower(ltrim($kueri['query'])), 'select')) {
                    continue;
                }

                if ($kueri['bindings'] !== []) {
                    throw new RuntimeException("Migrasi {$nama} memakai query dengan binding, susun SQL-nya secara manual.");
                }

                $perintah[] = rtrim($kueri['query'], ';').';';
            }

            $bagian[] = "-- {$nama}\n".implode("\n", $perintah);
        }

        $tabelMigrasi = $this->namaTabelMigrasi();
        $repositori = new DatabaseMigrationRepository(app('db'), $tabelMigrasi);
        $repositori->setSource(self::KONEKSI_PG);
        $perintahMigrasi = array_map(
            fn (array $kueri) => rtrim($kueri['query'], ';').';',
            $pg->pretend(fn () => $repositori->createRepository()),
        );

        $catatanMigrasi = array_map(
            fn (string $nama) => sprintf('insert into "%s" ("migration", "batch") values (%s, 1);', $tabelMigrasi, $this->literal($nama)),
            array_keys($berkas),
        );

        $semuaPerintah = implode("\n", [...$bagian, ...$perintahMigrasi]);
        preg_match_all('/create table "([^"]+)"/i', $semuaPerintah, $cocok);
        $tabel = array_values(array_unique($cocok[1]));

        $kunciAkses = array_map(
            fn (string $nama) => sprintf('alter table public."%s" enable row level security;', $nama),
            $tabel,
        );

        return implode("\n\n", [
            $this->kepala([
                'Skema database Sistem Lapor Padelegan untuk Supabase.',
                'Dibuat otomatis dari migrasi Laravel oleh `php artisan supabase:sql`. Jangan diedit manual.',
                'Jalankan sekali di SQL Editor pada database kosong. Seluruh isi berjalan dalam satu transaksi,',
                'jadi bila satu perintah gagal tidak ada perubahan yang tersimpan.',
                'Setelah migrasi baru, jalankan `php artisan migrate` ke Supabase atau susun ulang berkas ini.',
            ]),
            'begin;',
            implode("\n\n", $bagian),
            "-- Riwayat migrasi agar `php artisan migrate` berikutnya hanya menjalankan migrasi baru.\n"
                .implode("\n", [...$perintahMigrasi, ...$catatanMigrasi]),
            implode("\n", [
                '-- Kunci akses REST API Supabase (/rest/v1).',
                '-- Laravel terhubung sebagai pemilik tabel lewat koneksi PostgreSQL, jadi tidak terpengaruh.',
                '-- Tanpa langkah ini, publishable key yang memang publik bisa membaca tabel users dan reports.',
                ...$kunciAkses,
                'revoke all on all tables in schema public from anon, authenticated, service_role;',
                'revoke all on all sequences in schema public from anon, authenticated, service_role;',
                'alter default privileges for role postgres in schema public revoke all on tables from anon, authenticated, service_role;',
                'alter default privileges for role postgres in schema public revoke all on sequences from anon, authenticated, service_role;',
            ]),
            'commit;',
        ])."\n";
    }

    /**
     * Jalankan migrasi dan seeder pada SQLite sementara, lalu salin barisnya
     * menjadi INSERT PostgreSQL. ID dipertahankan agar foreign key tetap cocok.
     *
     * @return array{0: string, 1: string}
     */
    private function susunData(): array
    {
        $path = tempnam(sys_get_temp_dir(), 'padelegan-ekspor-');

        config(['database.connections.'.self::KONEKSI_SQLITE => [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);
        DB::purge(self::KONEKSI_SQLITE);

        // Akun petugas tidak ikut data awal. Hash password disusun terpisah.
        $akunAwal = config('padelegan.initial_accounts');
        config(['padelegan.initial_accounts' => []]);

        try {
            $this->callSilently('migrate', ['--database' => self::KONEKSI_SQLITE, '--force' => true]);
            $this->callSilently('db:seed', ['--database' => self::KONEKSI_SQLITE, '--force' => true]);

            $dataAwal = $this->salinTabel(self::TABEL_DATA_AWAL, [
                'Data awal Sistem Lapor Padelegan: dusun, kategori, jenis masalah, sumber QR, dan pengaturan.',
                'Jalankan setelah 01_skema.sql. Aman diulang karena baris yang sudah ada dilewati.',
            ]);
            $contohUsulan = $this->salinTabel(self::TABEL_CONTOH_USULAN, [
                'Usulan contoh untuk demo ke buyer. Opsional, lewati untuk database produksi yang bersih.',
                'Jalankan setelah 02_data_awal.sql karena merujuk ID dusun.',
            ]);
        } finally {
            config(['padelegan.initial_accounts' => $akunAwal]);
            DB::purge(self::KONEKSI_SQLITE);
            @unlink($path);
        }

        return [$dataAwal, $contohUsulan];
    }

    /**
     * @param  list<string>  $tabel
     * @param  list<string>  $keterangan
     */
    private function salinTabel(array $tabel, array $keterangan): string
    {
        $sqlite = DB::connection(self::KONEKSI_SQLITE);
        $bagian = [];

        foreach ($tabel as $nama) {
            $kolomBoolean = collect(Schema::connection(self::KONEKSI_SQLITE)->getColumns($nama))
                ->filter(fn (array $kolom) => in_array(strtolower($kolom['type_name']), ['tinyint', 'boolean'], true))
                ->pluck('name')
                ->all();

            $perintah = [];

            foreach ($sqlite->table($nama)->orderBy('id')->get() as $baris) {
                $baris = (array) $baris;
                $kolom = implode(', ', array_map(fn ($k) => '"'.$k.'"', array_keys($baris)));
                $nilai = implode(', ', array_map(
                    fn ($k, $v) => $this->literal($v, in_array($k, $kolomBoolean, true)),
                    array_keys($baris),
                    $baris,
                ));
                $perintah[] = sprintf('insert into "%s" (%s) values (%s) on conflict do nothing;', $nama, $kolom, $nilai);
            }

            // ID diisi eksplisit, jadi sequence perlu dimajukan agar data baru tidak bentrok.
            $perintah[] = sprintf(
                "select setval(pg_get_serial_sequence('public.\"%s\"', 'id'), (select coalesce(max(\"id\"), 1) from \"%s\"));",
                $nama,
                $nama,
            );

            $bagian[] = "-- {$nama}\n".implode("\n", $perintah);
        }

        return implode("\n\n", [$this->kepala($keterangan), 'begin;', ...$bagian, 'commit;'])."\n";
    }

    private function susunAkunAdmin(): ?string
    {
        $perintah = [];
        $sekarang = $this->literal(now()->format('Y-m-d H:i:s'));

        foreach (config('padelegan.initial_accounts', []) as $akun) {
            if (blank($akun['password'])) {
                continue;
            }

            $email = filled($akun['email'] ?? null) ? strtolower(trim($akun['email'])) : null;

            $perintah[] = sprintf(
                'insert into "users" ("name", "username", "email", "password", "role", "is_active", "created_at", "updated_at") '
                .'values (%s, %s, %s, %s, %s, true, %s, %s) '
                .'on conflict ("username") do update set "name" = excluded."name", "email" = coalesce(excluded."email", "users"."email"), '
                .'"password" = excluded."password", "role" = excluded."role", "is_active" = true, "updated_at" = excluded."updated_at";',
                $this->literal($akun['name']),
                $this->literal($akun['username']),
                $this->literal($email),
                $this->literal(Hash::make($akun['password'])),
                $this->literal($akun['role']),
                $sekarang,
                $sekarang,
            );
        }

        if ($perintah === []) {
            return null;
        }

        return implode("\n\n", [
            $this->kepala([
                'Akun petugas awal dari INITIAL_ADMIN_* dan INITIAL_ADMIN_DESA_* pada .env.',
                'Berisi hash password. Berkas ini diabaikan Git, jangan dibagikan atau di-commit.',
                'Jalankan setelah 01_skema.sql, lalu hapus berkas ini. Menjalankan ulang akan memperbarui password.',
            ]),
            'begin;',
            implode("\n", $perintah),
            'commit;',
        ])."\n";
    }

    private function literal(mixed $nilai, bool $boolean = false): string
    {
        if ($nilai === null) {
            return 'null';
        }

        if ($boolean) {
            return (bool) $nilai ? 'true' : 'false';
        }

        if (is_int($nilai) || is_float($nilai)) {
            return (string) $nilai;
        }

        return "'".str_replace("'", "''", (string) $nilai)."'";
    }

    private function namaTabelMigrasi(): string
    {
        $pengaturan = config('database.migrations');

        return is_array($pengaturan) ? ($pengaturan['table'] ?? 'migrations') : ($pengaturan ?: 'migrations');
    }

    /** @param  list<string>  $baris */
    private function kepala(array $baris): string
    {
        return implode("\n", array_map(fn (string $b) => '-- '.$b, $baris));
    }
}
