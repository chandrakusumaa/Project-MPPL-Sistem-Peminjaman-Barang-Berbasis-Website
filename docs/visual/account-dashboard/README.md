# Dashboard pribadi, sidebar, navbar, dan tagihan

Pemeriksaan 30 Oktober 2026, Chromium pada viewport **1440×1000** dan **390×844**. Acuan adalah snapshot MagicPath dalam `Pedoman/Desain/source/InventoryEngine.ts` (`renderUserDashboard`, `shell`, `userSidebar`) dan `OrgInventory.css`, ditampilkan melalui harness React lokal. Akses link MagicPath live dari environment ini tidak berhasil; pemeriksaan visual memakai snapshot source lokal, bukan canvas live terbaru.

## Masalah sebelum perubahan

- `/dashboard` dan `/organizations` sama-sama merender `Home`, termasuk form pembuatan ruang dan pencarian. Tidak ada ringkasan aktivitas pribadi yang berbeda.
- Header akun berisi breadcrumb; tidak menyediakan tiga tujuan navigasi utama. Daftar sidebar memakai ikon teks dan mempunyai menu Notifikasi terpisah.
- Tagihan pribadi hanya menjadi teks di bawah transaksi peminjaman; belum ada total dan riwayat pembayaran lintas ruang.

## Hasil visual

| Pemeriksaan | Referensi / sebelum | Implementasi |
| --- | --- | --- |
| Desktop sebelum | [before-desktop.png](before-desktop.png) | [after-desktop.png](after-desktop.png), [halaman penuh](after-desktop-full.png) |
| Source MagicPath desktop | [reference-desktop.png](reference-desktop.png) | Shell, judul, empat kartu ringkasan, Organization, dan riwayat memakai token sumber |
| Mobile | [before-mobile.png](before-mobile.png), [reference-mobile.png](reference-mobile.png) | [after-mobile.png](after-mobile.png), [dropdown/sidebar](after-mobile-menu.png) |
| Discovery terpisah | — | [explore-desktop.png](explore-desktop.png) |
| Tagihan & pembayaran | — | [charges-desktop.png](charges-desktop.png), [halaman penuh](charges-desktop-full.png), [mobile](charges-mobile.png) |
| Form buat ruang mobile | — | [create-mobile.png](create-mobile.png) |

Screenshot biasa memiliki ukuran viewport di atas; file `*-full.png` merekam seluruh halaman pada lebar viewport desktop. Data implementasi berasal dari MySQL melalui akun/ruang pengujian terisolasi: dua Organization aktif dengan role Admin/Member, empat transaksi, dua denda keterlambatan dari inspeksi, pembayaran terkonfirmasi/pending/voided. Fixture dibuat melalui action aplikasi dan dibersihkan setelah pengujian; angka tersebut tidak ditulis ke UI produksi.

[metrics.json](metrics.json) mencatat hasil pengukuran browser: topbar 74px, sidebar 245px dengan padding 23px/15px, judul pada x=283/y=102, kartu statistik pada x=283/y≈187, padding 17px/18px dan radius 16px. Posisi x/y serta lebar topbar, sidebar, judul, statistik, dan kartu pertama sesuai snapshot sumber dalam toleransi 1px. Tinggi sidebar mengikuti panjang konten masing-masing halaman.

Perubahan yang sengaja mengikuti permintaan terbaru: label Organization, dropdown daftar Organization di sidebar, ringkasan pinjaman berjalan, lonceng di samping profil, serta tiga tautan navbar tetap terlihat pada mobile. Karena navbar mobile kini dua baris, tingginya 118px dan posisi awal konten berbeda dari prototipe yang menyembunyikan tautan navbar. Identitas, role, jumlah kartu/row, dan wrapping konten mengikuti data backend sehingga tinggi seluruh halaman berbeda dari data simulasi MagicPath. Tidak ada klaim bahwa seluruh halaman identik piksel demi piksel dengan canvas.

## Pemeriksaan fungsional

- **62 pengujian Pest lulus, 275 assertion**, pada MySQL. Tujuh pengujian akun baru mencakup scope akun, pemisahan halaman, navbar/lonceng, saldo finansial, filter, dan pagination pembayaran yang independen.
- Build frontend, Pint, dan pemeriksaan whitespace lulus.
- `personal-account.mjs`: login, empat statistik akun, filter riwayat, tiga tujuan navbar, dropdown Organization, notifikasi/profil, saldo Rp300.000 − Rp200.000 = Rp100.000 (pending Rp25.000 tidak mengurangi saldo), filter tagihan/pembayaran, serta isolasi role Admin/Member dan penolakan ruang asing.
- Pada 390×844: tiga tautan navbar terlihat, drawer/dropdown dapat dibuka, ditutup dengan Escape/backdrop, lonceng dapat dibuka, form profil/buat ruang terbaca, dan halaman tidak overflow horizontal. Tabel mempunyai area scroll tersendiri.
- Regresi `room-dashboard.mjs` dan `smoke.mjs` lulus: pembuatan ruang/aset, pindah ruang, laporan, navigasi mobile, serta permission Admin/Staff/Member tetap bekerja.

Jalankan ulang terhadap runtime development Compose:

```bash
DOCKER_CONFIG=/workspace/.onboarding/docker BROWSER_DOCKER=1 node tests/Browser/personal-account.mjs
VISUAL_OUTPUT=/tmp/oi-room-regression node tests/Browser/room-dashboard.mjs
node tests/Browser/smoke.mjs
```

Pada PHP lokal, hilangkan `BROWSER_DOCKER=1`; gunakan `PHP_BINARY` jika executable PHP bukan `php`. Untuk ikut membandingkan source melalui harness lokal yang sudah berjalan, tambahkan `REFERENCE_URL=http://127.0.0.1:5173`. Browser fixture menolak environment selain local/testing dan menghapus hanya data run-nya sendiri. Jangan menjalankan dua browser suite yang memodifikasi data secara bersamaan.

## Perubahan backend dan batas pemeriksaan

Backend ditambah query baca untuk dashboard/tagihan lintas ruang milik akun, relasi Eloquent untuk nama ruang/aset/histori, named routes, serta pengalihan sesudah pembuatan ruang agar sidebar diperbarui. Tidak ada migration, perubahan schema, policy, maupun aturan approval/pembayaran. Draft/cancelled tidak menambah total tagihan aktif; hanya confirmed dan credits mengurangi saldo sesuai action yang sudah ada.

Visual diperiksa untuk layar pada laporan ini; belum mencakup seluruh kombinasi filter, ukuran perangkat, dan semua halaman produk. SMTP nyata, perangkat kamera, dan pembayaran eksternal tidak menjadi lingkup perubahan ini.

## Berkas yang diubah

Daftar source, pengujian, dan dokumentasi; PNG pada tabel screenshot di atas termasuk dalam commit.

- `Pedoman/PRD_Organization_Inventory_v0.4.md`
- `Pedoman/SHA256SUMS.txt`
- `Pedoman/SRS_Organization_Inventory_v1.2.md`
- `README.md`
- `app/Livewire/Home.php`
- `app/Livewire/PersonalCharges.php`
- `app/Livewire/PersonalDashboard.php`
- `app/Models/BorrowerCharge.php`
- `app/Models/Borrowing.php`
- `app/Models/ChargePayment.php`
- `app/Models/Membership.php`
- `app/View/Components/OrganizationMenu.php`
- `docs/IMPLEMENTATION.md`
- `docs/visual/account-dashboard/README.md`
- `docs/visual/account-dashboard/metrics.json`
- `resources/css/account.css`
- `resources/css/app.css`
- `resources/css/room.css`
- `resources/views/components/account/sidebar.blade.php`
- `resources/views/components/avatar.blade.php`
- `resources/views/components/icon.blade.php`
- `resources/views/components/layout.blade.php`
- `resources/views/components/layouts/account.blade.php`
- `resources/views/components/layouts/public.blade.php`
- `resources/views/components/navigation/topbar.blade.php`
- `resources/views/components/organization-card.blade.php`
- `resources/views/components/organization-menu.blade.php`
- `resources/views/components/room/sidebar.blade.php`
- `resources/views/components/room/stat.blade.php`
- `resources/views/components/room/status.blade.php`
- `resources/views/components/room/topbar.blade.php`
- `resources/views/landing.blade.php`
- `resources/views/livewire/home.blade.php`
- `resources/views/livewire/notification-bell.blade.php`
- `resources/views/livewire/personal-charges.blade.php`
- `resources/views/livewire/personal-dashboard.blade.php`
- `routes/web.php`
- `tests/Browser/fixtures/personal-account.php`
- `tests/Browser/personal-account.mjs`
- `tests/Browser/room-dashboard.mjs`
- `tests/Feature/PersonalAccountTest.php`
