# Histori keputusan keanggotaan sebelum Gabung langsung

Snapshot berikut menyimpan kebutuhan lama sebelum keputusan pemilik proyek pada 30 Oktober 2026. Isinya merupakan **histori, bukan spesifikasi aktif**. Approval keanggotaan, invitation, serta dua tabel terkait pada snapshot ini telah digantikan Gabung langsung member/active. Request, approval, rejection dan cancellation peminjaman barang tetap berlaku pada spesifikasi aktif.

- [PRD sebelum perubahan](PRD_Organization_Inventory_v0.4.md)
- [SRS sebelum perubahan](SRS_Organization_Inventory_v1.2.md)
- [ERD editable 16 tabel sebelum perubahan](Organization_Inventory_v1_2.drawio)
- [Kamus data sebelum perubahan](Kamus_Data_v1_2.md)
- [DDL historis yang dibekukan untuk migration awal](../../../database/schema/2026-10-27-inventory-legacy.sql)

Dokumen aktif berada pada root Pedoman dan Database dengan target **14 tabel (12 inti + 2 pendukung)**, di luar tabel framework. Snapshot ini bukan backup database. Data historis request/invitation yang benar-benar ada di suatu deployment diarsipkan migration ke disk privat sebelum kedua tabel dihapus.
