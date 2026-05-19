# Installasi Development

Panduan ini menjelaskan cara menyiapkan `project-ar` di lingkungan development pada mesin lokal Linux.

## 1. Clone Repository

```bash
git clone <repo-url> project-ar
cd project-ar
```

## 2. Pasang Dependensi PHP

```bash
composer install
```

## 3. Pasang Dependensi JavaScript

```bash
npm install
```

## 4. Siapkan Berkas Lingkungan

Duplikat file environment atau buat file baru:

```bash
cp .env.example .env
```

Kemudian atur koneksi database dan konfigurasi lain di `.env`.

## 5. Generate App Key

```bash
php artisan key:generate
```

## 6. Migrasi Database

```bash
php artisan migrate
```

## 7. Buat Storage Link

```bash
php artisan storage:link
```

## 8. Kompilasi Aset Frontend

Untuk development, jalankan:

```bash
npm run dev
```

Jika Anda tidak sedang mengubah file JavaScript atau CSS, menjalankan `npm run dev` tidak wajib selama asset yang diperlukan sudah tersedia di `public/`.

## 9. Jalankan Server

```bash
php artisan serve
```

Akses aplikasi di `http://127.0.0.1:8000`.

## Blender untuk Konversi `.blend`

Jika Anda ingin mengonversi file `.blend` ke `.glb`, pastikan Blender sudah terpasang di mesin Anda. Aplikasi ini menggunakan Blender dalam pipeline konversi, jadi tanpa Blender file `.blend` tidak dapat diolah.

Contoh instalasi Blender di Linux:

```bash
sudo apt update
sudo apt install blender
```

## Queue Tanpa Redis

Jika Anda menggunakan Docker tetapi tidak memakai Redis sebagai antrean, gunakan driver antrean lain seperti `database` atau `sync`. Pastikan setting `QUEUE_CONNECTION` di `.env` disesuaikan.

Contoh untuk menggunakan database queue:

```bash
QUEUE_CONNECTION=database php artisan queue:work --tries=3
```

Atau untuk mode sinkron (tanpa worker terpisah):

```bash
QUEUE_CONNECTION=sync
```

Jika Anda memilih database queue, jalankan migrasi job table terlebih dahulu:

```bash
php artisan queue:table
php artisan migrate
```

## Tips Tambahan

- Jika memakai Docker, jalankan `docker-compose up --build` bila tersedia konfigurasi container.
- Pastikan direktori `storage/` dan `bootstrap/cache/` dapat ditulis oleh server.
- Periksa `public/storage` untuk file marker atau GLB yang diunggah.
