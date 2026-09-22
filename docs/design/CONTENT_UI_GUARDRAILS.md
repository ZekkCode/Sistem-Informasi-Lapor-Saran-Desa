# Standar copy dan antarmuka

Dokumen ini menjaga pengalaman Padelegan Lapor tetap terasa seperti layanan desa yang dirancang untuk warga. Gunakan bersama `FRONTEND_DIRECTION.md` dan `DESIGN_SYSTEM.md`.

Referensi editorial: [stop-slop](https://github.com/hardikpandya/stop-slop).

## Copy yang dipakai

Setiap teks harus membantu warga memahami keadaan atau mengambil tindakan.

- Mulai dari tindakan: `Masukkan nomor laporan`, `Pilih foto`, `Simpan nomor ini`.
- Sebutkan objek dan batasnya: `Pilih 1 sampai 5 foto, maksimal 5 MB per foto`.
- Beri jalan keluar pada pesan gagal: `Periksa nomor lalu coba lagi`.
- Pakai kalimat pendek pada tombol, label, status, dan bantuan field.
- Pakai istilah yang sama di seluruh alur.

Hapus pola berikut saat melakukan review:

- pembuka seperti `perlu diketahui`, `tentunya`, atau `pada dasarnya`;
- klaim seperti `solusi modern`, `pengalaman mulus`, atau `layanan terbaik`;
- heading yang diulang kembali oleh paragraf pertama;
- kontras buatan seperti `bukan sekadar X, tetapi Y`;
- tiga kalimat pendek dengan bentuk yang sama hanya untuk menciptakan ritme;
- tanda pisah panjang dan kata keterangan yang tidak mengubah makna.

## Hierarki halaman

1. Satu judul utama menjelaskan tujuan halaman.
2. Paragraf pembuka memuat informasi yang belum disampaikan judul.
3. Aksi utama tampil lebih dulu; aksi sekunder tetap terlihat tanpa bersaing.
4. Konten pendukung memakai garis, ruang, atau perubahan permukaan sebelum memakai panel baru.
5. Daftar langkah hanya dipakai ketika urutannya memengaruhi keberhasilan tugas.

## Karakter visual

- Manrope menjadi satu-satunya keluarga font aplikasi.
- Gunakan bobot 500 sampai 650 untuk hierarki. Bobot 700 ke atas hanya untuk tanda kecil yang membutuhkan kepadatan visual.
- Gunakan warna hijau Padelegan untuk identitas dan aksi, biru tambak untuk informasi, serta warna status untuk makna.
- Gunakan radius kontrol `10px` dan panel `14px` sebagai batas umum.
- Gunakan bayangan hanya ketika elemen benar-benar berada di atas lapisan lain, seperti dialog atau menu.
- Foto desa harus memiliki konteks, teks alternatif, dimensi eksplisit, dan fungsi editorial.

## Pola yang ditolak

- gradient dekoratif dan latar glow;
- glassmorphism dan panel transparan bertumpuk;
- kumpulan kartu identik dengan ikon besar;
- badge berbentuk kapsul untuk teks biasa;
- semua heading tebal atau seluruh label huruf kapital;
- animasi panjang, parallax besar, dan efek 3D pada perangkat sentuh;
- peta untuk input lokasi;
- bagian halaman yang hanya mengulang manfaat dengan kata berbeda.

## Review sebelum selesai

- Baca copy tanpa melihat desain. Hapus kalimat yang tidak mengubah keputusan pengguna.
- Uji lebar 320 px, 390 px, 768 px, dan desktop.
- Periksa navigasi keyboard, fokus, label, kontras, dan reduced motion.
- Periksa keadaan kosong, data contoh, validasi gagal, unggah gagal, dan konflik pembaruan petugas.
- Pastikan halaman awal menjelaskan fungsi layanan sebelum pengguna menggulir jauh.
