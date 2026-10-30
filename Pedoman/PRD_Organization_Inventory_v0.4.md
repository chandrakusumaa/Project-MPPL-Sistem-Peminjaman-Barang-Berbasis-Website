---
title: "ORGANIZATION INVENTORY"
subtitle: "PRODUCT REQUIREMENTS DOCUMENT (PRD)"
author: "Tim Pengembang Organization Inventory"
date: "Versi 0.4 - 27 Oktober 2026"
geometry: margin=0.8in
---

# Kontrol Dokumen

| Parameter | Detail |
| --- | --- |
| Nama Produk | Organization Inventory |
| Dokumen | Product Requirements Document (PRD) |
| Versi | 0.4 |
| Tanggal Revisi | 27 Oktober 2026 |
| Status | Draft untuk review konsep, kebutuhan produk, dan validasi persona |
| Target Eksekusi | Phase 1 (MVP) |
| Fokus Revisi | Definisi ruang inventaris, pengguna individu/kelompok, persona, role per ruang, dan alur akun pribadi |
| Basis | PRD v0.2, klarifikasi pemilik proyek, analisis konsep/persona, SRS v1.2, dan Desain Database v1.0 |
| Status Persona | Hipotesis awal; belum dinyatakan sebagai hasil wawancara calon pengguna |

Dokumen ini menetapkan kebutuhan produk dan kriteria penerimaan. Daftar fitur dan skenario bukan pernyataan bahwa implementasi atau pengujian aplikasi telah selesai. Fokus awal pengguna pada lingkungan kampus merupakan usulan segmen untuk validasi; kemampuan produk tetap mencakup individu maupun kelompok.

Nama produk tetap **Organization Inventory**. Istilah utama pada penjelasan dan antarmuka adalah **Ruang Inventaris**. Istilah organization/organisasi tetap digunakan ketika merujuk dokumen teknis dan entitas database yang sudah ada.

\newpage

# 1. Ringkasan Produk

**Organization Inventory adalah platform web untuk mengelola inventaris dan peminjaman barang melalui ruang inventaris yang dapat dibuat oleh individu maupun kelompok.** Setiap pengguna mempunyai satu akun pribadi yang dapat digunakan untuk menyediakan barang melalui ruang yang dikelolanya dan untuk mengajukan pinjaman pada ruang lain.

Ruang inventaris adalah wadah pengelolaan barang, keanggotaan, aturan peminjaman, transaksi, dan histori. Ruang tersebut dapat mewakili satu pemilik barang, kelompok informal, komunitas, maupun organisasi formal. Pembuatan ruang tidak mensyaratkan pengguna mewakili lembaga tertentu.

Pembuat ruang otomatis memperoleh role **Admin pada ruang tersebut**. Pengguna lain dapat mengajukan bergabung atau menerima undangan. Setelah memiliki keanggotaan aktif, pengguna memperoleh akses sesuai role pada ruang terkait. Satu akun dapat menjadi Admin pada ruang A dan Member pada ruang B secara bersamaan.

Nilai utama produk adalah memberikan catatan yang konsisten mengenai barang yang dikelola, ketersediaan, pemegang barang, tanggal pengembalian, kondisi fisik, dan histori transaksi. Sistem mendukung pengajuan, persetujuan, pengembalian, inspeksi, dan QR Code per unit barang.

## 1.1 Definisi dan Glosarium Produk

| Istilah | Definisi dalam produk |
| --- | --- |
| Pengguna / User | Orang yang mempunyai akun pribadi pada platform; dapat belum menjadi anggota ruang mana pun |
| Guest | Pengunjung yang belum login |
| Ruang Inventaris / Organization | Wadah pengelolaan inventaris, anggota, dan transaksi; dapat mewakili individu atau kelompok |
| Pengelola / Penyedia Barang | Pengguna yang mengelola penyediaan barang melalui suatu ruang |
| Peminjam | Pengguna yang mengajukan atau menerima pinjaman barang |
| Keanggotaan / Membership | Relasi akses antara pengguna dan satu ruang inventaris |
| Admin | Role pengelolaan penuh pada satu ruang; pembuat ruang menjadi Admin pertama |
| Staff | Role operasional pada satu ruang yang diberikan oleh Admin |
| Member | Role anggota pada satu ruang, dengan akses katalog dan pengajuan sesuai izin |
| Persona | Gambaran pola kebutuhan, konteks, tujuan, dan perilaku calon pengguna |
| Dashboard Pribadi | Halaman akun untuk profil, ruang yang diikuti/dikelola, peminjaman pribadi, dan pintasan |
| Dashboard Manajemen Ruang | Halaman operasional Admin/Staff untuk satu ruang yang sedang dibuka |
| Inventaris / Aset | Unit barang fisik yang dicatat dalam suatu ruang |
| Join Request | Permintaan memperoleh keanggotaan ruang; berbeda dari permintaan meminjam barang |
| Borrowing Request | Permintaan meminjam satu unit barang dari ruang yang diikuti |

Istilah organisasi di dalam sistem tidak menentukan bentuk kelembagaan pengguna. Contoh ruang yang valid secara konsep adalah **Peralatan Raka** untuk barang pribadi, **Perlengkapan Kos Melati** untuk barang yang dikelola bersama, dan **Inventaris Himpunan Informatika** untuk organisasi mahasiswa.

Ruang mengidentifikasi pihak yang mengelola pencatatan dan proses peminjaman. Keterkaitan aset dengan ruang tidak otomatis menjelaskan apakah barang dimiliki pribadi, bersama, atau dititipkan. Pencatatan pemilik barang yang berbeda dari pengelola belum menjadi fitur khusus MVP.

## 1.2 Tiga Konteks Pengalaman Pengguna

| Konteks | Pengguna | Fungsi utama |
| --- | --- | --- |
| Area publik | Guest dan pengguna terdaftar | Menemukan ruang dan melihat profil publik; inventaris privat tidak ditampilkan |
| Area akun pribadi | Semua pengguna terdaftar, termasuk yang belum menjadi anggota ruang | Profil, Ruang Saya, buat ruang, My Borrowing, notifikasi, dan Scan QR |
| Area ruang inventaris | Pengguna dengan membership aktif pada ruang terkait | Member menggunakan katalog; Admin/Staff juga mengakses pengelolaan sesuai kewenangan |

**Membuka dashboard tidak memberikan role.** Role diperoleh melalui pembuatan ruang atau pemberian kewenangan oleh Admin yang sah. Kembali dari dashboard ruang ke dashboard pribadi hanya mengganti konteks tampilan dan tidak mencabut role atau keanggotaan.

Analogi toko daring berlaku pada hubungan akun pribadi dan pengelolaan toko: akun yang sama dapat mengelola tempat penyediaan barang sendiri sekaligus mengakses tempat milik pihak lain. Alur bisnis Organization Inventory berfokus pada peminjaman dan pengembalian barang.

## 1.3 Referensi Praktik Nyata

**Pumpipumpe** adalah inisiatif berbagi barang yang dimulai di Bern, Swiss, pada 2012. Situs resminya menjelaskan penggunaan stiker pada kotak surat untuk menunjukkan barang yang bersedia dipinjamkan penghuni kepada tetangganya. Halaman About membahas barang rumah tangga yang jarang digunakan, seperti bor dan penyedot debu [R5–R6].

Praktik tersebut memperlihatkan konteks peminjaman barang antarindividu. Inferensi untuk perancangan produk ini adalah bahwa penyedia barang dapat berupa individu atau rumah tangga, sehingga ruang pengelolaan tidak harus mewakili organisasi formal.

Referensi tersebut digunakan sebagai contoh praktik yang sudah ada. Fitur Admin/Staff, persetujuan, QR, inspeksi, dan histori dalam PRD ini merupakan rancangan Organization Inventory. Pumpipumpe tidak dinyatakan sebagai pengguna, mitra, atau lokasi uji proyek ini. Kebutuhan calon pengguna lokal perlu divalidasi tersendiri.

---

# 2. Masalah, Tujuan, dan Prinsip Produk

## 2.1 Problem Statement

Individu dan kelompok yang meminjamkan barang membutuhkan informasi yang konsisten tentang inventaris, siapa yang sedang memegang barang, batas waktu pengembalian, serta kondisi terakhir. Peminjam membutuhkan informasi tentang barang yang dapat diakses, status permintaan, dan kewajiban pengembaliannya.

Hipotesis awal proyek adalah koordinasi melalui percakapan pesan dan catatan terpisah dapat menyulitkan pelacakan informasi tersebut. Bahan sumber belum memuat hasil wawancara yang membuktikan frekuensi atau besarnya masalah pada segmen awal. Hipotesis di bawah akan diperiksa melalui rencana validasi pada bagian 11.

| Hipotesis masalah | Pengguna yang terdampak | Kebutuhan yang diusulkan |
| --- | --- | --- |
| Catatan barang dan pemegangnya tersebar | Pengelola | Inventaris dan daftar pinjaman aktif dalam satu ruang |
| Tanggal kembali sulit ditelusuri | Pengelola dan peminjam | Tanggal jatuh tempo, status pinjaman, dan notifikasi |
| Kondisi awal/akhir tidak tercatat konsisten | Pengelola dan peminjam | Kondisi fisik dan inspeksi pengembalian |
| Ketersediaan barang sulit diketahui | Peminjam | Katalog dengan status terkini setelah memiliki akses |
| Keputusan permintaan sulit diikuti | Peminjam | Status pending, disetujui/aktif, ditolak, atau dibatalkan |
| Pengelolaan oleh beberapa orang membutuhkan batas kewenangan | Pengelola bersama | Role dan audit pada masing-masing ruang |

## 2.2 Primary Goals

1. Memusatkan data barang dan transaksi pada ruang inventaris yang dapat dibuat individu maupun kelompok.
2. Mempermudah pengajuan, persetujuan, peminjaman aktif, dan pengembalian barang.
3. Menyediakan pelacakan aset melalui QR Code dan histori penggunaan.
4. Menjaga status operasional dan kondisi fisik barang konsisten dengan proses yang dicatat.
5. Mendukung beberapa ruang dengan isolasi data dan role per ruang.
6. Menyediakan satu akun yang dapat digunakan sebagai pengelola maupun peminjam pada konteks berbeda.
7. Menegakkan permission pada backend dan menampilkan konteks ruang secara jelas di UI.

## 2.3 Secondary Goals

1. Mengurangi kebutuhan pencatatan berulang jika manfaatnya terkonfirmasi dalam validasi pengguna.
2. Meningkatkan keterlacakan tanggung jawab pengelola dan peminjam.
3. Menyediakan statistik penggunaan serta laporan operasional.
4. Mendukung pengelolaan sendiri maupun delegasi operasional kepada Staff.
5. Menjadi dasar pengembangan berikutnya setelah kebutuhan MVP tervalidasi.

## 2.4 Prinsip Produk

### Satu akun, peran sesuai ruang
Registrasi membuat akun pribadi. Sistem tidak meminta pilihan permanen akun penyedia dan akun peminjam. Kewenangan Admin/Staff/Member tersimpan pada keanggotaan ruang.

### Single Source of Truth
Database menjadi sumber status barang dan transaksi. UI menampilkan state terakhir yang berhasil tersimpan.

### Konteks yang jelas
UI menampilkan ruang yang sedang dibuka dan role pengguna pada ruang tersebut. Tindakan “Kembali ke Dashboard Pribadi”, “Logout”, dan pencabutan keanggotaan mempunyai makna berbeda.

### Confirm Before State-Changing Action
Keputusan sensitif dapat ditinjau sebelum disimpan, terutama persetujuan/penolakan, inspeksi, perubahan role, dan penghapusan.

### Least Privilege
Pengguna membaca dan mengubah data sesuai kewenangan. Pembatasan ditegakkan di server.

### Traceability by Default
Perubahan penting pada barang, membership, dan transaksi menghasilkan histori yang dapat ditelusuri.

### Explicit Uncertainty
Hipotesis kebutuhan, hasil validasi, rencana implementasi, dan hasil pengujian dicatat secara terpisah. Data atau status yang belum tersedia tidak dikarang.

---

# 3. Target Pengguna, Persona, dan Peran

## 3.1 Target Pengguna

**Usulan fokus validasi MVP adalah mahasiswa pemilik atau pengelola perlengkapan yang bersedia meminjamkan barang di lingkungan kampus, serta mahasiswa yang membutuhkan barang tersebut untuk penggunaan sementara.**

Contoh barang meliputi kamera, tripod, dan perlengkapan kegiatan atau berkemah. Konteks awal dipilih agar tim dapat mengamati akses barang melalui lingkungan yang dikenal, persetujuan pengelola, batas waktu, dan pemeriksaan pengembalian.

Kelompok informal, komunitas kampus, dan organisasi mahasiswa merupakan target tambahan yang dapat memakai mekanisme ruang yang sama. Fokus validasi kampus tidak menjadi pembatas bahwa hanya mahasiswa yang dapat mendaftar.

Bentuk individu/kelompok menggambarkan siapa yang mengelola ruang. Role menggambarkan kewenangan pada ruang. Persona menggambarkan kebutuhan pengguna dan harus diperiksa dengan bukti lapangan.

## 3.2 Persona Utama A — Raka, Pemilik/Pengelola Perlengkapan

**Status: persona hipotesis. Nama dan situasi merupakan ilustrasi desain, belum hasil wawancara.**

| Aspek | Uraian |
| --- | --- |
| Konteks | Mahasiswa yang memiliki kamera, tripod, dan tenda serta bersedia meminjamkannya kepada teman atau lingkungan yang dikenalnya |
| Pemicu | Ada teman meminta meminjam barang |
| Cara kerja yang perlu diperiksa | Koordinasi melalui percakapan pesan dan catatan terpisah |
| Masalah yang diasumsikan | Sulit menelusuri pemegang barang, tenggat, dan kondisi terakhir |
| Tujuan | Tetap dapat berbagi barang dengan catatan yang jelas dan mudah ditinjau |
| Kebutuhan | Buat ruang, inventaris per unit, persetujuan anggota, persetujuan pinjaman, pengembalian, inspeksi, histori |
| Role yang lazim | Admin pada ruang sendiri; dapat menjadi Member pada ruang lain |
| Keberhasilan tugas | Dapat menemukan pemegang/tenggat satu barang dan mencatat pengembalian beserta kondisinya |

## 3.3 Persona Utama B — Sinta, Peminjam Perlengkapan Sementara

**Status: persona hipotesis. Nama dan situasi merupakan ilustrasi desain, belum hasil wawancara.**

| Aspek | Uraian |
| --- | --- |
| Konteks | Mahasiswa yang membutuhkan barang untuk tugas atau kegiatan dalam waktu terbatas |
| Pemicu | Membutuhkan kamera selama dua hari untuk dokumentasi tugas |
| Cara kerja yang perlu diperiksa | Bertanya kepada beberapa teman dan menunggu informasi |
| Masalah yang diasumsikan | Sulit mengetahui ketersediaan barang dan perkembangan permintaan |
| Tujuan | Mengakses barang yang sesuai dan memahami kewajiban pengembaliannya |
| Kebutuhan | Temukan ruang, ajukan bergabung, katalog, permintaan pinjaman, status/tenggat, My Borrowing |
| Role yang lazim | Member pada ruang yang diikuti |
| Keberhasilan tugas | Dapat mengirim permintaan dan menjelaskan hasil keputusan serta tanggal pengembalian |

Dua persona utama menggambarkan pola kebutuhan yang dapat dimiliki orang yang sama. Raka dapat menjadi peminjam ketika mengakses barang dari ruang lain; Sinta dapat membuat ruang sendiri jika ingin meminjamkan barang.

## 3.4 Persona Pendukung — Dimas, Petugas Operasional

**Status: persona hipotesis untuk kebutuhan delegasi.**

Dimas membantu mengelola barang ketika ruang ditangani lebih dari satu orang. Ia membutuhkan daftar permintaan, pemrosesan pinjaman, pengembalian, dan penilaian kondisi, tanpa mengubah anggota, laporan administratif, atau pengaturan ruang. Role yang digunakan adalah Staff.

Pemilik yang mengelola barang sendiri dapat menjalankan alur utama sebagai Admin tanpa menunjuk Staff. Kebutuhan delegasi diuji pada calon pengguna yang memang mengelola barang bersama.

## 3.5 Akun dan Role

| Tingkat | Status / role | Makna |
| --- | --- | --- |
| Platform | Guest | Belum login; dapat melihat area publik |
| Platform | Pengguna terdaftar | Mempunyai dashboard pribadi, termasuk saat belum mengikuti ruang |
| Per ruang | Admin | Mengelola ruang, anggota, barang, transaksi, laporan, dan pengaturan |
| Per ruang | Staff | Mengelola operasi inventaris dan transaksi sesuai izin |
| Per ruang | Member | Mengakses katalog, meminta pinjaman, scan QR, dan melapor kerusakan |

Pembuat ruang terverifikasi otomatis menjadi Admin pertama pada ruang yang berhasil dibuat. Admin dapat memberikan kewenangan kepada pengguna lain. Sebuah ruang dapat memiliki lebih dari satu Admin; minimal satu Admin aktif harus dipertahankan.

Pengguna yang Admin pada satu ruang tidak mendapat kewenangan Admin pada ruang lain. Membuka alamat dashboard manajemen tidak membuat membership atau menaikkan role.

## 3.6 Hak Akun Pribadi

| Tindakan | Guest | Pengguna terdaftar |
| --- | --- | --- |
| Menemukan ruang dan membuka profil publik | Ya | Ya |
| Dashboard pribadi | Tidak | Ya, walaupun belum menjadi anggota |
| Membuat ruang | Login dahulu | Ya, setelah verifikasi email |
| Mengajukan bergabung ke ruang lain | Login dahulu | Ya, jika memenuhi aturan request |
| Melihat katalog privat | Tidak | Memerlukan membership aktif ruang terkait |
| My Borrowing | Tidak | Hanya transaksi pribadi |
| Pengaturan profil dan preferensi | Tidak | Hanya akun sendiri |

Hak akun tidak bergantung pada role di ruang lain. Admin/Staff dari suatu ruang tetap dapat menjelajah dan mengajukan bergabung ke ruang lain.

## 3.7 Permission Matrix pada Satu Ruang

Matriks berikut berlaku pada **ruang yang sedang diakses**, dengan membership aktif dan kondisi ruang yang mengizinkan tindakan.

| Area | Admin | Staff | Member |
| --- | :---: | :---: | :---: |
| Lihat katalog dan detail aset | Ya | Ya | Ya |
| Ajukan pinjaman sesuai eligibility | Ya | Ya | Ya |
| My Borrowing | Milik sendiri | Milik sendiri | Milik sendiri |
| Tambah/edit metadata aset | Ya | Ya | Tidak |
| Hapus aset secara soft delete | Ya, sesuai aturan | Ya, sesuai aturan | Tidak |
| Kelola kategori dan buat QR | Ya | Ya | Tidak |
| Scan QR dan lapor kerusakan | Ya | Ya | Ya |
| Approve/reject borrowing | Ya | Ya | Tidak |
| Proses return dan inspeksi | Ya | Ya | Tidak |
| Nilai laporan kerusakan dan proses maintenance | Ya | Ya | Tidak |
| Timeline aset | Detail operasional | Detail operasional | Data tersaring |
| Histori transaksi seluruh ruang | Ya | Ya | Tidak |
| Audit umum ruang | Ya | Tidak | Tidak |
| Kelola anggota dan role | Ya | Tidak, menu nonaktif | Tidak |
| Export laporan | Ya | Tidak, menu nonaktif | Tidak |
| Settings ruang | Ya | Tidak, menu nonaktif | Tidak |

Role Staff/Admin mencakup hak penggunaan anggota pada ruang terkait sebagaimana SRS. Keputusan pemilik proyek pada 27 Oktober 2026 (OD17): Admin/Staff boleh menyetujui peminjaman sendiri jika semua syarat peminjaman terpenuhi. Larangan persetujuan kewajiban finansial sendiri tetap berlaku.

Menu nonaktif untuk Staff adalah keputusan UI. Endpoint tetap wajib menolak tindakan tanpa izin.

## 3.8 Contoh Satu Akun pada Beberapa Konteks

| Konteks akun Raka | Role yang relevan | Hasil |
| --- | --- | --- |
| Dashboard pribadi | Tidak memakai satu role ruang sebagai role global | Lihat profil, peminjaman pribadi, dan daftar ruang |
| Peralatan Raka | Admin | Kelola aset dan transaksi ruang sendiri |
| Perlengkapan Kos Melati | Member | Akses katalog dan ajukan pinjaman |
| Ruang lain yang belum diikuti | Belum memiliki membership aktif | Lihat profil publik dan ajukan bergabung |

Kembali ke dashboard pribadi atau logout tidak menghapus role. Pencabutan keanggotaan atau perubahan role merupakan tindakan tersendiri dengan otorisasi, aturan pinjaman aktif, dan audit.

---

# 4. Tujuan dan Indikator Keberhasilan

Indikator berikut adalah target pengujian dan validasi, bukan hasil yang sudah dicapai.

| Kode | Tujuan | Indikator pengujian |
| --- | --- | --- |
| G01 | Traceability | Tim dapat menelusuri aset dari detail/QR ke histori yang menjadi hak aksesnya |
| G02 | Borrowing correctness | Permintaan tidak dapat disetujui untuk barang yang tidak memenuhi eligibility |
| G03 | Authorization | Pengguna tanpa hak pada ruang B tidak dapat membaca/mengubah data terbatas ruang B meskipun Admin di ruang A |
| G04 | Inventory accuracy | Status Available/Borrowed/Maintenance/Lost konsisten dengan transaksi dan kondisi |
| G05 | Operational usability | Pengelola menyelesaikan request, approval, return, dan inspeksi tanpa bantuan teknis |
| G06 | Data persistence | Data dan histori tetap tersedia sesuai izin setelah login ulang dan refresh |
| G07 | QR traceability | QR mengarah ke aset yang tepat dan tetap valid setelah perubahan metadata |
| G08 | Onboarding penyedia individu | Akun baru terverifikasi dapat membuat ruang dan menjadi Admin tanpa akun Admin awal atau penunjukan Staff |
| G09 | Perpindahan konteks | Satu akun yang Admin di ruang A dan Member di ruang B memperoleh kewenangan yang tepat pada masing-masing ruang |
| G10 | Pemahaman konsep | Partisipan dapat menjelaskan makna Ruang Inventaris, perbedaan bergabung/meminjam, dan fungsi dashboard pribadi |
| G11 | Akun tanpa keanggotaan | Akun baru tetap mempunyai dashboard pribadi dengan pilihan buat ruang dan temukan ruang |
| G12 | Validasi kebutuhan | Tim mencatat kejadian peminjaman nyata dari calon pengguna dan menghubungkannya dengan kebutuhan serta revisi persona |

---

# 5. Keputusan Produk dan Lingkup MVP

## 5.1 Keputusan Produk dan Teknologi

- Nama produk: **Organization Inventory**; label domain pada UI: **Ruang Inventaris**.
- Penyedia dapat berupa individu maupun kelompok; tidak diperlukan organisasi formal.
- Satu akun pribadi dapat membuat dan mengikuti beberapa ruang.
- Role disimpan per ruang, sesuai SRS v1.2 dan Desain Database v1.0.
- Arsitektur: Laravel (PHP), Eloquent ORM, Blade, dan Livewire.
- Database MVP: **MySQL 8.0.16+ / InnoDB**, mengikuti keputusan SRS v1.2. PostgreSQL merupakan alternatif pada PRD sebelumnya, bukan dua implementasi yang diwajibkan sekaligus.
- Styling: Tailwind CSS dan Flux UI.
- QR: Simple QrCode untuk pembuatan dan html5-qrcode untuk pemindaian.
- Analitik: Chart.js. File dan validasi mengikuti Laravel Storage dan Form Request.

## 5.2 Must Have — Phase 1 (MVP)

Kode F01–F11 dipertahankan untuk keterlacakan. F12 menambahkan tagihan dan pembayaran manual.

| Kode | Fitur | Prioritas | Cakupan utama |
| --- | --- | --- | --- |
| F01 | Account & Authentication | Wajib | Register, verifikasi email, login, reset password, akun pribadi |
| F02 | Ruang Inventaris, Discovery & Membership | Wajib | Buat ruang, Admin pembuat, Explore, profil publik, Ruang Saya, join request, undangan |
| F03 | Role & Authorization | Wajib | Role per ruang, membership aktif, kelola anggota/role, permission server dan UI |
| F04 | Inventory & Condition Management | Wajib | CRUD aset/kategori, kondisi fisik, damage report, maintenance, penanganan lost |
| F05 | QR Code | Wajib | QR unik per unit, unduh/cetak, scan dan input kode, pemeriksaan akses |
| F06 | Borrowing Request | Wajib | Pengajuan satu unit, pending, pembatalan, approval/rejection, overdue, My Borrowing |
| F07 | Return & Inspection | Wajib | Waktu kembali, inspeksi final, status/kondisi aset, pencatatan denda |
| F08 | Asset History & Audit Trail | Wajib | Timeline aset, audit tindakan sensitif, histori sesuai hak akses |
| F09 | Dashboard & Notifications | Wajib | Dashboard pribadi semua akun, dashboard ruang Admin/Staff, notifikasi in-app/email/toast |
| F10 | Reports & Operational Settings | Wajib untuk Admin | Export CSV, profil/aturan ruang, arsip/hapus dengan perlindungan histori |
| F11 | Account Settings | Wajib | Profil/email/password, preferensi notifikasi, hapus akun sesuai aturan, logout |
| F12 | Biaya Kerusakan, Tagihan & Pelunasan | Wajib | Penilaian charge, tagihan terpisah, pembayaran manual sebagian/penuh, verifikasi, kredit dan koreksi berjejak |

Damage reports dan notifikasi yang sudah terdapat dalam SRS dipetakan secara eksplisit ke F04 dan F09. Rincian teknis tetap berada di SRS.

## 5.3 Di Luar Rilis Awal / Pengembangan Berikutnya

- Pembayaran sewa, checkout, deposit, payment gateway, dan pemindahan dana otomatis. Pencatatan pembayaran manual tagihan termasuk MVP.
- Harga sewa atau model komisi platform.
- Pengiriman barang dan pelacakan kurir.
- Reservasi dengan jaminan ketersediaan atau kalender booking.
- Katalog seluruh barang yang dapat dibuka publik tanpa membership, serta pencarian barang lintas ruang.
- Keranjang beberapa barang dalam satu transaksi, stok kuantitas, atau barang habis pakai.
- Pencatatan khusus pemilik/titipan barang yang terpisah dari ruang pengelola.
- Sinkronisasi inventaris dengan sistem eksternal.
- Notifikasi push real-time kompleks, mobile native, approval bertingkat, dan analitik prediktif.
- Bulk import/export berskala besar dan integrasi RFID/NFC.

Item tersebut dapat ditinjau pada fase berikutnya. Penyebutan analogi toko daring tidak memasukkan fungsi perdagangan ke dalam MVP.

## 5.4 Batas Operasional MVP

| Aspek | Ketetapan |
| --- | --- |
| Akses awal | Guest dapat menemukan ruang dan melihat profil publik |
| Katalog | Detail inventaris memerlukan membership aktif pada ruang terkait |
| Keanggotaan | Melalui join request yang disetujui Admin atau penerimaan undangan yang sah |
| Persetujuan | Persetujuan bergabung dan persetujuan pinjaman merupakan dua keputusan berbeda |
| Barang | Satu record aset mewakili satu unit fisik dengan satu QR |
| Transaksi | Satu borrowing untuk satu unit dan satu inspeksi final |
| Tanggal | Request masa depan diperbolehkan; approval hanya pada tanggal mulai sesuai SRS |
| Ketersediaan | Pending bukan reservasi dan tidak menjamin barang tersedia |
| Serah terima | Diatur pengelola dan peminjam; approval pada tanggal mulai mencatat aktivasi pinjaman |
| Denda | Tagihan keterlambatan terpisah dari kerusakan; pembayaran/pelunasan dicatat manual |
| Zona waktu | Kalender bisnis Asia/Jakarta; penyimpanan timestamp UTC mengikuti SRS |

Kategori profil ruang memakai daftar yang telah ditetapkan SRS. Ruang pribadi dapat memakai kategori Lainnya; kategori tidak menentukan hak pengguna untuk membuat ruang.

---

# 6. User Stories dan Alur Utama

## 6.1 User Stories

US01–US12 dipertahankan dan diperjelas. US13–US20 melengkapi konsep akun pribadi, penyedia individu, delegasi, dan fungsi SRS.

| Kode | User story | Fitur |
| --- | --- | --- |
| US01 | Sebagai pengguna, saya ingin mendaftar, memverifikasi email, dan login agar dapat menggunakan akun pribadi | F01 |
| US02 | Sebagai pengguna, saya ingin menemukan ruang berdasarkan nama/kategori agar dapat memilih tempat peminjaman yang relevan | F02 |
| US03 | Sebagai pengguna yang belum menjadi anggota ruang, saya ingin mengajukan bergabung agar dapat memperoleh akses yang disetujui | F02 |
| US04 | Sebagai Admin ruang, saya ingin menyetujui/menolak join request agar akses inventaris tetap terkontrol | F02/F03 |
| US05 | Sebagai anggota aktif, saya ingin melihat katalog dan meminta pinjaman dengan tanggal serta alasan | F04/F06 |
| US06 | Sebagai Admin/Staff, saya ingin meninjau pending request dan memutuskan approve/reject | F06 |
| US07 | Sebagai Admin/Staff, saya ingin memproses pengembalian dan inspeksi agar catatan kondisi serta ketersediaan diperbarui | F07 |
| US08 | Sebagai pengguna, saya ingin memindai QR agar detail aset yang tepat dibuka setelah pemeriksaan akses | F05 |
| US09 | Sebagai Admin/Staff, saya ingin meninjau histori aset agar perjalanan barang dapat ditelusuri | F08 |
| US10 | Sebagai pengguna, saya ingin melihat Active/Pending/History pada My Borrowing untuk transaksi pribadi saya | F06/F08/F09 |
| US11 | Sebagai Admin, saya ingin melihat statistik ruang agar kondisi inventaris dapat dipantau | F09 |
| US12 | Sebagai pengguna, saya ingin memperbarui profil dan preferensi akun agar pengaturan tetap relevan | F11 |
| US13 | Sebagai pengguna terverifikasi yang ingin meminjamkan barang, saya ingin membuat ruang dan otomatis menjadi Admin ruang tersebut | F02/F03 |
| US14 | Sebagai akun baru tanpa membership, saya ingin tetap mempunyai dashboard pribadi dengan pilihan buat ruang dan temukan ruang | F01/F09 |
| US15 | Sebagai pengguna, saya ingin melihat ruang yang dikelola/diikuti beserta role dan status membership masing-masing | F02/F03/F09 |
| US16 | Sebagai pengelola, saya ingin kembali ke dashboard pribadi tanpa kehilangan kewenangan pada ruang | F03/F09 |
| US17 | Sebagai pengguna, saya ingin menjadi Admin di ruang sendiri dan Member di ruang lain dengan akun yang sama | F02/F03 |
| US18 | Sebagai Admin, saya ingin mengundang pengguna sebagai Staff/Member agar akses dan delegasi dapat diberikan | F02/F03 |
| US19 | Sebagai anggota aktif, saya ingin melaporkan kerusakan agar pengelola dapat menilai dan menindaklanjutinya | F04 |
| US20 | Sebagai pengguna, saya ingin menerima dan membuka notifikasi pribadi agar keputusan yang relevan dapat diikuti | F09/F11 |
| US21 | Sebagai pengelola, saya ingin menetapkan biaya kerusakan berdasarkan inspeksi dan kebijakan yang diketahui peminjam | F12 |
| US22 | Sebagai petugas, saya ingin mencatat pembayaran dan meminta verifikasi Admin | F12 |
| US23 | Sebagai peminjam, saya ingin melihat tagihan pribadi, pembayaran, dan sisa kewajiban | F12 |
| US24 | Sebagai Admin, saya ingin mengoreksi pembayaran atau memberi pembebasan dengan riwayat yang dapat ditelusuri | F12 |

## 6.2 Alur Landing dan Authentication

1. Guest membuka Landing Page dengan pilihan Temukan Ruang, Login, dan Daftar.
2. Registrasi meminta nama, email unik, dan password; tidak meminta role global penyedia/peminjam.
3. Pengguna memverifikasi email sesuai alur F01.
4. Login berhasil membuka dashboard pribadi atau kembali ke URL tujuan yang sah.
5. Dashboard pribadi tetap tersedia walaupun belum ada ruang atau pinjaman.
6. Lupa password dan reset password mengikuti mekanisme autentikasi.
7. Login gagal tidak menghasilkan sesi authenticated.

## 6.3 Alur Explore dan Join Ruang

`Landing / Dashboard Pribadi -> Temukan Ruang -> Profil Publik -> Ajukan Bergabung -> Pending -> Admin Review -> Approved / Rejected`

- Guest yang memilih ajukan bergabung diminta login; tujuan awal dipertahankan.
- Pengguna tidak boleh memiliki dua membership untuk pasangan akun–ruang yang sama.
- Maksimal satu join request pending pada pasangan tersebut.
- Approval membuat atau mengaktifkan kembali membership sebagai Member tanpa menggandakan atau menurunkan role membership aktif yang sudah ada.
- Rejection tidak memberikan akses katalog.
- Status request dan membership dipisahkan: request menyimpan proses keputusan; membership aktif/revoked menyimpan hubungan akses.
- Membership aktif tidak otomatis menghasilkan transaksi peminjaman.

## 6.4 Alur Borrowing

`Katalog Ruang -> Aset Available -> Ajukan Pinjaman -> Pending -> Admin/Staff Approve atau Reject`

Jika approved pada tanggal mulai:

`Pending -> Borrowed -> Overdue bila terlambat -> Return + Inspection -> Returned`

Status aset saat pengembalian menjadi Available, Maintenance, atau Lost sesuai inspeksi. Overdue berlaku pada borrowing; status aset tetap Borrowed selama pinjaman aktif.

Jika ditolak atau dibatalkan, request menjadi Rejected atau Cancelled tanpa mengaktifkan pinjaman. Pending tidak mengubah aset menjadi Borrowed dan tidak memesan ketersediaan di masa depan.

## 6.5 Alur Return dan Inspection

1. Admin/Staff membuka borrowing Borrowed atau Overdue.
2. Sistem memuat aset, peminjam, tanggal, dan snapshot kebijakan.
3. Petugas mencatat waktu pengembalian aktual serta hasil Good, Minor Damage, Major Damage, atau Lost.
4. Sistem menyimpan satu inspeksi final, catatan, kalkulasi/final denda, dan waktu pemrosesan.
5. Transaksi, status/kondisi aset, laporan inspeksi bila diperlukan, log, dan audit diperbarui bersama.
6. My Borrowing menampilkan hasil yang menjadi hak peminjam.

## 6.6 Alur QR

`Scan QR / Input Kode -> Resolve URL Aset -> Login bila diperlukan -> Authorization -> Detail Aset atau Ajakan Bergabung`

QR berisi identifier/URL yang stabil. Status dan kondisi diambil dari data terkini. Non-anggota tidak memperoleh detail aset hanya karena memiliki QR.

## 6.7 Alur Membuat Ruang sebagai Individu atau Kelompok

1. Pengguna terverifikasi membuka dashboard pribadi atau Ruang Saya.
2. Pengguna memilih **Buat Ruang Inventaris**.
3. Pengguna mengisi profil ruang; tidak diwajibkan membuat organisasi formal.
4. Sistem memvalidasi data dan membuat ruang serta membership Admin pembuat dalam satu transaksi.
5. Setelah berhasil, pengguna kembali ke Ruang Saya dengan badge Admin.
6. Pengguna membuka dashboard manajemen ruang lalu memasukkan barang.
7. Jika gagal, sistem menampilkan validasi dan tidak meninggalkan ruang tanpa Admin pembuat.

## 6.8 Alur Berpindah Konteks

1. Pengguna membuka Ruang Saya dari dashboard pribadi.
2. Sistem menampilkan daftar ruang dan role/status akun pada masing-masing ruang.
3. Membuka ruang sebagai Admin/Staff mengarah ke manajemen; sebagai Member mengarah ke katalog.
4. Sistem memeriksa membership dan role ruang tujuan pada setiap akses.
5. Tombol **Kembali ke Dashboard Pribadi** mengubah konteks tanpa mengubah role.
6. Membuka ruang lain menggunakan kewenangan pada ruang tersebut, bukan kewenangan ruang sebelumnya.

## 6.9 Serah Terima Fisik

Serah terima fisik dikoordinasikan pengelola dan peminjam. Pada MVP, pengelola melakukan approval di tanggal mulai ketika barang siap diserahkan; approval langsung mengaktifkan pinjaman. Sistem belum memisahkan status booking, siap diambil, dan pengiriman. Pemeriksaan kondisi akhir dilakukan saat pengembalian dicatat.

---

# 7. Kebutuhan Produk dan Kriteria Penerimaan

## F01 - Account & Authentication

### Kebutuhan

Sistem menyediakan registrasi, verifikasi email, login, logout, reset password, dan akun pribadi. Email unik dan password di-hash. Akun baru tidak harus mempunyai membership ruang.

### Acceptance Criteria - AC01

- Email baru yang valid menghasilkan akun; email yang sudah digunakan ditolak.
- Tidak ada pilihan permanen role Admin/Staff/Member atau jenis akun penyedia/peminjam pada registrasi.
- Verifikasi email tersedia dan diwajibkan sebelum membuat ruang sesuai SRS.
- Login hanya berhasil dengan kredensial valid; kegagalan tidak membuat sesi authenticated.
- Reset password dapat digunakan untuk mengganti password.
- Setelah logout, halaman terproteksi meminta autentikasi.
- Akun baru dapat membuka dashboard pribadi dengan empty state tanpa membership.
- Data pribadi dan sesi antar-akun tetap terisolasi.

---

## F02 - Ruang Inventaris, Discovery & Membership

### Kebutuhan

Pengguna menemukan ruang berdasarkan nama/kategori, membuka profil publik, membuat ruang, melihat Ruang Saya, mengajukan bergabung, atau menerima undangan. Ruang dapat dikelola satu individu maupun kelompok.

### Acceptance Criteria - AC02

- Search/filter menampilkan ruang yang relevan dan tidak diarsipkan.
- Profil publik tidak menampilkan katalog privat atau data sensitif.
- Pengguna terverifikasi dapat membuat ruang; keberhasilan membuat membership aktif sebagai Admin pembuat.
- Pembuatan ruang dan penetapan Admin bersifat atomik; kegagalan tidak meninggalkan ruang tanpa Admin.
- Ruang Saya menampilkan role dan status akses akun pada setiap ruang.
- Pengguna yang belum aktif pada ruang dapat mengajukan atau membatalkan join request.
- Duplicate pending request dicegah; rejection tidak memberi akses.
- Admin memproses approve/reject; approval membuat atau mereaktivasi membership Member.
- Membership aktif tidak diduplikasi atau diturunkan rolenya oleh proses join.
- Admin dapat mengundang email sebagai Staff/Member; token sekali pakai berlaku tujuh hari.
- Penerimaan undangan memeriksa email terverifikasi yang sesuai, masa berlaku, dan status; resend mengganti token lama.
- Persetujuan bergabung tidak membuat borrowing atau mengubah status barang.

---

## F03 - Role & Authorization

### Kebutuhan

Akses ditentukan oleh akun, membership aktif, role pada ruang yang diakses, serta kondisi ruang/aset. Role disimpan per ruang. Admin dapat mengelola anggota dan role; Staff menjalankan operasi harian sesuai matriks izin.

### Acceptance Criteria - AC03

- Role Admin/Staff/Member bukan atribut global yang memberikan akses lintas ruang.
- Akun yang Admin pada ruang A dan Member pada ruang B memperoleh kewenangan yang tepat pada masing-masing ruang.
- Mengakses URL manajemen tidak membuat membership atau menaikkan role.
- Kembali ke dashboard pribadi tidak menghapus role atau membership.
- Member tidak dapat membuat, mengubah, atau menghapus aset.
- Staff tidak dapat menjalankan pengelolaan anggota, export, settings, atau audit umum yang khusus Admin.
- Menu terlarang bagi Staff ditampilkan nonaktif; server tetap menolak endpoint tanpa izin.
- Pengguna tanpa membership aktif tidak dapat mengakses katalog privat.
- Admin terakhir tidak dapat dicabut atau diturunkan hingga ruang kehilangan seluruh Admin aktif.
- Anggota dengan Borrowed/Overdue tidak dapat dicabut; pencabutan yang sah mempertahankan histori dan membatalkan pending terkait.
- Perubahan role/keanggotaan dicatat pada audit.

---

## F04 - Inventory & Condition Management

### Kebutuhan

Admin/Staff mengelola aset per unit, kategori, metadata, kondisi fisik, laporan kerusakan, dan maintenance. Aset mempunyai nama, kode publik, kategori, spesifikasi, lokasi, status, kondisi fisik, dan ruang pengelola. Foto bersifat opsional sesuai batas SRS.

### Acceptance Criteria - AC04

- Aset baru mendapat kode unik dan status Available dengan kondisi awal good.
- Kategori harus berasal dari ruang yang sama; nama kategori unik per ruang.
- Validasi, pencarian, filter, pengurutan, dan pagination mengikuti scope ruang.
- Kode aset dan ruang pengelolanya tidak dapat diganti melalui edit metadata.
- Status operasional dan kondisi fisik merupakan data terpisah dan berubah melalui tindakan yang sah.
- Aset dengan pinjaman atau maintenance aktif tidak dapat dihapus.
- Penghapusan aset memakai soft delete, membatalkan pending terkait, dan mempertahankan histori.
- Anggota aktif dapat melaporkan kerusakan meskipun tidak sedang meminjam; Admin/Staff dapat membuat laporan manual.
- Admin/Staff menilai severity dan memilih tindak lanjut dengan catatan.
- Maksimal satu maintenance aktif per aset; aset maintenance/lost tidak eligible dipinjam.
- Selesai maintenance mencatat penyelesaian dan kondisi akhir good/minor_damage sebelum aset Available.
- Aset Available tidak boleh mempunyai kondisi fisik major_damage; penilaian major pada aset tersedia diikuti maintenance.
- Barang Lost yang ditemukan dinilai dan dicatat sebelum kembali tersedia, sesuai SRS.
- Operasi kondisi, laporan, dan maintenance menghasilkan log/audit.

---

## F05 - QR Code

### Kebutuhan

Setiap unit aset mempunyai QR yang mereferensikan URL stabil ke detail aset. Metadata atau status tidak disimpan sebagai snapshot dalam QR. Pengguna dapat memindai atau memasukkan kode secara manual.

### Acceptance Criteria - AC05

- QR menunjuk unit aset yang benar.
- Admin/Staff dapat menghasilkan, mengunduh, dan mencetak QR sesuai dukungan lingkungan; SVG tersedia dan PNG bergantung pada ekstensi yang ditetapkan SRS.
- Perubahan nama, spesifikasi, lokasi, atau nama ruang tidak memerlukan QR baru.
- Kode aset dan slug ruang stabil.
- Guest diminta login sebelum kembali ke URL tujuan.
- Non-anggota menerima arahan bergabung tanpa detail aset privat.
- Anggota aktif memperoleh detail dan tindakan sesuai hak akses.
- Kode/QR tidak valid menampilkan pesan dan pilihan mencoba kembali.
- Input manual kode tersedia sebagai alternatif kamera.
- URL yang diketahui atau ditebak tetap melewati pemeriksaan otorisasi.

---

## F06 - Borrowing Request dan Approval

### Kebutuhan

Anggota aktif yang memiliki hak penggunaan dapat meminta satu unit aset Available dengan tanggal pinjam, tanggal kembali, dan alasan. Pending menunggu keputusan pengelola dan tidak menjadi reservasi.

### Acceptance Criteria - AC06

- Pemohon harus memiliki membership aktif pada ruang aset.
- Tanggal pinjam minimal hari ini pada kalender Asia/Jakarta; due_date setelah borrow_date.
- Durasi mengikuti batas ruang; alasan memenuhi validasi SRS.
- Request masa depan boleh dibuat, tetapi approval hanya pada borrow_date sama dengan hari ini.
- Satu pengguna tidak dapat mempunyai lebih dari satu request pending/pinjaman aktif untuk aset yang sama.
- Beberapa pengguna boleh pending untuk aset yang sama; maksimum satu pinjaman aktif per aset.
- Peminjam dapat membatalkan pending miliknya.
- Approval memeriksa ulang role, membership, tanggal, status request, dan eligibility aset.
- Approval menyimpan petugas, waktu, snapshot tarif/batas durasi, dan perubahan borrowing/aset menjadi Borrowed secara atomik.
- Reject menyimpan alasan, petugas, dan waktu; status menjadi Rejected.
- Request yang tanggal mulainya telah lewat tidak diaktifkan dengan melewati aturan; perlu keputusan penolakan/pembatalan dan pengajuan ulang.
- Scheduler menandai keterlambatan menjadi Overdue tanpa membuat pinjaman ganda.
- My Borrowing menampilkan Active, Pending, dan History milik akun sendiri, dengan filter ruang.
- Dua pengelola tidak dapat mengaktifkan aset yang sama untuk dua peminjam bersamaan.

---

## F07 - Return & Inspection

### Kebutuhan

Admin/Staff memproses pengembalian Borrowed/Overdue dan mencatat satu inspeksi final. Hasil minimum adalah Good, Minor Damage, Major Damage, dan Lost. Sistem membedakan waktu pengembalian fisik dari waktu pencatatan.

### Acceptance Criteria - AC07

- Hanya borrowing aktif Borrowed/Overdue yang dapat dikembalikan.
- Return menyimpan waktu aktual, waktu pemrosesan, petugas, kondisi, dan catatan sesuai SRS.
- Waktu aktual tidak sebelum approval atau di masa depan.
- Good menghasilkan aset Available dengan kondisi good.
- Minor Damage menghasilkan aset Available dengan kondisi minor_damage dan laporan inspeksi.
- Major Damage menghasilkan aset Maintenance dengan kondisi major_damage dan laporan inspeksi dalam perawatan.
- Lost menghasilkan aset Lost dengan kondisi unknown.
- Catatan wajib untuk hasil selain Good.
- Denda dihitung dari hari kalender terlambat dan snapshot tarif approval; kalkulasi dan angka final disimpan.
- Perubahan angka denda memerlukan alasan; nilai final positif menjadi tagihan late_fee dan pelunasannya mengikuti F12.
- Satu borrowing hanya mempunyai satu inspeksi final dan tidak dapat dikembalikan dua kali.
- Borrowing, aset, laporan terkait, log, dan audit disimpan dalam transaksi yang sama.
- Hasil inspeksi yang menjadi hak peminjam tampil pada My Borrowing.

---

## F08 - Asset History & Audit Trail

### Kebutuhan

Asset History menampilkan perjalanan satu aset. Audit mencatat tindakan sensitif lintas entitas, termasuk membership dan settings. Histori ditampilkan berdasarkan hak akses dan tidak dapat diedit sebagai catatan biasa.

### Acceptance Criteria - AC08

- Event mempunyai actor/sumber sistem, timestamp, jenis tindakan, dan referensi yang relevan.
- Timeline aset dapat difilter dan ditampilkan secara kronologis sesuai UI.
- Perubahan aset, peminjaman, pengembalian, kondisi, dan maintenance dapat ditelusuri.
- Admin/Staff melihat detail operasional ruang sesuai izin.
- Member dapat melihat timeline aset yang disaring tanpa data pribadi peminjam lain, alasan pinjaman, denda, atau diff audit.
- My Borrowing hanya menampilkan transaksi pribadi akun.
- Audit umum ruang hanya dapat dibaca Admin terkait.
- Asset log dan audit bersifat append-only melalui UI/API biasa.
- Histori tetap tersedia sesuai otorisasi setelah data master di-soft-delete.

---

## F09 - Dashboard & Notifications

### Kebutuhan

Semua akun terdaftar mempunyai dashboard pribadi. Admin/Staff mempunyai dashboard operasional untuk satu ruang. Notifikasi pribadi menghubungkan pengguna dengan kejadian yang relevan sesuai SRS.

### Acceptance Criteria - AC09

- Akun tanpa ruang/pinjaman memperoleh empty state dan aksi Buat Ruang Inventaris serta Temukan Ruang.
- Dashboard pribadi menampilkan profil, ringkasan pinjaman/request pribadi, dan pintasan Ruang Saya, Scan QR, serta notifikasi.
- Dashboard pribadi dapat merangkum transaksi akun sendiri dari ruang yang relevan tanpa menampilkan transaksi pengguna lain.
- Klarifikasi pemilik proyek, 30 Oktober 2026: dashboard pribadi terpisah dari Jelajahi Organization. Urutannya adalah ringkasan Organization aktif/peminjaman/pengajuan menunggu, Organization yang dibuat atau diikuti, lalu riwayat peminjaman terbaru dengan statusnya.
- Navbar atas menyediakan Beranda (landing page, termasuk setelah login), Jelajahi Organization, dan Dashboard. Daftar Organization pada sidebar dibuka melalui dropdown berlabel Organization; notifikasi diakses melalui lonceng di samping profil, tanpa menu Notifikasi pada sidebar.
- Tagihan Saya merangkum total tagihan terbit, pembayaran terkonfirmasi, pembebasan, serta sisa kewajiban akun sendiri lintas ruang; daftar tagihan menampilkan keterangan/status lunas atau belum lunas dan riwayat pembayaran tetap mencakup pending, rejected, dan voided. Pembayaran pending tidak mengurangi saldo.
- Dashboard manajemen menampilkan Total, Available, Borrowed, Maintenance, Lost, Overdue, dan Pending hanya untuk ruang terkait.
- Grafik berasal dari data yang dapat ditelusuri; statistik penggunaan memakai pinjaman yang disetujui.
- Kembali ke dashboard pribadi tidak mengubah membership/role.
- Perubahan state yang berhasil tersimpan tercermin pada ringkasan.
- Notifikasi in-app hanya dapat dibaca penerimanya; tersedia filter, tandai dibaca, dan tautan ke resource yang tetap diperiksa otorisasinya.
- Notifikasi email dan toast mengikuti kejadian/preferensi SRS; email diantrekan setelah transaksi berhasil.
- Aktor tidak menerima notifikasi atas aksinya sendiri; kejadian berulang tidak menghasilkan notifikasi in-app ganda.

---

## F10 - Reports & Operational Settings

### Kebutuhan

Admin mengelola profil dan kebijakan ruang, mengekspor CSV, serta mengarsipkan atau menghapus ruang sesuai perlindungan transaksi dan histori.

### Acceptance Criteria - AC10

- Hanya Admin dapat export laporan aset, peminjaman, dan kerusakan sesuai filter.
- Export hanya memuat data ruang terkait dan mengikuti format CSV SRS.
- Admin dapat mengubah profil ruang serta batas durasi dan tarif denda nonnegatif.
- Perubahan settings dicatat pada audit; pinjaman yang disetujui memakai snapshot lama.
- Slug ruang tidak diubah ketika nama/profil diubah.
- Arsip dan soft delete ditolak saat ada pinjaman atau maintenance aktif.
- Ruang terarsip tidak muncul di Explore dan menolak mutasi domain, dengan pengecualian unarchive yang sah.
- Soft delete ruang membatalkan pending dan mencabut undangan pending sesuai SRS tanpa menghapus histori.
- Staff tidak dapat menjalankan export atau settings walaupun memanggil endpoint langsung.

---

## F11 - Account Settings

### Kebutuhan

Pengguna mengelola profil, perubahan email terverifikasi, password, preferensi email notifikasi, dan logout. Hapus akun mengikuti perlindungan pinjaman aktif dan Admin terakhir.

### Acceptance Criteria - AC11

- Perubahan profil divalidasi dan tersimpan pada akun sendiri.
- Email baru tidak menggantikan email aktif sebelum verifikasi berhasil.
- Password baru memerlukan verifikasi sesuai aturan akun.
- Preferensi email per kategori tersimpan; notifikasi in-app tetap aktif dan email transaksional mengikuti SRS.
- Hapus akun ditolak saat pengguna mempunyai Borrowed/Overdue atau menjadi Admin aktif terakhir pada ruang yang belum dihapus.
- Hapus akun yang sah memakai soft delete, mencabut membership/sesi, membatalkan pending, dan mempertahankan histori.
- Logout mengakhiri sesi tanpa menghapus role atau membership yang tersimpan.

---

## F12 - Biaya Kerusakan, Tagihan & Pelunasan

### Kebutuhan Biaya dan Pelunasan

Biaya kerusakan adalah tagihan kepada peminjam setelah penilaian; berbeda dari denda keterlambatan dan biaya operasional perbaikan pengelola. Laporan kerusakan tidak otomatis membuktikan tanggung jawab peminjam. Barang aus, kerusakan lama, laporan oleh anggota yang tidak meminjam, dan perbaikan internal tidak otomatis ditagihkan.

Satu tagihan terhubung ke satu peminjaman, aset, ruang, dan peminjam. Jenis tagihan: late_fee, damage, atau loss. Damage/loss memerlukan laporan inspeksi, uraian kerusakan, bukti yang relevan, dasar nominal, dan kebijakan yang telah diketahui peminjam. Damage dan loss untuk satu kejadian tidak diterbitkan bersamaan. Nominal dalam Rupiah bulat, lebih dari nol saat diterbitkan; penilaian tanpa biaya cukup dicatat pada inspeksi.

Kebijakan tanggung jawab dan cara penilaian ditampilkan sebelum peminjaman. Pengguna mengakui kebijakan pada pengajuan; snapshot dan waktunya disimpan. Jika kebijakan berubah sebelum approval, konfirmasi ulang diperlukan. Pinjaman lama tanpa snapshot tidak otomatis dikenai ketentuan baru; penerbitan biaya membutuhkan dasar persetujuan eksplisit yang dicatat.

### Kewenangan

| Tindakan pada ruang terkait | Admin | Staff | Member/peminjam |
| --- | --- | --- | --- |
| Menilai kondisi dan mengusulkan tagihan draft | Ya | Ya | Lihat rincian sendiri |
| Menerbitkan tagihan dan menetapkan nominal final | Ya | Tidak | Tidak |
| Mencatat pembayaran pending beserta bukti | Ya | Ya | Lihat pembayaran sendiri |
| Mengonfirmasi/menolak pembayaran | Ya | Tidak | Tidak |
| Membatalkan pembayaran yang sudah dikonfirmasi | Ya, dengan alasan | Tidak | Tidak |
| Membebaskan sebagian/seluruh saldo | Ya, dengan alasan | Tidak | Tidak |
| Export rekap tagihan/pembayaran | Ya | Tidak | Tidak |

Admin yang menjadi peminjam tidak boleh menerbitkan tagihan, mengonfirmasi pembayarannya sendiri, atau membebaskan kewajibannya sendiri; diperlukan Admin lain yang berwenang. Semua akses tetap diperiksa per ruang. Staff mencatat penerimaan sebagai pending; pencatatan oleh Admin dapat langsung dikonfirmasi dalam transaksi yang sama jika berwenang.

### Alur Tagihan dan Pembayaran

1. Admin/Staff mencatat hasil inspeksi dan bukti. Pengembalian fisik tetap dapat selesai meskipun tagihan belum lunas.
2. Pengelola mengusulkan nominal serta alasan pada draft. Admin meninjau dan menerbitkan tagihan.
3. Peminjam melihat jenis tagihan, dasar perhitungan, nominal, kredit pembebasan, pembayaran sah, dan sisa.
4. Pengelola mencatat pembayaran yang dilakukan di luar aplikasi melalui tunai, transfer, atau metode lain dengan catatan.
5. Admin memverifikasi bukti dan mengonfirmasi atau menolak. Pembayaran pending/rejected/voided tidak mengurangi saldo.
6. Pembayaran dapat sebagian atau penuh; sistem menghitung status pelunasan otomatis.
7. Kesalahan pembayaran dikoreksi dengan void oleh Admin disertai alasan, lalu catatan pengganti bila perlu. Riwayat asli tetap tersimpan.

Bukti transfer memerlukan referensi transaksi atau lampiran; tunai memerlukan catatan penerimaan. Tanggal pembayaran aktual dicatat terpisah dari waktu input/konfirmasi dan tidak boleh di masa depan atau mendahului penerbitan. Aplikasi hanya mencatat transaksi eksternal dan tidak memindahkan uang.

### Perhitungan Saldo dan Status

- Tagihan memiliki lifecycle draft, issued, cancelled.
- Nilai tagihan issued tidak diedit; koreksi penurunan melalui credit/pembebasan yang tetap tercatat.
- Kredit hanya dapat diberikan hingga sisa belum dibayar. Kredit tidak menghasilkan saldo negatif atau mencatat pengembalian uang.
- Total dibayar = jumlah pembayaran confirmed yang belum dibatalkan.
- Total bersih = nominal issued dikurangi kredit pembebasan.
- Sisa = total bersih dikurangi total dibayar; wajib >= 0.
- Status turunan: unpaid bila belum ada pembayaran; partial bila 0 < dibayar < total bersih; paid bila dibayar = total bersih > 0; waived bila total bersih = 0. Cancelled ditampilkan terpisah.
- Tagihan hanya boleh dibatalkan jika belum ada pembayaran confirmed dan belum ada kredit. Pembayaran pending ditolak dalam transaksi pembatalan.
- Tidak ada overpayment, refund, pembayaran lintas tagihan, atau perubahan saldo manual pada MVP.
- Beberapa percobaan pembayaran boleh pending; batas saldo diperiksa ulang saat konfirmasi di bawah lock.
- Kode idempotensi mencegah input/konfirmasi ganda. Pembayaran kedua yang melampaui sisa ditolak.
- Denda keterlambatan memakai fine_amount final borrowing; saat return, nilai positif disalin sekali sebagai tagihan late_fee issued. Actor transaksi/sistem tercatat. Setelah terbit, koreksi finansial menggunakan kredit, bukan mengubah histori borrowing.
- Tagihan damage/loss dinilai manual dan diterbitkan Admin; tidak dihitung otomatis dari tingkat minor/major.

### Batas Operasional Tambahan

Tagihan tidak mengubah status aset atau membatalkan return. Penutupan maintenance juga terpisah dari pelunasan. Kebijakan menolak pinjaman baru karena tunggakan belum diterapkan otomatis.

Arsip/soft delete ruang dan penghapusan akun peminjam ditolak jika masih ada tagihan draft/issued dengan saldo terbuka atau pembayaran pending. Pencabutan membership juga ditolak dalam kondisi tersebut agar penyelesaian dapat dilakukan. Transaksi keuangan baru tidak dilakukan pada ruang terarsip; histori lunas tetap terbaca sesuai izin. Referensi ke aset yang sudah soft delete tetap dipertahankan untuk penyelesaian tagihan.

### Acceptance Criteria - AC12

- AC12-01: Return rusak tanpa keputusan tanggung jawab tidak langsung menghasilkan tagihan damage.
- AC12-02: Admin menerbitkan damage/loss dengan bukti, alasan, dan dasar kebijakan yang disimpan.
- AC12-03: Denda keterlambatan dan biaya kerusakan ditampilkan sebagai tagihan terpisah; tidak menggunakan fine_amount untuk menggabungkan keduanya.
- AC12-04: Tagihan Rp150.000 dengan pembayaran confirmed Rp50.000 memiliki sisa Rp100.000 dan status partial.
- AC12-05: Pembayaran confirmed berikutnya Rp100.000 membuat status paid; pending Rp100.000 belum mengubah saldo.
- AC12-06: Nominal nol/negatif, pembayaran melebihi sisa, dan pencampuran akun/ruang/aset ditolak.
- AC12-07: Dua konfirmasi paralel tidak dapat menghasilkan overpayment; retry dengan kunci sama tidak membuat duplikasi.
- AC12-08: Void pembayaran Rp50.000 mengembalikan sisa Rp50.000 pada tagihan yang sebelumnya lunas, disertai alasan dan histori.
- AC12-09: Kredit Rp100.000 atas sisa Rp100.000 setelah pembayaran Rp50.000 menghasilkan total bersih Rp50.000 dan status paid; kredit penuh sebelum pembayaran menghasilkan waived.
- AC12-10: Staff tidak dapat menerbitkan, mengonfirmasi, membebaskan, atau void; peminjam hanya melihat tagihan sendiri.
- AC12-11: Admin peminjam tidak dapat menyetujui tindakan finansial untuk dirinya sendiri.
- AC12-12: Return/maintenance dapat selesai sebelum pelunasan; penghapusan/arsip tidak menghilangkan kewajiban aktif.
- AC12-13: Notifikasi tagihan dan keputusan pembayaran hanya kepada pihak terkait; lampiran privat diperiksa otorisasinya.
- AC12-14: Pembayaran ditolak/disalahinput tidak dihapus; actor dan waktu pembuatan/keputusan tetap tersimpan.


# 8. Aturan Sistem, Data, dan Integritas Transaksi

## 8.1 Scope Ruang dan Konteks Akun

Aset, kategori, borrowing, laporan kerusakan, membership, histori operasional, dan laporan export berada pada scope ruang. Pembacaan/mutasi wajib memeriksa ruang dan kewenangan terkait.

Dashboard pribadi dan My Borrowing menggunakan scope akun sendiri; keduanya tidak memberikan hak untuk melihat seluruh data ruang. Pengguna boleh mempunyai role berbeda pada beberapa ruang. Role tidak disalin ke akun sebagai kewenangan global.

## 8.2 Status Aset, Kondisi Fisik, dan Borrowing

Status aset: **Available, Borrowed, Maintenance, Lost**. Kondisi fisik terpisah: **good, minor_damage, major_damage, unknown**.

Status borrowing: **Pending, Rejected, Cancelled, Borrowed, Overdue, Returned**. Returned merupakan hasil transaksi pinjaman, bukan status menetap pada aset.

| Status awal aset | Tindakan | Status akhir aset | Kondisi akhir |
| --- | --- | --- | --- |
| Available | Approve pinjaman pada tanggal mulai | Borrowed | Kondisi tercatat sesuai penilaian |
| Borrowed | Return dengan Good | Available | good |
| Borrowed | Return dengan Minor Damage | Available | minor_damage |
| Borrowed | Return dengan Major Damage | Maintenance | major_damage |
| Borrowed | Return dengan Lost | Lost | unknown |
| Available | Mulai maintenance yang sah | Maintenance | Sesuai penilaian kerusakan |
| Maintenance | Selesai perbaikan dan inspeksi | Available | good/minor_damage |
| Lost | Tandai ditemukan setelah penilaian yang sah | Available | good/minor_damage |

Aset Available hanya boleh berkondisi good/minor_damage. Barang ditemukan yang membutuhkan perbaikan ditindaklanjuti melalui alur kerusakan/maintenance sesuai SRS. Transisi lain ditolak jika tidak memiliki action yang ditentukan.

## 8.3 Borrowing Integrity

- Satu unit aset maksimal mempunyai satu borrowing aktif Borrowed/Overdue.
- Satu pasangan pengguna–aset maksimal mempunyai satu Pending/Borrowed/Overdue; histori berulang tetap diperbolehkan.
- Pending bukan reservasi. Data terbaru diperiksa lagi saat approval.
- Approval langsung mengaktifkan pinjaman pada tanggal mulai.
- Perubahan borrowing, aset, laporan terkait, asset log, dan audit disimpan atomik.
- Row locking dan constraint mengikuti SRS; UI yang menyembunyikan tombol tidak cukup untuk mencegah persetujuan paralel.
- Returned/Rejected/Cancelled adalah status terminal. Identitas, tanggal, dan snapshot pinjaman yang disetujui tidak diedit melalui form biasa.
- Kalender bisnis Asia/Jakarta digunakan untuk jatuh tempo dan hari terlambat; timestamp disimpan UTC.

## 8.4 Membership Integrity

- Satu pasangan pengguna–ruang mempunyai satu membership yang dipertahankan untuk histori.
- Membership memakai active/revoked; pending/rejected berada pada join request.
- Pembuatan ruang dan Admin pembuat disimpan dalam transaksi yang sama.
- Perubahan role dilakukan Admin yang berwenang pada ruang terkait.
- Minimal satu Admin aktif dipertahankan, termasuk saat ruang diarsipkan.
- Anggota dengan pinjaman aktif tidak dapat dicabut.
- Pencabutan yang sah menutup akses ruang dan membatalkan pending terkait tanpa menghapus relasi historis.
- Kembali ke dashboard pribadi dan logout tidak mengubah membership.

## 8.5 QR Rules

- QR berisi URL/identifier stabil tanpa secret.
- Kode aset dan slug ruang tidak berubah; metadata nama/status/lokasi diambil saat detail dibuka.
- Mengetahui QR tidak menggantikan membership atau otorisasi.
- Guest diminta login; non-anggota diarahkan untuk bergabung tanpa memperoleh detail privat.
- Aset tidak valid atau sudah dihapus tidak memberi akses detail aktif; respons mengikuti aturan SRS.

## 8.6 Asset History vs Audit Log

- **Asset History:** timeline perjalanan satu unit barang.
- **Audit Log:** tindakan sensitif oleh actor pada aset, ruang, membership, settings, atau akun yang relevan.
- Log bersifat append-only; informasi sensitif disaring menurut role.
- Member melihat timeline aset tersaring dan rincian peminjaman pribadinya.
- Audit umum ruang tersedia hanya bagi Admin terkait.

## 8.7 Data Privacy dan Security

- Password di-hash dan semua endpoint terproteksi memeriksa autentikasi/otorisasi.
- Detail inventaris dan data anggota tidak tampil pada profil publik.
- Export dan agregasi dashboard manajemen mengikuti scope ruang.
- Token undangan disimpan sebagai hash, sekali pakai, dengan masa berlaku dan rotasi saat resend.
- Data pribadi peminjam lain tidak dibocorkan melalui timeline, notifikasi, atau URL.
- Error tidak membocorkan stack trace, query, secret, atau data ruang lain.
- Hapus akun/ruang/aset mengikuti soft delete dan perlindungan histori pada SRS.

## 8.8 Failure dan Edge Cases

Minimal diperiksa dalam implementasi:

- Akun baru belum mempunyai ruang.
- Pembuatan ruang gagal di tengah proses.
- Satu akun Admin di ruang A dan Member di ruang B.
- Pengguna kembali ke dashboard pribadi lalu membuka ruang lagi.
- Pengguna tanpa hak membuka URL manajemen.
- Dua operator menyetujui aset yang sama hampir bersamaan.
- Keanggotaan atau status aset berubah antara request dan approval.
- Request masa depan disetujui terlalu awal atau telah lewat tanggal.
- QR dipindai oleh guest/non-anggota atau untuk aset tidak valid.
- Return diproses dua kali atau bersamaan dengan scheduler overdue.
- Pencabutan Admin terakhir atau pengguna yang masih meminjam.
- Hapus/arsip aset atau ruang ketika masih ada pekerjaan aktif.
- Gagal jaringan, email, atau export setelah tindakan diproses.

## 8.9 Damage, Maintenance, dan Denda

Laporan kerusakan dapat berdiri sendiri atau terhubung pada inspeksi borrowing. Penilaian petugas menentukan tindak lanjut; laporan awal anggota tidak otomatis mengubah kondisi fisik.

Maksimal satu maintenance aktif per aset. Pengembalian minor/major menghasilkan laporan inspeksi sesuai SRS. Kondisi Good/Lost tidak membuat laporan inspeksi kerusakan otomatis.

Denda memakai tarif yang disalin saat approval. Sistem membedakan kalkulasi dan angka final; override memerlukan alasan. Pencatatan denda tidak menjadi bukti pembayaran; pelunasan hanya mengikuti pembayaran confirmed pada F12.

---

# 9. Struktur Layar dan Arahan UX/UI

## 9.1 Matriks Layar

Nomor layar 1–20 dipertahankan dari versi sebelumnya. Layar 21–23 memperjelas kebutuhan pembuatan ruang, kerusakan, dan notifikasi.

| Layar | Informasi / aksi utama |
| --- | --- |
| 1 — Landing Page | Penjelasan individu/kelompok, Temukan Ruang, Login, Daftar |
| 2 — Temukan Ruang | Pencarian, filter, kartu ruang |
| 3 — Profil Publik Ruang | Nama, kategori, deskripsi, logo, ringkasan, ajukan bergabung; tanpa katalog privat |
| 4 — Autentikasi | Login, daftar, verifikasi email, lupa/reset password |
| 5 — Dashboard Pribadi | Profil, pinjaman pribadi, pending, Buat Ruang, Temukan Ruang, notifikasi |
| 6 — Ruang Inventaris | Tab Semua Ruang dan Ruang Saya; role/status per ruang |
| 7 — Katalog Ruang | Aset yang dapat diakses anggota, filter, status, kondisi |
| 8 — Detail Aset | Spesifikasi, lokasi, kondisi, status, permintaan, laporan kerusakan, QR sesuai izin |
| 9 — Ajukan Peminjaman | Satu unit, tanggal, alasan, validasi, submit |
| 10 — My Borrowing | Active, Pending, History pribadi dan filter ruang |
| 11 — Scan QR | Kamera, input kode, akses ditolak/ajakan bergabung bila relevan |
| 12 — Dashboard Manajemen Ruang | Nama ruang, role, statistik, grafik, request, pinjaman aktif |
| 13 — Inventory | Daftar aset dan operasi pengelolaan sesuai izin |
| 14 — Form Aset | Metadata, kategori, foto, lokasi; identitas/kondisi/status mengikuti aturan |
| 15 — Pengelolaan Peminjaman | Pending Request, Active Borrowing, History |
| 16 — Return & Inspection | Waktu aktual, kondisi, catatan, denda, konfirmasi |
| 17 — Asset History | Timeline dan filter sesuai hak akses |
| 18 — Members | Join requests, undangan, anggota, role, revoke; Admin |
| 19 — Reports & Settings Ruang | Export CSV, profil, kebijakan, arsip/hapus; Admin |
| 20 — Account Settings | Profil/email/password, preferensi, hapus akun, logout |
| 21 — Buat Ruang Inventaris | Profil ruang, validasi, penetapan Admin pembuat |
| 22 — Damage Reports | Buat laporan, penilaian, keputusan, maintenance sesuai role |
| 24 — Tagihan & Pembayaran | Daftar tagihan, rincian sendiri/manajemen, catat pembayaran, verifikasi, pembebasan, histori |
| 23 — Notifications | Semua/belum dibaca, detail, tandai dibaca, tautan ke resource |

## 9.2 UI State Wajib

Layar operasional mendukung loading, empty, success, validation error, authorization denied, not found, network/server error, disabled action, dan konfirmasi untuk tindakan sensitif. Status tidak dibedakan hanya berdasarkan warna.

Empty state akun baru menjelaskan dua pilihan awal: **Buat ruang untuk mengelola barang** atau **Temukan ruang untuk mengajukan bergabung**.

## 9.3 UX Principle untuk Role

Nama ruang dan role aktif terlihat pada area manajemen. Staff melihat struktur dashboard yang konsisten dengan Admin, dengan Members, Reports, dan Settings nonaktif serta penjelasan izin.

Pengguna dapat menuju dashboard pribadi dari ruang yang dibuka. Tombol ini tidak memakai kata “keluar anggota” karena tidak mengubah keanggotaan. Logout dan pencabutan keanggotaan mempunyai alur tersendiri.

## 9.4 Konsistensi Istilah pada UI

| Maksud | Label yang digunakan |
| --- | --- |
| Menjadi penyedia/pengelola barang | Buat Ruang Inventaris |
| Melihat ruang yang dikelola/diikuti | Ruang Saya |
| Mencari ruang | Temukan Ruang |
| Meminta izin akses ruang | Ajukan Bergabung |
| Meminta satu barang | Ajukan Peminjaman |
| Meninggalkan tampilan manajemen | Kembali ke Dashboard Pribadi |

Nama organisasi formal tidak diwajibkan pada form ruang. Kategori profil mengikuti daftar SRS; Lainnya dapat digunakan untuk ruang individu.

---

# 10. Lingkungan Teknis, Integrasi, dan Dependensi

## 10.1 Stack

| Komponen | Teknologi / arah implementasi |
| --- | --- |
| Backend | Laravel (PHP) dan Eloquent ORM |
| Database MVP | MySQL 8.0.16+ / InnoDB sesuai SRS v1.2 |
| Frontend | Blade dan Livewire |
| Styling | Tailwind CSS dan Flux UI |
| QR generation | Simple QrCode |
| QR scanner | html5-qrcode; input kode manual sebagai alternatif |
| Grafik | Chart.js |
| File dan validasi | Laravel Storage dan Form Request |
| Proses latar | Queue email dan scheduler overdue sesuai SRS |
| Kualitas | Pengujian aturan bisnis/otorisasi dan pemeriksaan kualitas sesuai SRS |

Versi paket yang dipasang, konfigurasi, dan dukungan lingkungan harus dicatat saat implementasi. Daftar ini merupakan kebutuhan/arah teknis, bukan laporan bahwa seluruh dependency sudah berjalan.

## 10.2 Dependensi Implementasi yang Perlu Diverifikasi

- Hosting, staging/production, dan koneksi database.
- SMTP/provider email untuk verifikasi, reset password, undangan, dan notifikasi.
- File/media storage dan validasi upload.
- HTTPS, izin kamera, serta dukungan pembuatan QR PNG bila diperlukan.
- Queue worker dan scheduler.
- Penerapan transaksi, locking, constraint, dan pemeriksaan policy sesuai SRS.
- Backup, restore, monitoring, error reporting, dan retention operasional.

Pilihan database, model role, dan aturan siklus pinjaman dasar sudah mempunyai keputusan pada SRS v1.2. Rincian SQL berada dalam Desain Database v1.0; PRD tidak menduplikasi seluruh kamus data.

## 10.3 Kesiapan Demo

Environment demo memuat:

- Aplikasi dan database yang dapat diakses.
- Akun baru terverifikasi tanpa membership untuk memperlihatkan pembuatan ruang.
- Akun Raka dan Sinta serta satu akun Staff bila skenario delegasi diuji.
- Dua ruang, sehingga satu akun dapat menjadi Admin pada satu ruang dan Member pada ruang lain.
- Barang per unit, pending request, pinjaman aktif, QR, dan histori.
- Email/notifikasi atau status keterbatasan yang dijelaskan sesuai kondisi sebenarnya.

Fitur yang belum berjalan tidak dinyatakan selesai hanya karena desain layar atau dokumennya tersedia.

---

# 11. Skenario Demo dan Rencana Validasi

## 11.1 Skenario Konkret: Peralatan Raka

**Status: simulasi produk untuk demo, bukan laporan pengguna atau transaksi nyata.** Raka dan Sinta berasal dari persona hipotesis.

Raka memiliki kamera CAM-001, tripod TRI-001, dan tenda TEN-001 yang bersedia dipinjamkan kepada lingkungannya. Sinta membutuhkan kamera selama dua hari untuk dokumentasi tugas.

| Tahap | Tindakan | Hasil yang harus diperlihatkan |
| --- | --- | --- |
| A — Pengguna biasa | Raka login dengan akun terverifikasi yang belum mengikuti ruang | Dashboard pribadi tersedia, tanpa role global Admin |
| B — Buat ruang | Raka membuat Peralatan Raka | Ruang dan membership Admin pembuat berhasil dibuat |
| C — Inventaris | Raka membuat kategori dan tiga unit barang serta QR | Barang tersedia pada ruang; pengelolaan individu berjalan tanpa Staff |
| D — Bergabung | Sinta menemukan profil publik dan mengirim join request | Pending membership; katalog privat belum dapat dibuka |
| E — Izin akses | Raka menyetujui Sinta | Membership Sinta aktif sebagai Member |
| F — Meminta barang | Sinta memilih kamera Available untuk dua hari dan mengirim alasan | Borrowing Pending; kamera belum berubah Borrowed |
| G — Persetujuan | Pada tanggal mulai, Raka memeriksa dan menyetujui ketika kamera diserahkan | Borrowing dan kamera menjadi Borrowed; peminjam/tenggat tercatat |
| H — Memantau | Sinta membuka My Borrowing; Raka membuka pinjaman aktif ruang | Kedua tampilan membaca transaksi yang sama sesuai hak akses |
| I — Mengembalikan | Sinta mengembalikan kamera; Raka mencatat inspeksi Good | Borrowing Returned, kamera Available/good, histori tersimpan |
| J — Dashboard pribadi | Raka kembali ke dashboard pribadi, lalu membuka Peralatan Raka lagi | Role Admin tetap ada |
| K — Ruang lain | Raka membuka Perlengkapan Kos Melati yang diikutinya sebagai Member | Hanya hak Member pada ruang tersebut; dapat meminta barang yang eligible |

Skenario ini memperlihatkan penyedia individu, peminjam, dan satu akun dengan role berbeda. Bukti praktik nyata disajikan pada bagian 1.3; skenario demo tidak menggantikan validasi persona.

## 11.2 Skenario Tambahan End-to-End

### Skenario A — Setup dari Akun Baru

1. Daftar dan verifikasi akun tanpa membership.
2. Buka dashboard pribadi.
3. Buat ruang; periksa badge Admin.
4. Buka manajemen, buat kategori/aset, dan generate QR.
5. Pastikan barang Available/good dan ruang tidak memerlukan Staff untuk mulai digunakan.

### Skenario B — Join Member

1. Login sebagai akun yang belum aktif pada ruang tujuan.
2. Temukan ruang, buka profil publik, dan kirim join request.
3. Admin menyetujui; periksa membership aktif dan akses katalog.
4. Periksa bahwa approval membership tidak membuat borrowing.

### Skenario C — Borrowing

1. Anggota aktif mengajukan aset Available dengan tanggal valid.
2. Periksa Pending dan aksi pembatalan peminjam.
3. Pengelola menyetujui pada tanggal mulai; aset Borrowed.
4. Periksa penolakan approval terlalu awal dan double approval.

### Skenario D — Return dan Inspection

1. Buka Borrowed/Overdue.
2. Proses Good, Minor Damage, Major Damage, atau Lost pada data uji terpisah.
3. Periksa status/kondisi, catatan, laporan, denda, dan histori.
4. Periksa penolakan return kedua.

### Skenario E — QR Traceability

1. Scan QR sebagai anggota yang berhak.
2. Ubah nama/lokasi aset lalu scan QR yang sama.
3. Periksa data terbaru dan kode/slug yang tetap.
4. Scan sebagai guest/non-anggota dan pastikan detail privat tidak dibuka.

### Skenario F — Peran Lokal dan Perpindahan Konteks

1. Pakai satu akun yang Admin di ruang A dan Member di ruang B.
2. Buka kedua ruang dan periksa fitur yang tersedia.
3. Kembali ke dashboard pribadi dan buka A lagi.
4. Pastikan role tidak berubah dan URL manajemen B tetap ditolak.

### Skenario G — Kerusakan dan Pelunasan

Kamera kembali tepat waktu dengan kerusakan terverifikasi. Admin menerbitkan damage Rp150.000 dengan bukti; late fee Rp0 tidak diterbitkan. Pembayaran pertama Rp50.000 dikonfirmasi: sisa Rp100.000. Pembayaran berikutnya Rp100.000 masih pending: sisa tetap Rp100.000. Setelah konfirmasi: paid. Ulangi dengan data uji untuk penolakan bukti, void, pembebasan, double submit, dan dua konfirmasi paralel.


## 11.3 Validasi Kebutuhan dan Persona

Sebelum persona dinyatakan tervalidasi, wawancarai calon pengelola dan peminjam yang relevan dengan fokus MVP. Titik awal eksplorasi dapat mencakup dua orang yang pernah meminjamkan barang, dua peminjam, dan satu orang yang membantu pengelolaan bersama. Jumlah ini bukan dasar klaim statistik.

Pertanyaan utama:

1. Ceritakan kejadian meminjam atau meminjamkan barang terakhir yang benar-benar dialami.
2. Barang apa, siapa yang terlibat, dan bagaimana mereka berkoordinasi?
3. Bagaimana tanggal kembali dan kondisi dicatat?
4. Informasi apa yang pernah sulit ditemukan? Apa kejadian konkretnya?
5. Kepada siapa barang bersedia dipinjamkan dan bagaimana izin diberikan?
6. Bagaimana serah terima, penolakan, dan pemeriksaan pengembalian dilakukan?
7. Apakah bergabung sebelum melihat katalog sesuai dengan cara kerja mereka?
8. Informasi apa yang diperlukan sebelum meminta atau menyetujui pinjaman?

Catat tanggal, kode/nama samaran partisipan, konteks, contoh kejadian, cara kerja saat ini, kebutuhan, dan perubahan keputusan produk. Kutipan hanya diisi dari wawancara sebenarnya. Persona Raka/Sinta/Dimas diperbarui berdasarkan pola temuan.

## 11.4 Validasi Tahap 1 — UX / Mockup

Uji tugas dengan calon pengguna atau 3–5 partisipan awal yang relevan. Pengujian internal dapat digunakan untuk pemeriksaan awal dan dicatat sebagai uji internal, terpisah dari validasi kebutuhan calon pengguna.

Tugas meliputi memahami Ruang Inventaris, membuat ruang, bergabung, meminta barang, approval, return, scan QR, dan berpindah konteks dengan satu akun.

Catat keberhasilan tanpa bantuan, waktu penyelesaian, titik kebingungan, error, komentar, dan kebutuhan perbaikan teks. Minta partisipan menjelaskan kembali perbedaan akun, ruang, role, dan permintaan pinjaman.

Mockup memeriksa pemahaman dan alur; integritas database serta concurrency diperiksa pada implementasi.

## 11.5 Validasi Tahap 2 — Functional Build

Bukti uji memuat versi aplikasi/build, skenario, expected result, actual result, serta screenshot/log yang tidak mengekspos data sensitif.

Uji tambahan konsep mencakup pembuatan ruang atomik, akun tanpa membership, peran berbeda pada dua ruang, dashboard pribadi, dan tidak berubahnya role setelah perpindahan tampilan.

## 11.6 Validasi Tahap 3 — Security & Data Isolation

Uji akun A vs B, ruang A vs B, Guest/pengguna tanpa membership/Member/Staff/Admin, direct URL, manipulasi payload, export tanpa izin, dan akses notifikasi pengguna lain.

Sertakan satu akun Admin di ruang A yang mencoba mengelola ruang B tempat ia hanya Member.

## 11.7 Release Gate MVP

MVP siap diuji ketika:

- F01–F12 mempunyai bukti uji atau status belum selesai yang dicatat eksplisit.
- Alur akun baru, buat ruang, keanggotaan, pinjaman, pengembalian, dan inspeksi dapat direproduksi.
- Role per ruang dan perpindahan konteks sesuai aturan.
- Tidak ada akses lintas akun/ruang tanpa izin.
- State borrowing/aset, QR, histori, dan notifikasi dapat diperiksa sesuai batas implementasi.
- Pengujian negatif untuk permission dan integritas kritis selesai.
- Status persona, hasil wawancara, dan keputusan yang masih terbuka dicatat tanpa menganggap hipotesis sebagai temuan.

---

# 12. Roadmap Pengembangan

Tahapan berikut merupakan usulan urutan implementasi, bukan jadwal atau klaim penyelesaian.

| Tahap | Fokus | Output |
| --- | --- | --- |
| Phase A — Foundation | Akun pribadi, pembuatan ruang, membership, role lokal | Akun baru, dashboard pribadi, Admin pembuat, konteks ruang dan policy |
| Phase B — Inventory | Aset per unit, kategori, kondisi, QR | Katalog dan pengelolaan inventaris dengan akses yang benar |
| Phase C — Borrowing | Request, keputusan, pembatalan, pinjaman aktif/overdue | Alur peminjaman dan validasi integritas |
| Phase D — Return & Tracking | Inspeksi, kerusakan, maintenance, histori/audit | Siklus barang yang dapat ditelusuri |
| Phase E — Dashboard & Reports | Notifikasi, statistik, CSV, settings | Pemantauan pribadi dan operasi ruang |
| Phase F — QA & Hardening | Validasi pengguna, otorisasi, concurrency, edge cases | Temuan, revisi persona/UX, dan bukti uji implementasi |

Wawancara kebutuhan awal dilakukan sebelum atau selama Foundation. Validasi pemahaman konsep dan alur dilakukan sejak prototipe, lalu diperbarui berdasarkan temuan.

---

# 13. Catatan Keputusan dan Hal yang Perlu Divalidasi

Bagian ini menggantikan daftar “keputusan terbuka sebelum SRS” pada v0.2. Sebagian besar sudah memperoleh baseline dalam SRS v1.2. ID OD01–OD16 dipertahankan untuk penelusuran; status baseline tidak berarti implementasi sudah diuji.

## 13.1 Status Keputusan

| ID | Topik | Status dan baseline |
| --- | --- | --- |
| OD01 | Database | Ditetapkan pada SRS: MySQL 8.0.16+ / InnoDB |
| OD02 | Siapa dapat membuat ruang | Diperjelas: pengguna terverifikasi dapat membuat ruang dan otomatis menjadi Admin pembuat; dapat mewakili individu/kelompok |
| OD03 | Returned pada aset | Ditetapkan: Returned adalah status borrowing; aset memakai Available/Borrowed/Maintenance/Lost |
| OD04 | Minor Damage | Ditetapkan: aset Available/minor_damage dengan laporan inspeksi sesuai SRS |
| OD05 | Delete vs archive | Ditetapkan: soft delete/arsip mengikuti aturan aktivitas, histori, dan akses pada SRS |
| OD06 | Request masa depan | Ditetapkan: boleh pending, bukan reservasi; approval hanya pada tanggal mulai |
| OD07 | Kuota pinjaman aktif per pengguna | Belum ada angka kuota dalam baseline; batas satu pending/aktif per pengguna–aset dan satu aktif per aset tetap berlaku |
| OD08 | Banyak aset per request | Ditetapkan: satu borrowing untuk satu unit dan satu inspeksi final |
| OD09 | Batas kategori untuk approval | Baseline: Admin/Staff berwenang pada aset eligible dalam ruang; pembatasan kategori khusus belum menjadi fitur MVP |
| OD10 | Kanal notifikasi/provider | Kanal ditetapkan: in-app, email queued, toast; provider SMTP dan konfigurasi lingkungan perlu diverifikasi |
| OD11 | Export | Ditetapkan: CSV untuk aset, peminjaman, dan kerusakan sesuai filter; batas operasional mengikuti implementasi |
| OD12 | Foto/storage/retention | Baseline: foto opsional, Laravel Storage, validasi SRS; konfigurasi storage dan retention operasional perlu diverifikasi |
| OD13 | Concurrency | Ditetapkan pada desain: transaksi, urutan lock, constraint dan policy sesuai SRS; pengujian pada lingkungan target tetap diperlukan |
| OD14 | Maintenance | Ditetapkan: maksimal satu aktif per aset, penilaian/pencatatan penyelesaian dan kondisi akhir |
| OD15 | Lost dan recovery | Ditetapkan: mark found dengan catatan dan penilaian kondisi sesuai SRS; tagihan penggantian loss mengikuti F12 |
| OD16 | QR non-anggota | Ditetapkan: login bila guest; non-anggota diarahkan bergabung tanpa detail privat |
| OD17 | Approval pinjaman sendiri | Diputuskan pemilik proyek pada 27 Oktober 2026: Admin/Staff diizinkan menyetujui pinjaman sendiri. Larangan keputusan finansial sendiri tetap berlaku. |
| OD18 | Persona dan fokus pengguna awal | Hipotesis: mahasiswa pengelola/peminjam perlengkapan; validasi wawancara dan observasi belum dilakukan |

## 13.2 Hal yang Perlu Diperiksa dengan Calon Pengguna

- Apakah masalah pencatatan dan status yang diasumsikan benar-benar dialami.
- Barang apa yang bersedia dipinjamkan dan kepada siapa.
- Apakah membership sebelum melihat katalog sesuai kebiasaan pengguna.
- Informasi yang diperlukan sebelum menyetujui orang bergabung atau meminjam.
- Apakah penamaan Ruang Inventaris dan pemisahan dashboard dipahami.
- Apakah delegasi Staff diperlukan pada segmen awal.
- Apakah pengguna membutuhkan reservasi atau pembayaran otomatis; pencatatan pembayaran manual tagihan sudah termasuk F12.

Perubahan ke katalog publik, pinjaman tanpa membership, reservasi, atau penyewaan berbayar harus dicatat sebagai revisi lingkup dan diselaraskan dengan SRS/desain data.

---

# 14. Ringkasan Model Data Konseptual

Satu akun mempunyai relasi ke beberapa ruang. Role melekat pada relasi tersebut. Ruang menjadi pengelola inventaris dan transaksi; nama teknis organization tidak membatasi penyedia pada lembaga formal.

Model berikut mengikuti SRS v1.2, dengan ekstensi finansial terhadap Desain Database v1.0: **14 tabel inti dan dua tabel pendukung**. PRD menjelaskan makna bisnis; detail kolom, tipe, index, dan SQL berada pada SRS/desain database.

| Entitas / tabel | Peran dalam produk |
| --- | --- |
| User / users | Identitas akun pribadi, profil, verifikasi, dan preferensi |
| Ruang Inventaris / organizations | Profil, aturan, dan konteks pengelolaan ruang |
| Membership / organization_user | Hubungan akun–ruang, role, dan status akses |
| Join Request / membership_requests | Pengajuan akses ruang dan keputusan |
| Invitation / organization_invitations | Undangan yang terikat email dan masa berlaku |
| Asset Category / asset_categories | Kategori barang dalam ruang |
| Asset / assets | Satu unit fisik dengan kode/QR, status, dan kondisi |
| Borrowing / borrowings | Request sampai transaksi akhir, peminjam, keputusan, snapshot, dan inspeksi final |
| Damage Report / damage_reports | Laporan, penilaian, dan tindak lanjut kerusakan/maintenance |
| Asset History / asset_logs | Timeline kejadian pada satu aset |
| Audit Log / audit_logs | Tindakan sensitif dengan actor dan objek terkait |
| Notification / notifications | Notifikasi pribadi penerima; tabel pendukung |
| Borrower Charge / borrower_charges | Tagihan per peminjaman dengan jenis dan nominal terpisah |
| Charge Payment / charge_payments | Pembayaran manual dan status verifikasinya |
| Charge Credit / charge_credits | Pembebasan sebagian/penuh dengan actor dan alasan |
| Password Reset / password_reset_tokens | Dukungan reset password; tabel pendukung |

## 14.1 Relasi Konseptual

- User dan ruang memiliki relasi banyak-ke-banyak melalui membership.
- Satu pasangan akun–ruang mempunyai satu baris membership yang dipertahankan ketika revoked.
- Setiap membership mempunyai role per ruang; User tidak menjadi Admin global melalui relasi ini.
- Ruang mempunyai kategori, aset, membership, undangan, dan transaksi.
- Borrowing menghubungkan satu aset, satu peminjam, dan ruang yang sama.
- Laporan kerusakan terhubung pada aset; referensi borrowing opsional, kecuali laporan inspeksi terkait.
- Perubahan relevan menghasilkan asset log dan/atau audit.
- Notifikasi terikat penerima; tautan di dalamnya tidak memberikan akses tambahan.

## 14.2 Pemetaan Konsep ke Implementasi

Konsep QR tidak memerlukan tabel QR terpisah pada baseline; URL menggunakan slug ruang dan kode aset yang stabil. Konsep BorrowRequest dan BorrowTransaction direpresentasikan oleh tahapan status pada borrowings. Inspeksi final disimpan bersama borrowing; laporan kerusakan inspeksi dibuat sesuai hasil minor/major pada SRS.

Preferensi notifikasi berada pada akun. Statistik dan export berasal dari query berscope, sehingga PRD tidak mensyaratkan tabel ReportExport terpisah untuk MVP.

## 14.3 Prinsip Data

Integritas tidak mengandalkan tampilan UI. Constraint, validation, policy, transaksi, dan logika bisnis menjaga konsistensi ruang, role, dan lifecycle.

Soft delete mempertahankan referensi historis. Role tetap disimpan pada membership ketika pengguna berpindah dashboard. Perubahan label organisasi menjadi Ruang Inventaris pada UI tidak mewajibkan penggantian nama seluruh tabel.

---

# 15. Referensi dan Catatan Perubahan

## 15.1 Referensi

- **R1 — PRD Organization Inventory v0.2.** Sumber struktur, F01–F11, G01–G07, US01–US12, AC01–AC11, dan OD01–OD16.
- **R2 — SRS Organization Inventory v1.2, 27 Oktober 2026.** Acuan kebutuhan per ruang, pembuatan organisasi, role, aturan transaksi, kondisi, kerusakan, notifikasi, serta model data.
- **R3 — Desain Database Organization Inventory v1.0.** Acuan 13 tabel, relasi membership, pemisahan status, snapshot kebijakan, dan integritas data.
- **R4 — Klarifikasi pemilik proyek dan Analisis Konsep/Persona Organization Inventory, 26 Oktober 2026.** Acuan bahwa penyedia dapat individu/kelompok, organisasi berarti ruang inventaris, dan satu akun dapat mempunyai peran berbeda.
- **R5 — Pumpipumpe, Home.** https://pumpipumpe.ch/en/home-en/ — diakses 26 Oktober 2026.
- **R6 — Pumpipumpe, About.** https://pumpipumpe.ch/en/about-en/ — diakses 26 Oktober 2026.

PRD ORBHITA v0.4 merupakan referensi struktur yang tercatat pada PRD v0.2. Konsep produk dan keputusan kebutuhan pada revisi ini mengacu pada sumber Organization Inventory serta klarifikasi pemilik proyek.

## 15.2 Riwayat Revisi

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 0.2 | Tidak dicantumkan pada dokumen sumber | Expanded draft: role, permission, fitur, cerita pengguna, acceptance criteria, integritas, layar, validasi, roadmap, keputusan terbuka, dan model konseptual |
| 0.4 | 27 Oktober 2026 | Menambah F12 biaya kerusakan/loss, pembayaran manual, saldo, pelunasan, koreksi dan tambahan data; mengikuti SRS v1.2 |
| 0.3 | 26 Oktober 2026 | Integrasi konsep individu/kelompok dan ruang inventaris, persona hipotesis, role lokal, dashboard pribadi, referensi nyata, skenario konkret, serta penyelarasan kebutuhan dengan SRS v1.1 |

## 15.3 Rincian Perubahan v0.3

- Memperjelas makna Organization Inventory dan penggunaan label Ruang Inventaris.
- Membedakan akun, ruang, persona, role, penyedia, dan peminjam.
- Mengusulkan segmen validasi kampus serta persona Raka, Sinta, dan Dimas dengan status hipotesis.
- Menambahkan referensi praktik nyata Pumpipumpe beserta batas penggunaan referensinya.
- Menetapkan dashboard pribadi bagi akun baru tanpa membership dan perpindahan konteks tanpa perubahan role.
- Memperluas F02 untuk pembuatan ruang, Admin pembuat, undangan, dan membership.
- Mempertahankan F01–F11, US01–US12, AC01–AC11, G01–G07, dan OD01–OD16; menambahkan US13–US20, G08–G12, OD17–OD18.
- Memperjelas kerusakan/maintenance pada F04 dan notifikasi pada F09 sesuai SRS.
- Menyelaraskan database MVP, status aset/borrowing, keanggotaan active/revoked, tanggal approval, denda, dan inspeksi.
- Mempertegas batas katalog, membership, pembayaran, pengiriman, reservasi, dan transaksi per unit.
- Mengganti demo yang bermula dari Admin siap pakai dengan pengguna biasa yang membuat ruang.
- Menambahkan validasi persona, pengujian satu akun di dua ruang, serta penamaan UI.
- Menyesuaikan model konseptual dengan 13 tabel tanpa menduplikasi kamus data fisik.

## 15.4 Status Review dan Sinkronisasi

Dokumen berstatus draft review; persetujuan formal tim/dosen dan hasil wawancara/pengujian tidak dinyatakan telah ada.

PRD v0.4 digunakan bersama SRS v1.2. Proposal v1.1 dan paket SQL/ERD v1.0 merupakan baseline historis; tambahan finansial dirinci dalam SRS v1.2 dan belum menjadi migrasi SQL yang diuji. Pemetaan F01–F11 dipertahankan dan F12 ditambahkan.
