# View Structure

- `layouts`: kerangka halaman site, admin, dan auth.
- `components/ui`: primitive presentasional kecil.
- `components/site`: navigasi serta identitas publik.
- `components/reports`: komponen domain laporan yang dapat dipakai di beberapa halaman.
- `components/admin`: pola dashboard yang tidak dipakai pada area publik.
- `site`: halaman masyarakat.
- `admin`: halaman pemerintah desa.
- `auth`: login admin.

Gunakan Blade component untuk pola yang berulang atau mempunyai kontrak data jelas. Markup satu kali pakai tetap berada di halaman agar struktur tidak terfragmentasi.
