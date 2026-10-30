# Database v1.2

Acuan: [SRS v1.2](../SRS_Organization_Inventory_v1.2.md), terutama bagian 7 dan 7.10.

- [ERD editable](Organization_Inventory_v1_2.drawio): 5 halaman; 14 tabel (12 inti, 2 pendukung).
- [Kamus data teks](Kamus_Data_v1_2.md): ekstraksi atribut, enum, nullability, generated expression dan FK dari ERD, agar dapat dibaca tanpa aplikasi diagram.

## Status SQL

Migration v1.2 tersedia pada `../../database/migrations/` dengan DDL aktif 14 tabel pada `../../database/schema/inventory-v1.2.sql`. Migration awal memakai snapshot 16 tabel yang dibekukan di `../../database/schema/2026-10-27-inventory-legacy.sql` agar urutan migration lama tetap dapat direproduksi; migration baru `2026_10_30_000003_remove_legacy_membership_flows.php` menghapus dua tabel keanggotaan lama pada database yang sudah digunakan. DDL dan migration telah dijalankan pada MySQL 8.4; hasil integritas dan concurrency dicatat di `../../docs/IMPLEMENTATION.md`. SQL baseline v1.0 dari pekerjaan sebelumnya hanya mencakup 13 tabel dan tidak boleh digunakan seolah-olah sudah mendukung finansial. Paket ini sengaja tidak memasukkan SQL baseline sebagai schema aktif.

Perubahan berikutnya tetap harus mengikuti SRS/ERD v1.2 dan diuji pada MySQL target. Diagram dan kamus data saja belum membuktikan constraint sudah ditegakkan.

## Constraint dan aturan yang harus diterapkan

- UNIQUE membership (organization_id,user_id); kategori unik per ruang; slug ruang dan kode aset unik sesuai SRS.
- Gabung langsung membuat member/active di bawah transaksi/lock; role tidak berasal dari payload. Retry aktif tidak mengubah role/joined_at; revoked ditolak. Audit membership baru dan perubahan role tetap tersedia.
- Generated guard harus disertai UNIQUE: pinjaman aktif per aset, pending/aktif per user-aset, maintenance aktif per aset, serta laporan inspeksi per borrowing. Ikuti ekspresi/status pada kamus data dan kombinasi key dalam SRS.
- FK gabungan menjaga kategori, aset, borrowing, laporan, charge, pembayaran, serta kredit berada pada ruang dan objek yang sama. Definisikan UNIQUE pendukung pada parent sebagaimana SRS 7.10.
- Tagihan: UNIQUE(organization_id,idempotency_key), UNIQUE(borrowing_id,charge_type,active_guard), UNIQUE(id,organization_id,borrower_id). Damage dan loss aktif tidak boleh bersamaan pada kejadian yang sama; validasi dengan transaksi dan lock borrowing.
- Payment dan credit: idempotency key unik per ruang. Nominal Rupiah bulat positif; jangan menggunakan floating point untuk uang.
- Hanya pembayaran confirmed yang mengurangi saldo. Sisa = amount - total credit - total confirmed payment. Pending/rejected/voided tidak dihitung. Status unpaid/partial/paid/waived merupakan turunan; cancelled adalah lifecycle terpisah.
- Damage/loss memerlukan laporan terkait, dasar kebijakan/persetujuan, penilaian dan bukti. Laporan kerusakan tidak otomatis berarti peminjam bertanggung jawab.
- Issued charge tidak diubah nominalnya. Koreksi melalui credit, cancellation, atau void sesuai syarat SRS; jangan hard delete histori keuangan.
- Terapkan lock dan transaksi untuk approval, return, penerbitan, konfirmasi, void dan credit. Validasi overpayment memakai sisa terbaru dalam transaksi.
- Admin tidak boleh menetapkan/menyetujui kewajiban finansial sendiri. Staff hanya mengusulkan draft dan mencatat pembayaran pending.
- Blok arsip/hapus ruang, hapus akun dan revoke peminjam selama ada aktivitas/kewajiban terbuka sesuai SRS; pertahankan minimal satu Admin aktif.

Daftar ini merupakan panduan implementasi, bukan pengganti keseluruhan SRS. Uji juga aturan nullable per status dan pelestarian histori.

## Perubahan keanggotaan 30 Oktober 2026

`organization_user` tetap menyimpan role, status, joined_at dan histori revoked, dengan UNIQUE pasangan Organization–user. Dua tabel keanggotaan lama dihapus setelah dependensi aplikasi dibersihkan. Jika berisi data, migration mengambil lock WRITE dan membuat arsip JSONL, DDL, jumlah baris dan checksum pada disk privat local di `legacy-membership/` sebelum DROP. Arsip tidak boleh dipublikasikan atau dimasukkan ke Git.

Rollback hanya membuat ulang kedua tabel beserta FK/CHECK awal dalam keadaan kosong; tidak mengaktifkan invitation dan tidak memulihkan data. Pemulihan histori dari arsip adalah operasi terpisah yang harus memvalidasi FK dan mengabaikan kolom generated ketika mengimpor JSONL. Salinan spesifikasi sebelum perubahan tersedia di [arsip keanggotaan](../Arsip/keanggotaan-sebelum-gabung-langsung/README.md).
