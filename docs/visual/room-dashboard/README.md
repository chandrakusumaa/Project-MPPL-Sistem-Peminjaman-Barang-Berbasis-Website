# Penyelarasan visual — tahap 1

Tanggal pemeriksaan: 30 Oktober 2026. Lingkup: chrome manajemen ruang dan Dashboard Ruang Inventaris. Halaman modul lainnya tetap memakai action, validasi, pagination, dan permission yang sudah ada.

## Referensi dan batas akses

Referensi live https://www.magicpath.ai/files/463253972103364608 tidak dapat diambil melalui jaringan environment: HTTP CONNECT 403. Karena itu, pemeriksaan visual memakai render browser dari snapshot resmi di Pedoman/Desain/source/InventoryEngine.ts dan OrgInventory.css.

Screenshot referensi berasal dari aplikasi React referensi lokal, bukan screenshot situs MagicPath live. Toolbar review Desain/Wireframe disembunyikan hanya pada host screenshot; margin bawaan body dan offset toolbar 44 px dinormalisasi ke chrome aplikasi. Isi, angka, CSS desain, dan source referensi tidak diedit.

Viewport: desktop 1440×1000 dan mobile 390×844, device scale factor 1, Chromium headless. Tampilan referensi dan implementasi diperiksa melalui screenshot aktual, bukan hanya keberhasilan build.

## Perbedaan sebelum pengeditan

- Logo/sidebar akun menempati seluruh tinggi kiri; topbar dimulai setelah sidebar. Referensi menempatkan topbar 74 px melintasi seluruh halaman, diikuti sidebar ruang 245 px.
- Navigasi berupa menu akun dan tab horizontal; belum ada kelompok WORKSPACE/PENGELOLAAN/AKUN, konteks ruang/role, atau status aktif sidebar.
- Halaman awal ruang adalah katalog. Grafik besar muncul sebelum statistik dan menggeser konten utama ke bawah.
- Belum ada susunan empat kartu Total/Tersedia/Dipinjam/Pending, tabel permintaan dan inventaris terbaru berdampingan, serta kartu pintasan sesuai dashboard referensi.
- Judul, padding kartu, CTA, ikon glyph, dan pemilih ruang berbeda; pemilih ruang belum tersedia.
- Mobile memakai deretan menu akun horizontal, bukan drawer ruang yang dapat dibuka/tutup.

## Hasil penyelarasan

- Layout dokumen memilih konteks publik, akun pribadi, atau ruang. Markup akun lama dipertahankan dalam partial tersendiri.
- Chrome ruang, navigasi, ikon SVG dari source referensi, status, dan kartu statistik dibuat sebagai komponen Blade bersama. CSS baru dibatasi pada namespace .oi-room; style publik terpisah pada .oi-public.
- Topbar, sidebar, kartu konteks, judul, pemilih ruang, CTA, statistik, dua tabel, kartu pintasan, dan panel tagihan mengikuti struktur referensi.
- Nama, role, statistik, permintaan, aset, anggota, dan saldo berasal dari MySQL. Tidak ada angka contoh atau localStorage bisnis dari prototipe yang dibawa ke view aplikasi.
- Grafik lama tetap dapat dibuka pada bagian Analitik inventaris, di bawah ringkasan utama.
- Mobile memakai drawer yang dapat ditutup melalui tombol, backdrop, Escape, atau navigasi. Tabel memiliki scroll horizontal sendiri.

## Pengukuran desktop

Nilai berasal dari bounding box DOM dalam browser pada viewport yang sama. Browser test membandingkan origin dan lebar komponen utama dengan toleransi 1 px; tinggi elemen tetap juga diperiksa.

| Elemen | Referensi | Implementasi |
| --- | --- | --- |
| Topbar | y=0, tinggi 74 px | y=0, tinggi 74 px |
| Sidebar | x=0, y=74, lebar 245 px | x=0, y=74, lebar 245 px |
| Judul/header konten | x=283, y≈166.59 | x=283, y≈166.59 |
| Statistik | x=283, y≈251.67 | x=283, y≈251.67 |
| Kartu statistik pertama | lebar 268.5, tinggi ≈144.98, radius 16 px | lebar 268.5, tinggi ≈144.98, radius 16 px |
| Panel tabel pertama | x=283, y≈418.66, lebar 550.5 px | x=283, y≈418.66, lebar 550.5 px |

Padding konten: 28 px atas / 38 px kiri-kanan. Gap statistik 15 px; panel/pintasan 18 px. Warna utama #dc2626, latar #f7f8fb, teks #172033, muted #667085, border #e9edf3; font stack dan ikon mengikuti source. Angka pengukuran lengkap ada di [metrics.json](metrics.json).

## Screenshot

| Viewport | Referensi source | Sebelum | Sesudah |
| --- | --- | --- | --- |
| 1440×1000 | [referensi](reference-desktop.png) | [sebelum](before-desktop.png) | [sesudah](after-desktop.png) |
| 390×844 | [referensi](reference-mobile.png) | [sebelum](before-mobile.png) | [sesudah](after-mobile.png) |

Tambahan: [halaman desktop penuh](after-desktop-full.png) dan [drawer mobile terbuka](after-mobile-menu.png).

## Perubahan data tampilan

Tidak ada migration, model domain, policy, atau action transaksi yang diubah.

- Workspace menambah query berscope organisasi untuk statistik dashboard, empat permintaan terbaru, lima aset terbaru, overdue, laporan/maintenance, anggota, dan ringkasan finansial. Pembayaran pending tetap tidak mengurangi saldo.
- Concern SwitchesWorkspaces hanya mengizinkan perpindahan ke membership aktif dan memeriksa policy target. Role tidak dimutasi saat berpindah.
- Query string tab menghubungkan navigasi ruang ke view yang ada. Admin/Staff membuka dashboard; Member dan URL detail aset tetap membuka katalog.
- Home membaca query listing=mine agar tautan Ruang Saya membawa konteks yang benar.
- Reports memakai chrome ruang yang sama; NotificationBell hanya mendapat varian tampilan.
- CTA tambah aset dan tinjau permintaan membuka form/tab lama. Kategori/Lokasi mengarah ke field aset yang sudah tersedia, bukan menambahkan modul bisnis baru.

## Pemeriksaan aktual

- Build frontend lulus.
- 49 tes Pest lulus dengan 191 assertion pada MySQL, termasuk regresi scope dashboard, role Member, pergantian role antar ruang, action CTA/tinjau, dan saldo yang mengabaikan pending.
- Pint lulus untuk 74 file PHP.
- Smoke Chromium lama lulus: login, navigasi Livewire, pembuatan aset, logout, desktop/mobile.
- Browser test dashboard lulus: pengukuran referensi, screenshot, CTA dan fokus field, perpindahan ke ruang uji lalu kembali, konteks pribadi, laporan, chart yang dibuka, drawer/Escape, dan permission Admin/Staff/Member.
- Browser test membuat lalu soft-delete hanya ruang uji kosong yang dibuatnya sendiri. Source referensi dan data ruang yang sudah ada tidak diubah agar menyerupai angka demo.
- Tabel mobile memiliki overflow internal; halaman tidak melebar melewati 390 px.

Jalankan aplikasi dengan setup yang sudah ada dan demo seed, lalu:

    npm run build
    docker compose exec -T app vendor/bin/pest --compact
    docker compose exec -T app vendor/bin/pint --test
    node tests/Browser/smoke.mjs
    node tests/Browser/room-dashboard.mjs

Untuk mengambil ulang pembanding dari harness referensi lokal yang telah disiapkan:

    REFERENCE_URL=http://127.0.0.1:5173 node tests/Browser/room-dashboard.mjs

## Perbedaan yang tetap ada / belum diperiksa

- Data aplikasi berbeda dari fixture referensi: jumlah/baris/nama aset, kode ULID, request kosong, avatar pengguna, dan saldo. Tinggi panel serta lebar kolom mengikuti data tersebut; angka tidak dipalsukan untuk menyamakan screenshot.
- Lonceng notifikasi tetap tersedia pada topbar. Analitik dan status operasional tambahan tetap tersedia di bawah ringkasan, untuk mempertahankan fungsi aplikasi.
- Pada mobile, selector dan CTA menyesuaikan satu baris jika cukup ruang; drawer memakai backdrop dan menyediakan logout. Referensi source memiliki wrapping tombol yang berbeda.
- Detail halaman inventaris, form, peminjaman, finansial, dan pengaturan belum diselaraskan seluruhnya; tahap ini menyelesaikan shell bersama dan dashboard.
- Tidak ada klaim pixel-perfect terhadap situs live MagicPath, karena akses live diblokir. Screenshot ini memverifikasi snapshot source yang disertakan repo.

## File yang diubah/ditambahkan

- app/Livewire/Concerns/SwitchesWorkspaces.php
- app/Livewire/Home.php
- app/Livewire/NotificationBell.php
- app/Livewire/Reports.php
- app/Livewire/Workspace.php
- resources/css/app.css
- resources/css/room.css
- resources/views/components/feedback.blade.php
- resources/views/components/icon.blade.php
- resources/views/components/layout.blade.php
- resources/views/components/layouts/account.blade.php
- resources/views/components/layouts/public.blade.php
- resources/views/components/room/frame.blade.php
- resources/views/components/room/nav-item.blade.php
- resources/views/components/room/sidebar.blade.php
- resources/views/components/room/stat.blade.php
- resources/views/components/room/status.blade.php
- resources/views/components/room/topbar.blade.php
- resources/views/livewire/notification-bell.blade.php
- resources/views/livewire/reports.blade.php
- resources/views/livewire/room-dashboard.blade.php
- resources/views/livewire/workspace.blade.php
- tests/Browser/room-dashboard.mjs
- tests/Browser/smoke.mjs
- tests/Feature/InventoryTest.php

Artefak perbandingan: README ini, metrics.json, dan seluruh PNG pada direktori ini.
