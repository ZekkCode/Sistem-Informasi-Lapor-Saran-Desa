# Struktur JavaScript

`app.js` mendaftarkan Alpine dan memanggil modul fitur. Setiap modul memeriksa keberadaan elemen sebelum menjalankan perilakunya.

- `features/report-form.js`: pilihan subkategori, pratinjau foto, validasi, dan direct upload.
- `features/admin-report-form.js`: validasi serta direct upload bukti penanganan.
- `features/home-motion.js`: efek kedalaman ringan pada foto hero untuk pointer presisi.
- `features/site-motion.js`: AOS pada elemen bertanda dan Lenis pada perangkat yang mendukungnya.

`site-motion.js` memuat AOS dan Lenis secara dinamis. Pengguna dengan `prefers-reduced-motion` tidak menerima smooth scroll atau efek kedalaman.

Blade merender seluruh konten dan form. JavaScript menambah interaksi tanpa menjadi syarat untuk membaca halaman.
