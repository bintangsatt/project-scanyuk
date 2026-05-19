# Project Overview

`project-ar` adalah aplikasi web berbasis Laravel untuk membuat dan mengelola konten Augmented Reality (AR). Pengguna dapat memilih atau mengunggah marker, mengunggah model 3D (.glb), melihat preview di browser, lalu menghasilkan proyek AR yang siap diputar.

## Fitur Utama

- Pilih marker bawaan atau unggah marker kustom.
- Unggah file `.glb` untuk konten AR.
- Preview 3D interaktif dengan kontrol orientasi dan posisi.
- Pilihan animasi model dan pengiriman clip name yang benar.
- Theme halaman `create` terang, dengan area canvas preview gelap untuk visibilitas.
- Viewer AR runtime dengan A-Frame / MindAR untuk memutar pengalaman AR di perangkat.

## Struktur Dasar

- `app/Http/Controllers/` - kontroler Laravel untuk logika marker, proyek AR, dan upload file.
- `resources/views/` - tampilan Blade untuk wizard pembuatan AR, dashboard, dan viewer.
- `routes/web.php` / `routes/api.php` - rute web dan API aplikasi.
- `public/` - aset statis dan entry point aplikasi.
- `storage/` - tempat penyimpanan file yang diunggah dan cache runtime.
