# Organization Saya dan Jelajahi publik

Revisi 30 Oktober 2026 atas pemisahan navigasi yang diminta pemilik proyek. Pemeriksaan visual menggunakan Chromium pada **1440×1000** dan **390×844**. Screenshot merupakan hasil render Blade/Livewire dengan data MySQL, bukan mockup gambar.

## Perilaku terbaru

- **Organization Saya** pada `/my-organizations` adalah area akun terverifikasi. Tab Jelajahi Organization membuka daftar untuk bergabung langsung (tombol Gabung; sejak 30 Oktober 2026 tanpa pengajuan/persetujuan) pada halaman yang sama; sidebar tetap tampil. Tab terpilih dipertahankan setelah reload; pencarian, filter, dan pagination tetap berfungsi.
- **Jelajahi Organization** pada navbar membuka `/organizations`, daftar publik untuk guest maupun akun login. Halaman ini tidak mempunyai tab Organization Saya, tombol buat, atau aksi membership langsung; kartu menuju profil publik.
- Label/pesan bawaan di frontend dan email memakai **Organization**, termasuk tombol, kategori, konteks role, form, notifikasi, dan pesan arsip. Data nama/deskripsi yang dimasukkan pengguna tetap ditampilkan dari backend.
- Tulisan penjelasan di bawah sidebar dihapus. Lonceng, profil, navigasi mobile, dan role per Organization tetap berfungsi.

## Screenshot yang diperiksa

| Layar | Desktop | Mobile |
| --- | --- | --- |
| Organization Saya | [Daftar membership](my-organizations-desktop.png) | [Sidebar tanpa tulisan bawah](after-mobile-menu.png) |
| Jelajahi di area akun | [Tab akun dengan sidebar](account-explore-desktop.png) | [Tab tetap di area akun](account-explore-mobile.png) |
| Jelajahi publik, akun login | [Tanpa tab akun](explore-desktop.png) | [Daftar publik](explore-mobile.png) |
| Jelajahi publik, guest | [Tanpa pilihan Organization Saya](public-guest-desktop.png) | — |
| Form buat Organization | — | [Label baru](create-mobile.png) |

Fixture browser terisolasi dibuat melalui action aplikasi lalu dibersihkan. Organization yang belum diikuti dapat mempunyai profil publik; katalog asetnya tetap memerlukan membership aktif. Tampilan sebelum revisi ada pada [laporan sebelumnya](../account-dashboard/README.md).

## Verifikasi

- **67 tes Pest lulus, 328 assertion**, pada MySQL. Tambahan regresi mencakup pemisahan halaman publik/akun, guard login/verifikasi, pergantian tab, join/cancel, filter/pagination, dan kompatibilitas tautan lama.
- `personal-account.mjs` lulus: tab Jelajahi tidak meninggalkan `/my-organizations`, sidebar tetap ada setelah reload, `/organizations` publik tanpa tab/aksi akun, statistik/riwayat/tagihan, dropdown, role, dan navigasi mobile.
- Regresi `room-dashboard.mjs` dan `smoke.mjs` lulus setelah penyesuaian label Organization; pembuatan Organization/aset, pergantian konteks, laporan, dan permission Admin/Staff/Member tetap berfungsi.
- Build frontend, Pint, checksum pedoman, dan pemeriksaan whitespace lulus. Pada 390×844 tidak ada overflow horizontal halaman; tabel mempunyai area scroll sendiri dan form tetap terbaca.
- Pencarian source `app/` dan `resources/` tidak menemukan lagi kata Ruang pada salinan antarmuka bawaan. Path publik lama tetap didukung untuk kompatibilitas tautan.

Jalankan browser suite menggunakan runtime development Compose:

```bash
DOCKER_CONFIG=/workspace/.onboarding/docker BROWSER_DOCKER=1 VISUAL_OUTPUT=docs/visual/organization-discovery node tests/Browser/personal-account.mjs
```

Tidak ada migration atau perubahan schema. Perubahan backend berupa pemisahan query/rute daftar publik dan akun, penambahan nama rute join, serta perubahan teks pesan. Aturan bisnis, scope data pribadi, dan permission tetap diperiksa pada action/policy yang sama.

## Berkas yang diubah

- `AGENTS.md`
- `Pedoman/Desain/README.md`
- `Pedoman/PRD_Organization_Inventory_v0.4.md`
- `Pedoman/SHA256SUMS.txt`
- `Pedoman/SRS_Organization_Inventory_v1.2.md`
- `README.md`
- `app/Actions/BorrowAsset.php`
- `app/Actions/ManageMembers.php`
- `app/Actions/ManageWorkspace.php`
- `app/Actions/WorkspaceAction.php`
- `app/Livewire/ExploreOrganizations.php`
- `app/Livewire/Home.php`
- `app/Mail/InvitationMail.php`
- `app/Notifications/ActivityMail.php`
- `docs/IMPLEMENTATION.md`
- `docs/visual/account-dashboard/README.md`
- `docs/visual/organization-discovery/README.md`
- `docs/visual/organization-discovery/metrics.json`
- `resources/css/account.css`
- `resources/views/auth.blade.php`
- `resources/views/components/account/sidebar.blade.php`
- `resources/views/components/navigation/topbar.blade.php`
- `resources/views/components/organization-menu.blade.php`
- `resources/views/components/organization/filters.blade.php`
- `resources/views/components/room/frame.blade.php`
- `resources/views/components/room/sidebar.blade.php`
- `resources/views/components/room/topbar.blade.php`
- `resources/views/invitation.blade.php`
- `resources/views/landing.blade.php`
- `resources/views/livewire/explore-organizations.blade.php`
- `resources/views/livewire/home.blade.php`
- `resources/views/livewire/notifications.blade.php`
- `resources/views/livewire/personal-borrowings.blade.php`
- `resources/views/livewire/personal-dashboard.blade.php`
- `resources/views/livewire/workspace.blade.php`
- `resources/views/mail/invitation.blade.php`
- `resources/views/organization-public.blade.php`
- `routes/web.php`
- `tests/Browser/personal-account.mjs`
- `tests/Browser/room-dashboard.mjs`
- `tests/Browser/smoke.mjs`
- `tests/Feature/OrganizationDiscoveryTest.php`
- `tests/Feature/PersonalAccountTest.php`
- `tests/Feature/WorkspaceOnboardingTest.php`
