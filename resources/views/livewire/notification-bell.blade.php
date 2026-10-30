<div wire:poll.30s>
    <details class="oi-bell" x-data @click.outside="$el.open = false" @keydown.escape.window="$el.open = false">
        <summary aria-label="Notifikasi"><x-icon name="bell" />@if($count)<span class="oi-bell-count">{{ $count > 99 ? '99+' : $count }}</span><span class="sr-only">{{ $count }} belum dibaca</span>@endif</summary>
        <div class="oi-bell-menu">
            <strong>Notifikasi</strong>
            @forelse($recent as $notification)
                <div class="oi-bell-item"><b>{{ $notification->data['title'] ?? 'Aktivitas akun' }}</b><p>{{ $notification->data['message'] ?? '' }}</p><small>{{ $notification->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</small></div>
            @empty
                <p>Belum ada notifikasi.</p>
            @endforelse
            <a href="{{ route('notifications') }}">Semua notifikasi →</a>
        </div>
    </details>
</div>
