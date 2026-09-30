# AGENTS.md — Organization Inventory

## 1. Peran & Aturan Kerja
Kamu adalah senior Laravel engineer yang membangun aplikasi "Organization Inventory" dari nol.

Aturan kerja WAJIB:
1. Baca seluruh file ini sebelum mengerjakan tugas apa pun.
2. Kerjakan HANYA fase yang diminta. Jangan membuat fitur milik fase lain.
3. Sebelum coding, tulis rencana singkat (daftar file yang akan dibuat/diubah), lalu kerjakan.
4. Jika ada ambiguitas, konflik aturan, atau alur bisnis yang tidak tertulis, BERHENTI dan tanyakan. Jangan menebak alur bisnis.
5. Jangan mengubah keputusan di dokumen ini tanpa persetujuan eksplisit.
6. Logika bisnis diletakkan di Action classes (`app/Actions/...`). Komponen Livewire dibuat tipis (validasi, panggil Action, tampilkan hasil).
7. Setiap perubahan status memakai DB transaction dan mencatat `asset_logs` bila menyangkut aset.
8. Tulis test (Pest) untuk aturan bisnis dan otorisasi di setiap fase. Jalankan test dan Laravel Pint sebelum melapor.
9. Laporan akhir tiap fase berisi: apa yang dibuat, cara tes manual, asumsi yang diambil, dan hal yang belum selesai.

## 2. Ringkasan Produk
Aplikasi web multi-organisasi untuk mengelola aset, peminjaman, pengembalian, dan riwayat penggunaan berbasis QR Code.
Masalah yang diselesaikan: pencatatan manual/spreadsheet, aset sulit dilacak, riwayat pinjam sulit ditelusuri, data tersebar.
Target: MVP (Fase 1).

## 3. Tech Stack & Konvensi
- Laravel (versi stabil terbaru, minimal 12) memakai official Livewire starter kit (Livewire + Flux UI + Tailwind CSS). Cek dokumentasi resmi untuk perintah instalasi versi terbaru. Test framework: Pest.
- Database: MySQL 8. ORM: Eloquent. Semua tabel lewat migration, dilengkapi factory dan seeder.
- QR: `simplesoftwareio/simple-qrcode` (backend; SVG sebagai default, PNG hanya jika ekstensi imagick tersedia) dan `html5-qrcode` (scanner frontend, install via npm).
- Grafik: Chart.js via npm (bundle Vite, tanpa CDN).
- File upload: Laravel Storage disk `public`, jalankan `storage:link`. Gambar maks 2 MB, mimes jpg/jpeg/png/webp.
- Validasi: Laravel Form Request sebagai sumber aturan validasi. Livewire memakai ulang aturan itu (`(new XRequest)->rules()` dan `messages()`), jangan menulis aturan ganda.
- Queue: driver `database`. Mail dev: driver `log`. Scheduler dipakai untuk OVERDUE.
- Locale `id`, timezone `Asia/Jakarta`. Teks UI bahasa Indonesia. Nama class, tabel, kolom, dan kode dalam bahasa Inggris.
- Gunakan PHP backed Enums untuk semua status/role/kondisi.
- Struktur folder: `app/Actions/{Domain}`, `app/Enums`, `app/Policies`, `app/Http/Requests`, `app/Livewire/{Area}`, `app/Notifications`, `app/Events`, `app/Listeners`, `app/Console/Commands`.
- Soft deletes untuk: users, organizations, assets.
- Aktifkan `Model::preventLazyLoading()` di environment non-production.

## 4. Role & Hak Akses (Role PER ORGANISASI)
Satu user bisa menjadi Admin di organisasi A dan Member di organisasi B. Role disimpan di tabel pivot `organization_user`, BUKAN di tabel users.
Pembuat organisasi otomatis Admin. Satu organisasi boleh punya banyak Admin, tetapi tidak boleh tersisa 0 Admin.

| Aksi | Member | Staff | Admin |
|---|---|---|---|
| Explore organisasi, lihat profil publik | Ya | Ya | Ya |
| Ajukan join (jika belum anggota) | Ya | - | - |
| Lihat katalog aset organisasinya | Ya | Ya | Ya |
| Ajukan peminjaman | Ya | Ya | Ya |
| Scan QR, lapor kerusakan | Ya | Ya | Ya |
| CRUD aset dan kategori, generate/download QR | Tidak | Ya | Ya |
| Approve/Reject peminjaman | Tidak | Ya | Ya |
| Proses return dan inspeksi | Tidak | Ya | Ya |
| Kelola damage report dan maintenance | Tidak | Ya | Ya |
| Dashboard statistik organisasi | Tidak | Ya | Ya |
| Kelola anggota (join request, undang, role, remove) | Tidak | Tidak | Ya |
| Report / export | Tidak | Tidak | Ya |
| Settings organisasi (profil, durasi & denda, archive/delete) | Tidak | Tidak | Ya |

Staff memakai UI dashboard yang sama dengan Admin, tetapi menu Members, Reports, dan Settings TAMPIL NONAKTIF (grayed out, tooltip "Hanya untuk Admin"). Otorisasi TETAP ditegakkan di server (Policy/Gate, respons 403), bukan hanya disembunyikan di UI.

## 5. Struktur Route
Publik (guest):
- `/` Landing (3 jalur: Explore Organization, Login, Register)
- `/explore`, `/explore/{organization:slug}` (profil publik)
- `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`
- `/invitations/{token}` (terima undangan)

Area user (auth + email terverifikasi):
- `/dashboard` (Home Member)
- `/organizations` (tab: Semua Organisasi, Organisasi Saya), `/organizations/create`
- `/o/{organization:slug}` (katalog aset untuk anggota)
- `/o/{organization:slug}/assets/{asset:code}` (detail aset; URL ini yang disimpan di QR)
- `/my-borrowings` (tab: Active, Pending, History) dan detailnya
- `/notifications` dan detailnya
- `/scan`
- `/profile` (Personal Info, Email, Password, Preferensi Notifikasi, Hapus Akun, Logout)

Area manajemen organisasi (role Admin/Staff, prefix `/o/{organization:slug}/manage`):
- `/dashboard`, `/inventory`, `/categories`, `/borrowings` (tab: Pending Request, Active Borrowing, History), `/members`, `/damage-reports`, `/reports`, `/settings`

Gunakan scoped route binding dan middleware `organization.member` dan `organization.role:admin,staff` agar user organisasi A tidak bisa mengakses data organisasi B (cegah IDOR).

## 6. Model Data
- `users`: id, name, email (unique), email_verified_at, password, pending_email (nullable), notification_preferences (json), avatar (nullable), remember_token, timestamps, soft deletes.
- `organizations`: id, name, slug (unique), description, category, logo (nullable), max_borrow_days (int, default 7), late_fine_per_day (unsigned int rupiah, default 0), archived_at (nullable), created_by, timestamps, soft deletes.
- `organization_user`: id, organization_id, user_id, role (admin/staff/member), joined_at; unique(organization_id, user_id).
- `membership_requests`: id, organization_id, user_id, message (nullable), status (pending/approved/rejected/cancelled), reviewed_by (nullable), reviewed_at (nullable), timestamps.
- `organization_invitations`: id, organization_id, email, role (staff/member), token (unique), invited_by, expires_at (default 7 hari), accepted_at (nullable), status (pending/accepted/expired/revoked), timestamps.
- `asset_categories`: id, organization_id, name; unique(organization_id, name).
- `assets`: id, organization_id, asset_category_id, code (ULID unik, dipakai di URL/QR), name, description, specifications (text), location, purchase_date (nullable), photo (nullable), status (available/borrowed/maintenance/lost), timestamps, soft deletes.
- `borrowings`: id, organization_id, asset_id, user_id (peminjam), borrow_date, due_date, reason, status (pending/rejected/cancelled/borrowed/overdue/returned), approved_by, approved_at, rejection_reason, overdue_at, returned_at, processed_by (yang memproses return), return_condition (good/minor_damage/major_damage/lost), return_notes, fine_amount (unsigned int, default 0), timestamps.
- `damage_reports`: id, organization_id, asset_id, borrowing_id (nullable), reported_by, severity (nullable: minor/major), description, photo (nullable), status (open/in_maintenance/resolved/noted), handled_by (nullable), resolution_notes (nullable), resolved_at (nullable), timestamps.
- `asset_logs`: id, organization_id, asset_id, user_id (nullable), event (created/updated/borrowed/returned/overdue/damage_reported/maintenance_started/maintenance_finished/marked_lost/marked_found), description, metadata (json), created_at.
- Tabel bawaan Laravel: notifications, jobs, failed_jobs, cache, sessions, password_reset_tokens.
- Tambahkan index pada kolom yang sering difilter: organization_id, status, user_id, asset_id, due_date.

## 7. State Machine & Aturan Bisnis
### Status Aset
- AVAILABLE -> BORROWED (saat peminjaman di-approve)
- BORROWED -> AVAILABLE (return kondisi Good atau Minor Damage)
- BORROWED -> MAINTENANCE (return kondisi Major Damage)
- BORROWED -> LOST (return kondisi Lost)
- AVAILABLE -> MAINTENANCE (damage report dilanjutkan ke maintenance; TIDAK boleh jika aset sedang BORROWED)
- MAINTENANCE -> AVAILABLE (perbaikan selesai)
- LOST -> AVAILABLE (aksi "Tandai Ditemukan" oleh Admin/Staff, tercatat di log)
- Aset hanya bisa dipinjam jika statusnya AVAILABLE.

### Status Peminjaman
- PENDING -> REJECTED (Admin/Staff, alasan wajib) | CANCELLED (dibatalkan peminjam) | BORROWED (di-approve)
- BORROWED -> OVERDUE (otomatis oleh scheduler jika `due_date` < hari ini dan belum dikembalikan)
- BORROWED atau OVERDUE -> RETURNED (proses return + inspeksi)
- "Active Borrowing" = status BORROWED + OVERDUE.
- Beberapa user boleh PENDING untuk aset yang sama. Saat approve, aset dikunci (row lock) dan wajib masih AVAILABLE. Jika tidak, tampilkan pesan "Aset sedang tidak tersedia". Request PENDING lain tetap PENDING sampai Admin/Staff mengurusnya.

### Hasil Inspeksi Return
| Kondisi | Status Peminjaman | Status Aset | Damage Report |
|---|---|---|---|
| Good | RETURNED | AVAILABLE | Tidak |
| Minor Damage | RETURNED | AVAILABLE (tetap) | Ya, severity minor, status `noted` |
| Major Damage | RETURNED | MAINTENANCE | Ya, severity major, status `in_maintenance` |
| Lost | RETURNED | LOST | Tidak |

### Denda (tanpa pembayaran)
`fine_amount = hari_terlambat x organizations.late_fine_per_day` dihitung saat return, bisa diedit Admin/Staff sebelum konfirmasi. Hanya dicatat dan ditampilkan.

### Validasi Request Borrow
- `borrow_date` >= hari ini; `due_date` > `borrow_date`; durasi <= `max_borrow_days` organisasi; `reason` wajib (min. 10 karakter).
- Peminjam wajib anggota organisasi, organisasi tidak diarsipkan, aset AVAILABLE.
- Peminjam tidak boleh punya request PENDING/aktif ganda untuk aset yang sama.

### Aturan Lain
- Aset tidak boleh dihapus jika status BORROWED (atau ada peminjaman aktif). Hapus = soft delete.
- Kategori aset tidak boleh dihapus jika masih dipakai aset.
- Admin terakhir tidak boleh di-demote, di-remove, atau menghapus akunnya sendiri.
- Anggota dengan peminjaman aktif tidak boleh di-remove (request PENDING miliknya otomatis CANCELLED).
- Organisasi diarsipkan = read-only (tidak ada peminjaman/aset baru), tidak muncul di Explore. Hapus organisasi = soft delete, hanya jika tidak ada peminjaman aktif, dan konfirmasi dengan mengetik nama organisasi.

## 8. Aturan QR Code
- QR menyimpan URL absolut ke `/o/{organization:slug}/assets/{asset:code}`. TIDAK menyimpan data mentah aset. Ubah nama/spesifikasi/lokasi/kondisi tidak perlu cetak ulang QR.
- 1 aset = 1 QR (dari `assets.code`).
- Hasil scan diproses server:
  - QR/kode tidak valid atau aset tidak ada: tampil "QR / Aset Invalid".
  - User belum login: redirect ke login lalu kembali ke URL tujuan.
  - User bukan anggota organisasi: tampil pesan "Anda harus bergabung ke organisasi {nama} terlebih dahulu" + tombol Ajukan Bergabung. Detail aset TIDAK ditampilkan.
  - User anggota: tampilkan detail aset dengan aksi "Ajukan Peminjaman" dan "Laporkan Kerusakan".

## 9. Katalog Notifikasi
Channel: in-app (`database`), email (queued), dan pop-up toast di website. Email transaksional (verifikasi, reset password, undangan) selalu terkirim; email lainnya mengikuti preferensi user.

| Event | Penerima |
|---|---|
| Join request diajukan | Admin organisasi |
| Join request di-approve/reject | User pengaju |
| Undangan dikirim | Email target (+ in-app jika user sudah terdaftar) |
| Undangan diterima | Admin pengundang |
| Request peminjaman diajukan | Admin dan Staff organisasi |
| Peminjaman di-approve/reject | Peminjam |
| Peminjaman OVERDUE | Peminjam + Admin/Staff |
| Return diproses | Peminjam (berisi kondisi dan denda) |
| Damage report dibuat | Admin dan Staff organisasi |
| Role diubah / dikeluarkan dari organisasi | User terkait |

## 10. Di Luar Scope MVP (JANGAN dibuat)
Pembayaran denda, sistem stok/kuantitas, reservasi jadwal, export PDF/Excel, WebSocket/real-time push, aplikasi mobile native, multi-bahasa, integrasi pihak ketiga selain email.

## 11. Definition of Done (berlaku di setiap fase)
- Fitur sesuai spesifikasi fase dan berjalan end-to-end.
- Otorisasi diuji (role benar bisa, role salah 403, lintas organisasi 403/404).
- Test Pest hijau, Pint bersih, tidak ada N+1 di halaman yang dibuat.
- Pesan error/sukses berbahasa Indonesia dan tampil sebagai toast/alert.
- Ada empty state dan loading state pada daftar/tabel.
- Tampilan responsif (mobile-first, karena scanner QR sering dipakai di HP).
