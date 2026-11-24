<x-layout>
    <div class="page-container" style="max-width: 64rem; margin: 2rem auto; padding: 0 1rem; padding-bottom: 4rem;">
        
        <!-- HEADER NAVIGASI -->
        <div style="margin-bottom: 1.5rem;">
            <a href="{{ route('organizations') }}" class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.5rem 1rem; gap: 0.375rem; text-decoration: none;">
                &larr; Kembali ke Jelajahi Organization
            </a>
        </div>

        <!-- ORG PROFILE HEADER BANNER -->
        <div class="workspace-header-card" style="margin-bottom: 2rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 2rem 2.25rem; display: flex; justify-content: space-between; align-items: center; gap: 2rem; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div class="org-identity" style="display: flex; gap: 1.5rem; align-items: center; min-width: 0;">
                <div class="community-avatar" style="width: 64px; height: 64px; border-radius: 9999px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0;">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                </div>
                <div class="org-title-group" style="min-width: 0;">
                    <div class="org-main-name" style="font-size: 1.625rem; font-weight: 900; color: #0f172a; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        {{ $org->name }}
                        <span class="micro-badge micro-badge-green">
                            <span class="status-dot status-dot-green"></span> Publik
                        </span>
                    </div>
                    <p class="org-meta-desc" style="font-size: 0.875rem; color: #64748b; margin: 0 0 0.75rem 0; line-height: 1.5;">
                        {{ $org->description ?: 'Organization peminjaman perlengkapan dan inventaris fisik.' }}
                    </p>
                    <div style="display: flex; gap: 1rem; font-size: 0.78rem; color: #64748b; flex-wrap: wrap; align-items: center;">
                        <span>Kategori: <strong style="color: #0f172a;">{{ ucfirst($org->category) }}</strong></span>
                        <span>&bull;</span>
                        <span>Total Aset Fisik: <strong style="color: #0f172a;">{{ $assetCount }} Unit</strong></span>
                        <span>&bull;</span>
                        <span>Anggota: <strong style="color: #0f172a;">{{ $memberCount }} Orang</strong></span>
                        <span>&bull;</span>
                        <span>Maksimal Pinjam: <strong style="color: #0f172a;">7 Hari</strong></span>
                    </div>
                </div>
            </div>

            <div class="org-quick-actions" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                @auth
                    @if(\Illuminate\Support\Facades\Gate::allows('view', $org))
                        <a href="{{ route('workspace', $org) }}" class="pill-btn btn-solid-black btn-lg" style="text-decoration: none; font-weight: 700;">
                            Buka Workspace Organization &rarr;
                        </a>
                    @elseif(!auth()->user()->hasVerifiedEmail())
                        <a href="{{ route('verification.notice') }}" class="pill-btn btn-secondary-light btn-lg" style="text-decoration: none; font-weight: 700;">
                            Verifikasi Email untuk Bergabung
                        </a>
                    @elseif(\Illuminate\Support\Facades\Gate::allows('join', $org))
                        <form method="POST" action="{{ route('organization.join', $org) }}" x-data="{ joining: false }" @submit="joining = true" style="margin: 0;">
                            @csrf
                            <button type="submit" class="pill-btn btn-primary-red btn-lg" style="box-shadow: 0 4px 14px rgba(127, 17, 46, 0.25); font-weight: 800; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.5rem;" :disabled="joining">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span x-show="!joining">Gabung Langsung sebagai Member</span>
                                <span x-cloak x-show="joining">Bergabung…</span>
                            </button>
                        </form>
                    @else
                        <span class="micro-badge micro-badge-amber">Akses bergabung dibatasi</span>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="pill-btn btn-primary-red btn-lg" style="box-shadow: 0 4px 14px rgba(127, 17, 46, 0.25); font-weight: 800; text-decoration: none;">
                        Masuk untuk Bergabung &rarr;
                    </a>
                @endauth
            </div>
        </div>

        <!-- PREVIEW KATALOG BARANG FISIK PUBLIK -->
        <div class="panel-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div class="panel-header" style="margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <h2 class="panel-title" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">Katalog Barang Fisik (Pratinjau Publik)</h2>
                    <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0;">
                        Setiap unit fisik diawasi dengan kode unik stabil. Bergabunglah menjadi member untuk mengajukan peminjaman.
                    </p>
                </div>
            </div>

            @php
                $assets = $org->assets()->with('category')->latest('id')->limit(10)->get();
            @endphp

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 0.85rem 1rem; font-weight: 700;">Kode Fisik</th>
                            <th style="padding: 0.85rem 1rem; font-weight: 700; min-width: 200px;">Nama Aset</th>
                            <th style="padding: 0.85rem 1rem; font-weight: 700;">Kategori</th>
                            <th style="padding: 0.85rem 1rem; font-weight: 700;">Lokasi Fisik</th>
                            <th style="padding: 0.85rem 1rem; font-weight: 700;">Kondisi Fisik</th>
                            <th style="padding: 0.85rem 1rem; font-weight: 700; text-align: center;">Ketersediaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 1rem; white-space: nowrap;">
                                    <span class="micro-badge" style="font-family: monospace; font-size: 0.75rem; background: #f1f5f9; color: #1e293b; font-weight: 800;">
                                        {{ $asset->code }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-weight: 700; color: #0f172a;">
                                    {{ $asset->name }}
                                </td>
                                <td style="padding: 1rem; color: #475467;">
                                    {{ $asset->category?->name ?: 'Umum' }}
                                </td>
                                <td style="padding: 1rem; color: #64748b;">
                                    {{ $asset->location ?: 'Gedung Lab' }}
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="micro-badge micro-badge-green">
                                        {{ ucfirst($asset->physical_condition) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center; white-space: nowrap;">
                                    @if($asset->status === 'available')
                                        <span class="micro-badge micro-badge-green">
                                            <span class="status-dot status-dot-green"></span> Tersedia
                                        </span>
                                    @else
                                        <span class="micro-badge micro-badge-amber">
                                            <span class="status-dot status-dot-amber"></span> {{ ucfirst($asset->status) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                    Belum ada aset fisik yang terdaftar di organisasi ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layout>
