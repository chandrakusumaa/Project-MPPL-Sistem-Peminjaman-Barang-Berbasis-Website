# Laporan Validasi Desain Database

**Tanggal:** 20 Oktober 2026
**Hasil:** 280 pemeriksaan struktur dan 38 skenario constraint model lulus.

## Apa yang diuji

- Nama/kolom/constraint, urutan pembuatan FK, tipe FK yang sama, dan setiap target FK memiliki PK/UNIQUE lengkap.
- Keselarasan daftar tabel dengan DDL, guard deterministik, tidak ada DROP/penonaktifan FK, dan tidak ada cascade penghapusan histori.
- Ekspresi CHECK, FK gabungan, UNIQUE bersyarat, dan retensi histori dijalankan pada model SQLite in-memory yang diturunkan dari spesifikasi yang sama.
- ERD dirender dari DOT melalui Graphviz/Viz dan diperiksa secara visual.

## Batas verifikasi

**Server/client MySQL dan runtime PHP/Laravel tidak tersedia pada lingkungan pembuatan. DDL dan trigger belum diuji import/eksekusi langsung pada MySQL.** Model SQLite menguji perilaku relasional yang sepadan, bukan kompatibilitas parser, ENUM, collation, timezone, atau trigger MySQL. Perhitungan denda kalender, locking/concurrency, minimal satu Admin, Policy/Gate, scheduler, dan pengiriman email belum diuji sebagai aplikasi.

Sebelum memakai skema pada proyek, import pada database MySQL kosong, jalankan skenario integrasi di bagian 11 `database_design.md`, dan periksa query diagnostik. Angka pengujian di laporan ini bukan bukti bahwa seluruh kebutuhan produk sudah lulus.

## Skenario constraint

| Skenario | Hasil |
|---|---|
| Kategori organisasi lain tidak dapat dipasang ke aset | PASS — ditolak: FOREIGN KEY |
| Aset organisasi lain tidak dapat dipinjam melalui scope salah | PASS — ditolak: FOREIGN KEY |
| Damage report tidak boleh mereferensikan transaksi aset berbeda | PASS — ditolak: FOREIGN KEY |
| Satu pasangan user–organisasi memiliki satu membership | PASS — ditolak: UNIQUE |
| Nama kategori unik dalam organisasi yang sama | PASS — ditolak: UNIQUE |
| Nama kategori sama boleh dipakai organisasi berbeda | PASS — diizinkan |
| Join pending ganda ditolak | PASS — ditolak: UNIQUE |
| Histori join rejected berulang diperbolehkan | PASS — diizinkan |
| Request pending ganda user dan aset yang sama ditolak | PASS — ditolak: UNIQUE |
| Dua user boleh pending untuk aset yang sama | PASS — diizinkan |
| Borrowed dan overdue tetap berbagi batas satu pinjaman aktif | PASS — ditolak: UNIQUE |
| Peminjaman returned berulang user/aset yang sama diizinkan | PASS — diizinkan |
| Approval tanpa actor dan waktu ditolak | PASS — ditolak: ck_borrowing_approval |
| Penolakan tanpa actor ditolak | PASS — ditolak: ck_borrowing_reject |
| Penolakan tanpa alasan ditolak | PASS — ditolak: ck_borrowing_reject |
| Tanggal jatuh tempo harus sesudah tanggal mulai | PASS — ditolak: ck_borrowing_dates |
| Alasan peminjaman pendek ditolak | PASS — ditolak: ck_borrowing_reason |
| Denda final negatif ditolak | PASS — ditolak: ck_borrowing_fine |
| Override denda tanpa alasan ditolak | PASS — ditolak: ck_borrowing_override |
| Override denda dengan alasan diperbolehkan | PASS — diizinkan |
| Nilai kalkulasi denda harus sama dengan snapshot x hari | PASS — ditolak: ck_borrowing_calculation |
| Inspeksi minor tanpa catatan ditolak | PASS — ditolak: ck_borrowing_return_notes |
| Waktu return sebelum approve ditolak | PASS — ditolak: ck_borrowing_return_time |
| Dua maintenance aktif untuk satu aset ditolak | PASS — ditolak: UNIQUE |
| Laporan anggota boleh belum dinilai dan tanpa borrowing | PASS — diizinkan |
| Sumber inspeksi return wajib terkait borrowing | PASS — ditolak: ck_damage_source |
| Laporan otomatis satu inspeksi tidak boleh ganda | PASS — ditolak: UNIQUE |
| Log tidak boleh memakai scope organisasi yang salah | PASS — ditolak: FOREIGN KEY |
| Event key scheduler/log tidak dapat digandakan | PASS — ditolak: UNIQUE |
| Penerimaan setelah undangan kedaluwarsa ditolak | PASS — ditolak: ck_invitation_accept |
| Undangan pending ganda email/org ditolak | PASS — ditolak: UNIQUE |
| Membership revoked tanpa timestamp ditolak | PASS — ditolak: ck_membership_lifecycle |
| Aset major damage tidak boleh tersedia untuk dipinjam | PASS — ditolak: ck_asset_usable |
| Kerusakan major yang terjadi selama dipinjam tetap dapat direpresentasikan | PASS — diizinkan |
| Penghapusan fisik user yang masih direferensikan ditolak | PASS — ditolak: FOREIGN KEY |
| Event sistem tanpa actor diperbolehkan | PASS — diizinkan |
| Notifikasi event yang sama per penerima dideduplikasi | PASS — ditolak: UNIQUE |
| Soft delete/revoke tidak menghapus histori peminjaman | PASS — diizinkan |

## Ringkasan struktur

- Tabel: 13.
- Kolom: 172.
- Foreign key: 35.
- CHECK: 39.
- UNIQUE di luar PK: 19.
