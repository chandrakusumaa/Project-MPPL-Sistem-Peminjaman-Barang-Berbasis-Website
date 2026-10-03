---
title: "ORGANIZATION INVENTORY"
subtitle: "PRODUCT REQUIREMENTS DOCUMENT (PRD)"
author: "Sistem Pengelolaan Barang Organisasi"
date: "Versi 0.2 - Expanded Draft untuk Review Tim"
geometry: margin=0.8in
---

# Dokument Control

| Parameter | Detail |
| --- | --- |
| Nama Produk | Organization Inventory |
| Dokumen | Product Requirements Document (PRD) |
| Versi | 0.2 |
| Status | Expanded Draft untuk Review Tim |
| Target Eksekusi | Phase 1 (MVP) |
| Basis | PRD awal Organization Inventory + referensi struktur PRD ORBHITA v0.4 |

\newpage

# 1. Ringkasan Produk

**Organization Inventory** adalah sistem pengelolaan aset/barang organisasi yang menyediakan inventaris terpusat, alur pengajuan peminjaman, persetujuan, pengembalian dan inspeksi, pelacakan histori aset, serta QR Code unik untuk setiap aset.

Produk menggunakan pendekatan **multi-organisasi**, sehingga satu pengguna dapat menemukan organisasi, mengajukan keanggotaan, dan - setelah disetujui - mengakses inventaris organisasi tersebut sesuai role dan izin yang diberikan.

Pengalaman utama dibagi menjadi dua area:

- **Area publik/member** untuk eksplorasi organisasi, pengelolaan keanggotaan, katalog aset, pengajuan peminjaman, QR scanning, dan pemantauan peminjaman pribadi.
- **Area Admin/Staff** untuk dashboard operasional, CRUD inventory, persetujuan peminjaman, pemrosesan pengembalian, inspeksi kondisi, histori, dan fungsi organisasi yang sesuai permission.

Nilai utama produk adalah menyediakan satu sumber data inventaris yang dapat dilacak, mengurangi pencatatan manual, dan membuat setiap perubahan penting pada aset maupun transaksi memiliki jejak histori.

Semua tindakan yang mengubah status atau kepemilikan proses (misalnya approve borrow request, return inspection, revoke membership, atau perubahan aset) harus mengikuti aturan otorisasi dan dicatat agar dapat ditelusuri.

---

# 2. Masalah, Tujuan, dan Prinsip Produk

## 2.1 Problem Statement

Pengelolaan barang organisasi masih sering menggunakan pencatatan manual atau spreadsheet. Kondisi tersebut memicu beberapa masalah operasional:

- Risiko kehilangan pelacakan aset (asset untracking).
- Riwayat peminjaman dan pengembalian sulit ditelusuri.
- Informasi aset tersebar dan tidak terintegrasi secara terpusat.
- Status ketersediaan barang tidak selalu mutakhir.
- Persetujuan dan pengembalian berpotensi bergantung pada komunikasi manual.
- Sulit membedakan kewenangan Admin, Staff, dan Member secara konsisten.

## 2.2 Primary Goals

1. Memusatkan seluruh data aset organisasi dalam satu platform terpadu.
2. Mempermudah alur pengajuan, persetujuan, peminjaman aktif, dan pengembalian barang.
3. Menyediakan pelacakan aset berbasis QR Code dan histori penggunaan.
4. Menyediakan status aset yang konsisten dan dapat diperbarui berdasarkan transaksi nyata.
5. Mendukung multi-organisasi dengan isolasi data antarorganisasi.
6. Menegakkan role dan permission pada level backend dan UI.

## 2.3 Secondary Goals

1. Mengurangi ketergantungan pada kertas/spreadsheet manual.
2. Meningkatkan akuntabilitas anggota dan pengelola aset.
3. Menyediakan statistik penggunaan aset dan laporan operasional.
4. Menjadi fondasi untuk pengembangan lanjutan seperti notifikasi yang lebih lengkap, approval policy, dan analitik.

## 2.4 Prinsip Produk

### Single Source of Truth
Database sistem menjadi sumber status aset dan transaksi. UI tidak boleh menampilkan status operasional yang bertentangan dengan state terakhir yang tersimpan.

### Confirm Before State-Changing Action
Tindakan yang mengubah status penting harus dapat ditinjau sebelum disimpan bila konteksnya memerlukan konfirmasi, terutama reject/approve, return inspection, perubahan role, dan penghapusan data.

### Least Privilege
Pengguna hanya boleh membaca atau mengubah data yang memang menjadi kewenangannya. Pembatasan ini diterapkan di backend, bukan hanya dengan menyembunyikan tombol.

### Traceability by Default
Perubahan penting pada aset, membership, dan transaksi borrowing menghasilkan histori yang dapat ditelusuri.

### Explicit Uncertainty
Sistem tidak boleh mengarang status, alasan, atau data transaksi ketika informasi belum tersedia. Kondisi gagal dan data tidak lengkap harus ditampilkan sebagai kondisi yang belum terselesaikan.

---

# 3. Target Pengguna dan Peran

## 3.1 Target Pengguna

Target utama adalah organisasi yang perlu mengelola aset bersama dan memiliki aktivitas peminjaman internal. Pengguna operasional terdiri dari pengelola organisasi (Admin/Staff) dan anggota (Member).

## 3.2 User Roles

| Role | Tujuan penggunaan | Akses utama |
| --- | --- | --- |
| Admin | Mengelola organisasi dan seluruh operasi inventaris | Inventory, category, borrowing, return, inspection, members, reports, settings |
| Staff | Menjalankan operasi harian inventaris | Inventory, borrowing, return, inspection; fitur tanpa izin ditampilkan disabled/grayed-out |
| Member | Menggunakan aset organisasi sesuai hak keanggotaan | Explore organization, join request, katalog aset, QR scan, borrow request, borrowing history |

## 3.3 Permission Matrix

| Area | Admin | Staff | Member |
| --- | :---: | :---: | :---: |
| Explore organization | View | View | View |
| Join organization | N/A | N/A | Request |
| Approve/reject membership | Yes | No | No |
| Kelola role member | Yes | No | No |
| View inventory | Yes | Yes | Hanya organisasi yang diikuti |
| Create asset | Yes | Yes | No |
| Edit asset | Yes | Yes | No |
| Delete/archive asset | Yes | Sesuai izin operasional | No |
| Generate QR | Yes | Yes | No |
| Scan QR | Yes | Yes | Yes |
| Approve/reject borrow request | Yes | Yes | No |
| Process return | Yes | Yes | Pengajuan/konfirmasi sesuai flow |
| Record inspection | Yes | Yes | No |
| View organization-wide history | Yes | Yes | Tidak; hanya histori milik sendiri pada My Borrowing |
| Member management | Yes | No (disabled) | No |
| Reports | Yes | No (disabled) | No |
| Organization settings | Yes | No (disabled) | No |
| Account settings | Yes | Yes | Yes |

**Catatan:** UI grayed-out untuk Staff adalah keputusan pengalaman pengguna, bukan mekanisme security. Permission backend tetap wajib menolak akses yang tidak diizinkan.

---

# 4. Tujuan dan Indikator Keberhasilan

Indikator berikut adalah **indikator pengujian**, bukan hasil yang sudah dicapai.

| Kode | Tujuan | Indikator pengujian |
| --- | --- | --- |
| G01 | Traceability | Tim dapat menelusuri aset dari detail/QR ke histori transaksi yang relevan. |
| G02 | Borrowing correctness | Request tidak dapat disetujui untuk aset yang tidak eligible berdasarkan aturan status. |
| G03 | Authorization | Akun/member dari organisasi A tidak dapat mengakses atau memodifikasi data terbatas organisasi B. |
| G04 | Inventory accuracy | Status Available/Borrowed/Maintenance/Lost konsisten dengan transaksi terakhir. |
| G05 | Operational usability | Admin/Staff dapat menyelesaikan alur request -> approve -> return -> inspection tanpa bantuan teknis. |
| G06 | Data persistence | Data dan histori tetap tersedia setelah logout/login kembali dan setelah refresh. |
| G07 | QR traceability | QR unik mengarah ke aset yang tepat dan tetap valid setelah detail aset diperbarui. |

---

# 5. Keputusan Produk dan Lingkup MVP

## 5.1 Keputusan yang Sudah Berasal dari PRD Dasar

- Produk: **Organization Inventory**.
- Arsitektur aplikasi menggunakan **Laravel (PHP)** dengan Eloquent ORM.
- Database: **PostgreSQL atau MySQL**.
- Frontend: **Blade + Livewire**.
- Styling: **Tailwind CSS + Flux UI**.
- QR Code backend: **Simple QrCode**.
- QR scanner frontend: **html5-qrcode**.
- Analitik/grafik: **Chart.js**.
- Storage/validation: **Laravel Storage** dan **Laravel Form Request Validation**.

## 5.2 Must Have - Phase 1 (MVP)

| Kode | Fitur | Prioritas | Cakupan utama |
| --- | --- | --- | --- |
| F01 | Account & Authentication | Wajib | Register, login, reset password, profile minimal |
| F02 | Organization Discovery & Join | Wajib | Explore, search/filter, public profile, join request |
| F03 | Role & Authorization | Wajib | Admin/Staff/Member, permission matrix, UI disabled untuk Staff |
| F04 | Inventory Management | Wajib | CRUD asset, category, detail spesifikasi, status |
| F05 | QR Code | Wajib | Generate unique QR, scan, resolve asset URL |
| F06 | Borrowing Request | Wajib | Form request, validasi, Pending, approval/rejection |
| F07 | Return & Inspection | Wajib | Return processing, condition inspection, state update |
| F08 | Asset History | Wajib | Timeline/log peminjaman, approval, return, inspection, perubahan penting |
| F09 | Dashboard | Wajib | Ringkasan Member dan Admin/Staff |
| F10 | Reports & Operational Settings | Wajib untuk Admin | Export laporan dan pengaturan organisasi |
| F11 | Account Settings | Wajib | Profile, password, notification preference, logout |

## 5.3 Di Luar Rilis Awal / Pengembangan Berikutnya

- Sinkronisasi inventory dengan sistem eksternal.
- Notifikasi multi-channel kompleks atau real-time push yang belum ditentukan providernya.
- Mobile native dedicated app.
- Advanced approval policy berdasarkan jabatan/limit biaya.
- Predictive analytics dan anomaly detection.
- Bulk import/export berskala besar.
- Integrasi perangkat RFID/NFC.

Item di atas bukan batas permanen produk dan dapat ditinjau pada fase berikutnya.

---

# 6. User Stories dan Alur Utama

## 6.1 User Stories

| Kode | User story | Fitur |
| --- | --- | --- |
| US01 | Sebagai pengguna, saya ingin register/login agar dapat menggunakan sistem secara authenticated. | F01 |
| US02 | Sebagai pengguna, saya ingin menemukan organisasi berdasarkan nama/kategori agar dapat memilih organisasi yang relevan. | F02 |
| US03 | Sebagai Member, saya ingin mengajukan join organization agar dapat menggunakan aset organisasi. | F02 |
| US04 | Sebagai Admin, saya ingin menyetujui/menolak join request agar keanggotaan organisasi tetap terkontrol. | F03 |
| US05 | Sebagai Member aktif, saya ingin melihat katalog aset dan mengajukan peminjaman dengan tanggal dan alasan. | F04/F06 |
| US06 | Sebagai Staff/Admin, saya ingin melihat pending request dan mengambil keputusan approve/reject. | F06 |
| US07 | Sebagai Staff/Admin, saya ingin memproses pengembalian dan mencatat kondisi barang agar status aset diperbarui. | F07 |
| US08 | Sebagai pengguna, saya ingin memindai QR untuk membuka detail aset yang tepat. | F05 |
| US09 | Sebagai Admin/Staff, saya ingin melihat histori aset agar perjalanan barang dapat ditelusuri. | F08 |
| US10 | Sebagai Member, saya ingin melihat Active/Pending/History Borrowing agar saya mengetahui status permintaan dan peminjaman saya. | F06/F08 |
| US11 | Sebagai Admin, saya ingin melihat ringkasan statistik aset agar kondisi inventory dapat dipantau. | F09 |
| US12 | Sebagai pengguna, saya ingin memperbarui profil dan preferensi akun agar pengaturan saya tetap relevan. | F11 |

## 6.2 Alur Landing dan Authentication

1. Pengguna membuka Landing Page.
2. Pilihan utama: **Explore Organization**, **Login**, **Register**.
3. Register meminta Name, Email, Password.
4. Sistem memvalidasi uniqueness email.
5. Akun dibuat dan pengguna diarahkan ke Login.
6. Login menggunakan Email + Password.
7. Forgot Password mengirimkan reset link ke email terdaftar.
8. Kegagalan authentication harus menampilkan pesan yang dapat ditindaklanjuti dan tidak membuat sesi authenticated semu.

## 6.3 Alur Explore dan Join Organization

`Landing -> Explore Organization -> Search/Filter -> Public Organization Profile -> Join -> Pending -> Admin Review -> Approved/Rejected`

Aturan minimum:

- Hanya user authenticated yang dapat membuat join request.
- Satu user tidak boleh memiliki dua membership aktif pada organisasi yang sama.
- Request yang sudah Pending tidak boleh dibuat ulang secara identik.
- Admin menjadi pihak yang memproses permintaan membership.
- Member yang disetujui memperoleh akses ke data organisasi sesuai role Member.

**Status membership yang diusulkan untuk SRS:** `PENDING`, `ACTIVE`, `REJECTED`, `REVOKED`.

## 6.4 Alur Borrowing

`Asset Available -> Member Request -> Pending -> Admin/Staff Approve or Reject`

Jika approved:

`Approved -> Active Borrowing -> Return -> Inspection -> Available / Maintenance / Lost`

Jika rejected:

`Pending -> Rejected`

Request tidak mengubah status asset menjadi Borrowed sebelum approval/transaction benar-benar diproses.

## 6.5 Alur Return dan Inspection

1. Staff/Admin membuka Active Borrowing.
2. Sistem memuat detail asset dan borrower.
3. Pengelola mencatat waktu pengembalian aktual.
4. Pengelola memilih hasil inspeksi: `GOOD`, `MINOR_DAMAGE`, `MAJOR_DAMAGE`, atau `LOST`.
5. Sistem menyimpan inspection record.
6. Sistem memperbarui status asset sesuai aturan state transition.
7. Sistem menulis event ke Asset History.

## 6.6 Alur QR

`Scan QR -> Resolve unique asset URL -> Authorization Check -> Asset Detail`

QR hanya mengarah ke identifier/URL unik. Informasi status, spesifikasi, lokasi, atau kondisi diambil dari data terbaru saat halaman dibuka.

---

# 7. Kebutuhan Produk dan Kriteria Penerimaan

## F01 - Account & Authentication

### Kebutuhan

Sistem menyediakan register, login, logout, reset password, dan profile minimal. Email harus unik. Password tidak boleh disimpan dalam plaintext. Session/authentication mengikuti mekanisme Laravel.

### Acceptance Criteria - AC01

- Register dengan email baru membuat akun.
- Register dengan email yang sudah ada ditolak dengan pesan yang jelas.
- Login berhasil hanya dengan kredensial valid.
- Login gagal tidak membuat authenticated session.
- Reset password menghasilkan flow yang dapat digunakan untuk mengganti password.
- Setelah logout, halaman terproteksi tidak dapat diakses tanpa authentication.
- Dua akun tetap memiliki data pribadi dan akses yang terisolasi.

---

## F02 - Organization Discovery & Join

### Kebutuhan

Pengguna dapat mencari organisasi berdasarkan nama dan kategori, melihat profil publik organisasi, mengirim join request, serta melihat status permintaan.

### Acceptance Criteria - AC02

- Search/filter mengembalikan organisasi sesuai kata kunci/kategori.
- Profil publik organisasi dapat dibuka tanpa akses ke inventory privat organisasi.
- User yang belum menjadi member dapat mengirim join request.
- Duplicate pending request dicegah.
- Admin dapat Approve/Reject.
- Approval membuat membership menjadi ACTIVE.
- Rejection tidak memberikan akses ke inventory.
- User yang sudah ACTIVE tidak dapat membuat request join kedua untuk organisasi yang sama.

---

## F03 - Role & Authorization

### Kebutuhan

Akses ditentukan berdasarkan role dan konteks organisasi. Permission wajib ditegakkan server-side. Staff menggunakan dashboard yang sama, tetapi fitur tanpa izin ditampilkan disabled/grayed-out.

### Acceptance Criteria - AC03

- Staff tidak dapat memanggil action server untuk member management/report/settings organisasi yang tidak diizinkan.
- Member tidak dapat membuat/edit/delete asset.
- Member hanya dapat mengakses inventory organisasi yang sudah diikuti.
- Admin memiliki akses sesuai permission matrix.
- Modifikasi route atau request payload tidak boleh melewati permission check.
- Audit log mencatat actor dan action untuk perubahan sensitif.

---

## F04 - Inventory Management

### Kebutuhan

Admin/Staff dapat membuat, melihat, mengubah, dan mengelola aset. Asset minimal memiliki nama, kategori, identifier internal, spesifikasi/detail, lokasi penyimpanan, kondisi, status, dan organisasi pemilik.

Sistem menyediakan pencarian dan filtering inventory.

### Acceptance Criteria - AC04

- Asset baru memiliki identifier yang unik pada konteks organisasi.
- Field wajib divalidasi sebelum penyimpanan.
- Asset tidak dapat dipindahkan ke organisasi lain melalui update biasa tanpa aturan khusus.
- Status asset hanya dapat berubah melalui tindakan yang valid.
- Asset yang sedang BORROWED tidak dapat dihapus secara destruktif tanpa mekanisme pengamanan yang ditentukan SRS.
- Search/filter menampilkan data yang konsisten dengan organization scope.

---

## F05 - QR Code

### Kebutuhan

Setiap asset memiliki QR Code unik yang mereferensikan URL detail asset. QR tidak menyimpan data aset mentah secara hardcoded.

### Acceptance Criteria - AC05

- Generate QR menghasilkan QR unik yang menunjuk asset yang benar.
- Scan QR membuka asset yang benar.
- Perubahan nama/spesifikasi/status/lokasi tidak membutuhkan generate ulang QR.
- QR untuk asset yang sudah tidak tersedia mengikuti kebijakan status yang ditentukan sistem dan tidak menampilkan data sensitif.
- Identifier pada URL tidak boleh memungkinkan akses ke asset lain hanya dengan menebak ID jika authorization tidak terpenuhi.

---

## F06 - Borrowing Request dan Approval

### Kebutuhan

Member aktif dapat mengisi Request Borrow berupa tanggal peminjaman, estimasi pengembalian, alasan, dan asset yang diminta. Request berstatus Pending sampai diproses oleh Admin/Staff yang berwenang.

Validasi harus mencegah tanggal yang tidak masuk akal dan konflik terhadap kondisi asset.

### Acceptance Criteria - AC06

- Hanya ACTIVE member yang dapat membuat borrowing request.
- Asset harus berada pada status yang eligible untuk dipinjam.
- Tanggal peminjaman tidak boleh tidak valid menurut aturan sistem.
- Estimasi pengembalian wajib lebih besar dari atau sama dengan waktu peminjaman sesuai kebijakan produk.
- Request tersimpan sebagai PENDING sampai diproses.
- Approve menghasilkan active borrowing dan perubahan asset state yang konsisten.
- Reject menghasilkan REJECTED dan menyimpan alasan bila alasan diwajibkan oleh policy.
- Sistem mencegah dua approval aktif yang membuat satu asset tercatat dipinjam oleh dua pihak pada waktu yang sama.

---

## F07 - Return & Inspection

### Kebutuhan

Staff/Admin dapat memproses pengembalian aset dan mencatat hasil inspeksi kondisi. Pilihan minimum: Good, Minor Damage, Major Damage, Lost.

### Acceptance Criteria - AC07

- Hanya active borrowing yang dapat diproses sebagai return.
- Return menyimpan tanggal/waktu aktual dan actor.
- Inspection wajib disimpan bersama return.
- GOOD mengembalikan asset ke AVAILABLE.
- MINOR_DAMAGE mengikuti kebijakan produk untuk AVAILABLE atau status perbaikan ringan; kebijakan final ditetapkan pada SRS.
- MAJOR_DAMAGE mengalihkan asset ke MAINTENANCE.
- LOST mengalihkan asset ke LOST.
- Return tidak dapat diproses dua kali pada transaction yang sama.
- Semua hasil inspection muncul pada history.

---

## F08 - Asset History & Audit Trail

### Kebutuhan

History mencatat perjalanan aset dan transaksi penting. Minimal event yang dicatat: asset creation/update, QR generation, borrow request, approval/rejection, borrow activation, return, inspection, status change, dan perubahan membership yang relevan.

### Acceptance Criteria - AC08

- Setiap event memiliki actor, timestamp, event type, dan referensi entity/transaksi.
- History asset dapat ditampilkan dalam urutan kronologis.
- Event yang sudah tercatat tidak dapat diedit sebagai histori biasa.
- Perubahan state dapat ditelusuri dari event history.
- Member hanya dapat melihat histori yang menjadi haknya.

---

## F09 - Dashboard

### Kebutuhan

Dashboard Admin/Staff menampilkan statistik operasional: Total, Available, Borrowed, Maintenance, Lost. Dashboard Member menampilkan ringkasan profil dan borrowing yang berjalan.

### Acceptance Criteria - AC09

- Angka dashboard berasal dari data transaksi/status yang sama dengan inventory.
- Dashboard tidak menampilkan data organisasi lain.
- Perubahan status aset tercermin pada ringkasan setelah data berhasil tersimpan.
- Grafik Chart.js menggunakan data yang dapat ditelusuri ke query sumbernya.
- Empty state tersedia ketika organisasi belum memiliki asset/transaksi.

---

## F10 - Reports & Operational Settings

### Kebutuhan

Admin dapat mengekspor laporan operasional dan menyesuaikan konfigurasi organisasi. Staff melihat fitur tanpa izin sebagai disabled.

### Acceptance Criteria - AC10

- Hanya Admin yang dapat menjalankan export laporan organisasi.
- Export mengikuti filter/periode yang dipilih.
- Data export mengikuti organization scope.
- Perubahan settings dicatat pada audit log jika berdampak pada operasi.
- Staff tidak dapat menggunakan action server yang terkait report/organization setting.

---

## F11 - Account Settings

### Kebutuhan

Pengguna dapat memperbarui profile, mengganti password, mengatur preferensi notifikasi, dan logout. Detail kanal notifikasi akan ditetapkan pada keputusan teknis/integrasi.

### Acceptance Criteria - AC11

- Perubahan profile tervalidasi dan tersimpan.
- Password dapat diganti dengan verifikasi yang sesuai.
- Preferensi notifikasi tersimpan pada akun yang benar.
- Logout mengakhiri session.

---

# 8. Aturan Sistem, Data, dan Integritas Transaksi

## 8.1 Organization Scope

Data berikut minimal berada pada scope organisasi: asset, category, borrowing request/transaction, inspection, history, member role/membership, dan report.

Semua query yang menyentuh data organisasi harus menerapkan organization scope secara eksplisit atau melalui policy/global authorization yang dapat diuji.

## 8.2 Asset Status State Machine

Status operasional dasar:

`AVAILABLE -> BORROWED -> RETURNED -> AVAILABLE`

Cabang inspection:

`RETURNED -> MAINTENANCE` jika Major Damage.

`RETURNED -> LOST` jika Lost.

`RETURNED -> AVAILABLE` jika kondisi Good.

**Catatan implementasi yang diusulkan:** `RETURNED` lebih tepat diperlakukan sebagai state transisi singkat atau outcome transaction jika sistem tidak membutuhkan asset berada lama pada status tersebut. Keputusan final ditetapkan di SRS agar reporting tidak ambigu.

### Aturan transisi minimum

| From | Action | To | Actor |
| --- | --- | --- | --- |
| AVAILABLE | Approve borrow | BORROWED | Admin/Staff |
| BORROWED | Return + Good | AVAILABLE | Admin/Staff |
| BORROWED | Return + Major Damage | MAINTENANCE | Admin/Staff |
| BORROWED | Return + Lost | LOST | Admin/Staff |
| MAINTENANCE | Mark ready | AVAILABLE | Admin/Staff |
| LOST | Recovery/administrative resolution | TBD | Admin |

Transisi selain tabel di atas harus ditolak atau memerlukan action administratif khusus yang ditetapkan pada SRS.

## 8.3 Borrowing Integrity

- Satu asset tidak boleh mempunyai lebih dari satu active borrowing yang bertentangan secara waktu.
- Approval harus dilakukan terhadap data terbaru, bukan snapshot usang.
- Action approve/return yang bersifat state-changing harus menggunakan transaction/locking strategy yang sesuai agar dua operator tidak menghasilkan state ganda.
- Request yang sudah Rejected/Approved/Cancelled tidak dapat diproses kembali tanpa mekanisme khusus.

## 8.4 Membership Integrity

- Satu user hanya memiliki satu membership record per organization pada satu waktu.
- Role perubahan hanya dapat dilakukan oleh Admin.
- Revoked member kehilangan akses ke inventory organisasi tersebut.
- Membership status harus menjadi dasar authorization, bukan hanya role string.

## 8.5 QR Rules

- QR menyimpan URL/identifier, bukan snapshot metadata.
- URL tidak boleh mengandung secret credential.
- QR harus tetap resolvable setelah detail asset berubah.
- Jika asset dihapus/diarsipkan, perilaku endpoint QR harus terdefinisi: redirect ke status unavailable, tampilkan archived state, atau return not found.

## 8.6 Asset History vs Audit Log

Dua konsep dibedakan:

- **Asset History:** timeline yang berorientasi pada perjalanan penggunaan satu asset.
- **Audit Log:** catatan tindakan sensitif yang berorientasi pada actor, action, timestamp, dan perubahan.

Satu event dapat tampil pada keduanya bila relevan.

## 8.7 Data Privacy dan Security

- Password disimpan melalui hashing framework.
- Authorization diterapkan server-side.
- Tidak ada credential/secret di frontend publik.
- Export laporan harus terikat pada organization scope dan role.
- Data pribadi member tidak boleh tampil pada konteks public organization profile kecuali data publik yang memang ditentukan produk.
- Error tidak boleh membocorkan stack trace, query, secret, atau data organisasi lain ke end user.

## 8.8 Failure dan Edge Cases

Minimal harus diuji:

- Dua operator approve asset yang sama hampir bersamaan.
- Member mencoba borrow setelah membership dicabut.
- Asset berubah status setelah request dibuat tetapi sebelum approval.
- QR di-scan untuk asset archived/deleted.
- Return diproses dua kali.
- Network/request gagal saat state-changing action.
- Export gagal atau data terlalu besar.
- User membuka URL asset langsung tanpa membership yang sesuai.
- Staff mencoba endpoint Admin dengan manipulasi URL/request.

---

# 9. Struktur Layar dan Arahan UX/UI

## 9.1 Matriks Layar

| Layar | Informasi / aksi utama |
| --- | --- |
| 1 - Landing Page | Explore Organization, Login, Register |
| 2 - Explore Organization | Search, filter, organization cards |
| 3 - Public Organization Profile | Identitas organisasi, kategori, deskripsi, join action |
| 4 - Login / Register / Forgot Password | Authentication flows |
| 5 - Member Home | Profile summary, active borrowing, shortcut organization |
| 6 - All Organization | Daftar organisasi, status membership, join |
| 7 - Organization Detail / Asset Catalog | Search/filter asset, availability, scan QR |
| 8 - Asset Detail | Spesifikasi, kondisi, lokasi, status, QR, request borrow |
| 9 - Request Borrow | Asset, tanggal, estimasi return, alasan, submit |
| 10 - My Borrowing | Active, Pending Request, History Borrowing |
| 11 - QR Scanner | Camera preview, permission, scan result/error |
| 12 - Admin/Staff Dashboard | KPI, charts, pending requests, active borrowing |
| 13 - Inventory | List, search/filter, create/edit/delete/archive |
| 14 - Asset Form | Identity, category, specification, location, condition |
| 15 - Borrowing Management | Pending Request, Active Borrowing, approval actions |
| 16 - Return & Inspection | Return detail, inspection result, notes, submit |
| 17 - Asset History | Timeline/filter event |
| 18 - Member Management | Join requests, members, roles, revoke membership |
| 19 - Reports & Settings | Export report, organization configuration |
| 20 - Account Settings | Profile, password, notification preference, logout |

## 9.2 UI State Wajib

Setiap layar operasional perlu mendukung minimal state:

- Loading
- Empty
- Success
- Validation error
- Authorization denied
- Not found
- Network/server error
- Disabled action
- Confirmation modal untuk destructive/sensitive action

Informasi status tidak boleh hanya dibedakan berdasarkan warna. Gunakan label teks, icon yang jelas, atau nilai numerik.

## 9.3 UX Principle untuk Role

Staff tetap melihat struktur dashboard yang sama dengan Admin agar workflow operasional konsisten, tetapi menu/action tanpa izin ditampilkan disabled/grayed-out. Tooltip atau helper text dapat menjelaskan alasan pembatasan.

---

# 10. Lingkungan Teknis, Integrasi, dan Dependensi

## 10.1 Stack

| Komponen | Teknologi / Library |
| --- | --- |
| Backend Framework | Laravel (PHP) |
| ORM | Eloquent ORM |
| Database | PostgreSQL / MySQL |
| Frontend | Blade, Livewire |
| Styling | Tailwind CSS, Flux UI |
| QR Generation | Simple QrCode |
| QR Scanner | html5-qrcode |
| Analytics / Charts | Chart.js |
| Storage | Laravel Storage |
| Validation | Laravel Form Request Validation |

## 10.2 Dependensi Teknis yang Perlu Dikonfirmasi

- Pilihan final PostgreSQL atau MySQL.
- Mekanisme hosting/deployment dan environment production/staging.
- Strategi file/media storage bila asset membutuhkan attachment/foto.
- Email provider untuk reset password dan notification.
- Dukungan camera permission/browser untuk QR scanner.
- Strategi concurrency/locking pada approve dan return.
- Kebijakan backup, restore, dan retention history/audit.
- Monitoring/logging dan error reporting.

PRD menetapkan kebutuhan produk; konfigurasi database, endpoint, indexing, queue, cache, dan deployment detail ditetapkan pada SRS/technical design.

## 10.3 Kesiapan Demo

Untuk demo terjadwal, environment harus memiliki:

- Database aktif.
- Aplikasi dapat diakses melalui URL/device target.
- Seed/demo organization dan asset.
- Minimal satu akun per role.
- Sample pending borrow request.
- QR asset yang dapat discan.
- Data histori untuk dashboard/report.

Jika salah satu dependency gagal, bagian yang tidak berjalan harus diberi label keterbatasan dan tidak dilaporkan sebagai fitur selesai.

---

# 11. Skenario Demo dan Rencana Validasi

## 11.1 Skenario Demo End-to-End

### Skenario A - Setup Organisasi

1. Login sebagai Admin.
2. Verifikasi organisasi dan dashboard.
3. Buat category.
4. Buat asset.
5. Generate QR.
6. Pastikan asset muncul sebagai AVAILABLE.

### Skenario B - Join Member

1. Login sebagai user Member baru.
2. Explore organization.
3. Buka public organization profile.
4. Send Join Request.
5. Login Admin.
6. Approve membership.
7. Member melihat status ACTIVE.

### Skenario C - Borrowing

1. Member membuka asset catalog.
2. Memilih asset AVAILABLE.
3. Mengisi borrow request.
4. Request menjadi PENDING.
5. Admin/Staff membuka Pending Request.
6. Approve.
7. Asset berubah menjadi BORROWED.
8. Member melihat item pada Active Borrowing.

### Skenario D - Return dan Inspection

1. Staff/Admin membuka Active Borrowing.
2. Memproses return.
3. Pilih Good / Minor Damage / Major Damage / Lost.
4. Sistem memperbarui status asset.
5. History menampilkan event return + inspection.

### Skenario E - QR Traceability

1. Scan QR asset.
2. Buka detail asset.
3. Ubah nama/lokasi asset dari dashboard.
4. Scan QR yang sama.
5. Detail menunjukkan data terbaru.

## 11.2 Validasi Tahap 1 - UX / Mockup

Uji 3-5 partisipan internal untuk menyelesaikan task dasar: explore organization, join, borrow, approval, return, dan scan QR.

Catat:

- keberhasilan tanpa bantuan,
- waktu penyelesaian,
- error/titik kebingungan,
- komentar pengguna,
- kebutuhan copy/helper text.

Mockup tidak membuktikan integritas database atau concurrency.

## 11.3 Validasi Tahap 2 - Functional Build

Gunakan environment uji dengan akun/organization terpisah. Bukti uji minimal memuat:

- build/commit atau versi aplikasi,
- skenario,
- expected result,
- actual result,
- screenshot/log yang tidak mengekspos data sensitif.

## 11.4 Validasi Tahap 3 - Security & Data Isolation

Uji minimal:

- Account A vs Account B.
- Organization A vs Organization B.
- Member vs Staff vs Admin.
- Direct URL access.
- Manipulated request payload.
- Unauthorized export/action.

## 11.5 Release Gate MVP

MVP dianggap siap diuji ketika:

- F01-F11 diuji atau status belum selesai dicatat secara eksplisit.
- Tidak ada crash yang menghalangi alur utama.
- Tidak ada akses lintas organisasi.
- Borrowing state transition dapat direproduksi.
- Return inspection menghasilkan status yang benar.
- QR resolve ke asset yang benar.
- Asset history tersimpan.
- Permission negative test lulus untuk role kritis.

---

# 12. Roadmap Pengembangan

Roadmap berikut adalah **usulan sequencing implementasi**, bukan jadwal final.

| Tahap | Fokus | Output |
| --- | --- | --- |
| Phase A - Foundation | Auth, organization model, roles, permission | Login/register, organization context, role guards |
| Phase B - Inventory | Category, asset CRUD, search/filter, status | Inventory end-to-end + QR generation |
| Phase C - Borrowing | Request, approval, active borrow | Borrowing workflow + validation |
| Phase D - Return & Tracking | Inspection, status transition, history | Return workflow, asset timeline, audit events |
| Phase E - Dashboard & Reports | KPI, Chart.js, export, settings | Operational visibility + admin reporting |
| Phase F - QA & Hardening | Security, concurrency, edge cases, UX | Release candidate + evidence test |

---

# 13. Keputusan Terbuka Sebelum SRS

| ID | Keputusan terbuka | Dampak |
| --- | --- | --- |
| OD01 | PostgreSQL atau MySQL sebagai database final | Schema, indexing, deployment |
| OD02 | Apakah organization dapat dibuat oleh user biasa atau hanya proses admin/seed tertentu | Onboarding dan permission |
| OD03 | Status `RETURNED` disimpan sebagai asset state atau hanya transaction outcome | State machine dan reporting |
| OD04 | Perlakuan `MINOR_DAMAGE`: tetap AVAILABLE atau masuk status perbaikan terpisah | Inventory lifecycle |
| OD05 | Asset delete vs archive | Data retention dan QR behavior |
| OD06 | Apakah borrow request boleh memiliki tanggal peminjaman di masa depan dan bagaimana reservation window diterapkan | Availability/conflict |
| OD07 | Batas jumlah active borrowing per Member | Borrowing policy |
| OD08 | Apakah satu request hanya boleh memuat satu asset | UX dan data model |
| OD09 | Apakah Admin dan Staff dapat approve semua jenis asset atau ada kategori yang dibatasi | Permission policy |
| OD10 | Bentuk notification final dan provider email/push | Notification architecture |
| OD11 | Format export (CSV/XLSX/PDF) dan batas data | Reporting |
| OD12 | Storage/attachment foto asset dan aturan retention | Storage/security |
| OD13 | Strategi locking/concurrency untuk approval dan return | Transaction integrity |
| OD14 | Kebijakan maintenance workflow setelah Major Damage | Status transition |
| OD15 | Kebijakan asset LOST: final state, recovery, replacement, atau administrative closure | Lifecycle dan report |
| OD16 | Visibilitas asset saat QR dipindai oleh user yang bukan member | Privacy dan route guard |

Setiap keputusan terbuka harus diselesaikan sebelum implementasi terkait dinyatakan final. Detail teknisnya masuk SRS.

---

# 14. Ringkasan Model Data Konseptual

PRD tidak menetapkan schema database final. Entitas konseptual berikut menjadi dasar pembahasan SRS/ERD.

| Entitas | Peran |
| --- | --- |
| User | Identitas akun global |
| Organization | Identitas organisasi |
| OrganizationMember | Relasi user-organization, status membership, role |
| AssetCategory | Kategori barang dalam organisasi |
| Asset | Data master barang/aset |
| AssetQRCode | Identifier/URL QR untuk asset |
| BorrowRequest | Pengajuan peminjaman |
| BorrowTransaction | Transaksi peminjaman yang disetujui/aktif |
| ReturnInspection | Catatan pengembalian dan hasil inspeksi |
| AssetHistory | Timeline penggunaan/perubahan asset |
| AuditLog | Catatan tindakan sensitif oleh actor |
| NotificationPreference | Preferensi kanal notifikasi akun |
| ReportExport | Metadata proses export bila dibutuhkan |

## 14.1 Relasi Konseptual

- User memiliki banyak OrganizationMember.
- Organization memiliki banyak OrganizationMember.
- Organization memiliki banyak AssetCategory dan Asset.
- Asset memiliki satu atau lebih event pada AssetHistory.
- Asset dapat memiliki QR identifier.
- Member dapat membuat banyak BorrowRequest.
- BorrowRequest yang disetujui menjadi/terhubung ke BorrowTransaction.
- BorrowTransaction dapat memiliki satu ReturnInspection.
- Action sensitif dapat menghasilkan AuditLog.

## 14.2 Prinsip Data

Database tidak boleh mengandalkan UI untuk menjaga integritas organisasi, role, atau status. Constraint, policy, transaction, validation, dan service-layer rules harus digunakan sesuai kebutuhan implementasi.

---

# 15. Referensi dan Catatan Perubahan

## 15.1 Referensi Utama

1. PRD awal **Organization Inventory (Sistem Pengelolaan Barang)** - digunakan sebagai sumber produk, role, flow, MVP scope, status aset, dan stack teknologi.
2. PRD **ORBHITA**, Versi 0.4 - digunakan sebagai referensi struktur dan kedalaman dokumen, khususnya:
   - ringkasan produk dan problem statement,
   - goals + indikator pengujian,
   - matriks fitur dan prioritas,
   - user stories + flow utama,
   - kebutuhan per fitur + acceptance criteria,
   - aturan sistem/data dan edge cases,
   - matriks layar + state UI,
   - lingkungan demo, dependensi, dan readiness,
   - skenario validasi,
   - roadmap,
   - keputusan terbuka sebelum SRS.

PRD ORBHITA menekankan bahwa requirement harus dibedakan dari hasil pengujian, dependency yang belum diverifikasi tidak boleh dinyatakan siap, serta keputusan implementasi yang belum final dipindahkan ke SRS/spike teknis. Prinsip dokumentasi tersebut diadaptasi di sini untuk konteks inventory organisasi.

## 15.2 Catatan Perubahan dari PRD Awal

Versi 0.2 memperluas PRD awal dengan:

- target pengguna, role, dan permission matrix;
- indikator keberhasilan yang dapat diuji;
- feature codes F01-F11;
- user stories US01-US12;
- acceptance criteria AC01-AC11;
- aturan organization scope dan data isolation;
- asset state machine dan aturan transisi;
- concurrency dan borrowing integrity;
- pemisahan asset history dan audit log;
- edge cases dan failure states;
- matriks layar yang lebih lengkap;
- non-functional/security expectations;
- skenario demo dan validation plan;
- roadmap sequencing;
- daftar open decisions sebelum SRS;
- model data konseptual.

Bagian yang bertanda **diusulkan**, **TBD**, atau **perlu dikonfirmasi** tidak dianggap sebagai keputusan final produk sampai disetujui tim dan diturunkan ke SRS.

---

