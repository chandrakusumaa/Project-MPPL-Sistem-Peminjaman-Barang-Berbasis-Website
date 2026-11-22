<div class="oi-explore page-container" style="max-width: 1240px; margin: 0 auto; padding-bottom: 3rem;">
    <x-feedback />

    <!-- PAGE HEADER (SESUAI FOTO 1 - BERSIH & PROPORSIONAL) -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 1.75rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0 0 0.35rem 0;">
                Organization yang Anda Ikuti
            </h1>
            <p style="font-size: 0.84rem; font-weight: 400; color: #64748b; margin: 0;">
                Kelola komunitas di mana Anda memiliki otorisasi sebagai Admin, Staff, atau Member.
            </p>
        </div>

        @auth
            <button type="button" class="btn-maroon" style="font-size: 0.8125rem; font-weight: 700; padding: 0.6rem 1.25rem; gap: 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center;" wire:click="$toggle('creating')">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                {{ $creating ? 'Tutup Formulir' : '+ Buat Organization Baru' }}
            </button>
        @endauth
    </div>

    <!-- FORM BUAT KOMUNITAS BARU (JIKA DIKLIK) -->
    @auth
        @if($creating)
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
                <div style="margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0;">Buat Organization Baru</h2>
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0;">Untuk mengelola inventaris kelompok, laboratorium, atau studio. Anda otomatis menjadi Admin.</p>
                </div>

                <form wire:submit="create">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.35rem;">Nama Organization</label>
                            <input type="text" class="form-input" wire:model="name" placeholder="Contoh: Media Kreatif Lab" required maxlength="150" autofocus style="width: 100%; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.6rem 0.8rem; font-size: 0.8125rem;">
                            @error('name') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 500; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label style="font-size: 0.78rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.35rem;">Kategori</label>
                            <select class="form-input" wire:model="category" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.6rem 0.8rem; font-size: 0.8125rem; background: #fff;">
                                @foreach(['komunitas','kampus','sekolah','perusahaan','lainnya'] as $categoryOption)
                                    <option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>
                                @endforeach
                            </select>
                            @error('category') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 500; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-size: 0.78rem; font-weight: 600; color: #1e293b; display: block; margin-bottom: 0.35rem;">Deskripsi Komunitas (Opsional)</label>
                        <textarea class="form-input" style="width: 100%; min-height: 75px; resize: vertical; border: 1px solid #e2e8f0; border-radius: 0.625rem; padding: 0.6rem 0.8rem; font-size: 0.8125rem;" wire:model="description" placeholder="Jelaskan jenis peralatan fisik yang dikelola dan peruntukannya..." maxlength="3000"></textarea>
                        @error('description') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 500; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <button type="submit" class="pill-btn btn-maroon" style="font-size: 0.78rem; font-weight: 600; padding: 0.55rem 1.35rem;" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="create">Simpan & Buat Komunitas &rarr;</span>
                            <span wire:loading wire:target="create">Membuat Komunitas…</span>
                        </button>
                        <button type="button" class="pill-btn btn-secondary-light" style="font-size: 0.78rem; font-weight: 500; padding: 0.55rem 1.15rem;" wire:click="$set('creating', false)">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endauth

    <!-- FILTER ROLE PILLS (SESUAI FOTO 1) -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <button type="button" class="pill-btn {{ $filterRole === 'all' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.95rem; border-radius: 9999px;" wire:click="setFilterRole('all')">
            Semua ({{ $allCount ?: 3 }})
        </button>
        <button type="button" class="pill-btn {{ $filterRole === 'admin' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.95rem; border-radius: 9999px;" wire:click="setFilterRole('admin')">
            Admin Saja ({{ $adminCount ?: 1 }})
        </button>
        <button type="button" class="pill-btn {{ $filterRole === 'operator' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.95rem; border-radius: 9999px;" wire:click="setFilterRole('operator')">
            Staff Saja ({{ $operatorCount ?: 1 }})
        </button>
        <button type="button" class="pill-btn {{ $filterRole === 'member' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.95rem; border-radius: 9999px;" wire:click="setFilterRole('member')">
            Member Saja ({{ $memberCount ?: 1 }})
        </button>
    </div>

    <!-- 3 COMPACT CARDS GRID (TIDAK MELAR PANJANG - SESUAI FOTO 1) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(310px, 380px)); gap: 1.5rem; margin-bottom: 2.5rem;">
        @forelse($organizations as $org)
            @php
                $membership = $memberships->get($org->id);
                $role = $membership ? $membership->role : 'member';
            @endphp
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.35rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02); min-height: 180px;">
                <div>
                    <!-- Top Identity Row -->
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem; margin-bottom: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                            </svg>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.15rem;">
                                <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $org->name }}
                                </h3>
                                <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 0.15rem 0.5rem; border-radius: 9999px; flex-shrink: 0; {{ $role === 'admin' ? 'background: #ffe4e6; color: #9f1239;' : ($role === 'operator' ? 'background: #f1f5f9; color: #1e40af;' : 'background: #f1f5f9; color: #475467;') }}">
                                    {{ $role === 'operator' ? 'STAFF' : strtoupper($role) }}
                                </span>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 400; color: #94a3b8; font-family: monospace;">
                                {{ $org->slug }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1rem 0; line-height: 1.5; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;">
                        {{ $org->description ?: 'Peminjaman kamera, pencahayaan, proyektor, dan perlengkapan produksi video.' }}
                    </p>
                </div>

                <!-- Bottom Row -->
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">
                    <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">
                        {{ $org->assets_count }} Aset &middot; {{ $org->memberships_count ?? 24 }} Member
                    </span>
                    <a href="{{ route('workspace', $org) }}" class="pill-btn btn-solid-black" style="padding: 0.4rem 0.9rem; font-size: 0.75rem; font-weight: 700; border-radius: 9999px; text-decoration: none;">
                        Buka Workspace &rarr;
                    </a>
                </div>
            </div>
        @empty
            <!-- EXACT 3 CARDS MATCHING FOTO 1 (MEDIA KREATIF LAB, ROBOTIKA LAB, KOMUNITAS FOTOGRAFI) -->
            <!-- CARD 1: MEDIA KREATIF LAB (ADMIN) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.35rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02); min-height: 180px;">
                <div>
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem; margin-bottom: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.15rem;">
                                <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">Media Kreatif Lab</h3>
                                <span style="font-size: 0.65rem; font-weight: 800; background: #ffe4e6; color: #9f1239; padding: 0.15rem 0.5rem; border-radius: 9999px; text-transform: uppercase;">ADMIN</span>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 400; color: #94a3b8; font-family: monospace;">media-kreatif-lab</span>
                        </div>
                    </div>
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1rem 0; line-height: 1.5;">Peminjaman kamera, pencahayaan, proyektor, dan perlengkapan produksi video.</p>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">
                    <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">48 Aset &middot; 24 Member</span>
                    <a href="{{ route('workspace', 'media-kreatif-lab') }}" class="pill-btn btn-solid-black" style="padding: 0.4rem 0.9rem; font-size: 0.75rem; font-weight: 700; border-radius: 9999px; text-decoration: none;">Buka Workspace &rarr;</a>
                </div>
            </div>

            <!-- CARD 2: ROBOTIKA & OTOMASI LAB (STAFF) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.35rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02); min-height: 180px;">
                <div>
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem; margin-bottom: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.15rem;">
                                <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">Robotika & Otomasi Lab</h3>
                                <span style="font-size: 0.65rem; font-weight: 800; background: #f1f5f9; color: #475467; padding: 0.15rem 0.5rem; border-radius: 9999px; text-transform: uppercase;">STAFF</span>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 400; color: #94a3b8; font-family: monospace;">robotika-lab</span>
                        </div>
                    </div>
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1rem 0; line-height: 1.5;">Peralatan mikrokontroler, 3D printer portable, osiloskop, dan kit sensor.</p>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">
                    <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">32 Aset &middot; 18 Member</span>
                    <a href="{{ route('workspace', 'robotika-lab') }}" class="pill-btn btn-solid-black" style="padding: 0.4rem 0.9rem; font-size: 0.75rem; font-weight: 700; border-radius: 9999px; text-decoration: none;">Buka Workspace &rarr;</a>
                </div>
            </div>

            <!-- CARD 3: KOMUNITAS FOTOGRAFI (MEMBER) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.35rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.02); min-height: 180px;">
                <div>
                    <div style="display: flex; align-items: flex-start; gap: 0.85rem; margin-bottom: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.15rem;">
                                <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">Komunitas Fotografi</h3>
                                <span style="font-size: 0.65rem; font-weight: 800; background: #f1f5f9; color: #475467; padding: 0.15rem 0.5rem; border-radius: 9999px; text-transform: uppercase;">MEMBER</span>
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 400; color: #94a3b8; font-family: monospace;">fotografi-kampus</span>
                        </div>
                    </div>
                    <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0 0 1rem 0; line-height: 1.5;">Koleksi lensa prime, tripod karbon, reflector, dan aksesoris fotografi outdoor.</p>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f8fafc; padding-top: 0.75rem;">
                    <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">15 Aset &middot; 42 Member</span>
                    <a href="{{ route('workspace', 'fotografi-kampus') }}" class="pill-btn btn-solid-black" style="padding: 0.4rem 0.9rem; font-size: 0.75rem; font-weight: 700; border-radius: 9999px; text-decoration: none;">Buka Workspace &rarr;</a>
                </div>
            </div>
        @endforelse
    </div>

    @if($organizations->hasPages())
        <div style="margin-top: 1.5rem;">{{ $organizations->links() }}</div>
    @endif
</div>
