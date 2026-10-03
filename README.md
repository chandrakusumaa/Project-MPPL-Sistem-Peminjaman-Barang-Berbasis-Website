# Organization Inventory

## Menjalankan Queue Worker untuk Notifikasi Email

Sistem notifikasi pada aplikasi ini menggunakan mekanisme antrean (queue) yang berjalan di latar belakang (background) agar pengguna tidak perlu menunggu proses pengiriman email selesai untuk melanjutkan aksinya. Oleh karena itu, *Queue Worker* harus selalu berjalan saat aplikasi sedang beroperasi.

Untuk menjalankan Queue Worker:
```bash
php artisan queue:work
```

### Konfigurasi
- **Queue Driver**: Aplikasi ini dikonfigurasi untuk menggunakan driver database (`QUEUE_CONNECTION=database`) atau sesuai di file `.env`.
- **Email Driver**: Untuk tahap pengembangan, pengiriman email dialihkan ke file log lokal. Jika Anda ingin melihat notifikasi email masuk, periksa file `storage/logs/laravel.log`. (Ini dikarenakan konfigurasi `MAIL_MAILER=log` pada `.env`).

## Fitur Fase 7
Aplikasi ini sudah mengimplementasikan notifikasi *In-App* dan *Email* di berbagai event, termasuk:
- Permintaan Bergabung (Membership Request)
- Undangan Bergabung (Invitation)
- Pengajuan, Persetujuan, Penolakan Peminjaman
- Jatuh Tempo Pengembalian
- Pengembalian yang Diproses
- Laporan Kerusakan
- Perubahan Role/Penghapusan Member
