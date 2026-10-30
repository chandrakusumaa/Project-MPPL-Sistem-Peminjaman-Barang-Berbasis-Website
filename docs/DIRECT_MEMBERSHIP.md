# Gabung langsung sebagai Member

Keputusan pemilik proyek, 30 Oktober 2026. Branch: `feat/direct-organization-membership`. Laporan ini merangkum implementasi dan upgrade; spesifikasi aktif berada pada PRD/SRS di Pedoman.

## Perilaku aktif

- Login dan verifikasi email diperlukan. **Gabung** langsung membuat membership `member` / `active`; role/status/user ID dari payload tidak digunakan.
- GET/profil publik tidak membuat membership. User/Organization soft delete, Organization terarsip, serta membership revoked menolak join.
- Join aktif idempoten: satu baris, role dan timestamp tidak berubah; replay tidak menambah audit/notifikasi. Hanya Admin lokal dapat mengubah role, termasuk Member menjadi Staff.
- Policy/Gate, transaksi, lock user/Organization/membership, UNIQUE `(organization_id,user_id)`, last Admin dan perlindungan kewajiban tetap berlaku. Join dibatasi 10 percobaan per akun per menit melalui action yang sama untuk HTTP dan Livewire.
- Membership baru dan perubahan role diaudit. Join memberi notifikasi hanya kepada Admin; perubahan role juga kepada pengguna terkait, selain aktor. Preferensi email dihormati.
- Review/cancel keanggotaan, invitation, mail/job/model serta endpoint terkait dihapus. Request/approval/rejection/cancellation **peminjaman barang** tetap berlaku.

UI menyediakan loading/disabled Gabung, feedback, badge Member dan **Buka Organization**. Dropdown sidebar langsung diperbarui. Daftar anggota dan kontrol role khusus Admin tetap tersedia tanpa formulir undangan/approval.

## Migration dan deployment

Migration baru: `2026_10_30_000003_remove_legacy_membership_flows.php`.

1. Backup database/storage. Sebelum mengganti kode lama, hentikan trafik tulis dan selesaikan/hentikan worker lama agar tidak ada job invitation yang tertinggal.
2. Deploy kode baru, bersihkan route/view cache, lalu jalankan migration dengan hak DDL dan LOCK TABLES pada database target.
3. Restart worker memakai kode baru dan buka kembali aplikasi.

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan queue:restart
npm run build
```

Pada Compose, gunakan `docker compose exec app` sebelum perintah PHP. Tidak perlu mengubah `.env` atau mereset database.

### Histori dan arsip privat

Migration memeriksa FK yang mengarah ke kedua tabel lama, serta trigger yang terpasang/merujuk tabel tersebut. Dependensi custom menyebabkan migration berhenti sebelum penghapusan dan perlu ditinjau operator. Schema standar kedua tabel hanya mempunyai FK keluar ke users/organizations.

Jika salah satu tabel berisi data, migration mengambil WRITE lock lalu menyimpan JSONL, DDL, serta manifest jumlah baris/checksum SHA-256 pada disk local privat:

`storage/app/private/legacy-membership/<waktu-UTC>-<uuid>/`

Kegagalan arsip menghentikan DROP. Arsip dapat berisi email/hash token; pertahankan dalam backup privat dan jangan publikasikan atau masukkan ke Git. Request/invitation historis tidak otomatis dikonversi menjadi Member/Staff; pengguna tetap perlu menekan Gabung.

Di cloud development, kedua tabel berisi **0 baris**, tidak ada trigger atau job invitation tertinggal. Migration telah diterapkan tanpa reset; membership tetap 10 baris dan borrowings tetap 0 sebelum/sesudahnya. Database laptop pengguna tidak diakses dari cloud; jalankan migration setelah menarik branch ini.

### Rollback

```bash
php artisan migrate:rollback --step=1
```

Rollback membuat ulang kedua tabel **kosong**, lengkap dengan FK, UNIQUE/generated guards dan CHECK lifecycle. Data tidak dipulihkan otomatis dan endpoint invitation tidak diaktifkan oleh rollback struktur. Restore JSONL adalah operasi terpisah: validasi FK dan abaikan kolom generated saat insert.

Migration baru mengembalikan struktur/check lama sebelum rollback hardening sebelumnya. Migration awal kini membaca snapshot DDL yang dibekukan pada `database/schema/2026-10-27-inventory-legacy.sql`, sehingga urutan historis tetap reproducible. DDL aktif `database/schema/inventory-v1.2.sql` berisi hasil akhir **14 tabel (12 inti + 2 pendukung)** dan CHECK lifecycle, di luar tabel framework.

## Verifikasi aktual

- **93 tes lulus, 518 assertion; 1 skipped**, pada MySQL. Suite default mencakup Feature, Concurrency dan Migrations.
- Dua proses PHP menunggu barrier lalu join bersamaan: satu ID/member aktif dan satu audit, tanpa duplikasi.
- Guest/unverified, payload role palsu, retry semua role/timestamp, revoked, stale model soft delete/arsip, self promotion Member/Staff, isolasi Admin, last Admin, kewajiban, serta penghapusan akun/Organization diperiksa.
- Peminjaman request/cancel/approve/reject/return serta suite finansial/concurrency yang sudah ada tetap lulus. Endpoint invitation GET/POST menghasilkan 404 tanpa perubahan membership.
- Migration diuji dengan histori berisi data: arsip JSONL/checksum, rollback kosong, penolakan FK custom, kegagalan arsip tanpa DROP, dan import DDL aktif 14 tabel.
- Satu tes membuat trigger custom skipped karena MySQL binary logging memerlukan `SUPER`; privilege global/server tidak diubah. Guard terhadap trigger custom belum dapat dibuktikan melalui tes tersebut.
- Build, Pint dan checksum pedoman lulus. `.env` tidak diubah.
- Chromium memeriksa Gabung disabled saat request berlangsung → Member → Buka Organization, POST role palsu, promosi Admin ke Staff, dropdown sidebar, serta desktop 1440×1000/mobile 390×844. Regresi akun/dashboard/tagihan/management/inventaris juga lulus.

[Snapshot visual](visual/direct-membership/README.md). SMTP nyata dan seluruh konfigurasi database custom di deployment lain belum diuji.

```bash
docker compose exec app vendor/bin/pest
docker compose exec app vendor/bin/pint --test
DOCKER_CONFIG=/workspace/.onboarding/docker BROWSER_DOCKER=1 node tests/Browser/direct-membership.mjs
```

Sesuaikan DOCKER_CONFIG dengan host; untuk PHP lokal hilangkan BROWSER_DOCKER=1. Browser membuat fixture sendiri dan membersihkan hanya data run-nya. Jalankan browser suite yang menulis database secara berurutan.

## Sinkronisasi spesifikasi

PRD/SRS, AGENTS, ERD editable lima halaman, kamus data, SQL aktif, indeks dan checksum diperbarui. DDL, overview ERD dan kamus data mempunyai 14 tabel yang sama tanpa referensi graph menggantung. [Snapshot keputusan lama](../Pedoman/Arsip/keanggotaan-sebelum-gabung-langsung/README.md) terpisah dari persyaratan aktif. Source React MagicPath lama dipertahankan sebagai referensi visual, bukan perilaku keanggotaan produksi.

## Berkas yang diubah

Daftar source, pengujian dan dokumentasi (termasuk berkas yang dihapus/dipindah); screenshot ditautkan pada laporan visual.

- `AGENTS.md`
- `Pedoman/Arsip/keanggotaan-sebelum-gabung-langsung/Kamus_Data_v1_2.md`
- `Pedoman/Arsip/keanggotaan-sebelum-gabung-langsung/Organization_Inventory_v1_2.drawio`
- `Pedoman/Arsip/keanggotaan-sebelum-gabung-langsung/PRD_Organization_Inventory_v0.4.md`
- `Pedoman/Arsip/keanggotaan-sebelum-gabung-langsung/README.md`
- `Pedoman/Arsip/keanggotaan-sebelum-gabung-langsung/SRS_Organization_Inventory_v1.2.md`
- `Pedoman/Database/Kamus_Data_v1_2.md`
- `Pedoman/Database/Organization_Inventory_v1_2.drawio`
- `Pedoman/Database/README.md`
- `Pedoman/Desain/README.md`
- `Pedoman/Implementasi.md`
- `Pedoman/PRD_Organization_Inventory_v0.4.md`
- `Pedoman/README.md`
- `Pedoman/SHA256SUMS.txt`
- `Pedoman/SRS_Organization_Inventory_v1.2.md`
- `README.md`
- `app/Actions/DeleteAccount.php`
- `app/Actions/InviteMember.php`
- `app/Actions/ManageMembers.php`
- `app/Actions/ManageWorkspace.php`
- `app/Actions/WorkspaceAction.php`
- `app/Jobs/SendInvitation.php`
- `app/Livewire/Home.php`
- `app/Livewire/OrganizationMenu.php`
- `app/Livewire/Workspace.php`
- `app/Mail/InvitationMail.php`
- `app/Models/MembershipRequest.php`
- `app/Models/OrganizationInvitation.php`
- `app/Policies/OrganizationPolicy.php`
- `app/View/Components/OrganizationMenu.php`
- `database/migrations/2026_10_27_000000_create_inventory_schema.php`
- `database/migrations/2026_10_30_000003_remove_legacy_membership_flows.php`
- `database/schema/2026-10-27-inventory-legacy.sql`
- `database/schema/inventory-v1.2.sql`
- `docs/DIRECT_MEMBERSHIP.md`
- `docs/IMPLEMENTATION.md`
- `docs/visual/direct-membership/README.md`
- `phpunit.xml`
- `resources/views/components/account/sidebar.blade.php`
- `resources/views/components/organization-menu.blade.php`
- `resources/views/components/room/sidebar.blade.php`
- `resources/views/invitation.blade.php`
- `resources/views/join-required.blade.php`
- `resources/views/livewire/home.blade.php`
- `resources/views/livewire/organization-menu.blade.php`
- `resources/views/livewire/workspace.blade.php`
- `resources/views/mail/invitation.blade.php`
- `resources/views/organization-public.blade.php`
- `routes/web.php`
- `tests/Browser/direct-membership.mjs`
- `tests/Browser/fixtures/personal-account.php`
- `tests/Concurrency/MembershipRaceTest.php`
- `tests/Feature/DirectMembershipTest.php`
- `tests/Feature/InventoryTest.php`
- `tests/Feature/OrganizationDiscoveryTest.php`
- `tests/Feature/PersonalAccountTest.php`
- `tests/Feature/WorkspaceOnboardingTest.php`
- `tests/Migrations/DirectMembershipMigrationTest.php`
- `tests/Pest.php`
- `tests/Support/membership-worker.php`
