# Alur Kerja Implementasi

## Persiapan

Baca AGENTS.md dan indeks Pedoman. Inspeksi struktur repo dan dependency aktual sebelum memilih atau mengganti stack. Pada saat paket ini disusun, repo hanya berisi pedoman; belum ada aplikasi atau migration yang dapat dinyatakan berjalan.

## Urutan kerja

1. Turunkan matriks kebutuhan PRD F01–F12 dan acceptance criteria SRS ke tugas implementasi; identifikasi keputusan terbuka.
2. Siapkan stack sesuai SRS dan kondisi repo. Definisikan kontrak API untuk autentikasi, ruang/role, inventaris, borrowing, inspeksi, kerusakan, finansial, notifikasi dan laporan.
3. Pertahankan migration historis dan tambah migration perubahan ke 14 tabel aktif, key/index/check/FK dan generated guard yang benar. Jalankan migration serta uji integritas pada database target.
4. Implementasikan satu alur FE–BE sampai terhubung sebelum beralih: akun dan buat ruang; keanggotaan; aset dan kategori; request/approval; return/maintenance; finansial; notifikasi/laporan.
5. Adaptasi source MagicPath menjadi komponen/rute. Terapkan tema asli; ganti simulasi localStorage dengan API. Jangan memakai akses demo sebagai permission backend.
6. Verifikasi role per ruang dan pemisahan data, validasi form, loading/empty/error/success, direct URL, serta tampilan mobile.
7. Dokumentasikan setup, environment contoh tanpa rahasia, perintah migration, menjalankan aplikasi, dan hasil uji yang benar-benar dilakukan.

## Skenario minimum

- Akun baru terverifikasi membuat ruang dan menjadi Admin; perpindahan dashboard tidak menghapus role.
- Admin A yang Member di B tidak dapat mengelola B. Non-anggota tidak bisa membuka katalog privat.
- Gabung langsung membuat Member aktif bagi user terverifikasi. Retry aktif idempoten; revoked/arsip/soft delete ditolak. Borrowing pending/approval/rejection/cancellation tetap terpisah dan harus tetap lulus uji.
- Uji dua join MySQL bersamaan, payload role palsu, promosi hanya Admin lokal, last Admin, audit/notifikasi terbatas, serta backup/rollback struktur dua tabel lama.
- Satu unit aset tidak memiliki dua pinjaman aktif. Request masa depan bukan reservasi; approval pada tanggal mulai sesuai SRS.
- Return good/minor/major/lost menghasilkan kondisi dan status yang sesuai; inspeksi final tidak dapat diproses dua kali.
- Kerusakan Rp150.000, confirmed Rp50.000: sisa Rp100.000. Pending Rp100.000 belum mengurangi saldo; setelah konfirmasi menjadi lunas.
- Overpayment ditolak; void, credit, cancellation dan retry idempoten tetap menghasilkan histori yang dapat ditelusuri.
- Staff tidak menerbitkan charge/konfirmasi/credit/void. Admin peminjam tidak menyetujui kewajiban sendiri.
- Arsip/hapus/revoke ditolak selama pinjaman atau kewajiban terbuka sebagaimana SRS.
- Uji transaksi paralel di database target. Mock/SQLite/prototipe tidak membuktikan constraint dan locking MySQL telah benar.

Pengujian lengkap tetap mengikuti SRS, termasuk AC12-01..14. Tandai secara eksplisit pemeriksaan yang belum dapat dijalankan.
