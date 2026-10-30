# Organization Inventory

Aplikasi monolit Laravel untuk Ruang Inventaris individu/kelompok, aset per unit fisik, peminjaman, inspeksi, dan pembayaran manual. Frontend Blade/Livewire memakai data MySQL; source React di Pedoman/Desain/source tetap menjadi referensi desain.

Acuan produk: [indeks pedoman](Pedoman/README.md), PRD v0.4, SRS v1.2, ERD/kamus data v1.2, dan [AGENTS.md](AGENTS.md). Lihat [status implementasi dan pengujian](docs/IMPLEMENTATION.md).

## Menjalankan development

Prasyarat: Docker dengan Compose v2, Node 22.12+ (di cloud diuji Node 24), npm, dan Python 3. Setup Docker ini ditujukan untuk host Linux. PHP 8.4, PDO MySQL, GD, Imagick, dan Composer disediakan image PHP yang dipin di docker/Dockerfile. Database: MySQL 8.4 dengan volume inventory_db.

    bash scripts/setup.sh
    docker compose exec app php artisan db:seed
    docker compose ps

Skrip membuat .env dari .env.example hanya jika belum ada, memasang dependency dari lockfile, mempertahankan application key yang ada, menjalankan migration, menyiapkan Storage, membangun aset, lalu menyalakan aplikasi pada port 8000, queue, dan scheduler. Skrip dapat dijalankan ulang; tidak menjalankan migrate:fresh atau mereset volume.

Di cloud yang memakai direktori Docker terpisah, gunakan variabel yang sama untuk setiap perintah:

    export DOCKER_CONFIG=/workspace/.onboarding/docker
    export NPM_CONFIG_CACHE=/workspace/.onboarding/npm-cache
    bash scripts/setup.sh

Installer Composer memakai jaringan host dan meneruskan variabel proxy yang tersedia; nilai proxy tidak ditulis ke repo. Ini menghindari keterbatasan DNS eksternal pada jaringan Docker cloud. CA tepercaya host dipasang read-only dan verifikasi TLS tetap aktif.\n\nPada Docker dengan storage driver vfs, helper setup meratakan image PHP baru untuk mengurangi salinan layer. Filesystem berasal dari image yang diverifikasi saat pull; TLS/checksum tidak dinonaktifkan. Runtime yang sudah dipakai aplikasi tidak dihapus oleh helper.

.env.example hanya memuat konfigurasi **development lokal**. Sesuaikan APP_URL dengan origin aplikasi agar tautan email/QR benar. Password database contoh bukan kredensial produksi. Seeder menolak APP_ENV=production.

| Akun demo | Peran di Peralatan Raka |
| --- | --- |
| admin@example.test | Admin |
| staff@example.test | Staff |
| member@example.test | Member |
| other@example.test | Belum bergabung |

Password demo: DemoPassword123! Akun demo telah diverifikasi; gunakan akun baru untuk menguji registrasi/verifikasi. Seeder idempotent mempertahankan data yang sudah ada.

Jika menjalankan aplikasi dengan PHP langsung (misalnya `php artisan serve`), tambahkan akun demo ke database lokal dengan:

    php artisan db:seed

Keluar dari akun yang belum terverifikasi, lalu masuk sebagai `other@example.test` dengan password demo di atas untuk mencoba membuat ruang dari akun tanpa keanggotaan. Pembuat ruang otomatis menjadi Admin pada ruang barunya. Gunakan `admin@example.test` untuk langsung mengelola ruang demo Peralatan Raka. Seeder tidak mengganti password akun demo yang sudah ada.

## Database dan storage

Migration membuat **16 tabel domain/pendukung v1.2**, termasuk users, notifications, dan password_reset_tokens, tanpa migration framework ganda. Tabel infrastruktur tambahan: migrations, sessions, jobs/job_batches/failed_jobs, cache/cache_locks.

    docker compose exec app php artisan migrate
    docker compose exec app php artisan migrate:status
    docker compose exec app php artisan storage:link

DDL aktif ada di database/schema/inventory-v1.2.sql, diikuti migration hardening lifecycle. SQL Pedoman/Arsip tidak digunakan untuk menyiapkan aplikasi.

Foto aset/avatar berada pada disk public. Bukti tagihan/pembayaran berada pada disk privat local dan diunduh melalui pemeriksaan pemilik/role. Backup database bersama storage/app. Jangan mengubah saldo/nominal/status finansial langsung melalui SQL.

## Queue, scheduler, dan email

Compose menjalankan worker dan scheduler sebagai service terpisah:

    docker compose logs --tail=50 queue scheduler
    docker compose exec app php artisan queue:failed
    docker compose exec app php artisan inventory:overdue

Alternatif dengan PHP terpasang:

    php artisan serve
    php artisan queue:work --tries=3 --timeout=90
    php artisan schedule:work

Pada server dengan cron, jalankan php artisan schedule:run setiap menit menggantikan schedule:work. Kalender bisnis Asia/Jakarta; timestamp UTC.

Email development menggunakan MAIL_MAILER=log. Tautan verifikasi/reset/undangan berada di log development. Untuk pengiriman nyata, isi pengaturan SMTP secara aman di lingkungan deployment; jangan commit .env.

Untuk memverifikasi akun development yang memakai email contoh tanpa kotak masuk:

1. Pastikan `.env` lokal memakai `MAIL_MAILER=log`. Jika baru mengubah konfigurasi, jalankan `php artisan config:clear`.
2. Jalankan `php artisan queue:work` di terminal terpisah agar email diproses. Worker sudah tersedia bila memakai Compose.
3. Masuk ke akun tersebut, buka `/email/verify`, lalu kirim ulang email verifikasi.
4. Buka tautan verifikasi terbaru dari `storage/logs/laravel.log` di browser yang masih masuk sebagai akun yang sama. Pastikan `APP_URL` sesuai alamat aplikasi. Jika tautan kedaluwarsa, kirim ulang.

Pada halaman Jelajahi Ruang, akun belum terverifikasi mendapat petunjuk menuju verifikasi email. Aksi membuat ruang, bergabung, atau membatalkan pengajuan juga mengarahkan ke verifikasi; sesi yang sudah keluar diarahkan ke login. Syarat verifikasi tetap berlaku di backend.

## Pengujian

MySQL wajib digunakan. Setup menyiapkan database **terpisah** orginventory_test beserta grant development tanpa mereset database aplikasi.

    docker compose exec app vendor/bin/pest
    docker compose exec app vendor/bin/pint --test
    docker compose exec app composer check-platform-reqs
    npm run build

Suite menjalankan migration pada database pengujian. Tes concurrency memakai dua proses PHP terpisah pada MySQL, bukan SQLite atau dua panggilan berurutan.

Smoke test browser memerlukan Chromium dan demo seed. Test membuat satu aset development dengan nama unik:

    CHROMIUM_PATH=/usr/bin/chromium node tests/Browser/smoke.mjs

Screenshot ditulis ke /tmp/orginventory-desktop.png dan /tmp/orginventory-mobile.png. Hasil aktual dan batas verifikasi: [docs/IMPLEMENTATION.md](docs/IMPLEMENTATION.md). Checksum pedoman harus diperiksa dari direktori Pedoman:

    (cd Pedoman && sha256sum -c SHA256SUMS.txt)

Dashboard pribadi tersedia pada `/dashboard`, Organization Saya beserta tab Jelajahi akun pada `/my-organizations`, daftar publik pada `/organizations`, dan tagihan lintas ruang milik akun pada `/my-charges`. Laporan pemisahan navigasi dan istilah terbaru: [Organization discovery](docs/visual/organization-discovery/README.md). Laporan awal dashboard desktop/mobile serta cara menjalankan browser test akun: [docs/visual/account-dashboard/README.md](docs/visual/account-dashboard/README.md).

## Aturan utama

Role berasal dari membership aktif **per ruang**. Satu user dapat Admin di A dan Member di B. Katalog privat memerlukan membership aktif. Aset adalah satu unit dengan ULID/QR tetap. Pengajuan masa depan bukan reservasi.

OD17, keputusan pemilik proyek: Admin/Staff **boleh menyetujui pinjaman sendiri** jika syaratnya terpenuhi. Mereka tetap **tidak boleh menyetujui kewajiban finansial sendiri**. Hanya pembayaran confirmed dan kredit mengurangi saldo. Void/cancellation mempertahankan histori.

## Struktur kode

- app/Livewire dan resources/views: halaman, formulir, dan tampilan.
- app/Actions: transaksi, locking, validasi state, audit, dan aturan bisnis.
- app/Policies dan app/Http/Requests: kewenangan per ruang dan validasi akun.
- app/Models dan database: entitas, relasi, schema, migration, dan demo seeder.
- tests/Feature, tests/Concurrency, tests/Browser: verifikasi integrasi.

Untuk penggunaan produksi, lakukan pengujian penerimaan lengkap dan review deployment yang dijelaskan pada dokumen status. Implementasi development ini bukan sertifikasi bahwa seluruh acceptance criteria sudah lulus.
