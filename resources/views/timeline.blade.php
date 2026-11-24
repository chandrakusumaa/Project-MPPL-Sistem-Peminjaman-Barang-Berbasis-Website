<x-layout>
    <div class="page-container" style="max-width: 56rem; margin: 2rem auto; padding: 0 1rem; padding-bottom: 3.5rem;">
        
        <!-- HEADER KEMBALI -->
        <div style="margin-bottom: 1.5rem;">
            <a href="{{ route('workspace', $asset->organization) }}" class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.5rem 1rem; gap: 0.375rem; text-decoration: none;">
                &larr; Kembali ke Workspace {{ $asset->organization->name }}
            </a>
        </div>

        <!-- ASSET SUMMARY CARD -->
        <div class="workspace-header-card" style="margin-bottom: 2rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem 2rem; display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div class="org-identity" style="display: flex; gap: 1.25rem; align-items: center; min-width: 0;">
                <div style="width: 5rem; height: 5rem; border-radius: 1rem; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="4"/></svg>
                </div>
                <div class="org-title-group" style="min-width: 0;">
                    <div style="font-family: monospace; font-size: 0.8125rem; color: #7f112e; font-weight: 700; margin-bottom: 0.25rem;">
                        {{ $asset->code }}
                    </div>
                    <h1 class="org-main-name" style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0 0 0.35rem 0; letter-spacing: -0.03em;">
                        {{ $asset->name }}
                    </h1>
                    <p class="org-meta-desc" style="font-size: 0.8125rem; color: #64748b; margin: 0 0 0.5rem 0;">
                        {{ $asset->organization->name }} &middot; Lokasi: <strong style="color: #334155;">{{ $asset->location }}</strong> &middot; Kategori: <strong style="color: #334155;">{{ $asset->category?->name ?: 'Umum' }}</strong>
                    </p>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <span class="micro-badge {{ $asset->status === 'available' ? 'micro-badge-green' : ($asset->status === 'borrowed' ? 'micro-badge-amber' : 'micro-badge-red') }}">
                            <span class="status-dot {{ $asset->status === 'available' ? 'status-dot-green' : ($asset->status === 'borrowed' ? 'status-dot-amber' : 'status-dot-red') }}"></span>
                            {{ $asset->status === 'available' ? 'Tersedia di Rak' : ucfirst($asset->status) }}
                        </span>
                        <span class="micro-badge" style="background: #f8fafc; border-color: #cbd5e1; font-weight: 700;">
                            Kondisi: {{ ucfirst($asset->physical_condition) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="org-quick-actions" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <button type="button" onclick="window.print()" class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.55rem 1.15rem; font-weight: 700;">
                    Cetak Riwayat Unit
                </button>
                <a href="{{ route('workspace', $asset->organization) }}" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.55rem 1.25rem; font-weight: 700; text-decoration: none; box-shadow: 0 4px 12px rgba(127, 17, 46, 0.22);">
                    Buka Workspace &rarr;
                </a>
            </div>
        </div>

        @if($asset->description || $asset->specifications)
            <div class="panel-card" style="padding: 1.5rem 2rem; margin-bottom: 2rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; font-size: 0.84rem; color: #475467; line-height: 1.6;">
                @if($asset->description)<p style="margin: 0 0 0.5rem 0;"><strong>Deskripsi:</strong> {{ $asset->description }}</p>@endif
                @if($asset->specifications)<p style="margin: 0;"><strong>Spesifikasi:</strong> {{ $asset->specifications }}</p>@endif
            </div>
        @endif

        <!-- VERTICAL LIFECYCLE TIMELINE -->
        <div class="panel-card" style="padding: 2.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <h2 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0 0 2rem 0; letter-spacing: -0.02em;">
                Garis Waktu Perjalanan Fisik Unit Ini
            </h2>

            <div class="timeline-tree" style="position: relative; padding-left: 1.75rem; border-left: 2px solid #e2e8f0; margin-left: 0.75rem;">
                @forelse($logs as $l)
                    <div class="timeline-item" style="position: relative; margin-bottom: 2rem;">
                        <!-- Timeline Marker / Dot -->
                        <div class="timeline-marker {{ str_contains(strtolower($l->event), 'kembali') || str_contains(strtolower($l->event), 'inspeksi') ? 'marker-green' : (str_contains(strtolower($l->event), 'pinjam') ? 'marker-blue' : 'marker-red') }}" style="position: absolute; left: -2.35rem; top: 0.15rem; width: 1.15rem; height: 1.15rem; border-radius: 9999px; background: #0f172a; border: 2.5px solid #ffffff; box-shadow: 0 0 0 2px #e2e8f0; display: flex; align-items: center; justify-content: center;">
                            <span style="width: 5px; height: 5px; border-radius: 9999px; background: #ffffff;"></span>
                        </div>

                        <div class="timeline-content">
                            <div class="timeline-time" style="font-size: 0.75rem; color: #94a3b8; font-weight: 600; margin-bottom: 0.25rem;">
                                {{ $l->created_at->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB
                            </div>
                            <div class="timeline-header" style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">
                                {{ $l->event }}
                            </div>
                            @if($l->description)
                                <p class="timeline-desc" style="font-size: 0.8125rem; color: #64748b; margin: 0; line-height: 1.6;">
                                    {{ $l->description }}
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p style="color: #94a3b8; font-size: 0.8125rem; margin: 0;">Belum ada catatan aktivitas untuk aset fisik ini.</p>
                @endforelse
            </div>

            @if($logs->hasPages())
                <div style="margin-top: 1.5rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layout>
