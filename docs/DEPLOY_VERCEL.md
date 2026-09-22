# Deploy Padelegan Lapor ke Vercel

Proyek ini menjalankan Laravel sebagai satu Vercel Function menggunakan `vercel-php`. Aset Vite dilayani sebagai file statis, sedangkan foto laporan diunggah langsung dari browser ke Cloudflare R2 agar tidak melewati batas request Vercel.

## Layanan yang dibutuhkan

- Vercel untuk aplikasi dan aset frontend.
- MySQL atau PostgreSQL terkelola untuk data persisten. Jangan memakai SQLite production karena filesystem Function tidak persisten.
- Cloudflare R2 atau penyimpanan kompatibel S3 untuk foto laporan.
- Google Drive bersifat opsional dan tetap berfungsi sebagai salinan cadangan.

## 1. Siapkan database

Buat database MySQL/PostgreSQL yang lokasinya dekat dengan region Singapore. Salin connection string ke `DB_URL` dan pilih driver yang sesuai melalui `DB_CONNECTION=mysql` atau `DB_CONNECTION=pgsql`.

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
- `DB_CONNECTION` dan `DB_URL`.
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`, dan `AWS_ENDPOINT` dari R2.
- `REPORT_MEDIA_DISK=s3`, `REPORT_DIRECT_UPLOAD=true`, `REPORT_DIRECT_UPLOAD_REQUIRED=true`, dan `REPORT_DIRECT_UPLOAD_DISK=s3`.
- `SESSION_DRIVER=cookie`, `CACHE_STORE=array`, dan `QUEUE_CONNECTION=sync`. Setelah database stabil, session dan cache boleh dipindahkan ke driver `database`.
- `INITIAL_ADMIN_PASSWORD` hanya diperlukan ketika seeder pertama dijalankan.
- `SITE_SUPPORT_EMAIL` opsional. Isi alamat bantuan teknis desa agar tombol **Laporkan kendala situs** membuka email dengan format laporan yang sudah disiapkan.

Jangan memasukkan secret ke `vercel.json` atau repository.

## 4. Migrasi dan data awal

Tarik environment production ke file lokal yang sudah diabaikan Git:

```bash
npx vercel env pull .env.production --environment=production
php artisan migrate --env=production --force
php artisan db:seed --env=production --force
```

Jalankan seeder hanya pada instalasi pertama. Deployment berikutnya cukup menjalankan migrasi baru. Migrasi sengaja tidak berjalan otomatis saat build agar preview deployment tidak mengubah database production.

Pastikan `INITIAL_ADMIN_PASSWORD` berisi password awal yang kuat saat seeder pertama dijalankan. Setelah berhasil masuk, buat akun petugas sesuai kebutuhan melalui **Panel Petugas → Akun petugas**. Kosongkan kembali variable tersebut setelah akun awal tersedia agar secret bootstrap tidak disimpan lebih lama dari yang diperlukan.

## Mode pratinjau tanpa database

Halaman publik tetap dapat dibuka jika koneksi database belum tersedia atau tabel referensi masih kosong. Form menampilkan dusun dan kategori contoh, halaman Laporan Desa menampilkan tiga laporan contoh, dan `/cek-laporan` menyediakan nomor demo. Semua bagian diberi label **Mode pratinjau**, dan pengiriman laporan dinonaktifkan agar data contoh tidak pernah dianggap data warga.

Fallback berhenti otomatis setelah migrasi dan seeder selesai serta data asli sudah dapat dibaca. Tidak ada variable khusus yang perlu diubah. `APP_KEY` tetap wajib diisi karena Laravel memerlukannya untuk cookie dan CSRF.

## 5. Deploy

```bash
npx vercel
npx vercel --prod
```

Setelah deploy, periksa `/up`, beranda, form laporan, unggah foto, halaman sukses, pelacakan, halaman bantuan situs, login admin, dasbor, pembaruan status, ekspor CSV, data master, dan pengelolaan petugas.

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
