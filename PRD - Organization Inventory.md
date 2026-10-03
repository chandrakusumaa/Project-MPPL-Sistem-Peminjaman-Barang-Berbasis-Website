PRODUCT REQUIREMENTS DOCUMENT (PRD)
Organization Inventory (Sistem Pengelolaan Barang)
1. Informasi Umum
2. Problem Statement & Tujuan
### Problem Statement
Pengelolaan barang pada organisasi saat ini masih sering menggunakan pencatatan manual atau spreadsheet. Pendekatan ini memicu berbagai masalah operasional:
Risikonya tinggi terjadi kehilangan pelacakan aset (asset untracking).
Riwayat peminjaman dan pengembalian barang sulit ditelusuri.
Informasi aset tersebar dan tidak terintegrasi secara terpusat.
### Primary Goals
Memusatkan seluruh data aset organisasi dalam satu platform terpadu.
Mempermudah alur pengajuan peminjaman dan pemrosesan pengembalian barang.
Menyediakan sistem pelacakan dan riwayat penggunaan berbasis QR Code.
Membantu pemantauan kondisi fisik dan status operasional aset secara real-time.
Mendukung arsitektur multi-organisasi, memungkinkan pengguna mencari, menjelajahi, dan bergabung dengan organisasi yang relevan.
### Secondary Goals
Mengurangi ketergantungan pada pencatatan berbasis kertas atau spreadsheet manual.
Meningkatkan akuntabilitas dan tanggung jawab para anggota organisasi.
Menyediakan data analitik dan statistik penggunaan aset bagi pemangku kepentingan.
3. User Roles & Hak Akses
4. User Flows
A. Flow Landing Page & Autentikasi
Pengguna mengakses Landing Page awal dan disajikan tiga jalur navigasi utama: Explore Organization, Login, atau Register.
Explore Organization:
Pengguna dapat mencari dan menyaring (search/filter) organisasi berdasarkan nama atau kategori.
Pengguna dapat membuka profil publik organisasi dan kembali ke halaman utama kapan saja.
Register:
Pengguna mengisikan informasi dasar: Nama, Email, dan Password.
Sistem melakukan validasi email. Jika belum terdaftar, akun dibuat dan pengguna diarahkan langsung ke halaman Login.
Login & Forgot Password:
Authenticated access menggunakan kredensial Email dan Password.
Jika pengguna lupa password, tersedia alur pengiriman tautan reset password via email terdaftar.
Flowchart :
B. Flow Dashboard User (Member)
Home: Menampilkan ringkasan profil pengguna serta widget peminjaman barang yang sedang berjalan.
All Organization:
Pengguna dapat mencari dan menelusuri katalog organisasi.
Belum Bergabung: Pengguna dapat mengirim permintaan Join dan menunggu konfirmasi dari Admin organisasi.
Sudah Bergabung: Pengguna dapat mengakses daftar aset, mencari/menyaring barang, dan mengisi formulir Request Borrow (input tanggal peminjaman, estimasi pengembalian, dan alasan). Status pengajuan akan bernilai Pending sampai disetujui.
My Borrowing: Pusat pemantauan pribadi yang memuat tab Active Borrowing, Pending Request, dan History Borrowing.
Settings: Pengaturan akun pengguna mencakup pembaruan profil, perubahan password, preferensi notifikasi, dan opsi Logout.
Flowchart :
C. Flow Dashboard Admin / Staff
Home (Dashboard): Menampilkan panel analitik dan statistik aset secara real-time (Total, Available, Borrowed, Maintenance, dan Lost).
Inventory: Modul manajemen aset lengkap (CRUD), fitur pembuatan aset baru lengkap dengan generate QR Code unik, serta penyesuaian detail spesifikasi.
Borrowing:
Pending Request: Meninjau dan menentukan tindakan Approve atau Reject atas permohonan peminjaman.
Active Borrowing (Pengembalian): Memproses pengembalian aset dan mencatat hasil inspeksi kondisi (Good, Minor Damage, Major Damage, Lost), yang secara otomatis memperbarui status sistem.
History: Catatan riwayat seluruh transaksi peminjaman.
Member (Pengelolaan Anggota): Memproses permintaan join, mencabut keanggotaan, dan menetapkan Role pengguna. (Tampilan fitur ini diabu-abukan/disabled untuk role Staff).
Report & Settings: Ekspor data laporan operasional dan penyesuaian profil organisasi. (Tampilan fitur ini diabu-abukan/disabled untuk role Staff).
Flowchart :
### 5. Aturan Sistem Tambahan
Sistem QR Code
QR Code yang dihasilkan tidak memuat data mentah aset secara langsung (hardcoded data), melainkan menyimpan URL tautan unik menuju detail aset.
Dengan metode ini, perubahan informasi seperti nama aset, spesifikasi, lokasi penyimpanan, atau kondisi fisik tidak memerlukan pencetakan ulang QR Code.
Siklus & Status Aset
Status aset bergerak dalam siklus standar:
AVAILABLE $\rightarrow$ BORROWED $\rightarrow$ RETURNED $\rightarrow$ AVAILABLE
Pada tahap pengembalian (inspection phase), status aset dapat dialihkan keluar dari siklus normal apabila ditemukan kerusakan atau kehilangan:
Kerusakan berat $\rightarrow$ MAINTENANCE
Tidak ditemukan / hilang $\rightarrow$ LOST
### 6. MVP Scope (Diperbarui)
Must Have (Fase 1)
Autentikasi: Login, Register, dan Reset Password.
Otorisasi & Role: Manajemen hak akses Admin, Staff, dan Member (termasuk penerapan UI grayed-out untuk batasan akses Staff).
Multi-Organisasi: Modul Explore dan alur pengajuan Join Organization.
Manajemen Inventory: CRUD Aset dan Pengelolaan Kategori Barang.
Sistem QR Code: Penjanaan (generation) dan pemindaian (scanning) QR Code aset.
Sistem Peminjaman & Pengembalian: Form permohonan peminjaman, modul persetujuan, dan pencatatan inspeksi pengembalian.
Riwayat Aset: Tracking log perjalanan dan penggunaan setiap barang.
Dashboard Utama: Ringkasan data untuk antarmuka Anggota maupun Admin/Staff.
### 7. Teknologi & Tools

| Parameter | Detail |
| --- | --- |
| Nama Produk | Organization Inventory (Sistem Pengelolaan Barang) |
| Status Dokumen | Draft (Update berdasarkan integrasi Flowchart terbaru) |
| Target Eksekusi | Phase 1 (MVP) |

| Role | Cakupan Hak Akses & Privilese |
| --- | --- |
| Admin | Access penuh pada seluruh fitur dashboard. Berwenang mengelola inventory, anggota, persetujuan peminjaman, pemrosesan pengembalian, pemeliharaan (maintenance), sistem laporan, serta konfigurasi tingkat organisasi. |
| Staff | Menggunakan antarmuka dashboard yang sama dengan Admin, tetapi dengan batasan izin operasional. Fitur tanpa izin (seperti manajemen anggota, konfigurasi organisasi, dan laporan sensitif) ditampilkan dinonaktifkan (grayed out). Berwenang mengelola aset, memproses transaksi peminjaman/pengembalian, dan inspeksi kondisi. |
| Member | Akses terbatas melalui user interface publik/anggota. Berwenang menjelajahi daftar organisasi, mengajukan permohonan bergabung, melihat katalog aset, memindai QR Code, dan mengajukan draf peminjaman aset. |

| Komponen | Teknologi / Library yang Digunakan |
| --- | --- |
| Backend Framework | Laravel (PHP) |
| ORM | Eloquent ORM |
| Database | PostgreSQL / MySQL |
| Frontend Framework | Blade, Livewire |
| Styling & UI Kit | Tailwind CSS, Flux UI |
| QR Code Engine | Simple QrCode (Backend), html5-qrcode (Frontend Scanner) |
| Analitik & Grafik | Chart.js |
| Storage & Validasi | Laravel Storage, Laravel Form Request Validation |
