-- Data awal Sistem Lapor Padelegan: dusun, kategori, jenis masalah, sumber QR, dan pengaturan.
-- Jalankan setelah 01_skema.sql. Aman diulang karena baris yang sudah ada dilewati.

begin;

-- dusuns
insert into "dusuns" ("id", "code", "name", "description", "is_active", "created_at", "updated_at") values (1, 'BKL', 'Bangkal', null, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "dusuns" ("id", "code", "name", "description", "is_active", "created_at", "updated_at") values (2, 'ASB', 'Asam Batur', null, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "dusuns" ("id", "code", "name", "description", "is_active", "created_at", "updated_at") values (3, 'LTB', 'Laok Tambak', null, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "dusuns" ("id", "code", "name", "description", "is_active", "created_at", "updated_at") values (4, 'MDG', 'Modung', null, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "dusuns" ("id", "code", "name", "description", "is_active", "created_at", "updated_at") values (5, 'DTB', 'Dajah Tambak', null, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "dusuns" ("id", "code", "name", "description", "is_active", "created_at", "updated_at") values (6, 'MRA', 'Muarah', null, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
select setval(pg_get_serial_sequence('public."dusuns"', 'id'), (select coalesce(max("id"), 1) from "dusuns"));

-- categories
insert into "categories" ("id", "name", "slug", "icon", "sort_order", "is_active", "created_at", "updated_at") values (1, 'Fasilitas Desa', 'fasilitas-desa', null, 1, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "categories" ("id", "name", "slug", "icon", "sort_order", "is_active", "created_at", "updated_at") values (2, 'Lingkungan', 'lingkungan', null, 2, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "categories" ("id", "name", "slug", "icon", "sort_order", "is_active", "created_at", "updated_at") values (3, 'Infrastruktur', 'infrastruktur', null, 3, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "categories" ("id", "name", "slug", "icon", "sort_order", "is_active", "created_at", "updated_at") values (4, 'Pelayanan Desa', 'pelayanan-desa', null, 4, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "categories" ("id", "name", "slug", "icon", "sort_order", "is_active", "created_at", "updated_at") values (5, 'Aspirasi Masyarakat', 'aspirasi-masyarakat', null, 5, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
select setval(pg_get_serial_sequence('public."categories"', 'id'), (select coalesce(max("id"), 1) from "categories"));

-- subcategories
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (1, 1, 'Lampu dan Penerangan Jalan', 'lampu-dan-penerangan-jalan', 1, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (2, 1, 'Fasilitas Umum', 'fasilitas-umum', 2, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (3, 1, 'Lainnya', 'lainnya', 3, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (4, 2, 'Sampah', 'sampah', 1, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (5, 2, 'Drainase', 'drainase', 2, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (6, 2, 'Pencemaran', 'pencemaran', 3, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (7, 2, 'Lainnya', 'lainnya', 4, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (8, 3, 'Jalan', 'jalan', 1, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (9, 3, 'Jembatan', 'jembatan', 2, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (10, 3, 'Saluran Air', 'saluran-air', 3, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (11, 3, 'Lainnya', 'lainnya', 4, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (12, 4, 'Administrasi', 'administrasi', 1, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (13, 4, 'Informasi Publik', 'informasi-publik', 2, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (14, 4, 'Lainnya', 'lainnya', 3, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (15, 5, 'Usulan Kegiatan', 'usulan-kegiatan', 1, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (16, 5, 'Saran Pembangunan', 'saran-pembangunan', 2, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "subcategories" ("id", "category_id", "name", "slug", "sort_order", "is_active", "created_at", "updated_at") values (17, 5, 'Lainnya', 'lainnya', 3, true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
select setval(pg_get_serial_sequence('public."subcategories"', 'id'), (select coalesce(max("id"), 1) from "subcategories"));

-- qr_sources
insert into "qr_sources" ("id", "code", "name", "dusun_id", "placement", "is_active", "created_at", "updated_at") values (1, 'DSN01', 'QR Dusun Bangkal', 1, 'Titik informasi Dusun Bangkal', true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "qr_sources" ("id", "code", "name", "dusun_id", "placement", "is_active", "created_at", "updated_at") values (2, 'DSN02', 'QR Dusun Asam Batur', 2, 'Titik informasi Dusun Asam Batur', true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "qr_sources" ("id", "code", "name", "dusun_id", "placement", "is_active", "created_at", "updated_at") values (3, 'DSN03', 'QR Dusun Laok Tambak', 3, 'Titik informasi Dusun Laok Tambak', true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "qr_sources" ("id", "code", "name", "dusun_id", "placement", "is_active", "created_at", "updated_at") values (4, 'DSN04', 'QR Dusun Modung', 4, 'Titik informasi Dusun Modung', true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "qr_sources" ("id", "code", "name", "dusun_id", "placement", "is_active", "created_at", "updated_at") values (5, 'DSN05', 'QR Dusun Dajah Tambak', 5, 'Titik informasi Dusun Dajah Tambak', true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "qr_sources" ("id", "code", "name", "dusun_id", "placement", "is_active", "created_at", "updated_at") values (6, 'DSN06', 'QR Dusun Muarah', 6, 'Titik informasi Dusun Muarah', true, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
select setval(pg_get_serial_sequence('public."qr_sources"', 'id'), (select coalesce(max("id"), 1) from "qr_sources"));

-- settings
insert into "settings" ("id", "key", "value", "created_at", "updated_at") values (1, 'village_name', 'Desa Padelegan', '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "settings" ("id", "key", "value", "created_at", "updated_at") values (2, 'website_title', 'Sistem Lapor Padelegan', '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "settings" ("id", "key", "value", "created_at", "updated_at") values (3, 'contact_phone', null, '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
insert into "settings" ("id", "key", "value", "created_at", "updated_at") values (4, 'report_form_open', '1', '2026-10-10 23:24:49', '2026-10-10 23:24:49') on conflict do nothing;
select setval(pg_get_serial_sequence('public."settings"', 'id'), (select coalesce(max("id"), 1) from "settings"));

commit;
