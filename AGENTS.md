# Panduan kerja Padelegan Lapor

Arsitektur acuan mengikuti infografik buyer "Skema Sistem Website Padelegan Lapor" (fitur usulan dengan tabel usulan publik). Bila infografik dan brief lama (`Padelegan_Lapor_Project_Brief_PPL.md`) berbeda, infografik yang menang. Pertahankan konteks dan keputusan di `README.md` serta folder `docs/`. Baca `docs/design/CONTENT_UI_GUARDRAILS.md` sebelum mengubah copy atau antarmuka.

## Status implementasi

Sudah tersedia:
- Modul pengaduan penuh: kirim, verifikasi, status penanganan, riwayat, bukti, ekspor, audit.
- Modul usulan: kirim, lacak, tabel usulan publik, kelola status, publikasi anonim.
- Dua peran: `admin` (akses pengaduan dan usulan) dan `super_admin`.

Target infografik yang belum ada (kerjakan saat diminta):
- Pemisahan peran Admin Pengaduan dan Admin Usulan beserta pembatasan akses per modul.
- Polling usulan (satu suara per usulan) dan penentuan prioritas dari hasil polling.
- Halaman Rekap Usulan Terealisasi yang publik.
- Peta Masalah. Kolom `latitude`/`longitude` sudah dihapus dan perlu dipulihkan lebih dahulu.

## Peran dan akses

Empat tingkat sesuai infografik:
- Warga tanpa login: beranda, buat dan cek pengaduan, laporan desa, peta masalah, buat dan cek usulan, tabel usulan publik, rekap usulan terealisasi. Tidak ikut polling dan tidak mengubah data.
- Admin Pengaduan: hanya modul pengaduan. Melihat tabel pengaduan privat, mengelola, mengubah status, dan menindaklanjuti. Tidak dapat membuka usulan maupun polling.
- Admin Usulan: hanya modul usulan. Mengelola usulan, mengubah status, memberi satu suara polling per usulan, melihat hasil polling, menentukan prioritas, dan mengelola realisasi. Tidak dapat membuka tabel pengaduan.
- Super Admin: seluruh modul, mengelola akun Admin Pengaduan dan Admin Usulan, serta pengaturan situs.

Batasi otorisasi per modul, bukan sekadar per halaman. Admin Pengaduan diblokir dari route usulan dan sebaliknya, lewat middleware atau policy.

## Modul dan ruang lingkup

- Pengaduan: warga kirim, petugas verifikasi, status penanganan, riwayat. Identitas pelapor privat.
- Usulan: warga kirim, tabel usulan tampil ke publik (read-only), Admin Usulan mengelola, polling internal, prioritas dari hasil polling, lalu realisasi. Identitas pengusul opsional dan tidak pernah tampil di publik.
- Polling usulan: satu suara per usulan untuk tiap Admin Usulan dan Super Admin. Simpan suara, tampilkan agregat ke petugas, dan pakai untuk mengurutkan prioritas.
- Rekap Usulan Terealisasi: halaman publik berisi usulan yang sudah selesai beserta dokumentasi yang boleh dilihat atau diunduh warga.

## Produk dan bahasa

- Tulis bahasa Indonesia yang singkat, aktif, dan spesifik pada tindakan pengguna.
- Hapus pembuka kosong, slogan generik, klaim tanpa bukti, pertanyaan retoris, dan kalimat yang mengulang isi heading.
- Hindari pola kontras buatan, deretan tiga frasa yang terasa formulaik, tanda pisah panjang, serta kata sifat yang tidak menambah informasi.
- Gunakan istilah yang konsisten: `laporan` (sinonim `pengaduan`, publik memakai "laporan"), `nomor laporan`, `usulan`, `nomor usulan`, `cek status`, `petugas`, dan `warga`.
- Jelaskan kondisi gagal beserta tindakan pemulihannya.

## Frontend

- Gunakan Manrope, palet hijau Padelegan, permukaan datar, garis tipis, dan radius 8 sampai 14 px.
- Jangan menambahkan gradient, glassmorphism, blob dekoratif, glow, bayangan besar, kartu seragam berulang, atau semua heading dengan bobot tebal.
- Jaga halaman ringkas. Utamakan grid pada layar lebar dan susunan satu kolom yang padat pada layar sempit.
- AOS hanya untuk transisi masuk singkat. Lenis hanya aktif pada pointer presisi. Hormati `prefers-reduced-motion`.
- Peta Masalah memakai Leaflet.js dan OpenStreetMap tanpa API berbayar. Warna marker mengikuti status: merah belum terlaksana, kuning proses pelaksanaan, hijau sudah terlaksana. Jaga privasi lokasi: tampilkan tingkat dusun atau koordinat ter-offset untuk publik, koordinat presisi hanya untuk petugas. Warga tetap mengisi dusun dan patokan lokasi dalam bentuk teks, dan sediakan fallback teks saat peta atau GPS gagal.
- Semua field memakai label yang terlihat, pesan bantuan terkait melalui `aria-describedby`, target sentuh minimal 44 px, dan font kontrol minimal 16 px pada ponsel.
- Isi utama harus tetap terbaca saat JavaScript gagal.

## Arsitektur

- Ikuti struktur Laravel yang ada: controller untuk alur HTTP, Form Request untuk validasi, query object untuk pembacaan kompleks, dan service untuk transaksi bisnis.
- Tambahkan abstraksi setelah muncul kebutuhan nyata atau pemakaian ulang. Hapus scaffolding dan dependensi yang tidak dipakai.
- Pisahkan otorisasi antar modul pengaduan dan usulan melalui middleware atau policy, sejalan dengan peran di atas.
- Jaga idempotensi pengiriman warga dan pemeriksaan versi workflow saat beberapa petugas bekerja bersamaan. Polling memakai batasan satu suara unik per petugas per usulan di tingkat database.
- Media utama memakai disk yang dikonfigurasi. Google Drive berfungsi sebagai cadangan privat.
- Jalankan `php artisan test` dan `npm run build` setelah perubahan yang memengaruhi aplikasi.
