# Design System Padelegan Lapor

Design system diimplementasikan dengan **Blade anonymous components**, Tailwind CSS 4, semantic CSS tokens, dan Alpine.js. Struktur `.tsx` dari referensi Next.js tidak digunakan karena aplikasi ini memakai Laravel server-rendered.

## Stack frontend

| Kebutuhan | Implementasi |
|---|---|
| Rendering | Laravel Blade |
| Styling | Tailwind CSS 4 + CSS custom properties |
| Interaksi lokal | Alpine.js |
| Penggabungan class | `$attributes->class()` bawaan Blade |
| Ikon | SVG inline pada komponen terkait |
| Mobile drawer | `x-ui.sheet` + Alpine |
| Build | Vite |

Tidak diperlukan `clsx`, `tailwind-merge`, `lucide-react`, React, atau `next/font`.

## Token visual

Token berada di `resources/css/theme/tokens.css` dan dipisahkan menjadi:

- brand Padelegan: hijau bakau;
- surface: putih garam dan pasir hangat;
- accent: biru tambak;
- semantic state: success, warning, danger, dan info;
- status laporan: waiting, progress, dan complete.

Radius dan shadow dibatasi. Panel biasa memakai garis tanpa bayangan; shadow hanya untuk drawer atau dialog.

## Primitive components

```blade
<x-ui.action href="/lapor">Buat laporan</x-ui.action>
<x-ui.action variant="secondary">Cek laporan</x-ui.action>
<x-ui.action variant="danger">Tandai tidak valid</x-ui.action>

<x-ui.input name="reporter_name" />
<x-ui.textarea name="description" />
<x-ui.select name="dusun_id">...</x-ui.select>
<x-ui.field-error name="reporter_name" />

<x-ui.alert variant="info" title="Privasi warga">...</x-ui.alert>
<x-ui.badge variant="success">Selesai</x-ui.badge>
<x-ui.spinner />
<x-ui.empty-state title="Belum ada laporan" />
```

Daftar komponen:

- `action`: primary, secondary/outline, quiet/ghost, danger; ukuran sm, md, lg, icon;
- `input`, `textarea`, `select`, `field-error`;
- `alert`: info, success, warning, danger;
- `badge`: neutral, success, warning, danger, info;
- `card`, `card-header`, `card-content`, `card-footer`;
- `container`, `spinner`, `empty-state`, dan `sheet`.

## Komponen domain laporan

Halaman tidak melakukan mapping status sendiri-sendiri:

```blade
<x-reports.status :status="$report->status" />
<x-reports.verification-badge :status="$report->verification_status" />
<x-reports.priority-badge :priority="$report->citizen_priority" />
```

## Konvensi layout

- Horizontal page: `site-shell` atau `x-ui.container`.
- Section publik: `py-8` sampai `py-14`, disesuaikan dengan kepadatan konten.
- Form/card: `p-4`, `p-5`, atau `p-6`.
- Gap kecil: `gap-2`; normal: `gap-4`; grid: `gap-5/6`; section: `gap-8/12`.
- Pill hanya untuk status/tag, bukan seluruh tombol dan panel.

## Aturan penggunaan

1. Buat komponen hanya bila ada reuse atau kontrak data yang jelas.
2. Jangan membungkus setiap section homepage menjadi card.
3. Jangan menambahkan variant hanya untuk satu halaman tanpa alasan semantik.
4. Field selalu mempunyai label nyata, hint opsional, dan error yang terhubung lewat `aria-describedby`.
5. Drawer harus dapat ditutup dengan tombol, overlay, dan tombol Escape.
