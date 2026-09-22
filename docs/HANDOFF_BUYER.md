# Checklist Serah Terima Buyer

## Cakupan yang tersedia

- Form laporan warga tanpa akun, unggah 1 sampai 5 foto, dan nomor laporan acak.
- Cek status serta daftar laporan terverifikasi tanpa membocorkan identitas pelapor.
- Login petugas dengan pembatasan percobaan masuk dan sesi yang diregenerasi.
- Dasbor operasional, antrean, pencarian/filter, detail laporan, dan riwayat perubahan.
- Verifikasi, status penanganan, prioritas, catatan publik/internal, serta foto setelah penanganan.
- Akun Admin Desa dan Super Admin.
- Data master dusun, kategori, jenis masalah, dan sumber QR.
- Rekap CSV dan halaman cetak.
- Penyimpanan utama Cloudflare R2 dan cadangan Google Drive opsional.
- Mode pratinjau berlabel saat database belum tersambung atau belum berisi data awal.
- Halaman bantuan teknis dan tampilan error yang menjelaskan kapan laporan belum tersimpan.

Fitur peta tidak disertakan sesuai keputusan produk. Lokasi laporan memakai dusun dan keterangan lokasi tertulis.

## Perlindungan data dan konkurensi

- Database mempunyai indeks unik untuk nomor laporan, token submit, username, kode dusun, kode QR, dan slug data master.
- Token submit membuat klik ganda atau retry jaringan tidak menghasilkan laporan dan media ganda.
- Pembuatan laporan, media, serta riwayat awal berada dalam satu transaksi.
- Perubahan petugas memakai row lock dan `workflow_version`; form lama ditolak bila petugas lain sudah menyimpan lebih dahulu.
- Setiap perubahan penting dicatat pada audit log.
- Super Admin aktif terakhir dan akun yang sedang dipakai tidak dapat dinonaktifkan secara tidak sengaja.
- Data master dinonaktifkan alih-alih dihapus agar foreign key dan riwayat tetap utuh.
- Token upload terenkripsi, kedaluwarsa, memvalidasi signature file, dan untuk bukti penanganan terikat ke laporan tujuan.

## Environment production wajib

1. Isi `APP_KEY`, `APP_URL`, `DB_CONNECTION`, dan `DB_URL`.
2. Isi credential bucket R2 dan aktifkan direct upload sesuai `.env.vercel.example`.
3. Isi `INITIAL_ADMIN_PASSWORD` hanya untuk seed pertama.
4. Jalankan `php artisan migrate --force` lalu `php artisan db:seed --force` dari mesin aman yang memakai environment production.
5. Kosongkan `INITIAL_ADMIN_PASSWORD` setelah akun awal berhasil dibuat.
6. Aktifkan Google Drive hanya jika OAuth client, refresh token, dan folder privat sudah siap.
7. Isi `SITE_SUPPORT_EMAIL` bila buyer ingin menerima laporan kendala teknis melalui email.

Jangan pernah mengirim file `.env` lokal kepada buyer atau memasukkannya ke arsip/repository. Kirim `.env.vercel.example` sebagai daftar konfigurasi.

## Pemeriksaan sebelum go-live

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan test
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Uji manual minimal:

1. Buat satu laporan beserta foto dan simpan nomor laporan.
2. Ulangi request dengan token submit yang sama; jumlah laporan harus tetap satu.
3. Masuk sebagai Admin Desa, verifikasi laporan, dan ubah status.
4. Pastikan catatan publik muncul pada cek status tetapi identitas pelapor tidak muncul di halaman publik.
5. Masuk sebagai Super Admin, tambah akun petugas, lalu pastikan Admin Desa tidak dapat membuka data master atau akun petugas.
6. Unduh CSV dan buka halaman cetak.
7. Periksa object foto di R2 dan, bila diaktifkan, status cadangan Google Drive.

## Hasil verifikasi pengembangan terakhir

- Test otomatis: 30 test, 159 assertion lulus.
- Build Vite production: berhasil.
- PHP formatter: lulus.
- Pemeriksaan browser: beranda, form laporan, daftar laporan, bantuan situs, login, dasbor, data master, akun petugas, serta breakpoint ponsel/tablet/desktop tanpa overflow halaman.
