<div class="oi-explore oi-public-explore" style="max-width: 1140px; margin: 0 auto; padding-bottom: 4rem;">
    
    <!-- HEADER HERO SECTION (SESUAI FOTO 4) -->
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <h1 style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0 0 0.5rem 0;">
            Jelajahi Organization Publik
        </h1>
        <p style="font-size: 0.84rem; font-weight: 400; color: #64748b; max-width: 600px; margin: 0 auto 1.75rem auto; line-height: 1.5;">
            Temukan komunitas laboratorium, studio, atau komunitas kampus untuk meminjam peralatan fisik secara mandiri.
        </p>

        <!-- SEARCH BAR -->
        <div style="display: flex; max-width: 480px; margin: 0 auto 1.5rem auto; gap: 0.5rem;">
            <input type="text" class="form-input" placeholder="Cari nama organisasi, lab, atau jenis alat..." wire:model.live.debounce.300ms="search" style="border-radius: 9999px; padding: 0.65rem 1.25rem; font-size: 0.84rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <button type="button" class="btn-solid-black" style="border-radius: 9999px; padding: 0.65rem 1.5rem; font-size: 0.8125rem; font-weight: 700; cursor: pointer; border: none;">
                Cari
            </button>
        </div>

        <!-- CATEGORY PILLS -->
        <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" class="pill-btn {{ empty($category) ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.72rem; padding: 0.4rem 0.875rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;" wire:click="$set('category', '')">
                SEMUA KATEGORI
            </button>
            <button type="button" class="pill-btn {{ ($category ?? '') === 'laboratorium' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.72rem; padding: 0.4rem 0.875rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;" wire:click="$set('category', 'laboratorium')">
                LABORATORIUM
            </button>
            <button type="button" class="pill-btn {{ ($category ?? '') === 'studio' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.72rem; padding: 0.4rem 0.875rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;" wire:click="$set('category', 'studio')">
                STUDIO MULTIMEDIA
            </button>
            <button type="button" class="pill-btn {{ ($category ?? '') === 'komunitas' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.72rem; padding: 0.4rem 0.875rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;" wire:click="$set('category', 'komunitas')">
                KOMUNITAS HOBI
            </button>
            <button type="button" class="pill-btn {{ ($category ?? '') === 'kampus' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.72rem; padding: 0.4rem 0.875rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;" wire:click="$set('category', 'kampus')">
                UNIT KEGIATAN (UKM)
            </button>
        </div>
    </div>

    <!-- 3 COLUMNS ORGANIZATIONS GRID (SESUAI FOTO 4) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        @forelse($organizations as $org)
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div>
                    <!-- Top Identity Row -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="width: 44px; height: 44px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                            </svg>
                        </div>
                        <span style="background: #ecfdf5; color: #047857; border-radius: 9999px; padding: 0.2rem 0.55rem; font-size: 0.65rem; font-weight: 800; text-transform: uppercase;">
                            • PUBLIK
                        </span>
                    </div>

                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">
                        {{ $org->name }}
                    </h3>

                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1.25rem 0; line-height: 1.5; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden;">
                        {{ $org->description ?: 'Peminjaman kamera DSLR/Mirrorless, tripod karbon, pencahayaan LED studio, dan perlengkapan produksi film.' }}
                    </p>
                </div>

                <div>
                    <!-- Meta stats -->
                    <div style="font-size: 0.72rem; color: #64748b; font-weight: 600; margin-bottom: 1rem; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">
                        {{ $org->assets_count }} Aset Fisik &middot; 24 Anggota &middot; Maks 7 Hari
                    </div>

                    <!-- Action button -->
                    <a href="{{ route('organization.public', $org) }}" class="btn-maroon" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.8125rem; font-weight: 700; box-sizing: border-box; cursor: pointer; text-align: center; text-decoration: none;">
                        Lihat Profil Komunitas
                    </a>
                </div>
            </div>
        @empty
            <!-- Demo Cards matching Foto 4 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="width: 44px; height: 44px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center;"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
                        <span style="background: #ecfdf5; color: #047857; border-radius: 9999px; padding: 0.2rem 0.55rem; font-size: 0.65rem; font-weight: 800;">• PUBLIK</span>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">Media Kreatif Lab</h3>
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1.25rem 0; line-height: 1.5;">Peminjaman kamera DSLR/Mirrorless, tripod karbon, pencahayaan LED studio, dan perlengkapan produksi film.</p>
                </div>
                <div>
                    <div style="font-size: 0.72rem; color: #64748b; font-weight: 600; margin-bottom: 1rem; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">48 Aset Fisik &middot; 24 Anggota &middot; Maks 7 Hari</div>
                    <a href="{{ route('organizations') }}" class="btn-maroon" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.8125rem; font-weight: 700; box-sizing: border-box; text-align: center; text-decoration: none;">Lihat Profil Komunitas</a>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="width: 44px; height: 44px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center;"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
                        <span style="background: #ecfdf5; color: #047857; border-radius: 9999px; padding: 0.2rem 0.55rem; font-size: 0.65rem; font-weight: 800;">• PUBLIK</span>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">Robotika & Otomasi Lab</h3>
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1.25rem 0; line-height: 1.5;">Inventaris mikrokontroler Arduino/ESP32, modul sensor IoT, 3D printer portable, osiloskop, dan bor solder listrik.</p>
                </div>
                <div>
                    <div style="font-size: 0.72rem; color: #64748b; font-weight: 600; margin-bottom: 1rem; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">32 Aset Fisik &middot; 18 Anggota &middot; Maks 3 Hari</div>
                    <a href="{{ route('organizations') }}" class="btn-maroon" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.8125rem; font-weight: 700; box-sizing: border-box; text-align: center; text-decoration: none;">Lihat Profil Komunitas</a>
                </div>
            </div>

            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="width: 44px; height: 44px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center;"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
                        <span style="background: #ecfdf5; color: #047857; border-radius: 9999px; padding: 0.2rem 0.55rem; font-size: 0.65rem; font-weight: 800;">• PUBLIK</span>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">Komunitas Fotografi Kampus</h3>
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1.25rem 0; line-height: 1.5;">Koleksi lensa prime, telephoto, filter ND, gimbal stabilizer, dan baterai cadangan untuk kegiatan fotografi jalanan.</p>
                </div>
                <div>
                    <div style="font-size: 0.72rem; color: #64748b; font-weight: 600; margin-bottom: 1rem; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">15 Aset Fisik &middot; 42 Anggota &middot; Maks 5 Hari</div>
                    <a href="{{ route('organizations') }}" class="btn-maroon" style="width: 100%; padding: 0.65rem 1rem; font-size: 0.8125rem; font-weight: 700; box-sizing: border-box; text-align: center; text-decoration: none;">Lihat Profil Komunitas</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- BOTTOM CTA BANNER (SESUAI FOTO 4) -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 2rem 2.25rem; display: flex; justify-content: space-between; align-items: center; gap: 2rem; flex-wrap: wrap;">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">
                Kelola inventaris laboratorium atau organisasi Anda sendiri?
            </h3>
            <p style="font-size: 0.84rem; font-weight: 400; color: #64748b; margin: 0;">
                Buat komunitas Organization baru dalam 1 menit dan dapatkan kode QR fisik unik untuk setiap unit barang Anda.
            </p>
        </div>

        <a href="{{ route('personal.organizations', ['create' => 1]) }}" class="btn-solid-black" style="border-radius: 9999px; padding: 0.75rem 1.5rem; font-size: 0.84rem; font-weight: 700; white-space: nowrap; text-decoration: none; border: none;">
            + Buat Organization Baru
        </a>
    </div>

</div>
