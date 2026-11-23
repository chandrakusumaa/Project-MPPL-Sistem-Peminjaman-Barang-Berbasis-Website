<x-layout>
    <div style="max-width: 56rem; margin: 2rem auto; padding: 0 1rem; padding-bottom: 3rem;">
        <!-- HEADER KEMBALI -->
        <div style="margin-bottom: 1.5rem;">
            <a href="{{ route('workspace', $asset->organization) }}" class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.5rem 1rem; gap: 0.375rem;">
                &larr; Kembali ke Workspace {{ $asset->organization->name }}
            </a>
        </div>

        <!-- ASSET IDENTITY CARD -->
        <div class="feature-card" style="padding: 2.25rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                        <h1 style="font-size: 1.625rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0;">{{ $asset->name }}</h1>
                        <span class="micro-badge" style="font-family: monospace; font-size: 0.75rem;">{{ $asset->code }}</span>
                    </div>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0;">Lokasi Penyimpanan: <strong style="color: #334155;">{{ $asset->location }}</strong> · Kategori: <strong style="color: #334155;">{{ $asset->category?->name ?: 'Umum' }}</strong></p>
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span class="micro-badge {{ $asset->status === 'available' ? 'micro-badge-green' : ($asset->status === 'borrowed' ? 'micro-badge-red' : '') }}" style="padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 800;">
                        Status: {{ ucfirst($asset->status) }}
                    </span>
                    <span class="micro-badge" style="padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 800; background: #f8fafc; border-color: #cbd5e1;">
                        Kondisi: {{ ucfirst($asset->physical_condition) }}
                    </span>
                </div>
            </div>

            @if($asset->description || $asset->specifications)
                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; font-size: 0.8125rem; color: #64748b; line-height: 1.6;">
                    @if($asset->description)<p style="margin: 0 0 0.5rem 0;"><strong>Deskripsi:</strong> {{ $asset->description }}</p>@endif
                    @if($asset->specifications)<p style="margin: 0;"><strong>Spesifikasi:</strong> {{ $asset->specifications }}</p>@endif
                </div>
            @endif
        </div>

        <!-- TIMELINE VERTICAL -->
        <div class="feature-card" style="padding: 2rem;">
            <h2 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 1.5rem 0; letter-spacing: -0.02em;">
                Garis Waktu & Log Riwayat Aset Fisik
            </h2>

            <div style="position: relative; padding-left: 1.5rem; border-left: 2px solid #e2e8f0; margin-left: 0.75rem;">
                @forelse($logs as $l)
                    <div style="position: relative; margin-bottom: 1.75rem;">
                        <!-- Bullet Dot -->
                        <div style="position: absolute; left: -1.95rem; top: 0.25rem; width: 0.75rem; height: 0.75rem; border-radius: 9999px; background: #dc2626; border: 2px solid #ffffff; box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);"></div>

                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem;">
                                <strong style="font-size: 0.875rem; color: #0f172a;">{{ $l->event }}</strong>
                                <small style="font-size: 0.6875rem; color: #94a3b8;">{{ $l->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small>
                            </div>
                            @if($operator && $l->description)
                                <p style="font-size: 0.8125rem; color: #64748b; margin: 0.375rem 0 0 0; line-height: 1.5;">{{ $l->description }}</p>
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
