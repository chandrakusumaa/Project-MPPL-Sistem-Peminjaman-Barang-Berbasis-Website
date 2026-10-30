# Visual Gabung langsung

Chromium, 30 Oktober 2026: desktop **1440×1000**, mobile **390×844**. Shell, navbar/sidebar, kartu, palet dan tipografi implementasi MagicPath sebelumnya dipertahankan. Perubahan dibatasi pada aksi bergabung, feedback, pembaruan dropdown dan halaman anggota.

| Hasil | Screenshot |
| --- | --- |
| Sukses Gabung, Member aktif, Buka Organization dan sidebar baru | [Desktop](joined-desktop.png), [mobile](joined-mobile.png) |
| Akses katalog sebagai Member | [Mobile](member-mobile.png) |
| Daftar anggota tanpa invitation/approval, Admin mengubah role | [Desktop](admin-members-desktop.png), [mobile](admin-members-mobile.png) |
| Hak Staff setelah promosi Admin lokal | [Desktop](staff-context-desktop.png) |

Data berasal dari fixture MySQL terisolasi melalui action aplikasi lalu dibersihkan. Nama fixture Organization Privat Pengelola berarti katalog dibatasi membership; profil tetap publik sesuai spesifikasi.

Browser memeriksa Gabung disabled saat request ditahan, feedback sukses, badge Member/Buka Organization, sidebar dari 2 menjadi 3 membership, dan akses katalog. Replay POST role admin/staff tidak menaikkan hak. Admin benar-benar mempromosikan Member menjadi Staff; akses Members tetap nonaktif bagi Staff. Pada mobile halaman tidak overflow horizontal.

Screenshot diperiksa untuk bagian yang berubah, tanpa klaim pixel-identical seluruh produk atau canvas MagicPath live. Hasil backend/migration dan keterbatasan trigger custom terdapat di [laporan implementasi](../../DIRECT_MEMBERSHIP.md).
