# Pedoman Implementasi Organization Inventory

Paket pedoman disusun 27 Oktober 2026. Status: rancangan/draft review; bukan bukti implementasi sudah selesai.

## Mulai membaca

1. [PRD v0.4](PRD_Organization_Inventory_v0.4.md): konsep, persona hipotesis, lingkup F01–F12, user stories dan acceptance criteria.
2. [SRS v1.2](SRS_Organization_Inventory_v1.2.md): spesifikasi fungsional/teknis, role, aturan bisnis dan target data.
3. [Database v1.2](Database/README.md): ERD editable, kamus data teks dan aturan integritas.
4. [Desain dan source frontend](Desain/README.md): MagicPath, tema dan batas prototipe.
5. [Alur implementasi](Implementasi.md): urutan kerja dan validasi.
6. [Arsip proposal](Arsip/README.md): konteks historis yang belum diselaraskan ke finansial v1.2.

## Hierarki acuan

Gunakan klarifikasi eksplisit pemilik proyek yang terbaru, lalu PRD untuk lingkup produk, SRS untuk detail, ERD untuk struktur data, dan MagicPath/source untuk visual. Jika antar-acuan bertentangan secara material, catat pertentangannya dan minta keputusan; jangan diam-diam mengubah lingkup atau menebak keputusan.

PRD/SRS aktif diperbarui sesuai keputusan pemilik proyek. Snapshot sebelum keputusan Gabung langsung disimpan pada `Arsip/keanggotaan-sebelum-gabung-langsung/`. ERD berasal dari lampiran pemilik proyek `Organization_Inventory_v1_2.drawio`. Kamus data dibuat melalui ekstraksi label dan tooltip XML, tanpa mengubah ERD.

## Status penting

- Database target aktif: 14 tabel, 12 inti dan 2 pendukung; tabel framework dihitung terpisah. Gabung langsung member/active menggantikan request keanggotaan dan invitation.
- Implementasi awal Laravel dan migration v1.2 tersedia pada root repo. Status kemampuan dan hasil pengujian aktual dicatat di `../docs/IMPLEMENTATION.md`; dokumen spesifikasi sendiri tetap bukan bukti semua acceptance criteria telah lulus.
- Source MagicPath adalah prototipe lokal; belum terhubung API produksi.
- Persona masih hipotesis sesuai PRD; jangan mengklaim sudah diwawancarai/tervalidasi.
- OD17 telah diputuskan pemilik proyek: persetujuan pinjaman sendiri diizinkan bagi Admin/Staff. Persetujuan finansial sendiri tetap dilarang.

`SHA256SUMS.txt` mencatat checksum berkas dalam folder Pedoman selain file checksum itu sendiri. Perbarui ketika pedoman berubah.

## Referensi baseline dalam SRS

Gambar baseline tetap tersedia pada `assets/` agar tautan gambar SRS terbuka. Artefak SQL yang disebut pada bagian 7.9 diarsipkan pada `Arsip/database_v1_0/`, bukan schema aktif. Diagram target lengkap yang lebih baru tersedia pada `Database/Organization_Inventory_v1_2.drawio`.
