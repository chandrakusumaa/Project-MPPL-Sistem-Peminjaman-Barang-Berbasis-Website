# Prompt Pengembangan — Organization Inventory (Sistem Pengelolaan Barang)

Dokumen ini berisi **1 Master Context + 9 prompt fase** untuk AI agent (Claude Code, Cursor, Codex, dll). Setiap prompt ada di dalam kotak kode, jadi tinggal copy-paste.

---

## Ringkasan Fase

| Fase | Nama | Penjelasan singkat | Hasil akhir |
|---|---|---|---|
| 0 | Master Context | Aturan main, keputusan bisnis, skema data, dan state machine. Disimpan sekali sebagai `AGENTS.md`, dibaca agent di setiap fase. | File `AGENTS.md` |
| 1 | Fondasi & Database | Setup Laravel, konfigurasi, seluruh migration, model, enum, factory, seeder, layout dasar, dan kerangka role/middleware. | Proyek jalan + DB lengkap + data demo |
| 2 | Landing, Explore & Autentikasi | Landing page, Explore Organization publik, Register dengan verifikasi email, Login, Forgot/Reset Password. | Guest bisa jelajah, daftar, login |
| 3 | Organisasi, Keanggotaan & Role | Buat organisasi, join request, undangan email, kelola anggota dan role, settings organisasi. | Multi-organisasi + role per organisasi |
| 4 | Inventory, Kategori & QR Code | CRUD aset dan kategori, katalog member, generate/download/print QR, halaman Scan QR. | Aset lengkap dengan QR yang bisa discan |
| 5 | Peminjaman & Pengembalian | Request borrow, approve/reject, proses return + inspeksi, OVERDUE otomatis, denda (angka saja), history. | Siklus peminjaman end-to-end |
| 6 | Damage Report, Maintenance & Riwayat Aset | Laporan kerusakan dari member/staff, alur maintenance, tracking log per aset. | Kondisi dan riwayat aset terlacak |
| 7 | Notifikasi | Notifikasi in-app + email + pop-up toast, preferensi notifikasi. | Semua event penting ternotifikasi |
| 8 | Dashboard, Report & Profile | Dashboard Member dan Admin/Staff (Chart.js), export CSV, Profile/Settings user. | Statistik, laporan, akun user |
| 9 | QA, Security & Finalisasi | Audit otorisasi, performa, error page, README, skenario uji end-to-end, checklist deploy. | Siap rilis MVP |

## Cara Pakai

1. Buat repo kosong, lalu jalankan **Prompt 0** (atau simpan isinya sebagai `AGENTS.md` / `CLAUDE.md` / `.cursorrules` sesuai tool yang lu pakai).
2. Jalankan fase **berurutan** (1 → 9). Jangan lompat, karena tiap fase bergantung pada fase sebelumnya.
3. Di akhir tiap fase: cek manual fitur, jalankan test, lalu **commit** sebelum lanjut.
4. Kalau agent bertanya, jawab dulu. Itu memang diminta di aturan kerja supaya flow tidak melenceng.
5. Kalau hasilnya menyimpang, pakai **Prompt Koreksi** di bagian paling bawah.

## Keputusan yang Sudah Dikunci

- Menu Member digabung: Home, Organizations (Semua + Saya), My Borrowing, Notifications, Scan QR, Profile/Settings.
- Semua user boleh membuat organisasi dan otomatis menjadi Admin organisasi itu.
- Masuk organisasi lewat **join request** (member ajukan, Admin approve) dan **undangan email** (Admin undang).
- Semua menu dari flowchart masuk MVP: Damage Reports, Settings (durasi & denda, archive/delete), Report (export).
- Denda hanya **angka** (tanpa fitur pembayaran).
- Kondisi **Minor Damage** saat return: aset tetap `AVAILABLE`.
- `OVERDUE` ditandai **otomatis** oleh scheduler.
- **1 aset = 1 QR** (tanpa sistem stok).
- Member boleh **lapor kerusakan kapan saja** selama QR/aset valid dan dia anggota organisasinya.
- Scan QR oleh non-anggota: **harus bergabung ke organisasinya dulu**, detail aset tidak ditampilkan.
- Role disimpan **per organisasi**.
- Notifikasi: in-app (database), email, dan pop-up toast di website.
- Register wajib verifikasi email.

## Asumsi yang Gw Pakai (Ubah Kalau Perlu)

- Stack: **Laravel versi stabil terbaru (min. 12), MySQL 8, Livewire + Flux UI, Pest**, UI **bahasa Indonesia**, timezone `Asia/Jakarta`.
- Saat Admin approve, status peminjaman langsung `BORROWED` (flowchart: Set Approved → Set Borrowed). `RETURNED` hanya status peminjaman, aset langsung kembali `AVAILABLE`.
- Fine dihitung otomatis saat return (hari terlambat × denda per hari) dan bisa diedit Admin/Staff. Tidak ada pembayaran.
- Perubahan email di Profile memakai **link verifikasi** (bukan OTP).
- Kategori organisasi memakai daftar tetap di config (Kampus, Sekolah, Komunitas, Perusahaan, Lainnya).
- Report = export CSV (belum PDF/Excel).

---

## PROMPT 0 — Master Context (simpan sebagai `AGENTS.md`)

````markdown
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
````

---

## PROMPT FASE 1 — Fondasi Proyek & Database

**Penjelasan:** menyiapkan tanah dulu. Setelah fase ini proyek sudah jalan, seluruh tabel ada, data demo tersedia, dan kerangka layout/role siap dipakai fase berikutnya.

````markdown
Baca AGENTS.md sepenuhnya. Kerjakan HANYA FASE 1: Fondasi Proyek & Database. Tulis rencana singkat dulu sebelum coding.

## Tujuan
Proyek Laravel siap dikembangkan: konfigurasi benar, semua tabel/model/enum tersedia, data demo ada, dan kerangka layout serta otorisasi siap.

## Tugas
1. Buat proyek Laravel memakai official Livewire starter kit (Flux UI + Tailwind), test framework Pest. Cek dokumentasi resmi versi terbaru untuk perintah instalasi.
2. Konfigurasi `.env` / config: MySQL, `APP_LOCALE=id`, timezone `Asia/Jakarta`, `QUEUE_CONNECTION=database`, `MAIL_MAILER=log`, jalankan `storage:link`. Siapkan `.env.example`.
3. Buat semua Enum di `app/Enums`: OrganizationRole, AssetStatus, BorrowingStatus, ReturnCondition, DamageSeverity, DamageReportStatus, MembershipRequestStatus, InvitationStatus, AssetLogEvent. Tambahkan method `label()` bahasa Indonesia dan (bila relevan) `color()` untuk badge.
4. Buat SEMUA migration sesuai bagian "Model Data" di AGENTS.md, lengkap dengan foreign key, unique, dan index.
5. Buat semua Model: relasi, casts ke Enum, fillable, SoftDeletes sesuai aturan. Buat Factory untuk setiap model.
6. Buat helper keanggotaan di model User dan Organization, misalnya: `roleIn(Organization)`, `isAdminOf()`, `isStaffOf()`, `isMemberOf()`, `canManage()` (admin atau staff), serta scope `Organization::active()` (tidak diarsipkan).
7. Buat `config/inventory.php` berisi daftar kategori organisasi (Kampus, Sekolah, Komunitas, Perusahaan, Lainnya) dan konstanta umum (durasi undangan 7 hari, dll).
8. Buat middleware `organization.member` dan `organization.role:{roles}` (tolak dengan 403 bila gagal), daftarkan alias-nya, dan siapkan Policy kosong berkerangka untuk Organization, Asset, Borrowing, DamageReport. Terapkan scoped route binding.
9. Buat kerangka layout Blade/Livewire:
   - Layout publik (landing/guest).
   - Layout area user (navbar/sidebar: Home, Organizations, My Borrowing, Notifications, Scan QR, Profile).
   - Layout manajemen organisasi (sidebar: Dashboard, Inventory, Borrowing, Members, Damage Reports, Reports, Settings).
   - Komponen `<x-nav-item>` yang mendukung state `disabled` (grayed out + tooltip "Hanya untuk Admin") untuk Staff.
   - Komponen badge status, empty state, dan wrapper toast (Flux).
   Isi halaman masih placeholder.
10. Buat Seeder demo: 1 Admin, 1 Staff, 2 Member; 2 organisasi (Admin sebagai admin di organisasi A dan member di B, agar role per organisasi teruji); kategori aset; sekitar 15 aset dengan berbagai status; beberapa peminjaman dengan berbagai status; 2 damage report.
11. Aktifkan `Model::preventLazyLoading()` di non-production.
12. Tulis test: migrasi berjalan, relasi model, helper role per organisasi (user admin di A tapi member di B), middleware `organization.role`.

## Aturan
- Jangan membuat fitur UI selain kerangka layout. Tidak ada auth flow custom di fase ini.
- Nama tabel/kolom persis mengikuti AGENTS.md. Jika perlu menyimpang, tanyakan dulu.

## Selesai jika
- `php artisan migrate:fresh --seed` sukses, `php artisan test` hijau.
- Aplikasi bisa dibuka, layout placeholder tampil, dan item menu Staff bisa dirender dalam keadaan nonaktif.
````

---

## PROMPT FASE 2 — Landing, Explore Organization & Autentikasi

**Penjelasan:** pintu masuk aplikasi. Mengimplementasikan Flowchart A (Landing Page & Login Page).

````markdown
Baca AGENTS.md sepenuhnya. Kerjakan HANYA FASE 2 (Fase 1 sudah selesai). Tulis rencana singkat dulu.

## Tujuan
Guest dapat menjelajah organisasi, mendaftar (dengan verifikasi email), login, dan reset password. Ini mengimplementasikan Flowchart "Landing Page & Login Page".

## Flow yang HARUS diikuti
Landing Page (`/`) menyajikan 3 jalur: Explore Organization, Login, Register.

**A. Explore Organization**
1. Halaman daftar organisasi dengan input search (nama) dan filter kategori. Hanya organisasi yang tidak diarsipkan. Pagination. Search/filter memakai Livewire dan menyaring hasil sesuai input.
2. Klik salah satu organisasi membuka halaman profil publik (nama, kategori, deskripsi, logo, jumlah anggota, jumlah aset). Jangan tampilkan daftar aset atau data sensitif.
3. Tombol "Kembali" mengembalikan ke daftar (dan bisa memilih organisasi lain). Ada juga tombol kembali ke landing.
4. Tombol "Ajukan Bergabung" di profil publik: untuk guest arahkan ke login (simpan intended URL). Logikanya diselesaikan di Fase 3.

**B. Register**
1. Form: Nama, Email, Password (+ konfirmasi password).
2. Cek email: jika sudah terdaftar tampilkan error "Email sudah terdaftar" dan tetap di form.
3. Jika belum: simpan user ke database, kirim email verifikasi, lalu arahkan ke halaman Login dengan pesan sukses "Silakan cek email untuk verifikasi".

**C. Login & Forgot Password**
1. Login: input Email dan Password. Jika salah tampilkan "Email atau password salah" (pesan tunggal, tidak membedakan penyebabnya). Jika benar dan email sudah terverifikasi, arahkan ke `/dashboard`.
2. User yang belum verifikasi email diarahkan ke halaman "Verifikasi Email" dengan tombol kirim ulang (rate limited).
3. Di halaman Login ada jalur "Lupa Password": input email; jika email tidak ditemukan tampilkan "Email tidak ditemukan"; jika ditemukan kirim link reset password, tampilkan pesan sukses, lalu halaman reset (password baru + konfirmasi) dan kembali ke Login setelah berhasil.
4. Logout mengakhiri sesi.

## Tugas teknis
- Manfaatkan fitur auth dari starter kit (Fortify) bila ada, lalu sesuaikan dengan flow di atas: verifikasi email wajib (`MustVerifyEmail`), redirect, pesan error bahasa Indonesia.
- Buat Form Request untuk register/login/forgot/reset. Password minimal 8 karakter.
- Rate limiting untuk login, forgot password, dan kirim ulang verifikasi.
- User yang sudah login yang membuka halaman guest (login/register/landing) diarahkan ke `/dashboard`.
- Halaman `/dashboard` cukup placeholder "Selamat datang, {nama}".
- Template email verifikasi dan reset password berbahasa Indonesia.
- Test (Pest): register sukses/gagal (email duplikat), login benar/salah, login user belum verifikasi, forgot password (ada/tidak ada), reset password, search/filter explore, organisasi arsip tidak muncul.

## Aturan
- Jangan membuat fitur Join Organization, membuat organisasi, atau profile (fase lain).
- Catatan: menampilkan "Email tidak ditemukan" mengikuti flowchart; beri rate limit agar tidak mudah dieksploitasi.

## Selesai jika
Seluruh flow Flowchart A berjalan, termasuk jalur error-nya, dan test hijau.
````

---

## PROMPT FASE 3 — Organisasi, Keanggotaan & Role

**Penjelasan:** inti multi-organisasi. Mengimplementasikan bagian "My Organizations" (Flowchart B) dan "Members" + "Settings" (Flowchart C).

````markdown
Baca AGENTS.md sepenuhnya. Kerjakan HANYA FASE 3 (Fase 1-2 selesai). Tulis rencana singkat dulu.

## Tujuan
User bisa membuat organisasi, bergabung lewat join request atau undangan email, dan Admin bisa mengelola anggota, role, serta pengaturan organisasi. Role bersifat per organisasi.

## Flow yang HARUS diikuti

**A. Halaman `/organizations` (area user)**
- Tab "Semua Organisasi": search (nama) + filter kategori. Setiap kartu menampilkan status user terhadap organisasi tersebut:
  - Belum bergabung: tombol "Ajukan Bergabung" (opsional pesan singkat) -> membuat `membership_requests` PENDING. Setelah itu tampil "Menunggu persetujuan" dengan tombol "Batalkan". Tidak boleh ada request PENDING ganda.
  - Sudah bergabung: badge role + tombol "Buka".
- Tab "Organisasi Saya": daftar organisasi yang diikuti (badge role) + tombol "Buat Organisasi".
- Tombol "Buka": jika role Admin/Staff arahkan ke `/o/{slug}/manage/dashboard` (placeholder dulu); jika Member arahkan ke `/o/{slug}` (placeholder katalog, diisi Fase 4).

**B. Buat Organisasi (Flowchart B)**
Input nama, deskripsi, kategori, logo (opsional) -> Submit. Jika berhasil: slug unik otomatis, pembuat menjadi Admin, `max_borrow_days`=7 dan `late_fine_per_day`=0, kembali ke "Organisasi Saya" dengan toast sukses. Jika gagal: tampilkan "Gagal membuat organisasi" dengan detail validasi.

**C. Kelola Anggota (`/manage/members`, khusus Admin; Staff melihat menu nonaktif dan diakses langsung = 403)**
Halaman punya 3 tab:
1. **Anggota**: daftar (nama, email, role, tanggal bergabung). Aksi: ubah role (admin/staff/member) dan keluarkan anggota.
2. **Permintaan Bergabung**: daftar request PENDING dengan aksi Approve (user ditambahkan sebagai `member`) atau Reject.
3. **Undangan**: form "Undang via email" (email + role staff/member). Sistem membuat `organization_invitations` (token unik, kedaluwarsa 7 hari) dan mengirim email berisi link `/invitations/{token}`. Daftar undangan berstatus pending/accepted/expired/revoked, dengan aksi cabut dan kirim ulang.

**D. Terima Undangan (`/invitations/{token}`)**
- Token tidak valid atau expired: tampil halaman "Undangan sudah kedaluwarsa".
- Belum login: arahkan ke login/register (email undangan terisi), lalu kembali ke halaman ini.
- Login dengan email yang sama dengan undangan: tombol "Terima" -> user ditambahkan ke organisasi dengan role di undangan, status undangan `accepted`. Jika login dengan email berbeda, tampilkan pesan bahwa undangan untuk email lain.
- User yang sudah anggota: tampilkan info dan tandai undangan selesai.

**E. Settings Organisasi (`/manage/settings`, khusus Admin)**
Tiga sub-menu:
1. Edit Profil Organisasi (nama, deskripsi, kategori, logo).
2. Atur Durasi & Denda: `max_borrow_days` (integer >= 1) dan `late_fine_per_day` (integer >= 0, rupiah). Simpan Perubahan. Tidak ada fitur pembayaran.
3. Archive / Delete Organisasi. Archive: set `archived_at` (read-only, hilang dari Explore, bisa di-unarchive). Delete: soft delete, hanya jika tidak ada peminjaman aktif, konfirmasi dengan mengetik nama organisasi.

## Aturan bisnis
- Admin terakhir tidak boleh di-demote atau di-remove.
- Anggota dengan peminjaman aktif (BORROWED/OVERDUE) tidak boleh di-remove; request PENDING miliknya otomatis CANCELLED saat di-remove.
- Semua aksi Admin di atas dilindungi Policy; Staff dan Member mendapat 403.
- Setiap aksi memanggil Action class (mis. `SubmitJoinRequest`, `ApproveJoinRequest`, `CreateOrganization`, `InviteMember`, `AcceptInvitation`, `ChangeMemberRole`, `RemoveMember`, `ArchiveOrganization`), dan mem-fire Domain Event (mis. `JoinRequestSubmitted`, `JoinRequestReviewed`, `InvitationAccepted`, `MemberRoleChanged`). Listener notifikasi dibuat di Fase 7, jadi jangan buat listener sekarang.
- Pengecualian: email undangan dikirim langsung sekarang (Mailable berbahasa Indonesia, queued).
- Buat Form Request untuk semua input.

## Test (Pest)
Join request (sukses, duplikat, batal), approve/reject, buat organisasi (pembuat jadi admin), undangan (sukses, expired, email berbeda, sudah anggota), ubah role, rule admin terakhir, remove dengan peminjaman aktif, archive/delete, otorisasi Staff/Member/lintas organisasi.

## Selesai jika
Semua flow di atas jalan, role per organisasi teruji (user admin di A tetapi hanya member di B), test hijau.
````

---

## PROMPT FASE 4 — Inventory, Kategori & QR Code

**Penjelasan:** modul aset, jantung aplikasi. Mengimplementasikan cabang Inventory (Flowchart C) dan Scan QR (Flowchart B).

````markdown
Baca AGENTS.md sepenuhnya. Kerjakan HANYA FASE 4 (Fase 1-3 selesai). Tulis rencana singkat dulu.

## Tujuan
Admin/Staff mengelola aset dan kategori, membuat QR unik per aset; anggota melihat katalog dan bisa scan QR. Peminjaman dan damage report BELUM dibuat (Fase 5-6), cukup sediakan tombolnya dalam keadaan nonaktif/placeholder.

## Flow yang HARUS diikuti

**A. Inventory (`/manage/inventory`, Admin dan Staff)**
1. Tampilkan list aset: kolom foto kecil, nama, kode, kategori, lokasi, status (badge). Ada search (nama/kode), filter kategori dan status, sort, pagination.
2. Pilih aksi:
   - **Tambah Aset**: form (nama, kategori, deskripsi, spesifikasi, lokasi, tanggal beli opsional, foto opsional maks 2 MB). Validasi input: jika valid simpan aset dengan status AVAILABLE dan `code` ULID otomatis, lalu kembali ke list dengan toast sukses; jika tidak valid tampilkan error di form.
   - **Lihat Detail Aset** lalu pilih tindakan: **Generate QR Code**, **Edit**, **Hapus**, **History**.
3. **Generate QR Code**: tampilkan QR (berisi URL absolut `/o/{slug}/assets/{code}`), dengan tombol Download PNG/SVG dan Print Stiker (halaman/CSS print berisi QR + nama aset + kode + nama organisasi, ukuran cocok untuk stiker). PNG hanya jika ekstensi imagick tersedia, jika tidak, sembunyikan tombol PNG dan cukup SVG.
4. **Edit**: ubah semua field selain `code`. Setelah simpan kembali ke detail. QR TIDAK berubah.
5. **Hapus**: konfirmasi modal; soft delete. Ditolak dengan pesan jelas jika status aset BORROWED atau punya peminjaman aktif.
6. **History**: tab yang menampilkan `asset_logs` aset itu (kronologis terbaru dulu). Di fase ini baru event `created` dan `updated` yang terisi. Catat kedua event itu lewat Action.
7. Setelah tindakan selesai, kembali ke list ("Kembali ke List").

**B. Kategori Aset (`/manage/categories`)**
CRUD sederhana per organisasi (nama unik per organisasi). Kategori yang masih dipakai aset tidak boleh dihapus (tampilkan pesan).

**C. Katalog untuk Member (`/o/{slug}`)**
- Hanya anggota organisasi. Tampilkan aset dalam grid/list: foto, nama, kategori, lokasi, badge status. Search, filter kategori dan status, pagination.
- Klik aset membuka `/o/{slug}/assets/{code}` (detail aset: informasi lengkap + badge status). Tombol "Ajukan Peminjaman" dan "Laporkan Kerusakan" ditampilkan tetapi nonaktif dengan keterangan "Segera hadir" (akan diaktifkan di Fase 5-6).
- Admin/Staff juga bisa membuka halaman detail ini dan mendapat tautan cepat ke halaman manajemen aset tersebut.

**D. Scan QR (`/scan`, semua user login)**
1. Sistem membuka kamera memakai `html5-qrcode` (minta izin kamera; tampilkan pesan bantuan jika ditolak atau HTTPS tidak tersedia, kecuali localhost).
2. Sistem membaca URL/identifier hasil scan dan mengirimnya ke server untuk divalidasi. Ikuti "Aturan QR Code" di AGENTS.md persis:
   - Tidak valid: tampil "QR / Aset Invalid" dan tombol scan ulang.
   - Bukan anggota organisasi: pesan "Anda harus bergabung ke organisasi {nama} terlebih dahulu" + tombol Ajukan Bergabung (memakai Action Fase 3). Jangan bocorkan detail aset.
   - Anggota: redirect ke halaman detail aset.
3. Sediakan input manual kode aset sebagai fallback.
4. Jika URL QR dibuka langsung dari kamera bawaan HP oleh guest, alurnya login lalu kembali ke URL itu, lalu berlaku aturan yang sama.

## Aturan
- Semua query aset wajib scoped ke organisasi (tidak ada kebocoran lintas organisasi).
- Upload foto: validasi mimes/ukuran, simpan di disk `public`, hapus file lama saat diganti.
- Aksi di Action class (`CreateAsset`, `UpdateAsset`, `DeleteAsset`, `ResolveScannedAsset`, dll.) + Form Request.
- Aset tidak boleh diubah statusnya manual dari form edit (status hanya berubah lewat alur bisnis, kecuali fitur "Tandai Ditemukan" di Fase 6).
- Organisasi terarsip: read-only (tidak bisa tambah/edit/hapus aset).

## Test (Pest)
CRUD aset (valid/invalid), tidak bisa hapus aset BORROWED, kategori terpakai tidak bisa dihapus, QR memuat URL yang benar, scan (invalid / non-anggota / anggota / guest), otorisasi (Member 403 di manage, lintas organisasi 403/404), asset_logs terisi.

## Selesai jika
Admin/Staff dapat membuat aset lengkap dengan QR yang bisa discan, dan anggota dapat melihat katalog serta membuka detail aset lewat scan.
````

---

## PROMPT FASE 5 — Peminjaman & Pengembalian

**Penjelasan:** alur paling kritis: dari pengajuan, persetujuan, OVERDUE otomatis, sampai inspeksi return. Mengimplementasikan "My Borrowing" (Flowchart B) dan "Borrowings" (Flowchart C).

````markdown
Baca AGENTS.md sepenuhnya, terutama bagian "State Machine & Aturan Bisnis". Kerjakan HANYA FASE 5 (Fase 1-4 selesai). Tulis rencana singkat dulu. Jika ada celah aturan, tanyakan sebelum menebak.

## Tujuan
Siklus peminjaman lengkap: request -> approve/reject -> borrowed -> (overdue) -> return + inspeksi.

## Flow yang HARUS diikuti

**A. Request Borrow (Member, dari halaman detail aset)**
1. Aktifkan tombol "Ajukan Peminjaman" di detail aset (hanya jika aset AVAILABLE, organisasi tidak diarsipkan, user anggota).
2. Form: tanggal peminjaman, estimasi tanggal pengembalian, alasan. Validasi sesuai "Validasi Request Borrow" di AGENTS.md (termasuk batas `max_borrow_days`).
3. Simpan sebagai PENDING. Tampilkan toast dan arahkan ke My Borrowing tab Pending.

**B. My Borrowing (`/my-borrowings`)**
- Tab Active (BORROWED + OVERDUE, beri penanda visual untuk OVERDUE/hampir jatuh tempo), Pending, dan History (RETURNED/REJECTED/CANCELLED). Filter organisasi + search nama aset.
- Klik item membuka Borrowing Detail sesuai status:
  - Pending: info request + tombol "Batalkan" (-> CANCELLED).
  - Active: tanggal pinjam, jatuh tempo, sisa hari/keterlambatan, denda berjalan (informasi saja).
  - Returned: hasil inspeksi, catatan, denda, tanggal kembali. Untuk REJECTED tampilkan alasan penolakan.
- Widget "peminjaman berjalan" di Home akan dibuat di Fase 8.

**C. Borrowings (Admin/Staff, `/manage/borrowings`)** dengan tab Pending Request, Active Borrowing, History. Alurnya: Tampilkan List Peminjaman -> Pilih Transaksi -> cek status:
1. **Pending -> Approve/Reject**
   - Approve: dalam DB transaction dengan row lock pada aset. Wajib aset masih AVAILABLE (jika tidak, tampilkan "Aset sedang tidak tersedia" dan jangan ubah data). Set peminjaman menjadi BORROWED (isi `approved_by`, `approved_at`), set aset BORROWED, catat `asset_logs` (borrowed), fire event.
   - Reject: alasan penolakan wajib; set REJECTED, fire event (kirim notifikasi ke peminjam).
2. **Active -> Proses Return / Inspeksi**
   - Form: tanggal kembali (default hari ini), kondisi (Good / Minor Damage / Major Damage / Lost) wajib, catatan (wajib jika kondisi bukan Good), denda.
   - Jika hari ini > `due_date`: tandai peminjaman terlambat dan hitung otomatis `fine_amount` (hari terlambat x `late_fine_per_day`); Admin/Staff boleh mengedit angka sebelum konfirmasi. Tidak ada pembayaran.
   - Hasil sesuai tabel "Hasil Inspeksi Return" di AGENTS.md: Good -> aset AVAILABLE; Minor -> aset tetap AVAILABLE + buat damage_report (severity minor, status `noted`); Major -> aset MAINTENANCE + damage_report (severity major, status `in_maintenance`); Lost -> aset LOST. Semua -> peminjaman RETURNED (`returned_at`, `processed_by`, `return_condition`, `return_notes`, `fine_amount`). Semua dalam satu transaction + `asset_logs` (returned, marked_lost, maintenance_started, damage_reported sesuai kasus) + fire event.
   - UI damage report yang lengkap dibuat di Fase 6; di sini cukup membuat recordnya lewat Action.
3. **History**: semua transaksi dengan filter status, rentang tanggal, peminjam, aset.

**D. OVERDUE Otomatis**
- Artisan command `borrowings:mark-overdue`: semua peminjaman BORROWED dengan `due_date` < hari ini (timezone Asia/Jakarta) -> OVERDUE, isi `overdue_at`, catat `asset_logs` (overdue), fire event `BorrowingBecameOverdue`. Idempotent (aman dijalankan berulang).
- Daftarkan di scheduler (harian, dini hari, dan boleh tiap jam). Proses return tetap bekerja untuk BORROWED maupun OVERDUE.

## Aturan
- Semua perubahan status lewat Action class + DB transaction (`ApproveBorrowing`, `RejectBorrowing`, `CancelBorrowing`, `ProcessReturn`, `MarkOverdueBorrowings`, `RequestBorrowing`).
- Transisi status ilegal wajib ditolak (mis. approve peminjaman yang sudah REJECTED).
- Otorisasi: Member hanya melihat/membatalkan miliknya; Admin/Staff hanya untuk organisasinya.
- Organisasi terarsip: tidak bisa membuat request baru.
- Listener notifikasi dibuat di Fase 7 (jangan sekarang), cukup fire event.

## Test (Pest)
Request valid/invalid (tanggal, durasi > max, aset tidak available, duplikat), approve sukses, approve gagal saat aset sudah BORROWED, reject butuh alasan, cancel, return untuk keempat kondisi (cek status aset + damage_report + log), hitung denda, scheduler OVERDUE (termasuk idempotensi), transisi ilegal, otorisasi, dan test concurrency sederhana untuk approve ganda pada aset yang sama.

## Selesai jika
Seluruh siklus berjalan dan konsisten antara status peminjaman, status aset, dan asset_logs.
````

---

## PROMPT FASE 6 — Damage Report, Maintenance & Riwayat Aset

**Penjelasan:** melengkapi sisi kondisi aset: pelaporan kerusakan, perbaikan, dan jejak riwayat. Mengimplementasikan "Damage Reports" (Flowchart C) dan "Laporkan Kerusakan" (Flowchart B).

````markdown
Baca AGENTS.md sepenuhnya. Kerjakan HANYA FASE 6 (Fase 1-5 selesai). Tulis rencana singkat dulu.

## Tujuan
Member dan staff dapat melaporkan kerusakan, Admin/Staff mengelola maintenance, dan setiap aset punya riwayat lengkap.

## Flow yang HARUS diikuti

**A. Laporkan Kerusakan (Member, dari detail aset hasil scan/katalog)**
1. Aktifkan tombol "Laporkan Kerusakan" di halaman detail aset. Boleh dilakukan kapan saja selama user anggota organisasinya dan QR/aset valid (tidak harus sedang meminjam).
2. Form: deskripsi (wajib), foto (opsional, maks 2 MB). Severity dikosongkan (dinilai staff nanti, tampil "Belum dinilai").
3. Simpan `damage_reports` status `open`, `reported_by` = user, catat `asset_logs` (damage_reported), fire event. Tampilkan toast sukses.

**B. Damage Reports (Admin/Staff, `/manage/damage-reports`)**
1. Tampilkan list damage reports (filter status, severity, aset; search) dengan penanda sumber (dari member / hasil inspeksi return).
2. Buka detail: Admin/Staff mengisi/memperbarui detail (severity minor/major, catatan) lalu Simpan Damage Report. Admin/Staff juga bisa membuat damage report manual dari halaman detail aset.
3. Keputusan "Lanjut Maintenance?":
   - **Ya**: status aset -> MAINTENANCE, report -> `in_maintenance`, log `maintenance_started`. DITOLAK dengan pesan jelas jika aset sedang BORROWED/OVERDUE (report tetap `open`, catatan disimpan; maintenance bisa dilanjutkan setelah aset dikembalikan).
     - Aksi "Selesai Perbaikan": isi catatan penyelesaian, report -> `resolved` (`resolved_at`), aset -> AVAILABLE, log `maintenance_finished`.
   - **Tidak**: simpan catatan kerusakan, report -> `noted`, status aset tidak berubah.
4. Report dari inspeksi return (Fase 5): Minor = `noted`; Major = `in_maintenance` (aset sudah MAINTENANCE), tinggal aksi "Selesai Perbaikan".

**C. Aset LOST**
Di detail aset (Admin/Staff) tambahkan aksi "Tandai Ditemukan": LOST -> AVAILABLE, log `marked_found`, wajib catatan.

**D. Riwayat Aset (tab History di detail aset, semua anggota bisa melihat)**
Lengkapi tampilan `asset_logs` sebagai timeline: created, updated, borrowed (siapa), returned (kondisi), overdue, damage_reported, maintenance_started/finished, marked_lost/found. Tampilkan pelaku, waktu, dan keterangan. Filter berdasarkan jenis event, pagination. Pastikan semua Action dari fase sebelumnya sudah mencatat log dengan benar (audit dan perbaiki bila ada yang terlewat).

## Aturan
- Aksi di Action class (`ReportDamage`, `AssessDamageReport`, `StartMaintenance`, `FinishMaintenance`, `MarkAssetFound`) + Form Request.
- Aset MAINTENANCE/LOST tidak bisa dipinjam (pastikan tombol borrow dan validasi server konsisten).
- Member hanya bisa membuat dan melihat laporan miliknya; Admin/Staff mengelola semua di organisasinya.
- Fire event untuk notifikasi (dibuat Fase 7).

## Test (Pest)
Member lapor kerusakan (valid/invalid, non-anggota ditolak), alur maintenance ya/tidak, maintenance ditolak saat aset BORROWED, selesai perbaikan -> AVAILABLE, tandai ditemukan, aset MAINTENANCE tidak bisa dipinjam, timeline history lengkap, otorisasi.

## Selesai jika
Siklus kondisi aset (baik -> rusak -> maintenance -> baik / hilang -> ditemukan) berjalan dan tercatat lengkap.
````

---

## PROMPT FASE 7 — Notifikasi

**Penjelasan:** menyambungkan semua event yang sudah di-fire di fase sebelumnya menjadi notifikasi in-app, email, dan pop-up.

````markdown
Baca AGENTS.md sepenuhnya (terutama "Katalog Notifikasi"). Kerjakan HANYA FASE 7 (Fase 1-6 selesai). Tulis rencana singkat dulu.

## Tujuan
Semua event penting menghasilkan notifikasi in-app + email (sesuai preferensi) + pop-up toast di website.

## Tugas
1. **Notification classes** (`app/Notifications`) untuk setiap baris di Katalog Notifikasi. Channel `database` (payload: judul, pesan, URL tujuan, tipe/kategori, id resource) dan `mail` (queued, berbahasa Indonesia, tombol menuju halaman terkait).
2. **Listener** untuk semua Domain Event yang sudah di-fire (Fase 3, 5, 6). Penerima sesuai katalog. Aktor tidak menerima notifikasi atas aksinya sendiri. Listener di-queue.
3. **Preferensi notifikasi**: `users.notification_preferences` (json) dengan toggle email per kategori: `membership`, `borrowing`, `damage`. Notifikasi in-app selalu aktif. Email transaksional (verifikasi, reset password, undangan) tidak bisa dimatikan. Buat helper `via()` yang membaca preferensi. UI-nya diletakkan di Profile (Fase 8), sekarang siapkan struktur data dan default (semua email aktif).
4. **Lonceng notifikasi** di header layout: jumlah belum dibaca, dropdown 5 terbaru, tautan "Lihat semua". Perbarui dengan `wire:poll` (30 detik).
5. **Halaman `/notifications`**: daftar notifikasi (filter semua/belum dibaca), tandai sudah dibaca, tandai semua dibaca, pagination.
6. **Notification Detail**: klik notifikasi menampilkan detail (judul, isi, waktu, tombol menuju resource terkait) dan otomatis menandainya sudah dibaca. Jika resource sudah tidak ada, tampilkan pesan yang jelas.
7. **Pop-up toast**:
   - Semua aksi berhasil/gagal di aplikasi menampilkan toast (Flux toast) secara konsisten (audit halaman fase sebelumnya).
   - Notifikasi baru yang masuk saat user sedang membuka aplikasi muncul sebagai toast ringan sekali saja (jangan berulang pada polling berikutnya).
8. Pastikan email dev tampil di log; jelaskan di README cara menjalankan queue worker (`php artisan queue:work`).

## Aturan
- Jangan menambah event bisnis baru kecuali ada yang terlewat; jika ada, laporkan.
- Tanpa WebSocket/Reverb (di luar scope MVP): cukup polling.
- Notifikasi tidak boleh membocorkan data lintas organisasi.

## Test (Pest)
Gunakan `Notification::fake()` dan `Event::fake()` untuk memastikan: penerima yang tepat untuk tiap event, aktor tidak dinotifikasi, preferensi email dihormati (in-app tetap ada), email transaksional selalu terkirim, mark as read, detail notifikasi hanya bisa dilihat pemiliknya.

## Selesai jika
Setiap event di katalog menghasilkan notifikasi yang benar di in-app, email, dan toast.
````

---

## PROMPT FASE 8 — Dashboard, Report & Profile

**Penjelasan:** lapisan presentasi dan akun: ringkasan data, statistik grafik, export laporan, serta halaman Profile/Settings user (Flowchart B: Profile).

````markdown
Baca AGENTS.md sepenuhnya. Kerjakan HANYA FASE 8 (Fase 1-7 selesai). Tulis rencana singkat dulu.

## Tujuan
Dashboard Member dan Admin/Staff bernilai informatif, Admin bisa export laporan, dan user bisa mengelola akunnya.

## Tugas

**A. Home Member (`/dashboard`)**
- Ringkasan profil (nama, email, jumlah organisasi yang diikuti).
- Widget peminjaman berjalan (maks 5 terbaru; badge jatuh tempo/OVERDUE) + jumlah request Pending; tautan ke My Borrowing.
- Pintasan: Scan QR, Organizations. Tampilkan notifikasi belum dibaca terbaru.

**B. Dashboard Organisasi (`/manage/dashboard`, Admin dan Staff)**
- Kartu statistik real-time: Total, Available, Borrowed, Maintenance, Lost (+ jumlah Overdue dan Pending Request).
- Chart.js (bundle via Vite): doughnut komposisi status aset, bar peminjaman 6 bulan terakhir, top 5 aset paling sering dipinjam.
- Daftar "Jatuh tempo / Overdue" dan "Pending request terbaru".
- Query agregat efisien (`GROUP BY`, tanpa N+1), scoped organisasi.

**C. Report (`/manage/reports`, khusus Admin; Staff menu nonaktif dan 403)**
- Export CSV: (1) Daftar Aset, (2) Riwayat Peminjaman (filter rentang tanggal + status), (3) Damage Reports (filter rentang tanggal).
- Stream response (chunk/cursor), UTF-8 BOM agar terbuka benar di Excel, header kolom bahasa Indonesia, hanya data organisasi terkait. Belum ada PDF/Excel.

**D. Profile / Settings (`/profile`), mengikuti Flowchart B**
Sub-menu dan flow-nya:
1. **Personal Information**: ubah nama (+ avatar opsional). Klik Simpan -> jika data valid simpan; jika tidak, tampilkan "Data tidak valid".
2. **Email**: input email baru -> validasi (format + unik). Jika tidak valid tampilkan "Email tidak valid". Jika valid simpan ke `pending_email` dan kirim link verifikasi bertanda tangan (signed) ke email baru. Setelah link diklik, email baru resmi menggantikan email lama. Jika verifikasi gagal/kedaluwarsa tampilkan "Verifikasi gagal". Email lama tetap berlaku selama belum terverifikasi.
3. **Password**: input password lama dan baru (+ konfirmasi) -> Simpan. Jika valid simpan password baru; jika password lama salah atau validasi gagal tampilkan "Perubahan gagal" / "Password tidak valid".
4. **Preferensi Notifikasi**: toggle email per kategori (struktur dari Fase 7).
5. **Hapus Akun**: klik -> dialog konfirmasi ("Hapus akun?"). Batal = kembali ke profile. Lanjut = input password; jika salah tampilkan "Password salah" dan minta input ulang; jika benar dan lolos aturan, hapus akun lalu logout. Aturan: tolak jika user adalah satu-satunya Admin di suatu organisasi (minta transfer Admin atau hapus organisasi dulu). Hapus akun = soft delete, keanggotaan dihapus, request PENDING dibatalkan; riwayat peminjaman tetap utuh (tampilkan nama sebagai "Pengguna Terhapus" bila perlu).
6. **Logout**.

## Aturan
- Setiap aksi Profile memakai Form Request + Action, dan mem-flash toast.
- Perubahan email/password harus memicu ulang session yang relevan (mis. logout perangkat lain untuk password).
- Jangan menambah fitur di luar daftar ini.

## Test (Pest)
Angka statistik dashboard benar, otorisasi dashboard/report (Staff 403 untuk report), isi CSV benar dan terscope organisasi, seluruh cabang flow Profile (valid/invalid, verifikasi email baru sukses/gagal, password lama salah, hapus akun batal/salah password/sole admin/berhasil).

## Selesai jika
Home, dashboard, export, dan seluruh cabang Profile berjalan sesuai flowchart.
````

---

## PROMPT FASE 9 — QA, Security & Finalisasi

**Penjelasan:** pengetatan kualitas sebelum rilis MVP: keamanan, performa, pengalaman pengguna, dokumentasi, dan uji end-to-end.

````markdown
Baca AGENTS.md sepenuhnya. Kerjakan HANYA FASE 9 (Fase 1-8 selesai). Tulis rencana singkat dulu. Fase ini bersifat audit dan perbaikan, bukan fitur baru. Jika menemukan bug atau celah, perbaiki dan laporkan.

## Tugas
1. **Audit otorisasi**: untuk SETIAP route dan aksi Livewire, verifikasi Policy/middleware. Tulis test matriks: guest, member, staff, admin, dan user dari organisasi lain (IDOR) terhadap tiap halaman/aksi utama. Pastikan Staff tidak bisa mengakses Members/Reports/Settings (403) walau URL dibuka langsung.
2. **Audit keamanan**: mass assignment, validasi upload (mimes, ukuran, nama file aman), rate limiting (login, forgot password, resend verification, join request, scan endpoint), CSRF/Livewire actions, token undangan (acak, panjang cukup, sekali pakai), tidak ada data sensitif di response/log, signed URL untuk verifikasi email baru.
3. **Performa**: perbaiki N+1 (lazy loading disabled harus tidak error), cek index untuk query filter/list, gunakan pagination di semua daftar, eager loading di dashboard/report.
4. **UX**: halaman error 403/404/419/500 berbahasa Indonesia, empty state dan loading state di semua daftar, konfirmasi untuk aksi destruktif, pesan error konsisten, responsif di layar HP (khusus halaman Scan QR, detail aset, form request, dan return).
5. **Konsistensi data**: buat command/test yang memeriksa invarian: aset BORROWED harus punya tepat 1 peminjaman aktif; aset AVAILABLE tidak boleh punya peminjaman aktif; tiap organisasi punya minimal 1 Admin; `asset_logs` mencatat setiap perubahan status.
6. **README.md**: deskripsi proyek, kebutuhan sistem, langkah instalasi, konfigurasi `.env` (DB, mail, queue, APP_URL yang benar karena dipakai di QR), `storage:link`, menjalankan queue worker, scheduler (cron `* * * * * php artisan schedule:run`), akun demo dari seeder, cara menjalankan test, catatan HTTPS untuk kamera scanner, dan daftar fitur di luar scope.
7. **Seeder demo** yang rapi dan idempotent untuk demonstrasi.
8. **Skenario uji end-to-end** (Pest feature test atau Dusk bila tersedia) untuk:
   1. Register -> verifikasi email -> login.
   2. Buat organisasi -> tambah kategori dan aset -> generate QR.
   3. User lain: explore -> ajukan join -> Admin approve.
   4. Undang via email -> terima undangan.
   5. Member scan QR -> ajukan peminjaman -> Staff approve.
   6. Peminjaman lewat jatuh tempo -> scheduler -> OVERDUE + notifikasi.
   7. Return dengan tiap kondisi (Good, Minor, Major, Lost) -> cek status aset, denda, damage report.
   8. Member lapor kerusakan -> Admin lanjut maintenance -> selesai perbaikan.
   9. Non-anggota scan QR -> diminta bergabung.
   10. Staff mencoba akses Members/Reports/Settings -> menu nonaktif + 403.
   11. Export CSV -> isi benar.
   12. Hapus akun sole admin ditolak.
9. **Checklist deploy**: `APP_ENV=production`, `APP_DEBUG=false`, `php artisan optimize`, Supervisor untuk queue worker, cron scheduler, backup DB, konfigurasi disk storage, HTTPS.

## Selesai jika
Semua test hijau, tidak ada N+1, README lengkap, seluruh skenario end-to-end lolos, dan laporan berisi daftar temuan + perbaikan.
````

---

## Prompt Pendukung

### Prompt Koreksi (jika agent menyimpang)

````markdown
Hasil kamu menyimpang dari spesifikasi. Baca ulang AGENTS.md dan prompt fase yang sedang dikerjakan.
Masalahnya: {jelaskan singkat, sebutkan halaman/aksi/aturan yang salah}.
Perilaku yang benar: {kutip aturan dari AGENTS.md atau flow di prompt fase}.
Perbaiki hanya bagian yang salah, jangan mengubah bagian lain. Tambahkan/ubah test yang membuktikan perbaikannya, lalu laporkan file yang berubah.
````

### Prompt Review Antar Fase (opsional, sebelum lanjut ke fase berikutnya)

````markdown
Lakukan review FASE {N} tanpa menambah fitur baru:
1. Bandingkan implementasi dengan checklist "Selesai jika" dan flow di prompt fase.
2. Cek pelanggaran aturan di AGENTS.md (state machine, role, scoping organisasi, logging).
3. Jalankan test + Pint dan laporkan hasilnya.
4. Beri daftar celah/risiko yang ditemukan, urut dari paling kritis. Perbaiki yang kritis, tanyakan dulu untuk sisanya.
````
