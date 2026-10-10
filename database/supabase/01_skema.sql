-- Skema database Sistem Lapor Padelegan untuk Supabase.
-- Dibuat otomatis dari migrasi Laravel oleh `php artisan supabase:sql`. Jangan diedit manual.
-- Jalankan sekali di SQL Editor pada database kosong. Seluruh isi berjalan dalam satu transaksi,
-- jadi bila satu perintah gagal tidak ada perubahan yang tersimpan.
-- Setelah migrasi baru, jalankan `php artisan migrate` ke Supabase atau susun ulang berkas ini.

begin;

-- 0001_01_01_000000_create_users_table
create table "users" ("id" bigserial not null primary key, "name" varchar(255) not null, "username" varchar(80) not null, "email" varchar(255) null, "email_verified_at" timestamp(0) without time zone null, "password" varchar(255) not null, "role" varchar(32) not null default 'admin', "is_active" boolean not null default '1', "last_login_at" timestamp(0) without time zone null, "remember_token" varchar(100) null, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "users" add constraint "users_username_unique" unique ("username");
alter table "users" add constraint "users_email_unique" unique ("email");
create table "password_reset_tokens" ("email" varchar(255) not null, "token" varchar(255) not null, "created_at" timestamp(0) without time zone null);
alter table "password_reset_tokens" add primary key ("email");
create table "sessions" ("id" varchar(255) not null, "user_id" bigint null, "ip_address" varchar(45) null, "user_agent" text null, "payload" text not null, "last_activity" integer not null);
alter table "sessions" add primary key ("id");
create index "sessions_user_id_index" on "sessions" ("user_id");
create index "sessions_last_activity_index" on "sessions" ("last_activity");

-- 0001_01_01_000001_create_cache_table
create table "cache" ("key" varchar(255) not null, "value" text not null, "expiration" integer not null);
alter table "cache" add primary key ("key");
create table "cache_locks" ("key" varchar(255) not null, "owner" varchar(255) not null, "expiration" integer not null);
alter table "cache_locks" add primary key ("key");

-- 0001_01_01_000002_create_jobs_table
create table "jobs" ("id" bigserial not null primary key, "queue" varchar(255) not null, "payload" text not null, "attempts" smallint not null, "reserved_at" integer null, "available_at" integer not null, "created_at" integer not null);
create index "jobs_queue_index" on "jobs" ("queue");
create table "job_batches" ("id" varchar(255) not null, "name" varchar(255) not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text null, "cancelled_at" integer null, "created_at" integer not null, "finished_at" integer null);
alter table "job_batches" add primary key ("id");
create table "failed_jobs" ("id" bigserial not null primary key, "uuid" varchar(255) not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" timestamp(0) without time zone not null default CURRENT_TIMESTAMP);
alter table "failed_jobs" add constraint "failed_jobs_uuid_unique" unique ("uuid");

-- 2026_09_19_000100_create_dusuns_table
create table "dusuns" ("id" bigserial not null primary key, "code" varchar(20) not null, "name" varchar(120) not null, "description" text null, "is_active" boolean not null default '1', "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "dusuns" add constraint "dusuns_code_unique" unique ("code");

-- 2026_09_19_000110_create_categories_table
create table "categories" ("id" bigserial not null primary key, "name" varchar(100) not null, "slug" varchar(120) not null, "icon" varchar(50) null, "sort_order" integer not null default '0', "is_active" boolean not null default '1', "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "categories" add constraint "categories_slug_unique" unique ("slug");

-- 2026_09_19_000120_create_subcategories_table
create table "subcategories" ("id" bigserial not null primary key, "category_id" bigint not null, "name" varchar(120) not null, "slug" varchar(140) not null, "sort_order" integer not null default '0', "is_active" boolean not null default '1', "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "subcategories" add constraint "subcategories_category_id_foreign" foreign key ("category_id") references "categories" ("id") on delete restrict on update cascade;
alter table "subcategories" add constraint "subcategories_category_id_slug_unique" unique ("category_id", "slug");

-- 2026_09_19_000130_create_qr_sources_table
create table "qr_sources" ("id" bigserial not null primary key, "code" varchar(50) not null, "name" varchar(120) not null, "dusun_id" bigint null, "placement" varchar(200) null, "is_active" boolean not null default '1', "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "qr_sources" add constraint "qr_sources_dusun_id_foreign" foreign key ("dusun_id") references "dusuns" ("id") on delete set null;
alter table "qr_sources" add constraint "qr_sources_code_unique" unique ("code");

-- 2026_09_19_000140_create_reports_table
create table "reports" ("id" bigserial not null primary key, "report_code" varchar(30) not null, "reporter_name" varchar(120) not null, "reporter_phone" varchar(30) not null, "dusun_id" bigint not null, "subcategory_id" bigint not null, "qr_source_id" bigint null, "title" varchar(120) not null, "description" text not null, "location_text" varchar(255) not null, "latitude" decimal(10, 7) null, "longitude" decimal(10, 7) null, "citizen_priority" varchar(32) not null, "admin_priority" varchar(32) null, "verification_status" varchar(32) not null default 'pending', "status" varchar(32) not null default 'not_started', "current_public_note" text null, "verified_by" bigint null, "verified_at" timestamp(0) without time zone null, "completed_at" timestamp(0) without time zone null, "submitted_at" timestamp(0) without time zone not null default CURRENT_TIMESTAMP, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null, "deleted_at" timestamp(0) without time zone null);
alter table "reports" add constraint "reports_dusun_id_foreign" foreign key ("dusun_id") references "dusuns" ("id") on delete restrict;
alter table "reports" add constraint "reports_subcategory_id_foreign" foreign key ("subcategory_id") references "subcategories" ("id") on delete restrict;
alter table "reports" add constraint "reports_qr_source_id_foreign" foreign key ("qr_source_id") references "qr_sources" ("id") on delete set null;
alter table "reports" add constraint "reports_verified_by_foreign" foreign key ("verified_by") references "users" ("id") on delete set null;
create index "reports_submitted_at_index" on "reports" ("submitted_at");
create index "reports_status_index" on "reports" ("status");
create index "reports_verification_status_index" on "reports" ("verification_status");
create index "reports_public_listing_index" on "reports" ("verification_status", "status", "submitted_at");
alter table "reports" add constraint "reports_report_code_unique" unique ("report_code");

-- 2026_09_19_000150_create_report_media_table
create table "report_media" ("id" bigserial not null primary key, "report_id" bigint not null, "media_type" varchar(32) not null, "file_path" varchar(255) not null, "mime_type" varchar(100) not null, "file_size" bigint not null, "caption" varchar(255) null, "uploaded_by" bigint null, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "report_media" add constraint "report_media_report_id_foreign" foreign key ("report_id") references "reports" ("id") on delete cascade;
alter table "report_media" add constraint "report_media_uploaded_by_foreign" foreign key ("uploaded_by") references "users" ("id") on delete set null;

-- 2026_09_19_000160_create_report_status_histories_table
create table "report_status_histories" ("id" bigserial not null primary key, "report_id" bigint not null, "verification_status" varchar(32) null, "status" varchar(32) not null, "public_note" text null, "internal_note" text null, "changed_by" bigint null, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "report_status_histories" add constraint "report_status_histories_report_id_foreign" foreign key ("report_id") references "reports" ("id") on delete cascade;
alter table "report_status_histories" add constraint "report_status_histories_changed_by_foreign" foreign key ("changed_by") references "users" ("id") on delete set null;

-- 2026_09_19_000170_create_audit_logs_table
create table "audit_logs" ("id" bigserial not null primary key, "user_id" bigint null, "action" varchar(100) not null, "entity_type" varchar(100) not null, "entity_id" bigint null, "meta" json null, "ip_address" varchar(45) null, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "audit_logs" add constraint "audit_logs_user_id_foreign" foreign key ("user_id") references "users" ("id") on delete set null;
create index "audit_logs_entity_type_entity_id_index" on "audit_logs" ("entity_type", "entity_id");

-- 2026_09_19_000180_create_settings_table
create table "settings" ("id" bigserial not null primary key, "key" varchar(100) not null, "value" text null, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "settings" add constraint "settings_key_unique" unique ("key");

-- 2026_09_20_000000_remove_report_coordinates
alter table "reports" drop column "latitude", drop column "longitude";

-- 2026_09_20_010000_add_storage_mirroring_to_report_media_table
alter table "report_media" add column "storage_disk" varchar(50) not null default 'public';
alter table "report_media" add column "google_drive_status" varchar(20) null;
alter table "report_media" add column "google_drive_file_id" varchar(255) null;
alter table "report_media" add column "google_drive_attempts" smallint not null default '0';
alter table "report_media" add column "google_drive_synced_at" timestamp(0) without time zone null;
alter table "report_media" add column "google_drive_error" text null;
create index "report_media_google_drive_status_index" on "report_media" ("google_drive_status");

-- 2026_09_20_020000_add_unique_storage_path_to_report_media_table
alter table "report_media" add constraint "report_media_disk_path_unique" unique ("storage_disk", "file_path");

-- 2026_09_21_000000_add_submission_token_to_reports_table
alter table "reports" add column "submission_token" uuid null;
alter table "reports" add constraint "reports_submission_token_unique" unique ("submission_token");

-- 2026_09_21_010000_add_workflow_version_to_reports_table
alter table "reports" add column "workflow_version" integer not null default '0';

-- 2026_09_22_000100_create_usulan_table
create table "usulan" ("id" bigserial not null primary key, "kode" varchar(30) not null, "token_kirim" uuid null, "versi_alur" integer not null default '0', "nama_pengusul" varchar(120) null, "telepon_pengusul" varchar(30) null, "dusun_id" bigint not null, "jenis" varchar(32) not null, "judul" varchar(120) not null, "isi" text not null, "status" varchar(32) not null default 'baru', "tampil_publik" boolean not null default '0', "catatan_publik" text null, "ditangani_oleh" bigint null, "dipublikasikan_pada" timestamp(0) without time zone null, "dikirim_pada" timestamp(0) without time zone not null default CURRENT_TIMESTAMP, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null, "deleted_at" timestamp(0) without time zone null);
alter table "usulan" add constraint "usulan_dusun_id_foreign" foreign key ("dusun_id") references "dusuns" ("id") on delete restrict;
alter table "usulan" add constraint "usulan_ditangani_oleh_foreign" foreign key ("ditangani_oleh") references "users" ("id") on delete set null;
create index "usulan_dikirim_pada_index" on "usulan" ("dikirim_pada");
create index "usulan_status_index" on "usulan" ("status");
create index "usulan_tampil_publik_index" on "usulan" ("tampil_publik");
create index "usulan_daftar_publik_index" on "usulan" ("tampil_publik", "status", "dikirim_pada");
alter table "usulan" add constraint "usulan_kode_unique" unique ("kode");
alter table "usulan" add constraint "usulan_token_kirim_unique" unique ("token_kirim");

-- 2026_09_22_000110_create_riwayat_usulan_table
create table "riwayat_usulan" ("id" bigserial not null primary key, "usulan_id" bigint not null, "status" varchar(32) not null, "catatan_publik" text null, "catatan_internal" text null, "diubah_oleh" bigint null, "created_at" timestamp(0) without time zone null, "updated_at" timestamp(0) without time zone null);
alter table "riwayat_usulan" add constraint "riwayat_usulan_usulan_id_foreign" foreign key ("usulan_id") references "usulan" ("id") on delete cascade;
alter table "riwayat_usulan" add constraint "riwayat_usulan_diubah_oleh_foreign" foreign key ("diubah_oleh") references "users" ("id") on delete set null;

-- Riwayat migrasi agar `php artisan migrate` berikutnya hanya menjalankan migrasi baru.
create table "migrations" ("id" serial not null primary key, "migration" varchar(255) not null, "batch" integer not null);
insert into "migrations" ("migration", "batch") values ('0001_01_01_000000_create_users_table', 1);
insert into "migrations" ("migration", "batch") values ('0001_01_01_000001_create_cache_table', 1);
insert into "migrations" ("migration", "batch") values ('0001_01_01_000002_create_jobs_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000100_create_dusuns_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000110_create_categories_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000120_create_subcategories_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000130_create_qr_sources_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000140_create_reports_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000150_create_report_media_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000160_create_report_status_histories_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000170_create_audit_logs_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_19_000180_create_settings_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_20_000000_remove_report_coordinates', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_20_010000_add_storage_mirroring_to_report_media_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_20_020000_add_unique_storage_path_to_report_media_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_21_000000_add_submission_token_to_reports_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_21_010000_add_workflow_version_to_reports_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_22_000100_create_usulan_table', 1);
insert into "migrations" ("migration", "batch") values ('2026_09_22_000110_create_riwayat_usulan_table', 1);

-- Kunci akses REST API Supabase (/rest/v1).
-- Laravel terhubung sebagai pemilik tabel lewat koneksi PostgreSQL, jadi tidak terpengaruh.
-- Tanpa langkah ini, publishable key yang memang publik bisa membaca tabel users dan reports.
alter table public."users" enable row level security;
alter table public."password_reset_tokens" enable row level security;
alter table public."sessions" enable row level security;
alter table public."cache" enable row level security;
alter table public."cache_locks" enable row level security;
alter table public."jobs" enable row level security;
alter table public."job_batches" enable row level security;
alter table public."failed_jobs" enable row level security;
alter table public."dusuns" enable row level security;
alter table public."categories" enable row level security;
alter table public."subcategories" enable row level security;
alter table public."qr_sources" enable row level security;
alter table public."reports" enable row level security;
alter table public."report_media" enable row level security;
alter table public."report_status_histories" enable row level security;
alter table public."audit_logs" enable row level security;
alter table public."settings" enable row level security;
alter table public."usulan" enable row level security;
alter table public."riwayat_usulan" enable row level security;
alter table public."migrations" enable row level security;
revoke all on all tables in schema public from anon, authenticated, service_role;
revoke all on all sequences in schema public from anon, authenticated, service_role;
alter default privileges for role postgres in schema public revoke all on tables from anon, authenticated, service_role;
alter default privileges for role postgres in schema public revoke all on sequences from anon, authenticated, service_role;

commit;
