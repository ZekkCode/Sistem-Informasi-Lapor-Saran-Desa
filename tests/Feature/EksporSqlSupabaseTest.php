<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EksporSqlSupabaseTest extends TestCase
{
    private string $folder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->folder = sys_get_temp_dir().'/padelegan-uji-sql-'.uniqid();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->folder);

        parent::tearDown();
    }

    public function test_skema_disusun_untuk_postgres_tanpa_membuka_koneksi(): void
    {
        $this->artisan('supabase:sql', ['--keluaran' => $this->folder])->assertSuccessful();

        $skema = File::get($this->folder.'/01_skema.sql');

        foreach (['users', 'reports', 'usulan', 'riwayat_usulan', 'migrations'] as $tabel) {
            $this->assertStringContainsString('create table "'.$tabel.'"', $skema);
            $this->assertStringContainsString('alter table public."'.$tabel.'" enable row level security;', $skema);
        }

        $this->assertStringContainsString('"id" bigserial not null primary key', $skema);
        $this->assertStringContainsString('drop column "latitude"', $skema);
        $this->assertStringContainsString('add column "workflow_version"', $skema);
        $this->assertStringContainsString("values ('2026_09_22_000110_create_riwayat_usulan_table', 1);", $skema);
        $this->assertStringContainsString('revoke all on all tables in schema public from anon, authenticated, service_role;', $skema);
        $this->assertStringStartsWith('--', $skema);
        $this->assertStringEndsWith("commit;\n", $skema);
    }

    public function test_data_awal_memakai_boolean_postgres_dan_memajukan_sequence(): void
    {
        $this->artisan('supabase:sql', ['--keluaran' => $this->folder])->assertSuccessful();

        $data = File::get($this->folder.'/02_data_awal.sql');

        $this->assertMatchesRegularExpression("/insert into \"dusuns\" .* values \\(1, 'BKL', 'Bangkal', null, true, /", $data);
        $this->assertStringContainsString("'QR Dusun Bangkal'", $data);
        $this->assertStringContainsString("select setval(pg_get_serial_sequence('public.\"subcategories\"', 'id')", $data);
        $this->assertStringNotContainsString('insert into "users"', $data);
        $this->assertStringNotContainsString('insert into "usulan"', $data);

        $usulan = File::get($this->folder.'/03_contoh_usulan.sql');
        $this->assertStringContainsString('insert into "usulan"', $usulan);
        $this->assertStringContainsString('insert into "riwayat_usulan"', $usulan);
    }

    public function test_akun_admin_berisi_hash_bukan_password_asli(): void
    {
        config(['padelegan.initial_accounts' => [[
            'key' => 'INITIAL_ADMIN_DESA',
            'role' => 'admin',
            'name' => 'Admin Desa Padelegan',
            'username' => 'admindesa',
            'email' => 'LaporPadelegan@gmail.com',
            'password' => 'RahasiaUji#2026',
        ]]]);

        $this->artisan('supabase:sql', ['--keluaran' => $this->folder])->assertSuccessful();

        $akun = File::get($this->folder.'/akun-admin.local.sql');

        $this->assertStringNotContainsString('RahasiaUji#2026', $akun);
        $this->assertMatchesRegularExpression('/\'\$2y\$\d{2}\$[^\']{53}\'/', $akun);
        $this->assertStringContainsString("'laporpadelegan@gmail.com'", $akun);
        $this->assertStringContainsString("'admin', true,", $akun);
        $this->assertStringContainsString('on conflict ("username") do update', $akun);
    }

    public function test_akun_admin_dilewati_saat_password_kosong(): void
    {
        config(['padelegan.initial_accounts' => []]);

        $this->artisan('supabase:sql', ['--keluaran' => $this->folder])->assertSuccessful();

        $this->assertFileDoesNotExist($this->folder.'/akun-admin.local.sql');
    }
}
