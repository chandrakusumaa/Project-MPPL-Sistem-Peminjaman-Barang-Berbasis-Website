# Riwayat Keputusan Produk

Berkas ini menyimpan keputusan yang sudah **tidak berlaku**. Spesifikasi aktif ada pada PRD, SRS, ERD, dan kamus data. Jangan menjadikan isi berkas ini sebagai acuan implementasi.

## RK-01 — Keanggotaan Organization: join request dan undangan diganti gabung langsung

- **Tanggal keputusan:** 30 Oktober 2026, pemilik proyek.
- **Status keputusan lama:** DIGANTIKAN.
- **Keputusan aktif:** pengguna yang login dan terverifikasi menekan **Gabung**; backend langsung membuat membership aktif dengan role `member`. Hanya Admin Organization terkait yang menaikkan Member menjadi Staff/Admin. Pembuat Organization tetap otomatis Admin. Role berlaku per Organization.
- **Tidak berubah:** request, approval, rejection, dan cancellation peminjaman barang (`borrowings`).

### Ketentuan lama yang dihapus (FR-ORG-05..08, BR-06d, D02, D11, UC-05)

| Ketentuan lama | Pengganti aktif |
| --- | --- |
| User mengirim/membatalkan Join Request; maksimal satu request `pending` per pasangan user–organisasi; pengajuan ulang menjadi baris baru | Aksi Gabung membuat membership aktif langsung; tidak ada status pending |
| Admin Approve/Reject Join Request; approve membuat atau mereaktivasi membership `member` | Tidak ada persetujuan. Membership `revoked` **ditolak** saat Gabung; tidak ada reaktivasi mandiri |
| Admin mengundang email dengan role staff/member; token acak 7 hari, `token_hash` unik, resend merotasi token | Tidak ada undangan. Kenaikan role hanya lewat perubahan role oleh Admin |
| Penerimaan undangan memvalidasi hash, status, expiry, dan email terverifikasi yang sama | Endpoint `/invitations/{token}` dihapus (404) dan tidak dapat membuat atau menaikkan membership |
| D02: PENDING/REJECTED berada pada request, bukan membership; ajukan ulang mereaktivasi baris lama | `organization_user.status` hanya `active`/`revoked` |
| D11: token undangan disimpan sebagai `token_hash` | Tidak berlaku |
| Notifikasi: join request ke Admin; keputusan ke pengaju; undangan dikirim/diterima | Anggota baru memberi notifikasi hanya ke Admin; tidak ada email undangan |
| Soft delete Organization/akun membatalkan join request dan mencabut undangan pending | Hanya borrowing `pending` yang dibatalkan |

### Struktur lama yang dihapus

Tabel `membership_requests` dan `organization_invitations` (spesifikasi kolom rinci pada SRS 7.8.4–7.8.5 versi 1.2 dapat dilihat pada riwayat Git sebelum 30 Oktober 2026). Kedua tabel adalah tabel daun: tidak ada FK dari tabel lain yang merujuknya dan tidak ada trigger yang bergantung padanya. FK keluarannya hanya ke `organizations` dan `users`.

| Tabel | Constraint penting |
| --- | --- |
| `membership_requests` | UNIQUE `(organization_id,user_id,pending_guard)`; CHECK `hard_membership_requests_0..1`; FK `organization_id`, `user_id`, `reviewed_by`, `cancelled_by` |
| `organization_invitations` | UNIQUE `token_hash`, UNIQUE `(organization_id,email,pending_guard)`; CHECK `hard_organization_invitations_0..2`; FK `organization_id`, `invited_by`, `accepted_by`, `revoked_by` |

## Penghapusan tabel dan penanganan data historis

Migration: `database/migrations/2026_10_30_000000_drop_membership_requests_and_invitations.php`.

- **Edit migration lama tidak dilakukan.** Database yang sudah dipakai harus menerima perubahan lewat migration baru ini.
- `up()` menghapus `organization_invitations` lalu `membership_requests`.
- **Pengaman data:** jika salah satu tabel masih berisi baris, `up()` berhenti dengan galat. Lanjutkan hanya setelah (1) cadangan dibuat (mis. `mysqldump`), (2) pemilik proyek memutuskan apakah histori perlu diarsipkan di luar database, dan (3) `DROP_LEGACY_MEMBERSHIP_TABLES=true` ditetapkan secara sadar.
- `down()` hanya membuat ulang **struktur** (termasuk CHECK `hard_*`) agar urutan rollback tetap valid. **Rollback tidak memulihkan baris yang sudah dihapus.** Pemulihan data hanya dari cadangan.
- Urutan rollback penuh: migration ini dibatalkan lebih dulu (membuat ulang tabel), lalu `2026_10_27_000002_harden_lifecycle_checks` (menghapus CHECK `hard_*` pada tabel tersebut), lalu `2026_10_27_000000_create_inventory_schema`.
- Keputusan data historis yang **terbuka**: apakah riwayat persetujuan/penolakan join perlu diarsipkan sebagai berkas ekspor. Sampai ada keputusan, migration menolak berjalan pada tabel berisi data tanpa flag di atas. Membership yang sudah aktif pada `organization_user` tidak terpengaruh.
- Jangan memakai `migrate:fresh`, reset database, atau menghapus data transaksi lain.

## Jumlah tabel

Rancangan: **16 → 14 tabel** (12 inti + 2 pendukung: `notifications`, `password_reset_tokens`). Tabel infrastruktur framework dihitung terpisah: `migrations`, `sessions`, `jobs`, `job_batches`, `failed_jobs`, `cache`, `cache_locks`.
