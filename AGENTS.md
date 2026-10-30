# Panduan Agent — Organization Inventory

## Acuan wajib

Mulai dari `Pedoman/README.md`. Baca PRD v0.4 dan bagian SRS v1.2 yang relevan sebelum mengimplementasikan modul. Struktur data: `Pedoman/Database/Organization_Inventory_v1_2.drawio` dan `Pedoman/Database/Kamus_Data_v1_2.md`. Visual dan source referensi: `Pedoman/Desain/README.md` serta `Pedoman/Desain/source/`.

PRD/SRS adalah spesifikasi aktif. Proposal di `Pedoman/Arsip/` adalah historis. SQL baseline lama tidak mencakup finansial v1.2. Jangan menyatakan migration terbaru tersedia sebelum dibuat dan diuji.

## Konsep produk

- Istilah antarmuka yang dipilih pemilik proyek pada 30 Oktober 2026 adalah **Organization**, menggantikan label bawaan Ruang Inventaris/ruang. Istilah lama pada source prototipe dan spesifikasi historis mengacu konsep data yang sama.
- Ruang Inventaris dapat dibuat individu/kelompok; satu akun dapat Admin di ruang sendiri dan Member di ruang lain.
- Role melekat pada membership per ruang, bukan user global atau halaman yang dibuka.
- Katalog membutuhkan membership aktif. Join request berbeda dari borrowing request.
- Satu aset = satu unit fisik dengan kode/QR stabil; tidak ada sistem stok.
- Pengajuan masa depan bukan reservasi. Approval, pengembalian dan inspeksi mengikuti SRS.
- Tagihan late_fee/damage/loss terpisah. Pembayaran manual, tanpa gateway; hanya confirmed mengurangi saldo. Credit/void tidak menghapus histori.

## Pelaksanaan

- Inspeksi repo dan stack sebelum mengubah struktur. Ikuti stack SRS kecuali sudah ada keputusan eksplisit pemilik proyek yang menggantinya.
- Terapkan policy backend, isolasi ruang, constraint database, transaksi dan locking. Menyembunyikan tombol tidak cukup untuk otorisasi.
- Gunakan prototipe MagicPath sebagai referensi visual, bukan backend produksi. Pertahankan tema/palet awal dan hilangkan dependensi data demo ketika API tersedia.
- Ikuti `Pedoman/Implementasi.md`; kerjakan alur FE–BE bertahap dan verifikasi hasilnya.
- Jangan mengarang persona tervalidasi, endpoint tersedia, migration berhasil, hasil uji, atau keputusan produk yang belum ada. Catat benturan material antar-acuan dan keputusan terbuka.
- Gunakan environment contoh tanpa rahasia; jangan commit kredensial produksi atau data pribadi.
- Dokumentasikan perubahan dan jalankan pemeriksaan yang relevan. Laporkan secara tepat hasil serta keterbatasan verifikasi.

## Sinkronisasi dokumen

Jika kebutuhan atau skema berubah, selaraskan PRD/SRS, ERD, kamus data dan migration yang terdampak. Perbarui `Pedoman/SHA256SUMS.txt` sesudah perubahan pedoman.
