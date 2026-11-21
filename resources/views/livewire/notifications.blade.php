<div wire:poll.30s style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
    <div class="oi-page-head" style="margin-bottom: 0;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin: 0;">Notifikasi</h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0;">Pemberitahuan aktivitas peminjaman, status laporan, dan keanggotaan.</p>
        </div>
        <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:click="allRead">
            Tandai Semua Dibaca
        </button>
    </div>

    <section class="panel-card oi-panel">
        <div class="panel-body oi-panel-body" style="padding: 0;">
            @forelse($notifications as $n)
                <div class="list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 1.125rem 1.5rem; border-bottom: 1px solid #f1f5f9; background: {{ $n->read_at ? '#ffffff' : '#fef2f2' }};" wire:key="notif-{{ $n->id }}">
                    <div style="display: flex; align-items: flex-start; gap: 1rem;">
                        <div style="width: 36px; height: 36px; border-radius: 0.5rem; background: {{ $n->read_at ? '#f1f5f9' : '#fee2e2' }}; color: {{ $n->read_at ? '#64748b' : '#dc2626' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.125rem;">
                            <x-icon name="bell" :size="20" />
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.9375rem; color: #0f172a;">{{ $n->data['title'] ?? 'Aktivitas Komunitas' }}</div>
                            <div style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0;">{{ $n->created_at?->diffForHumans() }}</div>
                            <a href="{{ $n->data['url'] ?? '/dashboard' }}" style="font-size: 0.8125rem; font-weight: 700; color: #dc2626; text-decoration: none;">Lihat Detail Komunitas →</a>
                        </div>
                    </div>
                    @unless($n->read_at)
                        <button type="button" class="oi-btn oi-btn-ghost oi-btn-sm" style="border-radius: 9999px; font-size: 0.75rem;" wire:click="read('{{ $n->id }}')">
                            Tandai Dibaca
                        </button>
                    @endunless
                </div>
            @empty
                <div class="oi-empty" style="padding: 3rem 1.5rem;">
                    <x-icon name="bell" :size="32" />
                    <h3>Belum ada notifikasi</h3>
                    <p>Notifikasi aktivitas terbaru akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="oi-pagination" style="padding: 1rem 1.5rem;">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</div>
