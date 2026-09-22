# Panduan kerja Padelegan Lapor

Pertahankan konteks dan keputusan yang sudah tercatat di `README.md` serta folder `docs/`. Baca `docs/design/CONTENT_UI_GUARDRAILS.md` sebelum mengubah copy atau antarmuka.

## Produk dan bahasa

- Tulis bahasa Indonesia yang singkat, aktif, dan spesifik pada tindakan pengguna.
- Hapus pembuka kosong, slogan generik, klaim tanpa bukti, pertanyaan retoris, dan kalimat yang mengulang isi heading.
- Hindari pola kontras buatan, deretan tiga frasa yang terasa formulaik, tanda pisah panjang, serta kata sifat yang tidak menambah informasi.
- Gunakan istilah yang konsisten: `laporan`, `nomor laporan`, `cek status`, `petugas`, dan `warga`.
- Jelaskan kondisi gagal beserta tindakan pemulihannya.

## Frontend

- Gunakan Manrope, palet hijau Padelegan, permukaan datar, garis tipis, dan radius 8 sampai 14 px.
- Jangan menambahkan gradient, glassmorphism, blob dekoratif, glow, bayangan besar, kartu seragam berulang, atau semua heading dengan bobot tebal.
- Jaga halaman ringkas. Utamakan grid pada layar lebar dan susunan satu kolom yang padat pada layar sempit.
- AOS hanya untuk transisi masuk singkat. Lenis hanya aktif pada pointer presisi. Hormati `prefers-reduced-motion`.
- Jangan menambahkan peta. Warga mengisi dusun dan patokan lokasi dalam bentuk teks.
- Semua field memakai label yang terlihat, pesan bantuan terkait melalui `aria-describedby`, target sentuh minimal 44 px, dan font kontrol minimal 16 px pada ponsel.
- Isi utama harus tetap terbaca saat JavaScript gagal.

## Arsitektur

- Ikuti struktur Laravel yang ada: controller untuk alur HTTP, Form Request untuk validasi, query object untuk pembacaan kompleks, dan service untuk transaksi bisnis.
- Tambahkan abstraksi setelah muncul kebutuhan nyata atau pemakaian ulang. Hapus scaffolding dan dependensi yang tidak dipakai.
- Jaga idempotensi pengiriman warga dan pemeriksaan versi workflow saat beberapa petugas bekerja bersamaan.
- Media utama memakai disk yang dikonfigurasi. Google Drive berfungsi sebagai cadangan privat.
- Jalankan `php artisan test` dan `npm run build` setelah perubahan yang memengaruhi aplikasi.
