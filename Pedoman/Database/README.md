# Database v1.2

Acuan: [SRS v1.2](../SRS_Organization_Inventory_v1.2.md), terutama bagian 7 dan 7.10.

- [ERD editable](Organization_Inventory_v1_2.drawio): 5 halaman; 16 tabel (14 inti, 2 pendukung).
- [Kamus data teks](Kamus_Data_v1_2.md): ekstraksi atribut, enum, nullability, generated expression dan FK dari ERD, agar dapat dibaca tanpa aplikasi diagram.

## Status SQL

Migration v1.2 tersedia pada `../../database/migrations/` dengan DDL awal pada `../../database/schema/inventory-v1.2.sql`. DDL dan migration telah dijalankan pada MySQL 8.4; hasil integritas dan concurrency dicatat di `../../docs/IMPLEMENTATION.md`. SQL baseline v1.0 dari pekerjaan sebelumnya hanya mencakup 13 tabel dan tidak boleh digunakan seolah-olah sudah mendukung finansial. Paket ini sengaja tidak memasukkan SQL baseline sebagai schema aktif.

Perubahan berikutnya tetap harus mengikuti SRS/ERD v1.2 dan diuji pada MySQL target. Diagram dan kamus data saja belum membuktikan constraint sudah ditegakkan.

## Constraint dan aturan yang harus diterapkan

- UNIQUE membership (organization_id,user_id); kategori unik per ruang; slug ruang dan kode aset unik sesuai SRS.
- Generated guard harus disertai UNIQUE: request/undangan pending, pinjaman aktif per aset, pending/aktif per user-aset, maintenance aktif per aset, serta laporan inspeksi per borrowing. Ikuti ekspresi/status pada kamus data dan kombinasi key dalam SRS.
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
