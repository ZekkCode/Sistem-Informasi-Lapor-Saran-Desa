# Arah Frontend

## Konsep: Pesisir yang Melayani

Padelegan Lapor harus terasa seperti layanan milik Desa Padelegan. Lanskap tambak, bahasa yang sederhana, ritme layout yang tenang, dan status yang tegas membentuk identitasnya. Hindari pola dashboard SaaS generik.

Karakter visual:

- resmi tetapi tidak kaku;
- lokal tanpa ornamen tradisional yang dibuat-buat;
- hangat, jujur, dan langsung dipahami warga;
- padat informasi di admin, lebih lapang di area masyarakat.

## Keputusan visual

### Warna

- Hijau bakau menjadi warna aksi dan navigasi.
- Putih garam dan pasir hangat menjadi dasar halaman.
- Biru tambak menandai informasi. Antarmuka tidak memakai gradient dekoratif.
- Merah tanah, kuning oker, dan hijau digunakan khusus untuk status.

### Tipografi

- Seluruh aplikasi memakai Manrope Variable.
- Body memakai bobot 400 sampai 500; heading memakai bobot 500 sampai 650.
- Ukuran, ruang, dan warna membentuk hierarki sebelum bobot font.
- Font web harus di-self-host dalam format WOFF2; jangan bergantung pada font eksternal untuk fungsi dasar.

### Bentuk dan elevasi

- Radius kontrol berada pada 8 sampai 12 px. Bentuk kapsul hanya dipakai untuk status singkat.
- Bayangan tipis hanya untuk elemen yang benar-benar terangkat seperti dialog atau sticky panel.
- Section memakai ruang, garis, warna permukaan, atau komposisi sebagai pemisah. Panel putih hanya dipakai ketika konten membutuhkan batas.
- Badge berbentuk pill hanya untuk status/tag singkat.

### Foto

- `hero-padelegan.webp` menjadi foto utama beranda karena komposisi tambak dan langitnya bersih.
- `padelegan-dusk.webp` dipakai untuk halaman Tentang atau penutup halaman.
- Foto memakai crop yang terarah dan overlay gelap hanya bila dibutuhkan untuk keterbacaan teks.
- Jangan memakai ilustrasi stok generik jika foto lokal sudah menjelaskan konteks.

## Hal yang harus dihindari

- hero gradient hijau-ungu dengan blob dekoratif;
- headline terlalu besar yang menghabiskan satu layar ponsel;
- deretan kartu identik untuk setiap section;
- perataan tengah pada seluruh teks;
- emoji sebagai ikon antarmuka;
- glassmorphism, glow, dan shadow berat;
- terlalu banyak badge dan pill;
- copy seperti “solusi cerdas terpadu” tanpa informasi nyata;
- statistik hardcoded;
- animasi masuk pada setiap elemen;
- ikon tanpa label pada aksi penting;
- data contoh yang tampil seolah-olah data desa sungguhan.

## Sistem layout

- Lebar konten publik maksimum 1180–1240 px.
- Teks panjang maksimum sekitar 68 karakter per baris.
- Spacing mengikuti skala 4, 8, 12, 16, 24, 32, 48, 64, dan 96 px.
- Homepage boleh memakai komposisi asimetris; form dan halaman tracking lebih linear.
- Pada ponsel, aksi utama mudah dijangkau ibu jari dan area sentuh minimal 44 px.

## Komponen inti

Komponen aktif:

- `Action` dengan varian primary, secondary, quiet, dan danger;
- `Field`, `Select`, `Textarea`, `Checkbox`, serta pesan error;
- `ReportStatus` dengan label dan ikon yang tidak hanya mengandalkan warna;
- `ReportCard` untuk daftar publik;
- timeline untuk tracking;
- upload foto dengan preview, progres, dan error;
- filter responsif;
- `EmptyState`, `InlineNotice`, dan `ErrorSummary`;
- tabel admin dengan area gulir horizontal pada layar sempit.

## Form laporan

- Satu form memuat tiga langkah: masalah, lokasi dan foto, lalu pelapor.
- Perpindahan langkah mempertahankan semua data yang sudah diisi.
- Progress menunjukkan posisi dan pengguna dapat kembali ke langkah sebelumnya.
- Lokasi memakai pilihan dusun dan patokan tertulis.
- Error tampil di field dan dirangkum di atas form setelah submit gagal.
- Upload menunjukkan batas jumlah/ukuran sebelum pengguna memilih file.
- Tombol kirim memiliki state loading dan mencegah submit ganda.

## Dashboard admin

- Prioritaskan scanning: laporan baru, darurat, lama belum selesai, dan aksi berikutnya.
- KPI tidak semuanya memiliki bobot visual yang sama.
- Tabel memakai label kolom, filter terlihat, dan angka berformat konsisten.
- Detail laporan memisahkan fakta laporan dari panel tindakan admin.
- Data pribadi diberi label jelas dan tidak ikut ke screenshot/export publik secara tidak sengaja.

## Aksesibilitas dan performa

- Kontras teks mengikuti WCAG AA.
- Semua field memiliki label nyata, bukan placeholder sebagai label.
- Fokus keyboard selalu terlihat.
- Status memiliki teks/ikon selain warna.
- Preferensi `prefers-reduced-motion` dihormati.
- Foto desa dan laporan memakai WebP/AVIF bila didukung, ukuran responsif, dan lazy loading di bawah fold.
- JavaScript tidak boleh menjadi syarat untuk membaca status dasar laporan.
