# PANDUAN UTAMA — ORGANIZATION INVENTORY

Dokumen ini adalah acuan batas sistem (Guardrails). DILARANG membuat fitur atau halaman di luar dokumen ini.

## 1. Konsep Dasar Produk
- **Istilah Resmi**: Gunakan istilah **Organization** (menggantikan Ruang Inventaris/Toko/Rental).
- **Role Melekat per Ruang (Membership-based)**: Role bukan akun global. Satu user bisa jadi Admin di Organization A, Staff di Organization B, dan Member di Organization C.
- **Satu Aset = Satu Unit Fisik**: Setiap barang adalah 1 unit fisik nyata dengan kode serial fisik unik (misal: `AST-0024`). **TIDAK ADA sistem stok angka/kuantitas** (dilarang ada "Stok: 50 pcs"). Sistem tidak menggunakan konsep scan QR.
- **Gabung Langsung (Direct Join)**: User login langsung gabung menjadi Member Aktif. **TIDAK ADA sistem undangan (invitation) atau approval member**.
- **Peminjaman**: Member mengajukan pinjam -> Staff menyetujui -> Serah terima barang fisik -> Inspeksi pengembalian fisik.
- **Finansial Manual**: Denda keterlambatan dan kerusakan dibayar manual (transfer bank ke rekening organisasi). User upload foto bukti bayar, saldo HANYA berkurang jika telah dikonfirmasi manual oleh Admin. **TIDAK ADA payment gateway otomatis (Midtrans/Stripe)**.

## 2. Aturan Desain (Design System)
- **Aesthetic**: Minimalis & elegan ala Mobbin / Swiss typography.
- **Warna Utama**: Crimson Red (`#dc2626`), Slate Black (`#0f172a`), Background Off-White (`#f8fafc`), Card Putih Bersih (`#ffffff`) dengan border halus `1px solid #e2e8f0`.
- **DILARANG**:
  - Jangan gunakan gradie
  n warna-warni (indigo, ungu, hijau toska berlebihan).
  - Jangan gunakan emoji pada tombol atau kartu fitur (gunakan icon SVG garis).
  - Jangan buat tombol "Beralih Mode Demo".

## 3. Struktur Halaman yang Diizinkan (Kunci Lingkup)
1. `index.html` (Landing page publik)
2. `explore-orgs.html` (Daftar ruang publik)
3. `org-public-profile.html` (Profil publik organisasi & tombol gabung langsung)
4. `auth.html` (Login, Register, Lupa Sandi)
5. `user-dashboard.html` (Dashboard akun pribadi: barang yang dibawa, tagihan pribadi)
6. `my-organizations.html` (Daftar ruang yang diikuti + modal buat ruang baru)
7. `my-borrowings.html` (Riwayat pinjaman pribadi lintas ruang)
8. `my-charges.html` (Daftar denda pribadi + modal upload bukti transfer)
9. `workspace.html` (Satu halaman Workspace Organisasi berbasis TAB untuk Member, Staff, dan Admin)
10. `asset-timeline.html` (Riwayat perjalanan & audit trail 1 unit fisik aset)