# Software Requirements Specification (SRS)

## Organization Inventory — Sistem Pengelolaan Barang

| Parameter | Detail |
| --- | --- |
| **Nama Dokumen** | SRS Organization Inventory (Sistem Pengelolaan Barang) |
| **Versi** | 1.0 (Draft untuk review) |
| **Tanggal** | 3 Oktober 2026 |
| **Target Rilis** | Phase 1 (MVP) |
| **Sumber** | PRD Organization Inventory (Draft), tiga flowchart (Landing & Autentikasi, Dashboard User, Dashboard Organisasi), dan hasil klarifikasi dengan pemilik produk |
| **Standar Acuan** | Struktur mengikuti IEEE 830 / ISO/IEC/IEEE 29148, disesuaikan untuk proyek MVP |

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum](#2-deskripsi-umum)
3. [Kebutuhan Antarmuka Eksternal](#3-kebutuhan-antarmuka-eksternal)
4. [Kebutuhan Fungsional](#4-kebutuhan-fungsional)
5. [Use Case](#5-use-case)
6. [Aturan Bisnis](#6-aturan-bisnis)
7. [Kebutuhan Data](#7-kebutuhan-data)
8. [Kebutuhan Non-Fungsional](#8-kebutuhan-non-fungsional)
9. [Matriks Keterlacakan (Traceability)](#9-matriks-keterlacakan-traceability)
10. [Lampiran](#10-lampiran)

---

## 1. Pendahuluan

### 1.1 Tujuan

Dokumen ini mendefinisikan kebutuhan perangkat lunak untuk **Organization Inventory**, yaitu aplikasi web multi-organisasi untuk mengelola aset, peminjaman, pengembalian, dan riwayat penggunaan barang berbasis QR Code. Dokumen ditujukan bagi:

| Pembaca | Kegunaan |
| --- | --- |
| Pemilik produk | Memvalidasi bahwa kebutuhan sudah sesuai harapan |
| Developer / AI agent | Acuan implementasi (fungsional, data, aturan bisnis) |
| QA / Tester | Dasar penyusunan skenario dan kriteria uji |
| Pemangku kepentingan lain | Memahami cakupan dan batasan MVP |

### 1.2 Ruang Lingkup

Organization Inventory menjawab masalah pengelolaan barang yang masih manual (spreadsheet/kertas), yaitu aset sulit dilacak, riwayat peminjaman sulit ditelusuri, dan informasi aset tersebar.

**Dalam lingkup (MVP):**

- Autentikasi (registrasi dengan verifikasi email, login, reset password).
- Multi-organisasi: Explore, pembuatan organisasi, join request, undangan email.
- Role per organisasi (Admin, Staff, Member) dan pembatasan akses.
- Manajemen inventory (aset, kategori) dan QR Code (generate, download, print, scan).
- Peminjaman dan pengembalian dengan inspeksi kondisi, OVERDUE otomatis, dan pencatatan denda (angka).
- Damage report dan maintenance, riwayat aset (asset log).
- Notifikasi (in-app, email, pop-up toast).
- Dashboard (Member dan Admin/Staff), export laporan CSV, Profile/Settings.

**Di luar lingkup (MVP):** pembayaran denda, sistem stok/kuantitas, reservasi jadwal, export PDF/Excel, notifikasi real-time berbasis WebSocket, aplikasi mobile native, multi-bahasa, integrasi pihak ketiga selain email.

### 1.3 Definisi, Akronim, dan Istilah

| Istilah | Definisi |
| --- | --- |
| **Organisasi** | Entitas (kampus, komunitas, perusahaan, dll.) yang memiliki kumpulan aset dan anggota. |
| **Aset** | Satu unit barang fisik. Satu aset = satu record = satu QR Code. |
| **Admin** | Pengelola penuh organisasi (anggota, aset, peminjaman, laporan, pengaturan). |
| **Staff** | Pengelola operasional (aset, peminjaman, pengembalian, maintenance), tanpa akses anggota, laporan, dan pengaturan. |
| **Member** | Anggota biasa: melihat katalog, mengajukan peminjaman, scan QR, melapor kerusakan. |
| **Join Request** | Permintaan user untuk bergabung ke organisasi, perlu persetujuan Admin. |
| **Undangan (Invitation)** | Ajakan bergabung yang dikirim Admin ke email tertentu, berlaku 7 hari. |
| **Inspeksi** | Pemeriksaan kondisi aset saat dikembalikan: Good, Minor Damage, Major Damage, Lost. |
| **OVERDUE** | Status peminjaman yang melewati tanggal estimasi pengembalian dan belum dikembalikan. |
| **Damage Report** | Catatan kerusakan aset beserta tindak lanjutnya. |
| **Asset Log** | Catatan kronologis setiap peristiwa penting pada suatu aset. |
| **Slug** | Pengenal URL unik organisasi (mis. `himpunan-it`). |
| **ULID** | Identifier unik acak yang dipakai sebagai kode publik aset (`assets.code`). |
| **Soft delete** | Penghapusan logis (data ditandai terhapus, tidak dihapus fisik). |
| **MVP** | Minimum Viable Product, rilis awal (Phase 1). |
| **RBAC** | Role-Based Access Control. |
| **IDOR** | Insecure Direct Object Reference, akses data milik pihak lain lewat manipulasi ID/URL. |

### 1.4 Referensi

| Dokumen | Keterangan |
| --- | --- |
| PRD — Organization Inventory | Sumber utama kebutuhan produk (Draft, diperbarui dengan integrasi flowchart) |
| Flowchart Landing Page & Login Page | Alur guest: Explore, Register, Login, Forgot Password |
| Flowchart Dashboard User | Alur Member: Profile, My Organizations, Notification, My Borrowing, Scan QR |
| Flowchart Dashboard Organisasi | Alur Admin/Staff: Inventory, Borrowings, Members, Damage Reports, Settings |
| Klarifikasi pemilik produk | Keputusan yang menyelesaikan perbedaan PRD dan flowchart (lihat Lampiran 10.3) |

### 1.5 Gambaran Dokumen

Bagian 2 menjelaskan konteks produk dan pengguna. Bagian 3 menjelaskan antarmuka. Bagian 4 memuat kebutuhan fungsional per modul (ID `FR-*`). Bagian 5 memuat use case. Bagian 6 memuat aturan bisnis (ID `BR-*`). Bagian 7 memuat model data. Bagian 8 memuat kebutuhan non-fungsional (ID `NFR-*`). Bagian 9 memetakan PRD ke kebutuhan. Lampiran berisi route, keputusan desain, dan asumsi.

Seluruh kebutuhan berprioritas **Must** (MVP) kecuali disebutkan lain.

---

## 2. Deskripsi Umum

### 2.1 Perspektif Produk

Aplikasi web mandiri (monolit Laravel) dengan basis data relasional. Aplikasi bersifat **multi-organisasi** dalam satu database: data dipisahkan per organisasi lewat `organization_id`, dan role disimpan **per organisasi**, sehingga satu user dapat menjadi Admin di satu organisasi dan Member di organisasi lain.

Aplikasi berinteraksi dengan: (1) server email (SMTP) untuk verifikasi, reset password, undangan, dan notifikasi; (2) kamera perangkat untuk scan QR; (3) scheduler server untuk penandaan OVERDUE otomatis; (4) queue worker untuk pengiriman email.

### 2.2 Fungsi Utama Produk

| Area | Ringkasan Fungsi |
| --- | --- |
| Autentikasi | Register + verifikasi email, login, lupa/reset password |
| Multi-organisasi | Explore, buat organisasi, join request, undangan email, kelola anggota dan role |
| Inventory | CRUD aset dan kategori, foto aset, katalog untuk Member |
| QR Code | Generate, download PNG/SVG, print stiker, scan via kamera |
| Peminjaman | Request, approve/reject, borrowed, overdue, return + inspeksi, denda |
| Kondisi aset | Damage report, maintenance, tandai ditemukan |
| Riwayat | Timeline log per aset |
| Notifikasi | In-app, email, toast, preferensi |
| Analitik | Dashboard, grafik, export CSV |
| Akun | Profil, email, password, preferensi, hapus akun |

### 2.3 Karakteristik Pengguna

| Aktor | Deskripsi | Hak Akses Ringkas | Tingkat Keahlian |
| --- | --- | --- | --- |
| **Guest** | Pengunjung belum login | Landing, Explore, profil publik organisasi, Register, Login | Awam |
| **Member** | Anggota organisasi | Katalog, request borrow, scan QR, lapor kerusakan, My Borrowing | Awam |
| **Staff** | Petugas operasional | Semua hak Member + kelola aset, kategori, peminjaman, pengembalian, damage report, dashboard | Menengah |
| **Admin** | Pengelola organisasi | Semua hak Staff + anggota, report/export, settings organisasi | Menengah |
| **Sistem (Scheduler)** | Proses otomatis | Menandai OVERDUE, mengirim notifikasi terkait | Tidak berlaku |

Pembuat organisasi otomatis menjadi Admin organisasi tersebut. Sebuah organisasi boleh memiliki lebih dari satu Admin.

### 2.4 Batasan (Constraints)

| ID | Batasan |
| --- | --- |
| CON-01 | Backend: Laravel (PHP) dengan Eloquent ORM. |
| CON-02 | Frontend: Blade + Livewire, Tailwind CSS, Flux UI. |
| CON-03 | Database: MySQL (alternatif PostgreSQL sesuai PRD); target awal MySQL 8. |
| CON-04 | QR Code: Simple QrCode (backend) dan html5-qrcode (scanner frontend). |
| CON-05 | Grafik: Chart.js. Penyimpanan file: Laravel Storage. Validasi: Laravel Form Request. |
| CON-06 | Scan kamera memerlukan HTTPS (kecuali `localhost`) dan izin kamera dari pengguna. |
| CON-07 | Generate QR format PNG bergantung pada ekstensi `imagick`; SVG selalu tersedia. |

### 2.5 Asumsi dan Dependensi

| ID | Asumsi / Dependensi |
| --- | --- |
| ASM-01 | Pengguna memiliki alamat email aktif untuk verifikasi dan notifikasi. |
| ASM-02 | Server menjalankan queue worker dan cron scheduler (`schedule:run` tiap menit). |
| ASM-03 | Layanan SMTP tersedia di produksi (di development memakai driver `log`). |
| ASM-04 | Zona waktu sistem `Asia/Jakarta`, mata uang Rupiah, bahasa antarmuka Indonesia. |
| ASM-05 | Satu aset adalah satu unit fisik (tidak ada stok/kuantitas). |
| ASM-06 | Pengguna memakai browser modern (lihat NFR-PORT-01). |

### 2.6 Cakupan MVP

Mengacu pada PRD bagian "MVP Scope (Diperbarui)", seluruh butir berikut masuk Phase 1: Autentikasi; Otorisasi dan Role (termasuk UI grayed-out untuk Staff); Multi-Organisasi (Explore dan Join); Manajemen Inventory (CRUD Aset dan Kategori); Sistem QR Code (generate dan scan); Sistem Peminjaman dan Pengembalian; Riwayat Aset; Dashboard Utama. Berdasarkan flowchart, ditambahkan pula Damage Reports, Notifikasi, Settings organisasi, dan Report (export CSV).

---

## 3. Kebutuhan Antarmuka Eksternal

### 3.1 Antarmuka Pengguna

Antarmuka berbasis web, responsif (prioritas mobile karena scan QR dilakukan di HP), berbahasa Indonesia, dengan toast/alert untuk umpan balik aksi. Struktur menu:

| Area | Menu / Halaman |
| --- | --- |
| Publik (Guest) | Landing, Explore Organization, Profil Publik Organisasi, Login, Register, Lupa Password, Reset Password, Terima Undangan |
| User (Member) | Home, Organizations (Semua Organisasi / Organisasi Saya), My Borrowing (Active / Pending / History), Notifications, Scan QR, Profile/Settings |
| Katalog Organisasi (anggota) | Daftar aset, Detail Aset (aksi: Ajukan Peminjaman, Laporkan Kerusakan, History) |
| Manajemen Organisasi (Admin/Staff) | Dashboard, Inventory, Kategori, Borrowing (Pending Request / Active Borrowing / History), Members, Damage Reports, Reports, Settings |

Item **Members, Reports, dan Settings** ditampilkan **nonaktif (grayed out)** untuk Staff dengan tooltip "Hanya untuk Admin".

### 3.2 Antarmuka Perangkat Keras

| Perangkat | Kebutuhan |
| --- | --- |
| Kamera (HP/laptop) | Untuk scan QR Code melalui browser |
| Printer | Untuk mencetak stiker QR (opsional) |

### 3.3 Antarmuka Perangkat Lunak

| Komponen | Antarmuka |
| --- | --- |
| Database | MySQL/PostgreSQL via Eloquent ORM |
| Email | SMTP (verifikasi, reset password, undangan, notifikasi) |
| Queue | Driver database untuk pengiriman email dan listener notifikasi |
| Scheduler | Cron memanggil `php artisan schedule:run` |
| Storage | Laravel Storage (disk `public`) untuk foto dan logo |

### 3.4 Antarmuka Komunikasi

HTTP/HTTPS antara browser dan server. Komunikasi dengan server email melalui SMTP. Pembaruan notifikasi di browser memakai polling berkala (tanpa WebSocket pada MVP).

---

## 4. Kebutuhan Fungsional

### 4.1 Autentikasi dan Landing

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-AUTH-01 | Landing page menampilkan tiga jalur navigasi: Explore Organization, Login, dan Register. | Must |
| FR-AUTH-02 | Register menerima Nama, Email, dan Password (dengan konfirmasi; minimal 8 karakter). | Must |
| FR-AUTH-03 | Sistem memvalidasi keunikan email. Jika sudah terdaftar, tampilkan "Email sudah terdaftar" dan pengguna tetap di form. | Must |
| FR-AUTH-04 | Jika email belum terdaftar, sistem menyimpan akun ke database, mengirim email verifikasi, lalu mengarahkan pengguna ke halaman Login dengan pesan sukses. | Must |
| FR-AUTH-05 | Akun wajib terverifikasi email sebelum mengakses area user. Pengguna belum terverifikasi diarahkan ke halaman verifikasi dengan tombol kirim ulang (dibatasi rate limit). | Must |
| FR-AUTH-06 | Login memakai Email dan Password. Jika salah tampilkan pesan tunggal "Email atau password salah"; jika benar menuju halaman dashboard. | Must |
| FR-AUTH-07 | Lupa password: pengguna memasukkan email. Jika tidak ditemukan tampilkan "Email tidak ditemukan"; jika ditemukan sistem mengirim link reset password, lalu pengguna mengatur password baru dan kembali ke Login. | Must |
| FR-AUTH-08 | Pengguna dapat Logout dan sesi diakhiri. | Must |
| FR-AUTH-09 | Pengguna yang sudah login dan membuka halaman guest (landing/login/register) diarahkan ke dashboard. | Must |

### 4.2 Explore Organization (Publik)

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-EXP-01 | Menampilkan daftar organisasi (tidak diarsipkan) dengan pencarian nama, filter kategori, dan pagination. | Must |
| FR-EXP-02 | Pengguna dapat membuka profil publik organisasi (nama, kategori, deskripsi, logo, jumlah anggota, jumlah aset) tanpa menampilkan daftar aset atau data sensitif. | Must |
| FR-EXP-03 | Pengguna dapat kembali ke daftar dan ke halaman utama kapan saja. | Must |
| FR-EXP-04 | Tombol "Ajukan Bergabung" pada profil publik mengarahkan guest ke login (URL tujuan disimpan), lalu menjalankan alur join request. | Must |

### 4.3 Organisasi, Keanggotaan, dan Settings

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-ORG-01 | Setiap user terautentikasi dapat membuat organisasi dengan nama, deskripsi, kategori, dan logo (opsional). Slug dibuat unik otomatis, pembuat menjadi Admin, `max_borrow_days` default 7 dan `late_fine_per_day` default 0. | Must |
| FR-ORG-02 | Jika pembuatan organisasi gagal, tampilkan "Gagal membuat organisasi" beserta detail validasi. Jika berhasil, kembali ke Organisasi Saya. | Must |
| FR-ORG-03 | Halaman Organizations memiliki tab "Semua Organisasi" (search dan filter) dan "Organisasi Saya". Setiap organisasi menampilkan status user: belum bergabung, menunggu persetujuan, atau sudah bergabung (dengan badge role). | Must |
| FR-ORG-04 | Tombol "Buka" pada organisasi: Admin/Staff menuju dashboard manajemen; Member menuju katalog aset. | Must |
| FR-ORG-05 | User yang belum bergabung dapat mengirim Join Request (pesan opsional), membatalkannya, dan tidak boleh memiliki request PENDING ganda pada organisasi yang sama. | Must |
| FR-ORG-06 | Admin dapat meninjau Join Request: Approve (user menjadi `member`) atau Reject. | Must |
| FR-ORG-07 | Admin dapat mengundang via email (role staff atau member). Sistem membuat token unik dengan masa berlaku 7 hari dan mengirim email berisi link. Admin dapat mengirim ulang dan mencabut undangan. | Must |
| FR-ORG-08 | Halaman undangan: token tidak valid/kedaluwarsa menampilkan "Undangan sudah kedaluwarsa"; belum login diarahkan ke login/register (email terisi); login dengan email yang sama dapat menerima undangan sehingga user ditambahkan dengan role pada undangan; email berbeda ditolak dengan pesan jelas; user yang sudah anggota diberi info. | Must |
| FR-ORG-09 | Admin dapat melihat daftar anggota, mengubah role (admin/staff/member), dan mengeluarkan anggota. | Must |
| FR-ORG-10 | Admin terakhir pada organisasi tidak boleh di-demote, dikeluarkan, atau menghapus akunnya. | Must |
| FR-ORG-11 | Anggota yang masih memiliki peminjaman aktif (BORROWED/OVERDUE) tidak boleh dikeluarkan. Saat dikeluarkan, request PENDING miliknya otomatis menjadi CANCELLED. | Must |
| FR-ORG-12 | Admin dapat mengedit profil organisasi (nama, deskripsi, kategori, logo). | Must |
| FR-ORG-13 | Admin dapat mengatur durasi maksimal peminjaman (`max_borrow_days` ≥ 1) dan denda per hari (`late_fine_per_day` ≥ 0, Rupiah) lalu menyimpan perubahan. | Must |
| FR-ORG-14 | Admin dapat mengarsipkan organisasi (read-only, hilang dari Explore) dan membatalkan pengarsipan. | Must |
| FR-ORG-15 | Admin dapat menghapus organisasi (soft delete) hanya jika tidak ada peminjaman aktif, dengan konfirmasi mengetik nama organisasi. | Must |

### 4.4 Hak Akses dan Isolasi Data (RBAC)

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-RBAC-01 | Sistem mendukung tiga role per organisasi: Admin, Staff, Member, yang disimpan pada relasi user–organisasi (bukan pada user). | Must |
| FR-RBAC-02 | Untuk Staff, menu Members, Reports, dan Settings ditampilkan nonaktif (grayed out). | Must |
| FR-RBAC-03 | Seluruh pembatasan akses ditegakkan di server (respons 403), bukan hanya disembunyikan di UI. | Must |
| FR-RBAC-04 | Data organisasi A tidak dapat diakses oleh user yang bukan anggota/pengelola organisasi A (pencegahan IDOR). | Must |
| FR-RBAC-05 | Pada organisasi yang diarsipkan, semua aksi tulis (aset baru, peminjaman baru) ditolak. | Must |

### 4.5 Manajemen Inventory

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-INV-01 | Admin/Staff melihat daftar aset (foto, nama, kode, kategori, lokasi, status) dengan pencarian, filter kategori/status, pengurutan, dan pagination. | Must |
| FR-INV-02 | Admin/Staff dapat menambah aset: nama, kategori, deskripsi, spesifikasi, lokasi, tanggal beli (opsional), foto (opsional, maks 2 MB). Status awal AVAILABLE dan kode ULID dibuat otomatis. | Must |
| FR-INV-03 | Validasi input aset; jika tidak valid tampilkan error pada form, jika valid simpan dan kembali ke daftar. | Must |
| FR-INV-04 | Detail aset menampilkan informasi lengkap dan aksi: Generate QR, Edit, Hapus, History. | Must |
| FR-INV-05 | Edit aset dapat mengubah semua field kecuali `code`. Status aset tidak dapat diubah manual dari form edit. | Must |
| FR-INV-06 | Hapus aset berupa soft delete dengan konfirmasi; ditolak jika aset berstatus BORROWED atau memiliki peminjaman aktif. | Must |
| FR-INV-07 | Admin/Staff mengelola kategori aset per organisasi (nama unik per organisasi). Kategori yang masih dipakai aset tidak dapat dihapus. | Must |
| FR-INV-08 | Anggota dapat melihat katalog aset organisasinya (grid/list, foto, nama, kategori, lokasi, badge status) dengan pencarian, filter, dan pagination, serta membuka detail aset. | Must |
| FR-INV-09 | Admin/Staff dapat menandai aset LOST sebagai ditemukan (LOST → AVAILABLE) dengan catatan wajib. | Must |

### 4.6 QR Code

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-QR-01 | Sistem membuat QR Code unik per aset yang berisi URL absolut ke halaman detail aset (`/o/{slug}/assets/{code}`), bukan data mentah aset. | Must |
| FR-QR-02 | Admin/Staff dapat mengunduh QR dalam format SVG dan PNG (PNG hanya jika ekstensi imagick tersedia). | Must |
| FR-QR-03 | Admin/Staff dapat mencetak stiker QR (berisi QR, nama aset, kode, dan nama organisasi). | Must |
| FR-QR-04 | Halaman Scan QR membuka kamera, membaca URL/identifier dari QR, dan mengirimkannya ke server untuk divalidasi. | Must |
| FR-QR-05 | QR atau aset tidak valid menampilkan "QR / Aset Invalid" dan memungkinkan scan ulang. | Must |
| FR-QR-06 | Jika pemindai bukan anggota organisasi, tampil pesan harus bergabung dahulu beserta tombol Ajukan Bergabung. Detail aset tidak ditampilkan. | Must |
| FR-QR-07 | Jika pemindai adalah anggota, tampil detail aset dengan aksi Ajukan Peminjaman dan Laporkan Kerusakan. | Must |
| FR-QR-08 | Tersedia input manual kode aset sebagai alternatif scan. | Must |
| FR-QR-09 | Jika URL QR dibuka langsung (mis. kamera bawaan HP) oleh guest, sistem meminta login lalu kembali ke URL tujuan dan menerapkan aturan FR-QR-05 sampai FR-QR-07. | Must |
| FR-QR-10 | Perubahan data aset tidak mengubah QR sehingga tidak perlu cetak ulang. | Must |

### 4.7 Peminjaman dan Pengembalian

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-BRW-01 | Anggota dapat mengajukan peminjaman dari detail aset berstatus AVAILABLE dengan mengisi tanggal peminjaman, estimasi tanggal pengembalian, dan alasan. | Must |
| FR-BRW-02 | Validasi request: tanggal pinjam ≥ hari ini; tanggal kembali > tanggal pinjam; durasi ≤ `max_borrow_days`; alasan wajib (min. 10 karakter); tidak ada request PENDING/aktif ganda untuk aset yang sama oleh user yang sama. | Must |
| FR-BRW-03 | Request tersimpan berstatus PENDING sampai disetujui atau ditolak. | Must |
| FR-BRW-04 | Peminjam dapat membatalkan request PENDING miliknya (status CANCELLED). | Must |
| FR-BRW-05 | My Borrowing memiliki tab Active (BORROWED + OVERDUE), Pending, dan History (RETURNED/REJECTED/CANCELLED), dengan filter organisasi dan pencarian. | Must |
| FR-BRW-06 | Detail peminjaman menampilkan informasi sesuai status: Pending (info request, batalkan), Active (tanggal, sisa hari/keterlambatan, denda berjalan), Returned (hasil inspeksi, catatan, denda), Rejected (alasan penolakan). | Must |
| FR-BRW-07 | Admin/Staff melihat daftar peminjaman dengan tab Pending Request, Active Borrowing, dan History (filter status, rentang tanggal, peminjam, aset). | Must |
| FR-BRW-08 | Approve: dalam transaksi database dengan penguncian baris aset, aset wajib masih AVAILABLE; peminjaman menjadi BORROWED (`approved_by`, `approved_at`), aset menjadi BORROWED. Jika aset tidak tersedia, tampilkan "Aset sedang tidak tersedia" tanpa mengubah data. | Must |
| FR-BRW-09 | Reject memerlukan alasan penolakan; peminjaman menjadi REJECTED dan peminjam dinotifikasi. | Must |
| FR-BRW-10 | Proses return: Admin/Staff mengisi tanggal kembali (default hari ini), kondisi (Good / Minor Damage / Major Damage / Lost), catatan (wajib jika bukan Good), dan denda. | Must |
| FR-BRW-11 | Hasil return memperbarui status secara otomatis sesuai tabel BR-05 (status peminjaman, status aset, dan damage report). | Must |
| FR-BRW-12 | Jika tanggal kembali melewati estimasi, sistem menghitung denda otomatis (hari terlambat × `late_fine_per_day`) yang dapat diedit Admin/Staff sebelum konfirmasi. Denda hanya dicatat, tanpa fitur pembayaran. | Must |
| FR-BRW-13 | Scheduler menandai otomatis peminjaman BORROWED yang `due_date`-nya lewat menjadi OVERDUE dan mencatat `overdue_at`. Proses bersifat idempotent. | Must |
| FR-BRW-14 | Transisi status yang tidak sah ditolak (mis. approve peminjaman yang sudah REJECTED). | Must |
| FR-BRW-15 | Return tetap dapat diproses untuk peminjaman berstatus BORROWED maupun OVERDUE. | Must |

### 4.8 Damage Report dan Maintenance

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-DMG-01 | Anggota dapat melaporkan kerusakan kapan saja (tidak harus sedang meminjam) selama QR/aset valid dan ia anggota organisasinya. Input: deskripsi (wajib) dan foto (opsional, maks 2 MB). | Must |
| FR-DMG-02 | Admin/Staff dapat membuat damage report manual dari detail aset. | Must |
| FR-DMG-03 | Admin/Staff melihat daftar damage report dengan filter status, severity, aset, dan penanda sumber (anggota / hasil inspeksi return). | Must |
| FR-DMG-04 | Admin/Staff dapat menilai laporan (severity minor/major) dan menyimpan catatan. Laporan dari anggota awalnya "Belum dinilai". | Must |
| FR-DMG-05 | Keputusan "Lanjut Maintenance": Ya → aset MAINTENANCE dan laporan `in_maintenance`; ditolak dengan pesan jelas jika aset sedang BORROWED/OVERDUE (laporan tetap `open`). | Must |
| FR-DMG-06 | Aksi "Selesai Perbaikan": isi catatan penyelesaian, laporan `resolved`, aset kembali AVAILABLE. | Must |
| FR-DMG-07 | Keputusan Tidak lanjut: simpan catatan kerusakan, laporan `noted`, status aset tidak berubah. | Must |
| FR-DMG-08 | Aset berstatus MAINTENANCE atau LOST tidak dapat dipinjam (tombol dan validasi server konsisten). | Must |
| FR-DMG-09 | Setiap pembuatan laporan dan perubahan status maintenance tercatat di asset log. | Must |

### 4.9 Riwayat Aset

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-HIS-01 | Sistem mencatat asset log untuk peristiwa: created, updated, borrowed, returned, overdue, damage_reported, maintenance_started, maintenance_finished, marked_lost, marked_found. | Must |
| FR-HIS-02 | Detail aset menampilkan timeline log (terbaru dahulu) berisi pelaku, waktu, dan keterangan, dengan filter jenis event dan pagination. Dapat dilihat seluruh anggota. | Must |
| FR-HIS-03 | Asset log bersifat append-only: tidak dapat diubah atau dihapus melalui antarmuka. | Must |

### 4.10 Notifikasi

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-NTF-01 | Event pada BR-09 menghasilkan notifikasi melalui kanal in-app (database) dan email (queued). | Must |
| FR-NTF-02 | Aktor tidak menerima notifikasi atas aksinya sendiri. Notifikasi tidak boleh membocorkan data lintas organisasi. | Must |
| FR-NTF-03 | Header menampilkan ikon lonceng dengan jumlah belum dibaca dan dropdown 5 notifikasi terbaru, diperbarui berkala (polling). | Must |
| FR-NTF-04 | Halaman Notifications menampilkan daftar (filter semua/belum dibaca), tandai dibaca, dan tandai semua dibaca. | Must |
| FR-NTF-05 | Detail notifikasi menampilkan judul, isi, waktu, dan tautan ke resource terkait, serta menandainya sudah dibaca. | Must |
| FR-NTF-06 | Pop-up toast menampilkan umpan balik setiap aksi dan notifikasi baru yang masuk (sekali tampil per notifikasi). | Must |
| FR-NTF-07 | User dapat mengatur preferensi email per kategori (membership, borrowing, damage). Notifikasi in-app selalu aktif. | Must |
| FR-NTF-08 | Email transaksional (verifikasi, reset password, undangan) selalu terkirim terlepas dari preferensi. | Must |

### 4.11 Dashboard

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-DSH-01 | Home Member menampilkan ringkasan profil, widget peminjaman berjalan (maks. 5, dengan penanda jatuh tempo/OVERDUE), jumlah request Pending, dan pintasan Scan QR serta Organizations. | Must |
| FR-DSH-02 | Dashboard Admin/Staff menampilkan statistik real-time: Total, Available, Borrowed, Maintenance, Lost, serta jumlah Overdue dan Pending Request. | Must |
| FR-DSH-03 | Dashboard Admin/Staff menampilkan grafik Chart.js: komposisi status aset, peminjaman 6 bulan terakhir, dan 5 aset paling sering dipinjam. | Must |
| FR-DSH-04 | Dashboard Admin/Staff menampilkan daftar peminjaman jatuh tempo/overdue dan pending request terbaru. | Must |

### 4.12 Report

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-RPT-01 | Admin dapat mengekspor CSV: Daftar Aset, Riwayat Peminjaman (filter rentang tanggal dan status), dan Damage Reports (filter rentang tanggal). | Must |
| FR-RPT-02 | Ekspor hanya memuat data organisasi terkait, memakai header berbahasa Indonesia dan encoding UTF-8 dengan BOM agar terbuka benar di Excel. | Must |
| FR-RPT-03 | Fitur Report tidak dapat diakses Staff (nonaktif di UI dan 403 di server). | Must |

### 4.13 Profile dan Settings Akun

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-PRF-01 | Personal Information: ubah nama (dan avatar opsional); data tidak valid menampilkan "Data tidak valid". | Must |
| FR-PRF-02 | Ubah email: validasi format dan keunikan ("Email tidak valid" jika gagal); email baru disimpan sebagai `pending_email` dan link verifikasi bertanda tangan dikirim ke email baru. Email lama tetap berlaku sampai verifikasi berhasil; kegagalan menampilkan "Verifikasi gagal". | Must |
| FR-PRF-03 | Ubah password: input password lama dan baru; jika gagal tampilkan "Perubahan gagal" atau "Password tidak valid". | Must |
| FR-PRF-04 | Preferensi notifikasi (lihat FR-NTF-07). | Must |
| FR-PRF-05 | Hapus akun: dialog konfirmasi; batal kembali ke profil; lanjut meminta password (salah → "Password salah" dan input ulang). Ditolak jika user adalah satu-satunya Admin pada suatu organisasi. Hapus = soft delete, keanggotaan dihapus, request PENDING dibatalkan, riwayat peminjaman tetap utuh, lalu logout. | Must |
| FR-PRF-06 | Logout tersedia dari menu Profile/Settings. | Must |

---

## 5. Use Case

### 5.1 Daftar Aktor dan Use Case

| ID | Use Case | Aktor Utama |
| --- | --- | --- |
| UC-01 | Registrasi dan verifikasi email | Guest |
| UC-02 | Login dan reset password | Guest |
| UC-03 | Menjelajahi organisasi | Guest, Member |
| UC-04 | Membuat organisasi | User |
| UC-05 | Bergabung ke organisasi (join request / undangan) | User, Admin |
| UC-06 | Mengelola anggota dan role | Admin |
| UC-07 | Mengelola aset dan QR Code | Admin, Staff |
| UC-08 | Memindai QR Code | Member, Staff, Admin |
| UC-09 | Mengajukan peminjaman | Member |
| UC-10 | Memproses permintaan peminjaman | Admin, Staff |
| UC-11 | Memproses pengembalian dan inspeksi | Admin, Staff |
| UC-12 | Melaporkan dan menangani kerusakan | Member, Admin, Staff |
| UC-13 | Memantau notifikasi | Semua user |
| UC-14 | Melihat dashboard dan mengekspor laporan | Member, Admin, Staff |
| UC-15 | Mengelola profil akun | User |
| UC-16 | Menandai OVERDUE otomatis | Sistem |

### 5.2 Spesifikasi Use Case

| Use Case | Prasyarat | Alur Utama | Alternatif dan Pengecualian | Pascakondisi |
| --- | --- | --- | --- | --- |
| **UC-01** Registrasi | Belum memiliki akun | 1) Isi nama, email, password; 2) sistem validasi; 3) akun dibuat, email verifikasi dikirim; 4) diarahkan ke Login; 5) klik link verifikasi | Email sudah terdaftar → "Email sudah terdaftar"; link kedaluwarsa → kirim ulang | Akun terverifikasi |
| **UC-02** Login | Akun terverifikasi | 1) Isi email dan password; 2) sistem memvalidasi; 3) masuk ke dashboard | Salah → "Email atau password salah"; belum verifikasi → halaman verifikasi; lupa password → input email → link reset → password baru → Login | Sesi aktif |
| **UC-03** Explore | Tidak ada | 1) Buka Explore; 2) cari/filter; 3) buka profil publik; 4) kembali | Tidak ada hasil → empty state; Ajukan Bergabung oleh guest → login | Tidak ada perubahan data |
| **UC-04** Buat organisasi | Login | 1) Isi data organisasi; 2) submit; 3) organisasi dibuat, pembuat menjadi Admin; 4) kembali ke Organisasi Saya | Validasi gagal → "Gagal membuat organisasi" | Organisasi baru dengan 1 Admin |
| **UC-05** Bergabung | Login, belum anggota | A) Join request: ajukan → Admin approve → user menjadi member. B) Undangan: Admin kirim email → user klik link → terima | Request ditolak/dibatalkan; undangan kedaluwarsa atau email berbeda | User menjadi anggota |
| **UC-06** Kelola anggota | Role Admin | 1) Buka Members; 2) proses join request / undang / ubah role / keluarkan | Admin terakhir tidak boleh dihapus/demote; anggota dengan peminjaman aktif tidak boleh dikeluarkan | Keanggotaan dan role terbarui |
| **UC-07** Kelola aset dan QR | Admin/Staff, organisasi aktif | 1) Tambah/edit aset; 2) simpan; 3) generate QR; 4) download atau print | Validasi gagal → error form; hapus aset BORROWED ditolak | Aset tersimpan, QR tersedia, log tercatat |
| **UC-08** Scan QR | Login | 1) Buka Scan; 2) arahkan kamera; 3) server memvalidasi; 4) tampil detail aset | QR invalid → "QR / Aset Invalid"; bukan anggota → diminta bergabung; kamera ditolak → input manual | Detail aset ditampilkan |
| **UC-09** Ajukan peminjaman | Anggota, aset AVAILABLE | 1) Buka detail aset; 2) isi tanggal dan alasan; 3) submit; 4) status PENDING | Validasi gagal (tanggal/durasi); aset tidak tersedia; duplikat request | Request PENDING, Admin/Staff dinotifikasi |
| **UC-10** Proses request | Admin/Staff, ada request PENDING | 1) Buka tab Pending; 2) pilih request; 3) Approve atau Reject | Aset sudah tidak AVAILABLE → ditolak; Reject wajib alasan | BORROWED + aset BORROWED, atau REJECTED; peminjam dinotifikasi |
| **UC-11** Proses return | Admin/Staff, peminjaman BORROWED/OVERDUE | 1) Buka tab Active; 2) isi inspeksi (kondisi, catatan, denda); 3) konfirmasi | Terlambat → denda otomatis (dapat diedit); kondisi Minor/Major → damage report; Lost → aset LOST | Peminjaman RETURNED; status aset sesuai BR-05 |
| **UC-12** Kerusakan | Anggota (lapor); Admin/Staff (tindak lanjut) | 1) Anggota lapor dari detail aset; 2) Admin/Staff menilai; 3) Lanjut maintenance atau catat saja; 4) Selesai perbaikan | Maintenance ditolak jika aset sedang dipinjam | Aset AVAILABLE kembali, atau tetap dengan catatan |
| **UC-13** Notifikasi | Login | 1) Lonceng menampilkan jumlah belum dibaca; 2) buka daftar; 3) buka detail | Resource sudah dihapus → pesan jelas | Notifikasi ditandai dibaca |
| **UC-14** Dashboard & Report | Login (Report: Admin) | 1) Buka dashboard; 2) lihat statistik dan grafik; 3) Admin memilih export CSV | Staff mengakses Report → 403 | File CSV terunduh |
| **UC-15** Profil | Login | 1) Ubah nama / email / password / preferensi; 2) atau hapus akun | Verifikasi email baru gagal; password lama salah; sole admin tidak boleh hapus akun | Data akun terbarui atau akun dihapus |
| **UC-16** OVERDUE otomatis | Peminjaman BORROWED lewat `due_date` | 1) Scheduler berjalan; 2) status menjadi OVERDUE; 3) log dan notifikasi dibuat | Dijalankan ulang → tidak ada duplikasi | Peminjaman OVERDUE |

---

## 6. Aturan Bisnis

### 6.1 Status Aset (BR-01)

| Dari | Ke | Pemicu |
| --- | --- | --- |
| AVAILABLE | BORROWED | Peminjaman di-approve |
| BORROWED | AVAILABLE | Return dengan kondisi Good atau Minor Damage |
| BORROWED | MAINTENANCE | Return dengan kondisi Major Damage |
| BORROWED | LOST | Return dengan kondisi Lost |
| AVAILABLE | MAINTENANCE | Damage report dilanjutkan ke maintenance (tidak boleh jika aset sedang BORROWED) |
| MAINTENANCE | AVAILABLE | Perbaikan selesai |
| LOST | AVAILABLE | Aksi "Tandai Ditemukan" |

Siklus standar pada PRD (AVAILABLE → BORROWED → RETURNED → AVAILABLE) diimplementasikan dengan `RETURNED` sebagai status **peminjaman**; aset langsung kembali AVAILABLE setelah return diproses. Aset hanya dapat dipinjam jika berstatus AVAILABLE.

### 6.2 Status Peminjaman (BR-02)

| Dari | Ke | Pemicu |
| --- | --- | --- |
| (baru) | PENDING | Anggota mengajukan peminjaman |
| PENDING | BORROWED | Admin/Staff approve |
| PENDING | REJECTED | Admin/Staff reject (alasan wajib) |
| PENDING | CANCELLED | Peminjam membatalkan, atau anggota dikeluarkan |
| BORROWED | OVERDUE | Scheduler: `due_date` < hari ini |
| BORROWED / OVERDUE | RETURNED | Proses return dan inspeksi |

"Active Borrowing" didefinisikan sebagai status BORROWED + OVERDUE. Beberapa user boleh PENDING untuk aset yang sama; saat approve aset dikunci dan harus masih AVAILABLE, request PENDING lain tetap PENDING sampai ditangani.

### 6.3 Aturan Peminjaman (BR-03, BR-04)

| ID | Aturan |
| --- | --- |
| BR-03 | Peminjam harus anggota organisasi; organisasi tidak diarsipkan; aset AVAILABLE; tanggal pinjam ≥ hari ini; tanggal kembali > tanggal pinjam; durasi ≤ `max_borrow_days`; alasan wajib. |
| BR-04 | Denda = hari terlambat × `late_fine_per_day`, dihitung saat return, dapat diedit Admin/Staff, hanya dicatat (tanpa pembayaran). |

### 6.4 Hasil Inspeksi Return (BR-05)

| Kondisi | Status Peminjaman | Status Aset | Damage Report |
| --- | --- | --- | --- |
| Good | RETURNED | AVAILABLE | Tidak ada |
| Minor Damage | RETURNED | AVAILABLE (tetap) | Dibuat: severity minor, status `noted` |
| Major Damage | RETURNED | MAINTENANCE | Dibuat: severity major, status `in_maintenance` |
| Lost | RETURNED | LOST | Tidak ada |

### 6.5 Aturan Keanggotaan dan Organisasi (BR-06)

| ID | Aturan |
| --- | --- |
| BR-06a | Role bersifat per organisasi; pembuat organisasi otomatis Admin; Admin boleh lebih dari satu. |
| BR-06b | Organisasi tidak boleh tersisa tanpa Admin. |
| BR-06c | Anggota dengan peminjaman aktif tidak boleh dikeluarkan; request PENDING-nya otomatis CANCELLED. |
| BR-06d | Undangan berlaku 7 hari, token sekali pakai, hanya dapat diterima oleh email yang diundang. |
| BR-06e | Organisasi terarsip bersifat read-only dan tidak tampil di Explore. Penghapusan organisasi hanya jika tidak ada peminjaman aktif. |

### 6.6 Aturan Aset dan QR (BR-07, BR-08)

| ID | Aturan |
| --- | --- |
| BR-07 | Aset tidak boleh dihapus jika BORROWED/memiliki peminjaman aktif; kategori yang dipakai aset tidak boleh dihapus; kode aset (`code`) tidak dapat diubah. |
| BR-08 | QR berisi URL ke detail aset. Pemindai non-anggota tidak melihat detail aset dan diminta bergabung dahulu. Pemindai belum login diarahkan login lalu kembali ke URL tujuan. |

### 6.7 Katalog Notifikasi (BR-09)

| Event | Penerima |
| --- | --- |
| Join request diajukan | Admin organisasi |
| Join request di-approve/reject | User pengaju |
| Undangan dikirim | Email target (+ in-app jika user sudah terdaftar) |
| Undangan diterima | Admin pengundang |
| Request peminjaman diajukan | Admin dan Staff organisasi |
| Peminjaman di-approve/reject | Peminjam |
| Peminjaman OVERDUE | Peminjam, Admin, dan Staff |
| Return diproses | Peminjam (berisi kondisi dan denda) |
| Damage report dibuat | Admin dan Staff organisasi |
| Role diubah / dikeluarkan | User terkait |

### 6.8 Matriks Hak Akses (BR-10)

| Aksi | Member | Staff | Admin |
| --- | --- | --- | --- |
| Explore organisasi, lihat profil publik | Ya | Ya | Ya |
| Ajukan join (jika belum anggota) | Ya | — | — |
| Lihat katalog aset organisasinya | Ya | Ya | Ya |
| Ajukan peminjaman | Ya | Ya | Ya |
| Scan QR, lapor kerusakan | Ya | Ya | Ya |
| CRUD aset dan kategori, generate/download QR | Tidak | Ya | Ya |
| Approve/Reject peminjaman | Tidak | Ya | Ya |
| Proses return dan inspeksi | Tidak | Ya | Ya |
| Kelola damage report dan maintenance | Tidak | Ya | Ya |
| Dashboard statistik organisasi | Tidak | Ya | Ya |
| Kelola anggota (join request, undang, role, keluarkan) | Tidak | Tidak | Ya |
| Report / export | Tidak | Tidak | Ya |
| Settings organisasi (profil, durasi dan denda, arsip/hapus) | Tidak | Tidak | Ya |

---

## 7. Kebutuhan Data

### 7.1 Diagram Relasi Entitas (ERD)

![ERD Organization Inventory](erd_organization_inventory.png)

*Catatan: tabel bawaan framework (notifications, jobs, failed_jobs, cache, sessions, password_reset_tokens) tidak digambarkan.*

### 7.2 Kamus Data Ringkas

| Entitas | Atribut Utama | Keterangan |
| --- | --- | --- |
| `users` | name, email (unik), email_verified_at, password, pending_email, notification_preferences, avatar | Soft delete |
| `organizations` | name, slug (unik), description, category, logo, max_borrow_days, late_fine_per_day, archived_at, created_by | Soft delete; default durasi 7 hari, denda 0 |
| `organization_user` | organization_id, user_id, role (admin/staff/member), joined_at | Unik (organization_id, user_id); sumber role per organisasi |
| `membership_requests` | organization_id, user_id, message, status (pending/approved/rejected/cancelled), reviewed_by, reviewed_at | Join request |
| `organization_invitations` | organization_id, email, role (staff/member), token (unik), invited_by, expires_at, accepted_at, status (pending/accepted/expired/revoked) | Berlaku 7 hari |
| `asset_categories` | organization_id, name | Nama unik per organisasi |
| `assets` | organization_id, asset_category_id, code (ULID unik), name, description, specifications, location, purchase_date, photo, status (available/borrowed/maintenance/lost) | Soft delete; `code` dipakai di URL/QR |
| `borrowings` | organization_id, asset_id, user_id, borrow_date, due_date, reason, status (pending/rejected/cancelled/borrowed/overdue/returned), approved_by, approved_at, rejection_reason, overdue_at, returned_at, processed_by, return_condition (good/minor_damage/major_damage/lost), return_notes, fine_amount | Transaksi peminjaman |
| `damage_reports` | organization_id, asset_id, borrowing_id (nullable), reported_by, severity (nullable: minor/major), description, photo, status (open/in_maintenance/resolved/noted), handled_by, resolution_notes, resolved_at | Dari anggota, staff, atau hasil inspeksi |
| `asset_logs` | organization_id, asset_id, user_id (nullable), event, description, metadata (json), created_at | Append-only |

### 7.3 Relasi Utama

| Relasi | Kardinalitas |
| --- | --- |
| users — organizations (melalui `organization_user`) | N : M, dengan atribut role |
| organizations — assets | 1 : N |
| organizations — asset_categories | 1 : N |
| asset_categories — assets | 1 : N |
| assets — borrowings | 1 : N |
| users — borrowings (peminjam) | 1 : N |
| assets — damage_reports | 1 : N |
| borrowings — damage_reports | 1 : 0..N |
| assets — asset_logs | 1 : N |
| organizations — membership_requests / organization_invitations | 1 : N |

### 7.4 Retensi dan Integritas Data

| ID | Kebutuhan |
| --- | --- |
| DAT-01 | Users, organizations, dan assets memakai soft delete agar riwayat transaksi tetap utuh. |
| DAT-02 | Setiap perubahan status aset menghasilkan catatan di `asset_logs` dalam transaksi database yang sama. |
| DAT-03 | Invarian: aset BORROWED memiliki tepat satu peminjaman aktif; aset AVAILABLE tidak memiliki peminjaman aktif; setiap organisasi memiliki minimal satu Admin. |
| DAT-04 | Index pada kolom yang sering difilter: organization_id, status, user_id, asset_id, due_date. |

---

## 8. Kebutuhan Non-Fungsional

> Angka target pada bagian ini adalah **usulan** dan dapat disesuaikan pemilik produk.

### 8.1 Performa

| ID | Kebutuhan |
| --- | --- |
| NFR-PERF-01 | Halaman daftar (aset, peminjaman, anggota) memakai pagination dan merespons ≤ 2 detik (p95) untuk organisasi hingga 5.000 aset pada koneksi normal. |
| NFR-PERF-02 | Tidak ada query N+1 pada halaman daftar, dashboard, dan report; statistik dashboard memakai query agregat. |
| NFR-PERF-03 | Pengiriman email dan pemrosesan notifikasi dilakukan asynchronous melalui queue agar tidak memblokir request pengguna. |
| NFR-PERF-04 | Hasil scan QR divalidasi dan diarahkan ke detail aset dalam ≤ 2 detik setelah QR terbaca. |

### 8.2 Keamanan

| ID | Kebutuhan |
| --- | --- |
| NFR-SEC-01 | Password disimpan dengan hashing bawaan Laravel (bcrypt/argon). |
| NFR-SEC-02 | Perlindungan CSRF, escaping output (XSS), dan query terparameter via Eloquent. |
| NFR-SEC-03 | Otorisasi memakai Policy/Gate di server untuk setiap route dan aksi; akses lintas organisasi ditolak (403/404). |
| NFR-SEC-04 | Rate limiting pada login, lupa password, kirim ulang verifikasi, join request, dan endpoint scan. |
| NFR-SEC-05 | Upload hanya jpg/jpeg/png/webp maks 2 MB dengan validasi mime dan nama file aman. |
| NFR-SEC-06 | Token undangan acak, cukup panjang, dan sekali pakai; link verifikasi email baru memakai signed URL. |
| NFR-SEC-07 | Produksi wajib HTTPS (juga untuk akses kamera). |
| NFR-SEC-08 | Pesan "Email tidak ditemukan" pada lupa password mengikuti flowchart; risiko enumerasi email dimitigasi dengan rate limiting. |

### 8.3 Usability

| ID | Kebutuhan |
| --- | --- |
| NFR-USA-01 | Antarmuka responsif dan nyaman di layar HP (khususnya Scan QR, detail aset, form request, dan return). |
| NFR-USA-02 | Seluruh teks, pesan error, dan notifikasi berbahasa Indonesia dan konsisten. |
| NFR-USA-03 | Tersedia empty state dan loading state pada daftar/tabel, serta konfirmasi untuk aksi destruktif. |
| NFR-USA-04 | Halaman error 403, 404, 419, dan 500 berbahasa Indonesia. |

### 8.4 Keandalan dan Integritas

| ID | Kebutuhan |
| --- | --- |
| NFR-REL-01 | Perubahan status yang melibatkan beberapa tabel dijalankan dalam transaksi database; approve peminjaman memakai row lock untuk mencegah double-booking. |
| NFR-REL-02 | Penandaan OVERDUE idempotent dan aman dijalankan berulang. |
| NFR-REL-03 | Backup database berkala pada lingkungan produksi. |

### 8.5 Maintainability dan Kualitas Kode

| ID | Kebutuhan |
| --- | --- |
| NFR-MNT-01 | Logika bisnis ditempatkan di Action classes; komponen Livewire tipis; status/role memakai PHP Enum. |
| NFR-MNT-02 | Validasi memakai Form Request sebagai sumber tunggal aturan. |
| NFR-MNT-03 | Setiap aturan bisnis (BR-*) dan otorisasi memiliki automated test (Pest); kode dirapikan dengan Laravel Pint. |
| NFR-MNT-04 | Tersedia README berisi instalasi, konfigurasi (.env, queue, scheduler, storage link), akun demo, dan cara menjalankan test. |

### 8.6 Portabilitas dan Lokalisasi

| ID | Kebutuhan |
| --- | --- |
| NFR-PORT-01 | Mendukung dua versi terbaru Chrome, Edge, Firefox, dan Safari (termasuk mobile) dengan dukungan kamera untuk scan QR. |
| NFR-LOC-01 | Zona waktu `Asia/Jakarta`, mata uang Rupiah, format tanggal lokal Indonesia. |

---

## 9. Matriks Keterlacakan (Traceability)

| Butir PRD / Flowchart | Kebutuhan Terkait |
| --- | --- |
| PRD 4.A Landing Page, Explore, Register, Login, Forgot Password | FR-AUTH-01 s.d. 09, FR-EXP-01 s.d. 04 |
| PRD 4.B Home (profil + widget peminjaman) | FR-DSH-01 |
| PRD 4.B All Organization, Join, Request Borrow | FR-ORG-03, 05, FR-INV-08, FR-BRW-01 s.d. 04 |
| PRD 4.B My Borrowing | FR-BRW-05, 06 |
| PRD 4.B Settings (profil, password, notifikasi, logout) | FR-PRF-01 s.d. 06 |
| PRD 4.C Dashboard statistik | FR-DSH-02 s.d. 04 |
| PRD 4.C Inventory (CRUD, QR) | FR-INV-01 s.d. 07, FR-QR-01 s.d. 03, 10 |
| PRD 4.C Borrowing (Pending, Active, History) | FR-BRW-07 s.d. 15 |
| PRD 4.C Member (join, cabut, role) | FR-ORG-06 s.d. 11, FR-RBAC-01 s.d. 04 |
| PRD 4.C Report dan Settings | FR-RPT-01 s.d. 03, FR-ORG-12 s.d. 15 |
| PRD 3 Role dan UI grayed-out Staff | FR-RBAC-01 s.d. 03, BR-10 |
| PRD 5 Sistem QR Code (URL unik) | FR-QR-01, 10, BR-08 |
| PRD 5 Siklus dan status aset | BR-01, BR-02, BR-05 |
| PRD 6 Riwayat Aset | FR-HIS-01 s.d. 03 |
| PRD 6 Manajemen Inventory (kategori) | FR-INV-07 |
| PRD 6 Scan QR | FR-QR-04 s.d. 09 |
| PRD 7 Teknologi dan Tools | CON-01 s.d. 05 |
| Flowchart User: Create Organization | FR-ORG-01, 02 |
| Flowchart User: Notification | FR-NTF-01 s.d. 08 |
| Flowchart User: Scan QR, Laporkan Kerusakan | FR-QR-04 s.d. 09, FR-DMG-01 |
| Flowchart User: Profile (email OTP/link, hapus akun) | FR-PRF-02, 05 |
| Flowchart Org: Damage Reports, Maintenance | FR-DMG-02 s.d. 09 |
| Flowchart Org: Settings (durasi dan denda, arsip/hapus) | FR-ORG-13 s.d. 15 |
| Flowchart Org: Undangan member (Kirim Undangan, Invitation Expired) | FR-ORG-07, 08 |

---

## 10. Lampiran

### 10.1 Daftar Route

| Area | Route |
| --- | --- |
| Publik | `/`, `/explore`, `/explore/{slug}`, `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/invitations/{token}` |
| User | `/dashboard`, `/organizations`, `/organizations/create`, `/o/{slug}`, `/o/{slug}/assets/{code}`, `/my-borrowings`, `/notifications`, `/scan`, `/profile` |
| Manajemen (Admin/Staff) | `/o/{slug}/manage/dashboard`, `/inventory`, `/categories`, `/borrowings`, `/members`, `/damage-reports`, `/reports`, `/settings` |

### 10.2 Teknologi

| Komponen | Teknologi |
| --- | --- |
| Backend | Laravel (versi stabil terbaru, minimal 12), PHP, Eloquent ORM |
| Database | MySQL 8 (alternatif PostgreSQL) |
| Frontend | Blade, Livewire, Tailwind CSS, Flux UI |
| QR Code | Simple QrCode (backend), html5-qrcode (scanner) |
| Grafik | Chart.js |
| Storage dan Validasi | Laravel Storage, Laravel Form Request |
| Testing dan Kualitas | Pest, Laravel Pint |
| Infrastruktur | Queue (database), Scheduler (cron), SMTP |

### 10.3 Keputusan Desain (Penyelesaian Perbedaan PRD vs Flowchart)

| No | Topik | Keputusan |
| --- | --- | --- |
| 1 | Menu Member | Digabung: Home, Organizations (Semua + Saya), My Borrowing, Notifications, Scan QR, Profile/Settings. |
| 2 | Pembuatan organisasi | Semua user boleh membuat organisasi dan otomatis menjadi Admin. |
| 3 | Cara masuk organisasi | Join request (disetujui Admin) dan undangan email (diundang Admin). |
| 4 | Menu Admin | Semua masuk MVP: Damage Reports, Settings, dan Report (export CSV). |
| 5 | Denda | Hanya angka (denda per hari); tanpa fitur pembayaran. |
| 6 | Minor Damage | Aset tetap AVAILABLE; damage report dicatat. |
| 7 | OVERDUE | Ditandai otomatis oleh scheduler. |
| 8 | Aset dan QR | Satu aset = satu QR; tanpa stok. |
| 9 | Laporan kerusakan | Member dapat melapor kapan saja selama QR/aset valid dan ia anggota. |
| 10 | Scan oleh non-anggota | Harus bergabung dahulu; detail aset tidak ditampilkan. |
| 11 | Role | Disimpan per organisasi. |
| 12 | Notifikasi | In-app, email, dan pop-up toast. |
| 13 | Registrasi | Wajib verifikasi email. |
| 14 | Stack | Laravel versi stabil terbaru, MySQL, UI Indonesia. |

### 10.4 Asumsi Tambahan dan Risiko

| ID | Item | Keterangan |
| --- | --- | --- |
| AST-01 | Approve langsung BORROWED | Flowchart "Set Approved → Set Borrowed" disederhanakan menjadi satu langkah. |
| AST-02 | Denda otomatis | Dihitung otomatis saat return dan dapat diedit; tidak ada pembayaran. |
| AST-03 | Perubahan email | Memakai link verifikasi (bukan OTP). |
| AST-04 | Kategori organisasi | Daftar tetap di konfigurasi: Kampus, Sekolah, Komunitas, Perusahaan, Lainnya. |
| AST-05 | Report | Hanya CSV; PDF/Excel menyusul. |
| RSK-01 | Ekstensi imagick | Tanpanya, QR PNG tidak tersedia (SVG tetap ada). |
| RSK-02 | HTTPS | Tanpa HTTPS, scan kamera tidak berfungsi di produksi. |
| RSK-03 | Enumerasi email | Pesan "Email tidak ditemukan" memungkinkan penebakan email; dimitigasi rate limiting. |
| RSK-04 | Polling notifikasi | Bukan real-time penuh; ada jeda hingga interval polling. |

### 10.5 Riwayat Revisi

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 3 Oktober 2026 | Draf awal berdasarkan PRD, flowchart, dan klarifikasi pemilik produk |
