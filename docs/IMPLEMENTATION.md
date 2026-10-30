# Status implementasi

Acuan: PRD v0.4, SRS v1.2, ERD/kamus data v1.2. Implementasi dimulai dari origin/main commit a7e9c0e18d8cf57d6bf7f1a7070f0cbe5c763855 pada branch feat/organization-inventory.

Dokumen membedakan kode yang tersedia, skenario yang benar-benar diperiksa, dan hal yang belum diverifikasi. Adanya halaman/endpoint bukan bukti seluruh acceptance criteria sudah lulus.

## Kemampuan yang tersedia

| Lingkup | Frontend–backend | Bukti utama |
| --- | --- | --- |
| F01 Akun | Register, normalisasi email, signed verification, login/logout, reset, rate limit | Pest register/unverified/signed/forged URL/reset; Chromium login/logout |
| F02 Organization & membership | Buat Organization/Admin atomik, discovery/filter, Gabung langsung Member, role lokal; revoked menolak join ulang | Pest/Chromium Gabung, payload role palsu, idempotency, concurrency MySQL, promosi Admin dan last Admin; profil tidak membuat membership |
| F03 Otorisasi | Policy server, role lokal, membership aktif, scope resource, menu Admin nonaktif untuk Staff, arsip read-only | Pest lintas akun/ruang, mutasi Member, export Staff, arsip/unarchive |
| F04 Inventaris | Kategori, metadata/foto, filter/sort/pagination, soft delete, ULID immutable, damage report, penilaian, maintenance, ditemukan kembali | Pest FK lintas ruang, identifier, inspeksi major/perbaikan; Chromium membuat aset |
| F05 QR | Simple QrCode SVG/PNG, stiker, html5-qrcode, kode manual, URL stabil | Pest SVG, signature PNG asli, stiker, pembatasan anggota |
| F06 Peminjaman | Request/cancel/approve/reject, tanggal bisnis, snapshot kebijakan/pengakuan, My Borrowing/filter manajemen | Pest future date, duplikat, dua approval simultan, OD17 |
| F07 Pengembalian | Waktu fisik terpisah, inspeksi final, good/minor/major/lost, denda/override, laporan inspeksi, maintenance terpisah dari pelunasan | Pest inspeksi berulang, major → maintenance → available, snapshot tarif, override tanpa alasan |
| F08 Histori/audit | Timeline tersaring, actor/waktu/before/after aman, append-only pada model, histori soft delete | Pest larangan update audit dan revisi nominal draft dengan nilai sebelumnya |
| F09 Dashboard/notifikasi | Statistik MySQL, komposisi, enam bulan approval/top aset, ringkasan pribadi, polling/inbox/preferensi, queued mail | Query scope ruang; reset email benar-benar diproses worker ke mail log |
| F10 Laporan/settings | CSV UTF-8 BOM/periode/status, aset/pinjaman/kerusakan/tagihan/pembayaran/kredit/saldo, kebijakan, arsip/soft delete | Pest Admin vs Staff, CSV tidak menghitung pending, blok kewajiban terbuka |
| F11 Profil | Nama/avatar/password, pending email berversi, preferensi, soft delete/cabut sesi | Pest email lama hingga verifikasi, sole Admin, soft delete |
| F12 Finansial | Draft/edit/issue, bukti privat, damage/loss terpisah, consent transaksi tanpa snapshot, pembayaran/confirm/reject/credit/void/cancel/saldo turunan | Pest contoh Rp150.000 → Rp100.000 → paid → void; staff/self approval, evidence, damage/loss, concurrency |

Action/constraint menolak peminjaman ganda, maintenance ganda, inspeksi berulang, pencampuran tenant/peminjam, dan overpayment. Lock organisasi menyerialkan mutasi singkat per ruang. Nominal memakai integer Rupiah/DECIMAL tanpa pecahan. Email diantrekan setelah commit. Aplikasi tidak memindahkan uang.

## Keputusan produk

OD17 diputuskan pemilik proyek pada 27 Oktober 2026: **izinkan approval pinjaman sendiri** untuk Admin/Staff. Larangan persetujuan finansial sendiri tetap berlaku. PRD/SRS dan checksum diselaraskan.

Persona/riset pengguna tetap hipotesis sesuai PRD. Tidak ada klaim wawancara/validasi pengguna atau keputusan produk tambahan.

## Hasil verifikasi

Runtime: PHP 8.4.27, MySQL 8.4.13, Laravel 12, Node 24, Livewire 3, Flux 2, Tailwind 4.

- DDL v1.2 dan migration dijalankan pada MySQL asli.
- Setup Compose diuji dengan database development baru dan dengan data yang dipertahankan.
- Aplikasi, worker, dan scheduler dijalankan dari konfigurasi reusable.
- Composer platform requirements, build frontend, dan Pint diperiksa.
- Reset email masuk queue dan dirender ke mail log; failed_jobs = 0 saat diperiksa.
- Chromium memeriksa login, tab Livewire, pembuatan aset di database, logout, viewport desktop 1440/mobile 390, dan overflow halaman.
- Uji paralel memakai proses PHP terpisah: confirm/confirm, credit/confirm, void/void, issue/archive, dan approval/approval.
- Pemeriksaan terakhir tercatat pada bagian hasil akhir di bawah.

## Belum selesai / belum diverifikasi

- Pengiriman SMTP nyata, kamera perangkat fisik/izin pada berbagai browser, dan printer fisik belum diuji. Development memakai mail log, Chromium headless, dan halaman stiker.
- Load test target p95/5.000 aset, audit aksesibilitas menyeluruh, dan pixel comparison semua frame MagicPath belum dilakukan.
- Suite mencakup skenario inti dan sebagian acceptance criteria, belum pemetaan assertion satu per satu untuk seluruh FR/AC. Tidak ada klaim sertifikasi seluruh F01–F12 atau kesiapan produksi.
- Semua kombinasi filter/role/interaksi seluruh layar belum diuji melalui browser; keberhasilan backend tidak otomatis membuktikan seluruh kombinasi UI.
- Deployment, publikasi environment, backup/restore produksi, dan validasi pada task cloud baru belum dilakukan.

Keterbatasan ini dipisahkan dari integrasi development yang sudah diperiksa. SQL arsip tidak digunakan sebagai implementasi finansial aktif.

## Hasil akhir task

- **44 pengujian Pest lulus, 163 assertion**, pada MySQL 8.4.13 dengan PHP 8.4.27. Termasuk lima pengujian dua proses paralel.
- Build frontend, Composer platform requirements, Pint, dan pemeriksaan whitespace lulus.
- Smoke test Chromium desktop/mobile lulus pada runtime Compose final.
- Setup dijalankan ulang dan mempertahankan application key/data yang sudah ada.
- Satu paket QR benar-benar diunduh ulang dari lockfile melalui jaringan host/proxy dengan verifikasi TLS tetap aktif; pemeriksaan ini tidak hanya mengandalkan vendor/cache hangat.
- Semua 24 checksum pedoman lulus setelah sinkronisasi OD17.
- install_script dan start_skill cloud disimpan sebagai draft baru untuk workflow Laravel. Penyimpanan draft tidak memublikasikan environment; fresh-task restoration belum divalidasi.

## Perbaikan onboarding ruang — 30 Oktober 2026

Halaman publik `/organizations` sebelumnya menampilkan form pembuatan ruang kepada akun belum terverifikasi, sedangkan aksi Livewire menolak dengan 403. Sekarang halaman menampilkan petunjuk verifikasi dan menyembunyikan form tersebut. Aksi create/join/cancelJoin mengarahkan akun belum terverifikasi ke halaman verifikasi dan tamu ke login sebelum melakukan perubahan data. Aturan pada action backend tetap mewajibkan verifikasi email.

- Regresi 403 direproduksi sebelum perbaikan. Suite setelah perbaikan: **55 pengujian Pest lulus, 214 assertion**; build frontend dan Pint lulus.
- Chromium memeriksa registrasi akun belum terverifikasi, login, petunjuk pada halaman publik, serta pengalihan aksi Livewire tanpa popup Forbidden.
- Login `other@example.test` dan pembuatan ruang melalui UI Livewire diperiksa di Chromium; pembuat mendapat role Admin. Ruang pengujian dihapus melalui pengaturan Admin sesudah pemeriksaan.
- Akun dummy terverifikasi sudah disediakan seeder; README kini menjelaskan pemakaian dengan PHP langsung serta verifikasi akun development melalui mail log. Database pada laptop pengguna tidak diakses atau diubah dari environment cloud.

## Dashboard pribadi dan navigasi — 30 Oktober 2026

`/dashboard` sekarang merender `PersonalDashboard`, terpisah dari discovery `Home` pada `/organizations`. Ringkasan, Organization yang dibuat/diikuti, pinjaman berjalan, dan riwayat terbaru berasal dari data akun sendiri. Daftar Organization pada sidebar akun/ruang memakai dropdown. Header bersama menyediakan Beranda, Jelajahi Organization, Dashboard, dan lonceng di samping profil; Beranda tetap membuka landing page setelah login.

`/my-charges` menambahkan Tagihan Saya dengan total terbit, pembayaran terkonfirmasi, pembebasan, sisa, keterangan, filter tagihan, serta riwayat pembayaran dengan pagination independen. Relasi dan query baca baru tidak mengubah schema atau otorisasi finansial/ruang.

- Suite: **62 pengujian Pest lulus, 275 assertion**. Build frontend dan Pint lulus.
- Browser akun baru, regresi manajemen ruang, dan smoke test inventaris lulus, termasuk perpindahan role lokal dan mobile 390×844.
- Screenshot sebelum/referensi/hasil, pengukuran desktop, daftar pemeriksaan dan perbedaan visual: [laporan dashboard pribadi](visual/account-dashboard/README.md).

## Pemisahan discovery dan istilah Organization — 30 Oktober 2026

Jelajahi Organization dari navbar kini memakai komponen publik `ExploreOrganizations` pada `/organizations`: pencarian, filter, dan profil tanpa tab Organization Saya atau tombol pengelolaan. Area akun `/my-organizations` tetap memakai `Home`, dengan tab Jelajahi dan Organization Saya yang berpindah melalui Livewire tanpa meninggalkan layout akun. Link lama `/organizations?listing=mine` dan form create dialihkan ke URL akun yang baru.

Label/pesan bawaan di halaman, kontrol aksesibilitas, validasi bisnis yang tampil ke pengguna, serta email menggunakan Organization. Tulisan penjelasan di bawah sidebar dihapus. Permission, binding Livewire, aturan pembayaran/peminjaman, dan skema data tetap sama.

- **67 pengujian Pest lulus, 328 assertion**. Build frontend dan pemeriksaan browser akun lulus, termasuk tab akun yang tetap berada di URL privat, reload tab, daftar publik untuk guest/login, dan viewport desktop/mobile.
- Detail screenshot serta hasil pemeriksaan: [laporan Organization discovery](visual/organization-discovery/README.md).

## Gabung langsung sebagai Member — 30 Oktober 2026

Keputusan baru menggantikan approval keanggotaan dan invitation. Gabung membuat member/active melalui Policy/Gate, transaksi/lock dan UNIQUE. Active retry tidak mengubah role/tanggal; revoked/arsip/soft delete ditolak. Role hanya diubah Admin lokal; audit/notifikasi terbatas tetap berlaku. Peminjaman request/approval/rejection/cancellation dan aturan finansial dipertahankan.

Target aktif **14 tabel**. Migration baru mengarsipkan histori privat sebelum menghapus dua tabel lama; rollback hanya struktur. Salinan spesifikasi sebelumnya dipisahkan ke Arsip. Detail berkas, migration/deployment, dan hasil terbaru: [laporan Gabung langsung](DIRECT_MEMBERSHIP.md).

- **93 tes lulus, 518 assertion, 1 skipped**; build, Pint, browser desktop/mobile dan regresi fitur lama lulus. Skip terkait privilege SUPER untuk membuat trigger custom; tidak ada perubahan privilege global.
- Migration diterapkan ke cloud development tanpa reset. `.env` pengguna tidak diubah.

Bagian hasil task terdahulu pada dokumen ini merupakan histori versi sebelumnya.

## Form laporan kerusakan melalui tombol — 30 Oktober 2026

Katalog dan detail aset menampilkan tombol **Laporkan Kerusakan** pada tiap aset. Form baru ditampilkan sesudah tombol ditekan, menyebut aset yang dipilih, dan menyediakan uraian wajib, peminjaman terkait opsional, serta foto opsional. Batal menutup dan membersihkan draft; pengiriman berhasil juga menutup form dengan pesan sukses. Fokus diarahkan ke uraian ketika membuka form dan kembali ke tombol aset ketika ditutup.

State laporan dipisahkan dari pengajuan peminjaman dan formulir operasional lain. Pilihan peminjaman berasal dari aset yang dipilih pada Organization aktif, dengan pembatasan pemilik untuk Member. Identitas aset dikunci oleh Livewire dan diperiksa ulang pada backend; penyimpanan tetap melalui `MaintainAsset`, termasuk permission, transaksi, audit, dan storage foto privat yang sudah ada. Tidak ada perubahan skema atau migration.

Berkas aplikasi yang berubah: `app/Livewire/Workspace.php`, `resources/views/livewire/workspace.blade.php`, dan partial baru `resources/views/livewire/damage-report-form.blade.php`.

- **43 pengujian inventaris lulus, 173 assertion**; build frontend, Pint, dan pemeriksaan diff lulus.
- Chromium nyata dengan fixture terisolasi memeriksa Member/Admin: form awal tidak ada, buka tanpa membuat laporan, fokus, Batal/reset, validasi server, peminjaman terkait, unggah foto privat, penyimpanan, serta laporan muncul di daftar. Isian pengajuan peminjaman tetap terjaga. Fixture dan foto uji dibersihkan sesudah pemeriksaan.
- Screenshot desktop 1440×1000 dan mobile 390×844 telah dilihat; form terbaca dan halaman tidak melebar. Tabel tetap memiliki area scroll horizontal tersendiri. Pemeriksaan ini memakai aplikasi berjalan, tanpa membandingkan ulang canvas MagicPath live.

## Logo dan posisi navbar — 30 Oktober 2026

Logo huruf O diganti dengan versi PNG transparan dari gambar yang dikirim pemilik proyek, termasuk simbol burgundy/navy dan wordmark "Organizing Inventory". Navbar bersama memakai lebar halaman penuh sehingga gambar berada 24 px dari kiri pada desktop dan 14 px pada mobile; container konten halaman tetap mengikuti layout masing-masing. Tinggi gambar 56/48 px dengan rasio asli dan tautan ke Beranda.

Berkas aplikasi yang berubah: `public/images/organizing-inventory-logo.png`, `resources/views/components/navigation/topbar.blade.php`, dan `resources/css/room.css`. Acuan visual di `Pedoman/Desain/README.md` dan checksum diselaraskan. Backend, skema, dan data aplikasi tidak berubah.

- Build frontend dan **12 tes akun/discovery lulus, 114 assertion**. Checksum 29 berkas pedoman serta pemeriksaan diff lulus.
- Chromium nyata memeriksa halaman publik, dashboard pribadi, dan dashboard Organization pada 1440×1000 serta 390×844. Ukuran layar tambahan 320, 700, 701, 768, 940, dan 1024 px juga diperiksa: logo termuat, proporsi terjaga, posisi kiri konsisten, dan halaman tidak melebar.
- Screenshot sebelum/sesudah telah diambil dan hasil desktop/mobile dilihat. Link logo, tiga menu navbar, profil, lonceng notifikasi, dan buka/tutup sidebar akun/Organization tetap berjalan. Pemeriksaan visual memakai aplikasi berjalan dan gambar pengguna, tanpa membuka ulang canvas MagicPath live.
