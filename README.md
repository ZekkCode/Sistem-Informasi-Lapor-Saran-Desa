# Padelegan Lapor

Aplikasi pelaporan masyarakat Desa Padelegan berbasis Laravel, Blade, Tailwind CSS, dan Alpine.js. Produksi memakai PostgreSQL/MySQL, Cloudflare R2 untuk file utama, serta Google Drive sebagai cadangan opsional.

Kebutuhan lokal: PHP 8.2+ dengan ekstensi GD dan EXIF, Composer, Node.js, serta npm.

## Status

Alur publik dan operasional petugas sudah tersedia:

- Laravel 12 dan dependency frontend terpasang;
- migration serta seeder master data tersedia;
- warga dapat mengirim laporan dengan 1 sampai 5 foto;
- warga dapat mengirim usulan dan saran secara anonim tanpa foto;
- nomor laporan dan nomor usulan publik dibuat secara acak;
- halaman cek laporan, cek usulan, daftar laporan terverifikasi, dan daftar usulan warga tersedia;
- identitas pelapor tidak dirender pada halaman publik;
- login petugas, dasbor, filter, verifikasi, prioritas, status, catatan publik/internal, serta bukti penanganan;
- pengelolaan usulan warga oleh Admin Desa dan Super Admin: tinjau status, catatan publik, dan publikasi anonim ke halaman warga;
- peran Admin Desa dan Super Admin dengan pembatasan akses;
- pengelolaan akun petugas serta data master tanpa menghapus riwayat lama;
- rekap CSV dan halaman cetak untuk pimpinan atau tim pelaksana;
- submit idempoten dan kunci versi workflow untuk mencegah data ganda atau perubahan petugas saling menimpa.

Dokumen utama:

- [Struktur project](docs/architecture/PROJECT_STRUCTURE.md)
- [Arah frontend](docs/design/FRONTEND_DIRECTION.md)
- [Standar copy dan antarmuka](docs/design/CONTENT_UI_GUARDRAILS.md)
- [Blueprint halaman](docs/design/PAGE_BLUEPRINTS.md)
- [Design system dan komponen UI](docs/design/DESIGN_SYSTEM.md)
- [Panduan deploy Vercel](docs/DEPLOY_VERCEL.md)
- [Checklist serah terima buyer](docs/HANDOFF_BUYER.md)

## Prinsip teknis

- Area masyarakat dan admin dipisahkan dengan jelas.
- Controller hanya menangani alur HTTP; aturan bisnis laporan berada di service.
- Tidak memakai repository pattern untuk MVP.
- Data asli berasal dari database; data contoh berlabel hanya muncul otomatis saat koneksi atau data awal belum tersedia.
- Identitas pelapor tidak pernah masuk ke view atau response publik.
- Setiap form warga membawa token submit unik; retry dengan token yang sama mengembalikan laporan yang sudah dibuat.
- Perubahan laporan memakai transaksi, row lock, dan nomor versi agar dua petugas tidak saling menimpa.
- Data master dinonaktifkan, bukan dihapus, supaya relasi laporan lama tetap valid.
- UI dibangun mobile-first dan diuji juga pada keadaan kosong, loading, gagal, dan jaringan lambat.

## Menjalankan project

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve
```

Untuk membuat admin awal, isi `INITIAL_ADMIN_PASSWORD` pada `.env`, lalu jalankan:

```bash
php artisan db:seed --class=AdminSeeder
```

Jalankan pemeriksaan otomatis dengan database SQLite terisolasi di memori:

```bash
php artisan test
```

Database pengembangan tidak disentuh oleh test suite.

Jika database belum tersambung atau master data belum di-seed, halaman form, daftar laporan, dan cek status tetap dapat dipreview memakai data contoh. Pengiriman laporan otomatis dinonaktifkan sampai data asli siap.

## Penyimpanan foto: R2 + Google Drive

Foto laporan selalu masuk ke penyimpanan utama lebih dahulu. Untuk produksi, arahkan disk utama ke Cloudflare R2 melalui driver S3:

```dotenv
REPORT_MEDIA_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=auto
AWS_BUCKET=
AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
AWS_URL=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Google Drive berfungsi sebagai salinan cadangan privat, bukan sumber file yang dipakai halaman warga. Gunakan akun Google khusus operasional, buat OAuth 2.0 client dan refresh token, lalu arahkan `GOOGLE_DRIVE_FOLDER_ID` ke folder backup:

```dotenv
GOOGLE_DRIVE_BACKUP_ENABLED=true
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER_ID=
GOOGLE_DRIVE_BACKUP_QUEUE=backups
```

Jalankan queue worker agar pencadangan diproses di belakang layar:

```bash
php artisan queue:work --queue=backups,default --tries=5
```

Setiap media menyimpan status `pending`, `syncing`, `synced`, atau `failed`. Job mencoba ulang secara bertahap dan mengecek ID media sebelum mengunggah, sehingga retry tidak mudah membuat salinan ganda. Kegagalan Google Drive tidak membatalkan laporan yang sudah tersimpan di disk utama.

## Referensi sumber

Dokumen brief, PDF, ERD, user flow, dan prototype HTML di root project dipertahankan sebagai bahan referensi. Prototype HTML adalah referensi fitur, bukan acuan visual final.
