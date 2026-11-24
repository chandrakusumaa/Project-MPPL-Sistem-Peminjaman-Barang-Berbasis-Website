@php
    $notifUser = auth()->user();
    $modalNotifications = $notifUser ? $notifUser->notifications()->latest()->limit(15)->get() : collect();
    $unreadNotifCount = $notifUser ? $notifUser->unreadNotifications()->count() : 0;
@endphp
<div id="modalNotifications" class="modal-backdrop" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;" onclick="if(event.target === this) closeModal('modalNotifications')">
  <div class="modal-dialog" style="max-width: 520px; width: 100%; background: #ffffff; border-radius: 1.25rem; box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
    <div class="modal-header" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9;">
      <div style="display: flex; align-items: center; gap: 0.625rem;">
        <svg width="20" height="20" fill="none" stroke="#7f112e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <h3 class="modal-title" style="font-size: 1.0625rem; font-weight: 800; color: #0f172a; margin: 0;">Notifikasi Sistem</h3>
        @if($unreadNotifCount > 0)
          <span style="background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; border-radius: 9999px; padding: 0.1rem 0.5rem; font-size: 0.6875rem; font-weight: 800;">{{ $unreadNotifCount }} baru</span>
        @endif
      </div>
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        @if($unreadNotifCount > 0)
          <form method="POST" action="{{ route('notifications.read-all') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="pill-btn btn-ghost btn-sm" style="font-size: 0.75rem; color: #64748b; font-weight: 600; background: none; border: none; cursor: pointer; padding: 0.25rem 0.5rem;" onmouseover="this.style.color='#7f112e'" onmouseout="this.style.color='#64748b'">
              Tandai Semua Dibaca
            </button>
          </form>
        @endif
        <button type="button" onclick="closeModal('modalNotifications')" class="modal-close-btn" style="background: none; border: none; font-size: 1.25rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0.25rem;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
      </div>
    </div>
    <div class="modal-body" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 0.75rem; max-height: 65vh; overflow-y: auto;">
      @forelse($modalNotifications as $n)
        @php
            $isUnread = $n->read_at === null;
            $event = $n->data['event'] ?? '';
            $title = $n->data['title'] ?? 'Aktivitas baru';
            $url = $n->data['url'] ?? null;
        @endphp
        <div class="notif-card-item" style="background: {{ $isUnread ? '#fffbeb' : '#f8fafc' }}; border: 1px solid {{ $isUnread ? '#fde68a' : '#e2e8f0' }}; border-radius: 0.875rem; padding: 1rem 1.15rem;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem;">
            <div style="min-width: 0;">
              <div style="font-size: 0.875rem; color: {{ $isUnread ? '#92400e' : '#0f172a' }}; font-weight: 800;">
                {{ $title }}
              </div>
              @if($event && $event !== $title)
                <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0 0;">{{ $event }}</p>
              @endif
              @if($url)
                <div style="margin-top: 0.6rem;">
                  <a href="{{ $url }}" style="font-size: 0.75rem; padding: 0.35rem 0.85rem; background: #7f112e; color: #ffffff; text-decoration: none; border-radius: 0.5rem; display: inline-block; font-weight: 700;">
                    Buka Detail &rarr;
                  </a>
                </div>
              @endif
            </div>
            <span style="font-size: 0.6875rem; color: #94a3b8; white-space: nowrap; font-weight: 600;">
              {{ $n->created_at?->timezone('Asia/Jakarta')->format('d M, H:i') }} WIB
            </span>
          </div>
        </div>
      @empty
        <div style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
          <div style="font-size: 0.9375rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada notifikasi</div>
          <p style="font-size: 0.8125rem; margin: 0;">Aktivitas peminjaman, pengembalian, kerusakan, dan tagihan akan muncul di sini.</p>
        </div>
      @endforelse
    </div>
    <div class="modal-footer" style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
      <a href="{{ route('notifications') }}" style="font-size: 0.75rem; font-weight: 700; color: #7f112e; text-decoration: none;">Lihat semua notifikasi &rarr;</a>
      <button type="button" onclick="closeModal('modalNotifications')" style="padding: 0.5rem 1.35rem; font-size: 0.8125rem; font-weight: 700; background: #05214c; color: #ffffff; border-radius: 0.5rem; border: none; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#021430'" onmouseout="this.style.background='#05214c'">
        Tutup
      </button>
    </div>
  </div>
</div>
