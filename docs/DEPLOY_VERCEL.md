# Deploy Padelegan Lapor ke Vercel

Proyek ini menjalankan Laravel sebagai satu Vercel Function menggunakan `vercel-php`. Aset Vite dilayani sebagai file statis, sedangkan foto laporan diunggah langsung dari browser ke Cloudflare R2 agar tidak melewati batas request Vercel.

## Layanan yang dibutuhkan

- Vercel untuk aplikasi dan aset frontend.
- MySQL atau PostgreSQL terkelola untuk data persisten. Jangan memakai SQLite production karena filesystem Function tidak persisten.
- Cloudflare R2 atau penyimpanan kompatibel S3 untuk foto laporan.
- Google Drive bersifat opsional dan tetap berfungsi sebagai salinan cadangan.

## 1. Siapkan database Supabase

Laravel terhubung ke Supabase lewat koneksi PostgreSQL langsung. Publishable key, secret key, dan URL `/rest/v1` tidak dipakai aplikasi, jadi jangan disimpan di environment.

### Connection string

Di dashboard Supabase klik **Connect**, pilih **Session pooler**, lalu salin string-nya ke `DB_URL` di Vercel. Ganti `[YOUR-PASSWORD]` dengan password database dari Project Settings, Database. Encode karakter khusus pada password: `@` jadi `%40`, `#` jadi `%23`, `?` jadi `%3F`, `&` jadi `%26`, `/` jadi `%2F`. Tambahkan `?sslmode=require` di akhir.

```text
postgresql://postgres.wgadkdgapbvxcqojfbqb:PASSWORD@aws-0-ap-northeast-2.pooler.supabase.com:5432/postgres?sslmode=require
```

Salin host dari dialog Connect, karena awalan `aws-0` atau `aws-1` tidak bisa ditebak dari region. Cukup isi `DB_URL`. Koneksi otomatis memakai `pgsql` bila URL diawali `postgres://` atau `postgresql://`.

Jangan memakai **Transaction pooler** port 6543. Mode itu mewajibkan prepared statement dimatikan, dan pada Laravel semua kondisi boolean seperti `is_active = true` lalu gagal dengan `operator does not exist: boolean = integer`. Session pooler port 5432 mendukung prepared statement dan tetap bisa dijangkau lewat IPv4 dari Vercel. Koneksi langsung `db.<ref>.supabase.co` hanya IPv6 pada paket gratis.

### Skema dan data lewat SQL Editor

Berkas SQL ada di `database/supabase/` dan disusun dari migrasi serta seeder dengan `php artisan supabase:sql`. Jalankan berurutan di SQL Editor Supabase:

1. `01_skema.sql`: 20 tabel, indeks, foreign key, riwayat migrasi, dan kunci akses REST API.
2. `02_data_awal.sql`: 6 dusun, 5 kategori, 17 jenis masalah, 6 sumber QR (`DSN01` sampai `DSN06`), dan pengaturan.
3. `03_contoh_usulan.sql`: opsional, usulan contoh untuk demo.
4. `akun-admin.local.sql`: akun `admin` dan `admindesa` dengan hash password dari `.env` lokal. Berkas ini diabaikan Git. Hapus setelah dijalankan.

Setiap berkas berjalan dalam satu transaksi. Bila ada perintah yang gagal, tidak ada perubahan yang tersimpan. Berkas `02` dan `03` aman diulang. Berkas `01` hanya untuk database kosong.

Setelah ada migrasi baru, susun ulang berkas dengan `php artisan supabase:sql`, lalu jalankan bagian migrasi yang baru saja. Cara lain, jalankan `php artisan migrate` langsung ke Supabase dari mesin yang punya ekstensi `pdo_pgsql`. Riwayat migrasi di `01_skema.sql` membuat Laravel hanya menjalankan migrasi baru.

### Keamanan Data API

`01_skema.sql` menyalakan Row Level Security tanpa policy di semua tabel dan mencabut hak akses `anon`, `authenticated`, serta `service_role`. Hasilnya, endpoint `/rest/v1` tidak bisa membaca data walaupun memakai publishable key atau secret key. Laravel tidak terpengaruh karena terhubung sebagai pemilik tabel. Sebagai lapisan tambahan, matikan Data API di Project Settings, Data API bila tidak dipakai.

Secret key yang pernah dibagikan di luar dashboard perlu dirotasi. Buat secret key baru di Project Settings, API Keys, lalu hapus yang lama.

## 2. Siapkan Cloudflare R2

Buat bucket privat dan API token dengan izin baca/tulis object pada bucket tersebut. Tambahkan CORS berikut pada bucket; ganti domain production dan preview sesuai proyek:

```json
[
  {
    "AllowedOrigins": [
      "https://nama-project.vercel.app",
      "http://127.0.0.1:8001",
      "http://localhost:8000"
    ],
    "AllowedMethods": ["PUT"],
    "AllowedHeaders": ["Content-Type"],
    "ExposeHeaders": ["ETag"],
    "MaxAgeSeconds": 3600
  }
]
```

Tambahkan lifecycle rule yang menghapus object dengan prefix `temporary/report-media/` setelah satu hari. Object sementara yang tidak pernah disertakan dalam laporan tidak akan menumpuk.

## 3. Buat project Vercel

Import repository ini dengan root directory project. Vercel membaca `vercel.json`, menjalankan build Vite, kemudian membungkus `api/index.php` sebagai PHP Function di region `sin1`.

Salin seluruh variable dari `.env.vercel.example` ke Settings → Environment Variables. Nilai wajib:

- `APP_KEY`: hasil `php artisan key:generate --show`.
- `APP_URL`: domain production menggunakan HTTPS.
- `DB_URL` dari Supabase Session pooler (port 5432) dengan `?sslmode=require`. `DB_CONNECTION` tidak perlu diisi karena terdeteksi dari URL. Lihat bagian 1.
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, dan `AWS_ENDPOINT` dari R2. Begitu `AWS_BUCKET` terisi, disk media pindah ke `s3` dan unggah langsung aktif otomatis, jadi `REPORT_MEDIA_DISK` dan `REPORT_DIRECT_UPLOAD*` tidak perlu diisi kecuali ingin menimpa.
- `SESSION_DRIVER=cookie`, `CACHE_STORE=array`, dan `QUEUE_CONNECTION=sync`. Setelah database stabil, session dan cache boleh dipindahkan ke driver `database`.
- `INITIAL_ADMIN_PASSWORD` dan `INITIAL_ADMIN_DESA_PASSWORD` hanya diperlukan ketika seeder pertama dijalankan.
- `SITE_SUPPORT_EMAIL` opsional. Isi alamat bantuan teknis desa agar tombol **Laporkan kendala situs** membuka email dengan format laporan yang sudah disiapkan.

Jangan memasukkan secret ke `vercel.json` atau repository.

## 4. Migrasi dan data awal

Untuk Supabase, pakai berkas SQL di bagian 1. Untuk database lain, atau bila mesin lokal punya ekstensi `pdo_pgsql`, tarik environment production ke file lokal yang diabaikan Git lalu jalankan migrasi:

```bash
npx vercel env pull .env.production --environment=production
php artisan migrate --env=production --force
php artisan db:seed --env=production --force
```

Jalankan seeder hanya pada instalasi pertama. Deployment berikutnya cukup menjalankan migrasi baru. Migrasi sengaja tidak berjalan otomatis saat build agar preview deployment tidak mengubah database production.

Pastikan `INITIAL_ADMIN_PASSWORD` berisi password awal yang kuat saat seeder pertama dijalankan. Setelah berhasil masuk, buat akun petugas sesuai kebutuhan melalui **Panel Petugas → Akun petugas**. Kosongkan kembali variable tersebut setelah akun awal tersedia agar secret bootstrap tidak disimpan lebih lama dari yang diperlukan.

### Masuk petugas dengan Google

Petugas dapat masuk lewat akun Google, tetapi hanya untuk email di `GOOGLE_SSO_ALLOWED_EMAILS`. Nilai bawaannya akun resmi desa `laporpadelegan@gmail.com`. Login username dan password tetap tersedia.

Aturan peran:
- Email di daftar yang belum punya akun dibuatkan akun petugas dengan peran `GOOGLE_SSO_ROLE` (bawaan `admin`, tampil sebagai Admin Desa). Password akun itu diisi acak, jadi akun hanya bisa dibuka lewat Google sampai Super Admin mengaturnya.
- Email yang sudah terpasang pada akun petugas tetap memakai peran akun tersebut.
- Akun yang dinonaktifkan Super Admin tetap ditolak walau emailnya ada di daftar.
- Email di luar daftar selalu ditolak.

Langkah setup:
1. Masuk ke Google Cloud Console dengan akun resmi desa. Buka APIs & Services, OAuth consent screen, pilih External, lalu tambahkan `laporpadelegan@gmail.com` sebagai test user. Scope yang dipakai hanya `openid`, `email`, dan `profile`, jadi tidak perlu verifikasi aplikasi.
2. Buka Credentials, buat OAuth client ID jenis Web application, dan daftarkan Authorized redirect URI `https://DOMAIN-ANDA/admin/login/google/callback`. Untuk lokal tambahkan `http://127.0.0.1:8001/admin/login/google/callback`.
3. Isi `GOOGLE_SSO_CLIENT_ID` dan `GOOGLE_SSO_CLIENT_SECRET` di Vercel. Kosongkan `GOOGLE_SSO_REDIRECT_URI` agar callback mengikuti domain yang diakses, atau isi bila memakai domain kustom.
4. Opsional: isi `INITIAL_ADMIN_DESA_EMAIL=laporpadelegan@gmail.com` sebelum seeder agar akun `admindesa` dan akun Google menjadi satu akun.

Popup penolakan muncul untuk setiap kondisi gagal: email di luar daftar ("Akun ini bukan akun admin desa"), akun nonaktif, email Google belum terverifikasi, proses dibatalkan, dan database belum tersambung. Login berhasil, pembuatan akun otomatis, dan setiap penolakan tercatat di audit log. Tombol Google otomatis disembunyikan selama client ID dan secret belum diisi.

SSO membutuhkan database karena sistem mencocokkan email Google dengan tabel `users`. Di produksi, fitur ini baru berfungsi setelah Supabase tersambung.

### Akun demo untuk buyer

`AdminSeeder` membuat dua akun dari environment variable, jadi begitu Supabase tersambung dan seeder dijalankan sekali, akun langsung siap dipakai di `/admin/login`:

| Peran | Username | Password (dari env) | Akses |
| --- | --- | --- | --- |
| Super Admin | `INITIAL_ADMIN_USERNAME` (mis. `admin`) | `INITIAL_ADMIN_PASSWORD` | Semua fitur, data master, dan akun petugas |
| Admin Desa | `INITIAL_ADMIN_DESA_USERNAME` (mis. `admindesa`) | `INITIAL_ADMIN_DESA_PASSWORD` | Laporan, usulan, verifikasi, status, dan rekap |

Untuk Supabase, akun ini dibuat lewat `database/supabase/akun-admin.local.sql` yang dihasilkan `php artisan supabase:sql` dari nilai `INITIAL_*` di `.env` lokal. Akun `admindesa` ditautkan ke `laporpadelegan@gmail.com` lewat `INITIAL_ADMIN_DESA_EMAIL`, jadi login Google dan login password membuka akun yang sama.

Nilai demo yang siap pakai ada di `.env.vercel.example`. Seeder aman dijalankan ulang (`updateOrCreate` per username) dan otomatis melewati akun yang password-nya kosong. Alur otomatisnya: isi env di Vercel, jalankan `php artisan db:seed --force` sekali, akun langsung aktif. Ganti password lewat panel setelah demo.

## Mode pratinjau tanpa database

Halaman publik tetap dapat dibuka jika koneksi database belum tersedia atau tabel referensi masih kosong. Form menampilkan dusun dan kategori contoh, halaman Laporan Desa menampilkan tiga laporan contoh, dan `/cek-laporan` menyediakan nomor demo. Semua bagian diberi label **Mode pratinjau**, dan pengiriman laporan dinonaktifkan agar data contoh tidak pernah dianggap data warga.

Fallback berhenti otomatis setelah migrasi dan seeder selesai serta data asli sudah dapat dibaca. Tidak ada variable khusus yang perlu diubah. `APP_KEY` tetap wajib diisi karena Laravel memerlukannya untuk cookie dan CSRF.

## 5. Deploy

```bash
npx vercel
npx vercel --prod
```

Setelah deploy, periksa `/up`, beranda, form laporan, unggah foto, halaman sukses, pelacakan, form usulan (`/usulan`), cek usulan (`/cek-usulan`), daftar usulan warga (`/usulan-warga`), halaman bantuan situs, login admin, dasbor, pembaruan status laporan dan usulan, ekspor CSV, data master, dan pengelolaan petugas.

Fitur usulan menambah tabel `usulan` dan `riwayat_usulan`. Jalankan `php artisan migrate --env=production --force` sekali lagi setelah deploy versi ini agar kedua tabel tersedia.

## Peran pengguna

- **Warga** tidak perlu akun: membuat laporan dan memantau nomor laporan.
- **Admin Desa**: melihat identitas pelapor, memverifikasi, memprioritaskan, dan memperbarui penanganan.
- **Super Admin**: seluruh akses Admin Desa ditambah data master dan akun petugas.
- **Pimpinan/tim pelaksana** dapat memakai rekap CSV atau halaman cetak dari akun internal yang berwenang.

Tidak ada fitur peta pada rilis ini sesuai keputusan produk. Lokasi disimpan sebagai dusun dan keterangan lokasi tertulis.

## Catatan queue dan Google Drive

Vercel tidak menjalankan worker Laravel yang hidup terus-menerus. Karena itu production menggunakan `QUEUE_CONNECTION=sync`; pencadangan Google Drive yang diaktifkan akan dikerjakan setelah respons laporan dalam invocation yang sama. Jika volume laporan bertambah besar, pindahkan queue ke layanan worker eksternal seperti SQS/Redis beserta worker terpisah.

## Struktur serverless

- `api/index.php` memindahkan storage runtime dan view hasil kompilasi ke `/tmp`; manifest dependency hasil build tetap dibaca dari bundle aplikasi.
- Session serta cache aplikasi disimpan di database agar konsisten antar-instance.
- Foto masuk langsung ke R2 menggunakan presigned URL berumur pendek dan token terenkripsi.
- Production mewajibkan upload langsung; submit multipart tanpa JavaScript ditolak dengan pesan validasi karena runtime PHP Vercel tidak menyediakan GD.
- Hanya `build`, `images`, favicon, dan robots yang dilayani sebagai file statis. Semua URL lain masuk ke Laravel.
