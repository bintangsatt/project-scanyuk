# Alur Kerja Website

Aplikasi ini menggunakan wizard pembuatan proyek AR dengan langkah yang jelas.

## Langkah 1: Upload Marker / Pilih Marker

Pengguna dapat memilih marker yang sudah tersedia dari library atau mengunggah marker kustom. Marker ini akan digunakan sebagai target fisik AR.

- Marker diproses lewat endpoint API.
- Saat upload, sistem melakukan polling hingga marker siap.
- Marker yang dipilih ditampilkan sebagai thumbnail di wizard.

## Langkah 2: Pilih Konten AR

Pengguna dapat mengunggah file `.glb` untuk konten 3D yang akan ditampilkan di AR.

- File `.glb` dimuat ke preview Three.js.
- Semua animasi (`clips`) diambil dan ditampilkan.

## Langkah 3: Preview & Posisi

Pada halaman preview, pengguna dapat mengatur posisi dan rotasi model.

- Area canvas preview dibuat gelap agar perubahan gerakan lebih nyata.
- Overlay marker dibuat minimal: hanya tanda plus kecil di lantai.
- Sumbu X, Y, dan Z ditampilkan di canvas.

## Langkah 4: Generate AR

Setelah konfigurasi selesai, pengguna mengirimkan data proyek.

- Form mengirim `anim_clip` sebagai nama clip animasi, bukan index.
- Data marker, model, posisi, dan animasi disimpan dalam proyek AR.

## Viewer Runtime

Setelah proyek berhasil dibuat, pengguna dapat membuka viewer AR.

- Viewer memakai A-Frame + MindAR.
- Model dimuat dengan animasi yang dipilih.
- Marker yang telah dibuat menjadi pemicu AR di perangkat yang mendukung.
