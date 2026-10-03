# Laporan Perbaikan Kelompok 1 — Damage Report & Notification Preferences

Status: **selesai, menunggu persetujuan sebelum lanjut ke Kelompok 2.**
Test: 27 lulus (74 assertion) pada `DamageReportGroup1Test`, `DamageReportTest`, `NotificationTest`, `ProfileTest`. Pint bersih pada file yang diubah.

## Yang dikerjakan

| # | Perbaikan | File |
|---|-----------|------|
| 1 | Blokir "Laporkan Kerusakan" untuk aset `Lost` (UI: tombol nonaktif; Livewire: error + toast; Action: exception) | `resources/views/livewire/catalog/show.blade.php`, `app/Actions/Asset/ReportDamage.php` |
| 2 | Toggle email `damage` dinonaktifkan di Profile (checked + disabled, ada penjelasan) dan dipaksa `true` di server | `resources/views/livewire/settings/profile.blade.php` |
| 3 | `DamageReportedNotification::via()` selalu `['database','mail']`; `User::wantsEmailFor('damage')` selalu `true` | `app/Notifications/DamageReportedNotification.php`, `app/Models/User.php` |
| 4 | Validasi skema preferensi lewat Form Request (semua key wajib, boolean, `damage` harus true) dan normalisasi JSON | `app/Http/Requests/Settings/UpdateNotificationPreferencesRequest.php`, `User::normalizeNotificationPreferences()` |
| 5 | Tooltip severity (form member dan form staff) | `catalog/show.blade.php`, `manage/damage-reports/show.blade.php` |
| 6 | `simplePaginate(15)` pada daftar damage report | `manage/damage-reports/index.blade.php` |
| 7 | Toast sukses/error setelah staff assess / mulai maintenance / catat / selesai maintenance | `manage/damage-reports/show.blade.php` |
| 8 | Toast sukses saat member mengirim laporan (modal ditutup, tanpa redirect) | `catalog/show.blade.php` |

## Perbaikan tambahan (ditemukan saat membaca kode)

- **Kebocoran lintas organisasi pada pencarian damage report**: `orWhereHas('reporter')` tidak dikelompokkan, sehingga bisa lolos dari filter `organization_id`. Sekarang dibungkus `where(function…)`.
- **Layout sidebar tidak punya `<flux:toast>`**, sehingga toast di halaman katalog dan settings tidak pernah tampil. Ditambahkan `<x-toast-wrapper />` di `layouts/app/sidebar.blade.php`.
- **Redirect ke route yang tidak ada** (`organization.catalog.show`) pada submit damage report. Redirect dihapus.
- **`User::roleIn()` melempar exception debug** ("Membership is null!") untuk non-anggota. Sekarang mengembalikan `null` sehingga `hasRoleIn()` bernilai `false`.
- Label sumber laporan menjadi "Dari Member" / "Hasil Inspeksi Return" sesuai AGENTS.md.
- Listener damage melakukan `loadMissing` relasi, supaya tidak kena lazy-loading violation di queue.
- Foto damage dihapus bila Action gagal (tidak ada file yatim); validasi mimes jpg/jpeg/png/webp.

## Test baru (`tests/Feature/DamageReportGroup1Test.php`)

- Email damage tidak bisa dimatikan lewat preferensi.
- Notifikasi damage selalu memakai channel `database` dan `mail`.
- Listener mengirim ke admin dan staff, bukan ke pelapor.
- Tidak bisa melapor kerusakan aset Lost (Action dan komponen).
- Member bisa melapor dari katalog tanpa severity.
- Profile memaksa `damage = true` dan menyimpan skema lengkap.
- Form Request menolak `damage=false`, key hilang, dan tipe malformed.
- Preferensi tersimpan yang rusak jatuh ke default aman.

## Temuan penting di luar kelompok 1 (belum diperbaiki)

> [!WARNING]
> **Middleware `can:manage,organization` pada route Volt menerima slug (string), bukan model `Organization`.**
> Terbukti lewat HTTP test: `Gate manage => NULL args=["slug"]` sehingga **admin pun mendapat 403** di semua route `/o/{org}/manage/*` (juga route `can:accessCatalog`, `can:manageSettings`, dll.). Ini menjelaskan sebagian dari 36 test yang gagal di suite penuh.
> Ada juga halaman `manage.*` yang gagal pada `Volt::test` karena "multiple root elements".
> Perlu keputusan Anda sebelum diperbaiki karena menyentuh `routes/web.php` dan seluruh area manage.

Sisa kegagalan lain di suite penuh yang sudah ada sebelumnya: factory `Asset`/`AssetCategory` tanpa `code`/`name`, dan pola `Volt::test(...)->actingAs()` pada beberapa test lama.

## Cara tes manual

1. Login sebagai member, buka detail aset, klik "Lapor Kerusakan", isi deskripsi dan kirim. Toast sukses muncul dan modal tertutup.
2. Ubah status aset ke Lost. Tombol berubah nonaktif.
3. Buka `/settings/profile`. Checkbox kerusakan terkunci aktif; simpan preferensi.
4. Staff/admin menerima notifikasi in-app dan email walau preferensi lain dimatikan.
5. Di detail damage report, jalankan tiap aksi dan pastikan toast muncul.
