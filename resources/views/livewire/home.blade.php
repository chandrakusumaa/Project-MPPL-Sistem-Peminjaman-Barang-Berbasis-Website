<div class="oi-explore" style="max-width: 72rem; margin: 0 auto; padding-bottom: 3rem;">
    <x-feedback />

    <!-- PAGE HEADER -->
    <div class="oi-page-head" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.375rem 0;">
                {{ $listing === 'mine' ? 'Komunitas Saya' : 'Jelajahi Komunitas' }}
            </h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                {{ $listing === 'mine' ? 'Daftar komunitas yang Anda kelola maupun ikuti.' : 'Temukan komunitas dan langsung bergabung sebagai Member untuk meminjam barang.' }}
            </p>
        </div>

        @auth
            @if(auth()->user()->hasVerifiedEmail())
                <button type="button" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.625rem 1.25rem; gap: 0.5rem;" wire:click="$toggle('creating')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                    {{ $creating ? 'Tutup Formulir' : 'Buat Komunitas Baru' }}
                </button>
            @endif
        @endauth
    </div>

    <!-- FORM BUAT KOMUNITAS BARU (JIKA DIKLIK) -->
    @auth
        @if(!auth()->user()->hasVerifiedEmail())
            <div class="feature-card" style="padding: 1.5rem; margin-bottom: 2rem; border-color: #fecdd6; background: #fff1f2;">
                <h3 style="font-size: 1rem; font-weight: 800; color: #991b1b; margin-bottom: 0.25rem;">Verifikasi Email Diperlukan</h3>
                <p style="font-size: 0.8125rem; color: #7f1d1d; margin-bottom: 1rem;">Akun Anda belum terverifikasi. Silakan verifikasi email terlebih dahulu untuk membuat komunitas baru.</p>
                <a class="pill-btn btn-solid-black" style="font-size: 0.75rem; padding: 0.5rem 1rem;" href="{{ route('verification.notice') }}">Kirim Ulang Email Verifikasi</a>
            </div>
        @elseif($creating)
            <div class="feature-card" style="padding: 2rem; margin-bottom: 2.5rem; border-color: rgba(220, 38, 38, 0.3);">
                <div style="margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Buat Komunitas Baru</h2>
                    <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">Untuk mengelola inventaris kelompok, lab, atau komunitas. Anda otomatis menjadi <strong>Admin</strong>.</p>
                </div>

                <form wire:submit="create">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Nama Komunitas</label>
                            <input type="text" class="form-input" wire:model="name" placeholder="Contoh: Media Kreatif Lab" required maxlength="150" autofocus>
                            @error('name') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Kategori</label>
                            <select class="form-input" wire:model="category">
                                @foreach(['komunitas','kampus','sekolah','perusahaan','lainnya'] as $categoryOption)
                                    <option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>
                                @endforeach
                            </select>
                            @error('category') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.25rem;">
                        <label class="form-label">Deskripsi Komunitas (Opsional)</label>
                        <textarea class="form-input" style="min-height: 80px; resize: vertical;" wire:model="description" placeholder="Jelaskan jenis peralatan fisik yang dikelola dan peruntukannya..." maxlength="3000"></textarea>
                        @error('description') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="submit" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.625rem 1.5rem;" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="create">Simpan & Buat Komunitas &rarr;</span>
                            <span wire:loading wire:target="create">Membuat Komunitas…</span>
                        </button>
                        <button type="button" class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.625rem 1.25rem;" wire:click="$set('creating', false)">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endauth

    <!-- NAVIGASI TAB: KOMUNITAS SAYA vs JELAJAHI -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
        <button type="button" class="pill-btn {{ $listing === 'mine' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; padding: 0.45rem 1rem;" wire:click="showListing('mine')">
            Komunitas Saya
        </button>
        <button type="button" class="pill-btn {{ $listing === 'all' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; padding: 0.45rem 1rem;" wire:click="showListing('all')">
            Jelajahi Semua Komunitas
        </button>
    </div>

    <!-- FILTER & SEARCH -->
    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <input type="text" class="form-input" style="max-width: 20rem; padding: 0.5rem 0.875rem; font-size: 0.8125rem;" placeholder="Cari nama komunitas..." wire:model.live.debounce.300ms="search">
        <select class="form-input" style="max-width: 14rem; padding: 0.5rem 0.875rem; font-size: 0.8125rem;" wire:model.live="filterCategory">
            <option value="">Semua Kategori</option>
            @foreach(['komunitas','kampus','sekolah','perusahaan','lainnya'] as $catOption)
                <option value="{{ $catOption }}">{{ ucfirst($catOption) }}</option>
            @endforeach
        </select>
    </div>

    <div wire:loading.delay class="muted" style="font-size: 0.8125rem; margin-bottom: 1rem; font-weight: 600; color: #dc2626;">Memuat komunitas…</div>

    <!-- GRID KOMUNITAS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem;">
        @forelse($organizations as $org)
            <x-organization-card :org="$org" :membership="$memberships->get($org->id)?->status === 'active' ? $memberships->get($org->id) : null">
                @if($memberships->get($org->id)?->status === 'active')
                    <a class="pill-btn btn-solid-black" style="font-size: 0.75rem; padding: 0.4rem 0.875rem;" href="{{ route('workspace', $org) }}">
                        Buka Komunitas &rarr;
                    </a>
                @elseif(auth()->check() && !auth()->user()->hasVerifiedEmail())
                    <a class="pill-btn btn-secondary-light" style="font-size: 0.75rem; padding: 0.4rem 0.875rem;" href="{{ route('verification.notice') }}">
                        Verifikasi email untuk gabung
                    </a>
                @elseif($memberships->get($org->id)?->status === 'revoked')
                    <span class="micro-badge micro-badge-red">Akses dicabut</span>
                @elseif(auth()->check())
                    <button class="pill-btn btn-primary-red" style="font-size: 0.75rem; padding: 0.4rem 0.875rem;" wire:click="join({{ $org->id }})" wire:loading.attr="disabled" wire:target="join({{ $org->id }})">
                        <span wire:loading.remove wire:target="join({{ $org->id }})">Gabung Langsung &rarr;</span>
                        <span wire:loading wire:target="join({{ $org->id }})">Bergabung…</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="pill-btn btn-secondary-light" style="font-size: 0.75rem; padding: 0.4rem 0.875rem;">
                        Masuk untuk bergabung
                    </a>
                @endif
            </x-organization-card>
        @empty
            <div class="feature-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem 1.5rem;">
                <div style="width: 48px; height: 48px; margin: 0 auto 1rem auto; color: #94a3b8;">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Belum ada Komunitas yang sesuai</h3>
                <p style="font-size: 0.8125rem; color: #64748b; max-width: 24rem; margin: 0 auto 1.5rem auto;">
                    {{ $listing === 'mine' ? 'Buat komunitas sendiri atau jelajahi komunitas publik untuk mulai meminjam.' : 'Coba ubah kata kunci pencarian atau kategori lain.' }}
                </p>
                @if($listing === 'mine')
                    <button type="button" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.625rem 1.25rem;" wire:click="$set('creating', true)">
                        + Buat Komunitas Pertama
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    @if($organizations->hasPages())
        <div style="margin-top: 1.5rem;">{{ $organizations->links() }}</div>
    @endif
</div>
