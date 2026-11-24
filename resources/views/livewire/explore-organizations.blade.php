<div class="oi-explore oi-public-explore" style="max-width: 1240px; margin: 0 auto; padding-bottom: 4rem;">
    
    <!-- HEADER HERO SECTION -->
    <div style="text-align: center; max-width: 760px; margin: 2rem auto 3rem auto; position: relative; z-index: 2; padding: 0 1rem;">
        <h1 style="font-size: 2.25rem; font-weight: 900; color: #0f172a; letter-spacing: -0.04em; margin-bottom: 0.75rem;">
            Jelajahi Organization Publik
        </h1>
        <p style="font-size: 1rem; color: #64748b; line-height: 1.6; margin-bottom: 2rem;">
            Temukan komunitas laboratorium, studio, atau komunitas kampus untuk meminjam peralatan fisik secara mandiri.
        </p>

        <!-- SEARCH BAR -->
        <div style="max-width: 34rem; margin: 0 auto 1.5rem auto; display: flex; gap: 0.625rem;">
            <div class="search-input-wrapper" style="flex: 1; position: relative;">
                <input type="text" class="form-input" placeholder="Cari nama organisasi, lab, atau jenis alat..." wire:model.live.debounce.300ms="search" style="border-radius: 9999px; padding: 0.7rem 1.25rem; font-size: 0.875rem; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box;">
            </div>
            <button type="button" class="pill-btn btn-solid-black" style="padding: 0 1.5rem; font-size: 0.8125rem; font-weight: 700;">
                Cari
            </button>
        </div>

        <!-- CATEGORY PILLS -->
        <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" class="micro-badge {{ empty($category) ? 'micro-badge-dark' : '' }}" style="cursor: pointer; border: 1px solid #e2e8f0; {{ empty($category) ? 'background: #0f172a; color: #fff; border-color: #0f172a;' : 'background: #ffffff; color: #475467;' }} padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 700;" wire:click="$set('category', '')">
                Semua Kategori
            </button>
            <button type="button" class="micro-badge {{ $category === 'laboratorium' ? 'micro-badge-dark' : '' }}" style="cursor: pointer; border: 1px solid #e2e8f0; {{ $category === 'laboratorium' ? 'background: #0f172a; color: #fff; border-color: #0f172a;' : 'background: #ffffff; color: #475467;' }} padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 700;" wire:click="$set('category', 'laboratorium')">
                Laboratorium
            </button>
            <button type="button" class="micro-badge {{ $category === 'studio' ? 'micro-badge-dark' : '' }}" style="cursor: pointer; border: 1px solid #e2e8f0; {{ $category === 'studio' ? 'background: #0f172a; color: #fff; border-color: #0f172a;' : 'background: #ffffff; color: #475467;' }} padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 700;" wire:click="$set('category', 'studio')">
                Studio Multimedia
            </button>
            <button type="button" class="micro-badge {{ $category === 'komunitas' ? 'micro-badge-dark' : '' }}" style="cursor: pointer; border: 1px solid #e2e8f0; {{ $category === 'komunitas' ? 'background: #0f172a; color: #fff; border-color: #0f172a;' : 'background: #ffffff; color: #475467;' }} padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 700;" wire:click="$set('category', 'komunitas')">
                Komunitas Hobi
            </button>
            <button type="button" class="micro-badge {{ $category === 'kampus' ? 'micro-badge-dark' : '' }}" style="cursor: pointer; border: 1px solid #e2e8f0; {{ $category === 'kampus' ? 'background: #0f172a; color: #fff; border-color: #0f172a;' : 'background: #ffffff; color: #475467;' }} padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 700;" wire:click="$set('category', 'kampus')">
                Unit Kegiatan (UKM)
            </button>
        </div>
    </div>

    <!-- ORG CARDS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem; margin-bottom: 4rem;">
        @forelse($organizations as $org)
            <div class="feature-card" style="padding: 1.75rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div class="community-avatar" style="width: 48px; height: 48px; border-radius: 9999px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0;">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                            </svg>
                        </div>
                        <span class="micro-badge micro-badge-green">
                            <span class="status-dot status-dot-green"></span> Publik
                        </span>
                    </div>

                    <h3 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin-bottom: 0.375rem;">
                        {{ $org->name }}
                    </h3>

                    <p style="font-size: 0.8125rem; color: #64748b; line-height: 1.5; margin-bottom: 1rem; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden;">
                        {{ $org->description ?: 'Peminjaman kamera DSLR/Mirrorless, tripod karbon, pencahayaan LED studio, dan perlengkapan produksi film.' }}
                    </p>

                    <div style="display: flex; gap: 0.75rem; font-size: 0.75rem; color: #475467; padding-top: 0.75rem; border-top: 1px solid #f8fafc; align-items: center; flex-wrap: wrap;">
                        <span><strong>{{ $org->assets_count }}</strong> Aset Fisik</span>
                        <span>&middot;</span>
                        <span>Maks <strong>7</strong> Hari</span>
                        <span>&middot;</span>
                        <span style="font-family: monospace; color: #94a3b8;">{{ $org->slug }}</span>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem;">
                    <a href="{{ route('organization.public', $org) }}" class="pill-btn btn-primary-red" style="flex: 1; text-align: center; text-decoration: none; justify-content: center; font-weight: 700;">
                        Lihat Profil Organization
                    </a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; color: #94a3b8; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem;">
                <p style="font-weight: 800; font-size: 1rem; color: #0f172a; margin-bottom: 0.25rem;">Tidak ada Organization publik ditemukan</p>
                <p style="font-size: 0.8125rem; margin: 0;">Coba gunakan kata kunci pencarian lain atau pilih kategori yang berbeda.</p>
            </div>
        @endforelse
    </div>

    @if($organizations->hasPages())
        <div style="margin-bottom: 3rem;">
            {{ $organizations->links() }}
        </div>
    @endif

    <!-- BOTTOM CTA BANNER -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 2.25rem 2.5rem; display: flex; justify-content: space-between; align-items: center; gap: 2rem; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div>
            <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">
                Kelola inventaris laboratorium atau organisasi Anda sendiri?
            </h3>
            <p style="font-size: 0.875rem; font-weight: 400; color: #64748b; margin: 0;">
                Buat Organization baru dalam 1 menit dan dapatkan kode pelacakan fisik unik untuk setiap unit barang Anda.
            </p>
        </div>

        <a href="{{ route('personal.organizations', ['create' => 1]) }}" class="pill-btn btn-solid-black" style="border-radius: 9999px; padding: 0.75rem 1.625rem; font-size: 0.84rem; font-weight: 700; white-space: nowrap; text-decoration: none;">
            + Buat Organization Baru
        </a>
    </div>

</div>
