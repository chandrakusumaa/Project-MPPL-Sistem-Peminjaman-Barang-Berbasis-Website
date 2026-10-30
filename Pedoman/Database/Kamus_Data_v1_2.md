# Kamus Data Organization Inventory v1.2

Ekstraksi teks dari `Organization_Inventory_v1_2.drawio` yang dilampirkan pemilik proyek. Berisi atribut tabel utama dan tooltip relasi. Tabel berlabel KONTEKS pada diagram tidak dihitung sebagai tabel tambahan.

**Status:** rancangan, bukan migration yang sudah dijalankan. Aturan UNIQUE, CHECK, otorisasi, dan transaksi mengikuti SRS v1.2, terutama bagian 7.6 dan 7.10.

Total aktif: **14 tabel (12 inti + 2 pendukung)**, di luar tabel infrastruktur framework. Keputusan 30 Oktober 2026 mengganti alur lama dengan Gabung langsung sebagai Member; snapshot sebelumnya ada di `../Arsip/keanggotaan-sebelum-gabung-langsung/`.

## users

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| name  :  varchar(150) | VARCHAR(150)  /  Wajib  /  Nama tampilan. |
| email  :  varchar(254) | VARCHAR(254)  /  Wajib  /  Email aktif; normalisasi trim + lowercase oleh aplikasi. |
| email_verified_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu verifikasi email aktif. |
| password  :  varchar(255) | VARCHAR(255)  /  Wajib  /  Hash password Laravel; tidak boleh plaintext. |
| remember_token ?  :  varchar(100) | VARCHAR(100)  /  NULL diizinkan  /  Token remember-me Laravel. |
| pending_email ?  :  varchar(254) | VARCHAR(254)  /  NULL diizinkan  /  Usulan email baru; belum menggantikan email aktif. |
| pending_email_requested_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Pengikat versi permintaan perubahan email pada signed URL. |
| avatar ?  :  varchar(255) | VARCHAR(255)  /  NULL diizinkan  /  Path relatif file avatar pada Storage. |
| notification_preferences ?  :  json | JSON  /  NULL diizinkan  /  Preferensi email: membership, borrowing, damage. NULL memakai semua true. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |
| deleted_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Soft delete akun; identitas historis tetap disimpan. |

## organization_user

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Organisasi keanggotaan. |
| FK  user_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  User anggota. |
| role  :  enum | ENUM('admin','staff','member')  /  Wajib  /  Hak akses pada organisasi ini. |
| status  :  enum | ENUM('active','revoked')  /  Wajib  /  Hanya active + user/organisasi belum dihapus yang memberi akses. |
| joined_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu bergabung pertama; retry aktif tidak mengubahnya. Membership revoked tidak diaktifkan kembali lewat Gabung. |
| revoked_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu pencabutan; NULL saat aktif. |
| FK  revoked_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Pelaku pencabutan; NULL bila aksi sistem. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |

## audit_logs

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| FK  organization_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  NULL untuk aksi akun global. |
| FK  actor_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  User pelaku; NULL untuk sistem. |
| action  :  varchar(80) | VARCHAR(80)  /  Wajib  /  Kode aksi, mis. membership.revoked atau borrowing.approved. |
| subject_type  :  varchar(60) | VARCHAR(60)  /  Wajib  /  Alias jenis objek dalam allowlist aplikasi. |
| subject_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  ID objek; relasi polimorfik, tidak memiliki FK universal. |
| old_values ?  :  json | JSON  /  NULL diizinkan  /  Nilai sebelum perubahan yang telah disaring. |
| new_values ?  :  json | JSON  /  NULL diizinkan  /  Nilai sesudah perubahan yang telah disaring. |
| metadata ?  :  json | JSON  /  NULL diizinkan  /  Konteks tambahan tanpa password/token mentah. |
| event_key ?  :  varchar(191) | VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin  /  NULL diizinkan  /  Kunci deduplikasi opsional. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu kejadian UTC; tidak ada updated_at. |

## password_reset_tokens

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  email  :  varchar(254) | VARCHAR(254)  /  Wajib  /  Email akun yang meminta reset; primary key. |
| token  :  varchar(255) | VARCHAR(255)  /  Wajib  /  Hash token reset yang dikelola broker Laravel. |
| created_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu penerbitan token. |

## organizations

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| name  :  varchar(150) | VARCHAR(150)  /  Wajib  /  Nama organisasi. |
| slug  :  varchar(160) | VARCHAR(160) CHARACTER SET ascii COLLATE ascii_bin  /  Wajib  /  URL slug unik; immutable agar QR tetap valid. |
| description ?  :  text | TEXT  /  NULL diizinkan  /  Deskripsi profil publik. |
| category  :  enum | ENUM('kampus','sekolah','komunitas','perusahaan','lainnya')  /  Wajib  /  Kategori organisasi dari daftar tetap SRS. |
| logo ?  :  varchar(255) | VARCHAR(255)  /  NULL diizinkan  /  Path relatif logo. |
| max_borrow_days  :  smallint unsigned | SMALLINT UNSIGNED  /  Wajib  /  Batas durasi pinjam dalam hari. |
| late_fine_per_day  :  decimal(12,0) | DECIMAL(12,0)  /  Wajib  /  Tarif denda Rupiah per hari; tanpa pecahan. |
| damage_charge_policy ?  :  text | TEXT  /  NULL diizinkan  /  Kebijakan tanggung jawab dan cara penilaian biaya sebelum pengajuan. |
| FK  created_by  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  User pembuat organisasi; bukan sumber role. |
| archived_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  NULL = aktif; isi = diarsipkan/read-only. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |
| deleted_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Soft delete organisasi. |

## notifications

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  char(36) | CHAR(36) CHARACTER SET ascii COLLATE ascii_bin  /  Wajib  /  UUID notifikasi Laravel. |
| type  :  varchar(255) | VARCHAR(255)  /  Wajib  /  Nama kelas/alias notifikasi. |
| notifiable_type  :  varchar(255) | VARCHAR(255)  /  Wajib  /  Morph type Laravel; MVP hanya User. |
| notifiable_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  ID penerima; target polimorfik ditentukan aplikasi. |
| FK  organization_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Scope organisasi opsional; NULL untuk notifikasi global. |
| data  :  text | TEXT  /  Wajib  /  Payload JSON serialisasi Laravel: judul, isi, URL, referensi aman. |
| event_key ?  :  varchar(191) | VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin  /  NULL diizinkan  /  Kunci event untuk deduplikasi per penerima. |
| read_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  NULL = belum dibaca. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |

## asset_categories

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Organisasi pemilik. |
| name  :  varchar(120) | VARCHAR(120)  /  Wajib  /  Nama kategori; trim sebelum simpan. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |

## assets

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Organisasi pemilik; immutable. |
| FK  asset_category_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Kategori dari organisasi yang sama. |
| code  :  char(26) | CHAR(26) CHARACTER SET ascii COLLATE ascii_bin  /  Wajib  /  ULID publik immutable; QR memakai URL dengan kode ini. |
| name  :  varchar(180) | VARCHAR(180)  /  Wajib  /  Nama aset. |
| description ?  :  text | TEXT  /  NULL diizinkan  /  Deskripsi aset. |
| specifications ?  :  text | TEXT  /  NULL diizinkan  /  Spesifikasi bebas; tidak memerlukan struktur JSON. |
| location  :  varchar(180) | VARCHAR(180)  /  Wajib  /  Lokasi penyimpanan. |
| purchase_date ?  :  date | DATE  /  NULL diizinkan  /  Tanggal pembelian opsional. |
| photo ?  :  varchar(255) | VARCHAR(255)  /  NULL diizinkan  /  Path relatif satu foto aset. |
| status  :  enum | ENUM('available','borrowed','maintenance','lost')  /  Wajib  /  Ketersediaan operasional; hanya diubah lewat action yang sah. |
| physical_condition  :  enum | ENUM('good','minor_damage','major_damage','unknown')  /  Wajib  /  Kondisi fisik terkini; berbeda dari ketersediaan. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |
| deleted_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Soft delete aset; kode tidak dipakai ulang. |

## damage_reports

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Organisasi laporan. |
| FK  asset_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Aset yang dilaporkan. |
| FK  borrowing_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Opsional; harus borrowing untuk aset dan organisasi yang sama. |
| source  :  enum | ENUM('member_report','staff_report','return_inspection')  /  Wajib  /  Asal laporan tetap tersimpan walau role pelapor berubah. |
| FK  reported_by  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Pelapor; untuk hasil inspeksi adalah petugas pemroses return. |
| description  :  text | TEXT  /  Wajib  /  Deskripsi kerusakan wajib. |
| photo ?  :  varchar(255) | VARCHAR(255)  /  NULL diizinkan  /  Path relatif satu foto. |
| severity ?  :  enum | ENUM('minor','major')  /  NULL diizinkan  /  NULL = belum dinilai. |
| status  :  enum | ENUM('open','in_maintenance','resolved','noted')  /  Wajib  /  Status tindak lanjut. |
| FK  handled_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Petugas tindakan terakhir; detail pelaku tiap transisi tersimpan di audit. |
| assessed_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu penilaian severity. |
| assessment_notes ?  :  text | TEXT  /  NULL diizinkan  /  Catatan penilaian. |
| maintenance_started_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu mulai maintenance. |
| resolution_notes ?  :  text | TEXT  /  NULL diizinkan  /  Catatan penyelesaian atau keputusan cukup dicatat. |
| resolved_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu perbaikan selesai. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |
| G  maintenance_guard ?  :  tinyint | TINYINT  /  NULL diizinkan  /  Maksimal satu maintenance aktif per aset.  /  Generated: CASE WHEN status = 'in_maintenance' THEN 1 ELSE NULL END |
| G  inspection_guard ?  :  tinyint | TINYINT  /  NULL diizinkan  /  Satu laporan otomatis dari satu inspeksi return.  /  Generated: CASE WHEN source = 'return_inspection' THEN 1 ELSE NULL END |

## asset_logs

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Organisasi aset. |
| FK  asset_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Aset terkait. |
| FK  user_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Pelaku; NULL untuk scheduler/sistem. |
| FK  borrowing_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Referensi transaksi opsional. |
| FK  damage_report_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Referensi laporan opsional. |
| event  :  varchar(40) | VARCHAR(40)  /  Wajib  /  Jenis event terkontrol. |
| description  :  text | TEXT  /  Wajib  /  Keterangan event untuk timeline. |
| metadata ?  :  json | JSON  /  NULL diizinkan  /  Data tambahan, mis. perubahan status; bukan tempat secret. |
| event_key ?  :  varchar(191) | VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin  /  NULL diizinkan  /  Kunci idempotensi opsional, mis. borrowing:42:overdue. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu kejadian UTC; tidak ada updated_at. |

## borrowings

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Primary key internal; bukan identifier publik. |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Organisasi transaksi. |
| FK  asset_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Aset dari organisasi yang sama. |
| FK  user_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /  Peminjam global; histori tidak bergantung pada membership aktif. |
| borrow_date  :  date | DATE  /  Wajib  /  Tanggal mulai yang diminta dalam kalender Asia/Jakarta. |
| due_date  :  date | DATE  /  Wajib  /  Hari terakhir pengembalian tepat waktu dalam kalender Asia/Jakarta. |
| reason  :  text | TEXT  /  Wajib  /  Alasan peminjaman, minimal 10 karakter setelah trim. |
| status  :  enum | ENUM('pending','rejected','cancelled','borrowed','overdue','returned')  /  Wajib  /  Status workflow; tidak ada status approved terpisah. |
| FK  approved_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Admin/Staff yang menyetujui sekaligus memulai pinjam. |
| approved_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Awal peminjaman aktual; UTC. |
| max_borrow_days_snapshot ?  :  smallint unsigned | SMALLINT UNSIGNED  /  NULL diizinkan  /  Salinan batas durasi saat approve; immutable setelah approve. |
| late_fine_per_day_snapshot ?  :  decimal(12,0) | DECIMAL(12,0)  /  NULL diizinkan  /  Salinan tarif saat approve; perubahan settings tidak mengubah pinjaman lama. |
| damage_policy_snapshot ?  :  text | TEXT  /  NULL diizinkan  /  Kebijakan yang diakui saat pengajuan; immutable setelah approval. |
| damage_policy_acknowledged_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu pengakuan kebijakan; wajib jika kebijakan berlaku. |
| FK  rejected_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Admin/Staff penolak. |
| rejected_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu penolakan. |
| rejection_reason ?  :  text | TEXT  /  NULL diizinkan  /  Alasan wajib saat rejected. |
| FK  cancelled_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Pelaku pembatalan; NULL bila sistem. |
| cancelled_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu pembatalan. |
| cancellation_reason ?  :  text | TEXT  /  NULL diizinkan  /  Alasan pembatalan, termasuk revoke/delete account. |
| overdue_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu pertama scheduler menandai overdue; dipertahankan setelah return. |
| returned_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu pengembalian fisik; dapat berbeda dari waktu pemrosesan. |
| FK  processed_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Admin/Staff pemroses pengembalian. |
| return_processed_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Waktu petugas menyimpan hasil return; UTC. |
| return_condition ?  :  enum | ENUM('good','minor_damage','major_damage','lost')  /  NULL diizinkan  /  Hasil inspeksi satu kali saat return. |
| return_notes ?  :  text | TEXT  /  NULL diizinkan  /  Catatan wajib jika hasil bukan good. |
| late_days ?  :  int unsigned | INT UNSIGNED  /  NULL diizinkan  /  Hari kalender terlambat saat return; dihitung aplikasi dalam Asia/Jakarta. |
| calculated_fine_amount ?  :  decimal(14,0) | DECIMAL(14,0)  /  NULL diizinkan  /  late_days x snapshot tarif; nilai otomatis sebelum override. |
| fine_amount ?  :  decimal(14,0) | DECIMAL(14,0)  /  NULL diizinkan  /  Denda keterlambatan final sebagai sumber tagihan late_fee; bukan saldo atau biaya kerusakan. |
| fine_override_reason ?  :  text | TEXT  /  NULL diizinkan  /  Alasan wajib bila denda final berbeda dari hasil otomatis. |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pembuatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC. |
| G  active_guard ?  :  tinyint | TINYINT  /  NULL diizinkan  /  Maksimal satu pinjaman borrowed/overdue per aset.  /  Generated: CASE WHEN status IN ('borrowed','overdue') THEN 1 ELSE NULL END |
| G  open_user_guard ?  :  tinyint | TINYINT  /  NULL diizinkan  /  Maksimal satu pending/aktif per user dan aset.  /  Generated: CASE WHEN status IN ('pending','borrowed','overdue') THEN 1 ELSE NULL END |

## borrower_charges

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  borrowing_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  asset_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  borrower_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  damage_report_id ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Wajib untuk damage/loss; laporan harus terkait borrowing/aset/ruang yang sama. |
| charge_type  :  enum | ENUM('late_fee','damage','loss')  /  Wajib  /   |
| amount  :  decimal(14,0) | DECIMAL(14,0)  /  Wajib  /  Rupiah bulat >0; immutable setelah issued. |
| status  :  enum | ENUM('draft','issued','cancelled')  /  Wajib  /   |
| reason  :  text | TEXT  /  Wajib  /  Dasar nominal dan tanggung jawab. |
| evidence_path ?  :  varchar(255) | VARCHAR(255)  /  NULL diizinkan  /  Path bukti privat, bukan URL publik. |
| policy_basis  :  text | TEXT  /  Wajib  /  Snapshot dasar kebijakan/penilaian. |
| liability_acknowledged_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /  Persetujuan eksplisit untuk transaksi lama tanpa snapshot. |
| FK  created_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  NULL hanya untuk late_fee sistem. |
| FK  issued_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /  Admin penerbit; NULL hanya untuk late_fee sistem. |
| issued_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /   |
| FK  cancelled_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /   |
| cancelled_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /   |
| cancellation_reason ?  :  text | TEXT  /  NULL diizinkan  /   |
| idempotency_key  :  varchar(191) | VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin  /  Wajib  /  Unik per ruang; cegah pencatatan ganda. |
| G  active_guard ?  :  tinyint | TINYINT  /  NULL diizinkan  /  Kolom turunan; jangan ditulis aplikasi.  /  Generated: CASE WHEN status IN ('draft','issued') THEN 1 ELSE NULL END |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pencatatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC; diperbarui aplikasi. |

## charge_payments

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  charge_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  borrower_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| amount  :  decimal(14,0) | DECIMAL(14,0)  /  Wajib  /  Nominal positif; hanya confirmed mengurangi saldo. |
| method  :  enum | ENUM('cash','bank_transfer','other')  /  Wajib  /   |
| paid_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu aktual; >= issued_at dan tidak di masa depan; divalidasi Action. |
| reference ?  :  varchar(191) | VARCHAR(191)  /  NULL diizinkan  /   |
| evidence_path ?  :  varchar(255) | VARCHAR(255)  /  NULL diizinkan  /  Path bukti privat. |
| notes ?  :  text | TEXT  /  NULL diizinkan  /   |
| status  :  enum | ENUM('pending','confirmed','rejected','voided')  /  Wajib  /   |
| FK  recorded_by  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  confirmed_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /   |
| confirmed_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /   |
| FK  rejected_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /   |
| rejected_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /   |
| rejection_reason ?  :  text | TEXT  /  NULL diizinkan  /   |
| FK  voided_by ?  :  bigint unsigned | BIGINT UNSIGNED  /  NULL diizinkan  /   |
| voided_at ?  :  datetime(6) | DATETIME(6)  /  NULL diizinkan  /   |
| void_reason ?  :  text | TEXT  /  NULL diizinkan  /   |
| idempotency_key  :  varchar(191) | VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin  /  Wajib  /   |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pencatatan UTC. |
| updated_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu perubahan terakhir UTC; diperbarui aplikasi. |

## charge_credits

| Atribut / penanda | Tipe, nullability, dan keterangan |
| --- | --- |
| PK  id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  charge_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  organization_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| FK  borrower_id  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| amount  :  decimal(14,0) | DECIMAL(14,0)  /  Wajib  /  Pembebasan positif maksimal sebesar sisa; divalidasi di bawah lock charge. |
| reason  :  text | TEXT  /  Wajib  /   |
| FK  created_by  :  bigint unsigned | BIGINT UNSIGNED  /  Wajib  /   |
| idempotency_key  :  varchar(191) | VARCHAR(191) CHARACTER SET ascii COLLATE ascii_bin  /  Wajib  /   |
| created_at  :  datetime(6) | DATETIME(6)  /  Wajib  /  Waktu pencatatan UTC. |

## Relasi

FK gabungan ditulis utuh. Pastikan migration menyediakan key pendukung pada kolom target dan kebijakan penghapusan sesuai SRS.

- `organization_user(organization_id) -> organizations(id)`
- `organization_user(user_id) -> users(id)`
- `organization_user(revoked_by) -> users(id)`
- `organizations(created_by) -> users(id)`
- `audit_logs(organization_id) -> organizations(id)`
- `audit_logs(actor_id) -> users(id)`
- `notifications(organization_id) -> organizations(id)`
- `asset_categories(organization_id) -> organizations(id)`
- `assets(organization_id) -> organizations(id)`
- `assets(asset_category_id,organization_id) -> asset_categories(id,organization_id)`
- `damage_reports(organization_id) -> organizations(id)`
- `damage_reports(asset_id,organization_id) -> assets(id,organization_id)`
- `damage_reports(borrowing_id,asset_id,organization_id) -> borrowings(id,asset_id,organization_id)`
- `asset_logs(organization_id) -> organizations(id)`
- `asset_logs(asset_id,organization_id) -> assets(id,organization_id)`
- `asset_logs(borrowing_id,asset_id,organization_id) -> borrowings(id,asset_id,organization_id)`
- `asset_logs(damage_report_id,asset_id,organization_id) -> damage_reports(id,asset_id,organization_id)`
- `borrowings(organization_id) -> organizations(id)`
- `borrowings(asset_id,organization_id) -> assets(id,organization_id)`
- `borrowings(user_id) -> users(id)`
- `borrowings(approved_by) -> users(id)`
- `borrowings(rejected_by) -> users(id)`
- `borrowings(cancelled_by) -> users(id)`
- `borrowings(processed_by) -> users(id)`
- `borrower_charges(borrowing_id,asset_id,organization_id,borrower_id) -> borrowings(id,asset_id,organization_id,user_id)`
- `borrower_charges(damage_report_id,borrowing_id,asset_id,organization_id) -> damage_reports(id,borrowing_id,asset_id,organization_id)`
- `borrower_charges(created_by) -> users(id)`
- `borrower_charges(issued_by) -> users(id)`
- `borrower_charges(cancelled_by) -> users(id)`
- `charge_payments(charge_id,organization_id,borrower_id) -> borrower_charges(id,organization_id,borrower_id)`
- `charge_payments(recorded_by) -> users(id)`
- `charge_payments(confirmed_by) -> users(id)`
- `charge_payments(rejected_by) -> users(id)`
- `charge_payments(voided_by) -> users(id)`
- `charge_credits(charge_id,organization_id,borrower_id) -> borrower_charges(id,organization_id,borrower_id)`
- `charge_credits(created_by) -> users(id)`
