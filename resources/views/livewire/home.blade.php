<div class="oi-explore page-container" style="max-width: 1240px; margin: 0 auto; padding-bottom: 3.5rem;">
    <x-feedback />

    <!-- PAGE HEADER -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 class="page-title" style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.35rem 0;">
                Organization yang Anda Ikuti
            </h1>
            <p class="page-subtitle" style="font-size: 0.875rem; font-weight: 400; color: #64748b; margin: 0;">
                Kelola Organization di mana Anda memiliki otorisasi sebagai Admin, Staff, atau Member.
            </p>
        </div>

        @auth
            <button type="button" class="pill-btn btn-primary-red btn-lg" style="box-shadow: 0 4px 12px rgba(127, 17, 46, 0.22); font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem;" wire:click="$toggle('creating')">
                @if(!$creating)
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                @endif
                {{ $creating ? 'Tutup Formulir' : 'Buat Organization Baru' }}
            </button>
        @endauth
    </div>

    <!-- FORM BUAT ORGANIZATION BARU -->
    @auth
        @if($creating)
            <div class="panel-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <div style="margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                    <h2 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Buat Organization Baru</h2>
                    <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0;">Untuk mengelola inventaris kelompok, laboratorium, atau studio. Anda otomatis menjadi Admin.</p>
                </div>

                <form wire:submit="create">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">Nama Organization</label>
                            <input type="text" class="form-input" wire:model="name" placeholder="Contoh: Media Kreatif Lab" required maxlength="150" autofocus style="width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem; font-size: 0.875rem;">
                            @error('name') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">Kategori</label>
                            <select class="form-input" wire:model="category" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem; font-size: 0.875rem; background: #fff;">
                                @foreach(['komunitas','kampus','sekolah','perusahaan','lainnya'] as $categoryOption)
                                    <option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>
                                @endforeach
                            </select>
                            @error('category') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">Deskripsi Organization (Opsional)</label>
                        <textarea class="form-input" style="width: 100%; min-height: 85px; resize: vertical; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem; font-size: 0.875rem;" wire:model="description" placeholder="Jelaskan jenis peralatan fisik yang dikelola dan peruntukannya..." maxlength="3000"></textarea>
                        @error('description') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="submit" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; font-weight: 700; padding: 0.65rem 1.5rem;" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="create">Simpan & Buat Organization &rarr;</span>
                            <span wire:loading wire:target="create">Membuat Organization…</span>
                        </button>
                        <button type="button" class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; font-weight: 600; padding: 0.65rem 1.25rem;" wire:click="$set('creating', false)">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endauth

    <!-- FILTER ROLES PILLS -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.75rem; flex-wrap: wrap;">
        <button type="button" class="pill-btn {{ $filterRole === 'all' ? 'btn-solid-black' : 'btn-secondary-light' }} btn-sm" wire:click="setFilterRole('all')">
            Semua ({{ $allCount }})
        </button>
        <button type="button" class="pill-btn {{ $filterRole === 'admin' ? 'btn-solid-black' : 'btn-secondary-light' }} btn-sm" wire:click="setFilterRole('admin')">
            Admin Saja ({{ $adminCount }})
        </button>
        <button type="button" class="pill-btn {{ $filterRole === 'staff' ? 'btn-solid-black' : 'btn-secondary-light' }} btn-sm" wire:click="setFilterRole('staff')">
            Staff Saja ({{ $staffCount }})
        </button>
        <button type="button" class="pill-btn {{ $filterRole === 'member' ? 'btn-solid-black' : 'btn-secondary-light' }} btn-sm" wire:click="setFilterRole('member')">
            Member Saja ({{ $memberCount }})
        </button>
    </div>

    <!-- ORGS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        @forelse($organizations as $org)
            @php
                $membership = $memberships->get($org->id);
                $role = $membership ? $membership->role : 'member';
            @endphp
            <div class="panel-card" style="padding: 1.5rem; margin-bottom: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; min-height: 200px;">
                <div>
                    <!-- Top Identity Row -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="display: flex; gap: 1rem; align-items: center; min-width: 0;">
                            <div class="community-avatar" style="width: 48px; height: 48px; border-radius: 9999px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0;">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                            </div>
                            <div style="min-width: 0;">
                                <h3 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $org->name }}
                                </h3>
                                <span style="font-size: 0.75rem; color: #64748b; font-family: monospace;">{{ $org->slug }}</span>
                            </div>
                        </div>
                        <span class="micro-badge {{ $role === 'admin' ? 'badge-role-admin' : ($role === 'staff' ? 'badge-role-staff' : 'badge-role-member') }}">
                            {{ $role === 'admin' ? 'ADMIN' : ($role === 'staff' ? 'STAFF' : 'MEMBER') }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p style="font-size: 0.8125rem; color: #475467; line-height: 1.5; margin-bottom: 1.25rem; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;">
                        {{ $org->description ?: 'Peminjaman peralatan fisik dan inventaris mandiri.' }}
                    </p>
                </div>

                <!-- Footer row -->
                <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: #475467;">
                        <strong>{{ $org->assets_count }}</strong> Aset &middot; <strong>{{ $org->memberships_count }}</strong> Member
                    </span>
                    <a href="{{ route('workspace', $org) }}" class="pill-btn btn-solid-black btn-sm" style="text-decoration: none;">
                        Buka Workspace &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; color: #94a3b8; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem;">
                <p style="font-weight: 800; font-size: 1rem; color: #0f172a; margin-bottom: 0.25rem;">Belum ada Organization yang diikuti</p>
                <p style="font-size: 0.8125rem; margin: 0 0 1.25rem 0;">Anda dapat membuat organisasi baru atau mencari organisasi publik.</p>
                <a href="{{ route('organizations') }}" class="pill-btn btn-secondary-light btn-sm" style="text-decoration: none;">
                    Jelajahi Organization Lain &rarr;
                </a>
            </div>
        @endforelse
    </div>

    @if($organizations->hasPages())
        <div style="margin-top: 1.5rem;">
            {{ $organizations->links() }}
        </div>
    @endif
</div>
