# Struktur Project

Struktur ini memakai pola Laravel dan memisahkan area pengguna dari domain bisnis. Tambahkan lapisan ketika transaksi, query kompleks, atau pemakaian ulang membutuhkannya.

```text
Padelegan-Lapor/
├── app/
│   ├── Enums/
│   │   ├── AdminPriority.php
│   │   ├── CitizenPriority.php
│   │   ├── MediaBackupStatus.php
│   │   ├── ReportMediaType.php
│   │   ├── ReportStatus.php
│   │   ├── UserRole.php
│   │   └── VerificationStatus.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Site/
│   │   │   │   ├── HomeController.php
│   │   │   │   ├── PublicReportController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── ReportTrackingController.php
│   │   │   │   └── ReportUploadController.php
│   │   │   └── Admin/
│   │   │       ├── AuthController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── ReportController.php
│   │   │       ├── ReportWorkflowController.php
│   │   │       ├── ReportExportController.php
│   │   │       ├── ReportMediaController.php
│   │   │       ├── ReportUploadController.php
│   │   │       ├── UserController.php
│   │   │       └── *DataController.php
│   │   ├── Middleware/
│   │   └── Requests/
│   │       ├── Site/
│   │       │   ├── AuthorizeReportUploadsRequest.php
│   │       │   ├── StoreReportRequest.php
│   │       └── Admin/
│   │           ├── FilterReportsRequest.php
│   │           ├── LoginRequest.php
│   │           ├── StoreUserRequest.php
│   │           ├── UpdateReportWorkflowRequest.php
│   │           └── UpdateUserRequest.php
│   ├── Models/
│   ├── Queries/
│   │   └── AdminReportQuery.php
│   ├── Services/
│   │   ├── Admin/AuditService.php
│   │   ├── Reports/
│   │   │   ├── DirectUploadService.php
│   │   │   ├── ReportCodeGenerator.php
│   │   │   ├── ReportMediaService.php
│   │   │   ├── ReportSubmissionService.php
│   │   │   └── ReportWorkflowService.php
│   │   ├── Site/SiteDataService.php
│   │   └── Storage/GoogleDriveBackup.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/
│   ├── architecture/
│   └── design/
├── public/
│   └── images/
│       └── village/
├── resources/
│   ├── css/
│   │   ├── app.css
│   │   ├── base/document.css
│   │   ├── components/
│   │   │   ├── admin.css
│   │   │   ├── actions.css
│   │   │   ├── forms.css
│   │   │   ├── home.css
│   │   │   ├── motion.css
│   │   │   ├── public-flow.css
│   │   │   ├── report-form.css
│   │   │   └── status.css
│   │   └── theme/tokens.css
│   ├── js/
│   │   ├── app.js
│   │   ├── features/
│   │   │   ├── admin-report-form.js
│   │   │   ├── home-motion.js
│   │   │   ├── report-form.js
│   │   │   └── site-motion.js
│   │   └── README.md
│   └── views/
│       ├── layouts/
│       │   ├── site.blade.php
│       │   └── admin.blade.php
│       ├── components/
│       │   ├── ui/
│       │   ├── site/
│       │   └── reports/
│       ├── site/
│       │   ├── home.blade.php
│       │   ├── reports/
│       │   │   ├── create.blade.php
│       │   │   ├── success.blade.php
│       │   │   ├── track.blade.php
│       │   │   ├── index.blade.php
│       │   │   └── show.blade.php
│       │   └── about/index.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── reports/
│       │   │   ├── index.blade.php
│       │   │   ├── show.blade.php
│       │   │   └── print.blade.php
│       │   ├── master-data/index.blade.php
│       │   └── users/index.blade.php
│       └── auth/admin-login.blade.php
├── routes/
│   ├── web.php
│   └── admin.php
├── storage/app/public/reports/
│   └── {report-code}/
│       ├── before/
│       └── after/
└── tests/
    ├── Feature/
    │   ├── Site/
    │   ├── Admin/
    │   └── Privacy/
    └── Feature/Storage/
```

## Batas tanggung jawab

### `Controllers/Site`

Hanya menerima request masyarakat, memanggil query/service, lalu mengembalikan view atau redirect. Nama `Site` dipilih agar tidak rancu dengan folder web server `public/`.

### `Controllers/Admin`

Semua endpoint yang membutuhkan autentikasi pemerintah desa. Perubahan verifikasi, prioritas, status, catatan, dan media masuk melalui `ReportWorkflowController` agar alurnya mudah diaudit.

### `Queries`

Menampung query baca untuk dashboard, filter, dan daftar publik. Model tetap fokus pada relasi dan scope.

### `Services/Reports`

- `ReportSubmissionService`: menyimpan laporan, sumber QR, media awal, dan history pertama dalam transaksi.
- `ReportCodeGenerator`: membuat kode publik acak yang tidak dapat ditebak dari ID.
- `ReportMediaService`: validasi pasca-request, kompresi, penamaan acak, dan penyimpanan foto.
- `ReportWorkflowService`: verifikasi, perubahan prioritas/status, catatan, media penanganan, history, dan audit log dalam transaksi.

### `resources/views/components`

- `ui`: primitive kecil seperti button, field, dialog, dan empty state.
- `site`: navigasi, footer, dan elemen identitas situs.
- `reports`: kartu laporan, status, timeline, galeri, serta filter yang dipakai lintas halaman.
Komponen dibuat ketika memang dipakai ulang. Jangan memecah setiap `div` menjadi komponen.

### `resources/js/features`

JavaScript dikelompokkan berdasarkan fitur. Alpine menangani interaksi lokal; modul JavaScript dipakai untuk upload, pratinjau foto, dan gerak antarmuka.

## Aturan penyimpanan media

```text
storage/app/public/reports/PDL-2026-X7K9Q2/
├── before/
│   ├── 31c5d8a2.webp
│   └── 8b950ef1.webp
└── after/
    └── bd5ea413.webp
```

Nama asli file pengguna tidak disimpan sebagai nama file publik. Metadata sensitif gambar dihapus saat optimasi.

## Status implementasi

Fondasi, alur publik, autentikasi, workflow admin, pengguna, data master, ekspor, penyimpanan media, dan pengujian inti telah diimplementasikan. Struktur di atas menggambarkan file aktif. Tambahkan folder saat fitur yang memakainya mulai dikerjakan.
