# Referensi Frontend

[MagicPath — WireFrame Orgaization Inventory](https://www.magicpath.ai/files/463253972103364608)

Project ID: `463253972103364608`. Snapshot source disertakan dari pengerjaan desain pada 27 Oktober 2026. Link live bisa berubah setelah snapshot ini.

## Aturan visual

Pertahankan identitas desain awal: aksen merah, permukaan putih, latar abu-abu muda, tipografi, radius, kartu, tabel, sidebar, dan gaya form. Gunakan `source/OrgInventory.css` sebagai referensi token dan styling yang sesungguhnya. Nama produk Organization Inventory; label antarmuka utama **Organization**, sesuai klarifikasi pemilik proyek pada 30 Oktober 2026. Label Ruang Inventaris pada snapshot source merupakan istilah sebelumnya.

Jelajahi Organization pada navbar merupakan daftar publik tanpa tab Organization Saya atau aksi pengelolaan. Organization Saya berada di area akun tersendiri dengan tab Jelajahi yang tetap mempertahankan sidebar akun. Tulisan penjelasan di bawah sidebar dihapus.

Logo navbar mengikuti gambar pemilik proyek pada 30 Oktober 2026: simbol geometris burgundy/navy dan tulisan dua baris "Organizing Inventory". Versi PNG berlatar transparan tersedia di `../../public/images/organizing-inventory-logo.png`. Komponen navbar bersama dipakai halaman publik, akun, dan Organization. Navbar menggunakan lebar halaman penuh dengan inset kiri 24 px pada desktop dan 14 px pada mobile; tinggi gambar logo 56 px dan 48 px dengan rasio asli dipertahankan. Klik logo menuju Beranda. Snapshot MagicPath tetap disimpan sebagai referensi historis untuk bagian visual lainnya.

## Source referensi

- `source/App.tsx`: contoh entry React untuk menampilkan prototipe utama.
- `source/InventoryPrototype.tsx`: wrapper pratinjau dan navigasi antarhalaman.
- `source/InventoryEngine.ts`: source UI dan simulasi interaksi dari prototipe utama.
- `source/OrgInventory.css`: gaya asli prototipe.

Source merupakan bahan implementasi, bukan aplikasi produksi atau paket siap deploy. Untuk mencobanya, diperlukan project React/TypeScript yang menyediakan dependency React dan mengimpor entry tersebut. Folder ini tidak menyertakan package manifest, bundler, atau backend.

Keputusan pemilik proyek 30 Oktober 2026 mengganti keanggotaan menjadi Gabung langsung member/active. Snapshot React lama masih dapat memperlihatkan simulasi request/invitation; gunakan hanya referensi visualnya. Perilaku aktif mengikuti PRD/SRS terbaru: tanpa persetujuan anggota atau undangan, sementara request/approval/rejection/cancellation peminjaman tetap ada.

Simulasi memakai localStorage dan akun demo; ini bukan mekanisme login/permission produksi. Saat implementasi, pecah UI menjadi komponen/rute yang terpelihara; ganti sumber data dan event simulasi dengan API. Kontrol pratinjau Desain/Wireframe bukan fitur produk yang wajib dibawa ke aplikasi.

Akun `demo@orginventory.id` / `demo123` dan `sinta@example.test` / `demo123` adalah kredensial simulasi, bukan kredensial layanan nyata. Jangan gunakan data sensitif dalam prototipe.

## Layar pada canvas

1. Prototype Utama: dashboard manajemen ruang.
2. Halaman Publik & Explore: penjelasan produk dan profil ruang.
3. Beranda & Akun Pengguna: dashboard pribadi, Ruang Saya dan profil.
4. Inventory & Manajemen Aset.
5. Detail & Persetujuan Peminjaman.
6. Masuk, Daftar & Reset Sandi.
7. Tagihan Kerusakan & Pelunasan.
8. Inspeksi Pengembalian.

Source bersama memungkinkan navigasi ke layar finansial melalui menu pratinjau dan sidebar. Frame inspeksi khusus di canvas memiliki fixture tambahannya sendiri; snapshot prototype utama tidak mencakup setiap fixture dari seluruh frame.

## Acuan perilaku

PRD/SRS mengatur perilaku final. Prototipe memvisualisasikan alur dan masih memiliki keterbatasan: kamera QR, email, upload/storage privat, otorisasi server, transaksi database, concurrency serta integrasi API harus diimplementasikan dan diuji. Jangan menyatakan fitur lengkap hanya karena tombol/layar sudah tersedia.

Satu akun dapat menjadi Admin di ruang A dan Member di ruang B. Dashboard pribadi tidak menghapus role. Katalog privat membutuhkan membership aktif. Satu data aset mewakili satu unit fisik. Pembayaran manual dilakukan di luar aplikasi; sistem mencatat dan memverifikasinya tanpa payment gateway.
