@props(['org', 'operator', 'admin', 'tab', 'livewireNavigation' => true, 'showFeedback' => true])
<div class="app-wrapper oi-room" @oi-review-borrowing.window="$nextTick(() => document.getElementById('oi-borrowing-' + $event.detail.id)?.scrollIntoView({ block: 'center', behavior: 'smooth' }))" @oi-focus-field.window="$nextTick(() => { const field = document.getElementById('oi-' + $event.detail.field); if (field) { field.focus(); field.scrollIntoView({ block: 'center', behavior: 'smooth' }); } })" style="background: transparent; min-height: 100vh;">
    <!-- Elemen Backdrop untuk menutup sidebar saat klik di luar (khusus mobile) -->
    <div class="sidebar-backdrop" onclick="toggleSidebar()"></div>
    
    <x-room.sidebar :org="$org" :operator="$operator" :admin="$admin" :tab="$tab" :livewire-navigation="$livewireNavigation" />
    
    <div class="app-main" style="background-color: transparent;">
        <!-- TOPBAR (SESUAI FOTO 3) -->
        <header class="app-topbar" style="height: 64px; background: #ffffff; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; position: sticky; top: 0; z-index: 20;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button onclick="toggleSidebar()" class="btn-toggle-sidebar" title="Buka / Tutup Sidebar" style="background: none; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span style="font-size: 1.125rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">{{ $org->name }}</span>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.04em; padding: 0.35rem 0.85rem; border-radius: 9999px; text-transform: uppercase; {{ $admin ? 'background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3;' : ($operator ? 'background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;' : 'background: #f1f5f9; color: #475467; border: 1px solid #e2e8f0;') }}">
                    {{ $admin ? 'ADMIN' : ($operator ? 'STAFF' : 'MEMBER') }}
                </span>

                @if(!$operator)
                    <button type="button" style="padding: 0.5rem 1.15rem; font-size: 0.8125rem; font-weight: 700; border-radius: 0.625rem; background: #7f112e; color: #ffffff; border: none; cursor: pointer; transition: background 0.15s ease;" wire:click="$set('tab', 'inventory')" onmouseover="this.style.background='#680e2c'" onmouseout="this.style.background='#7f112e'">
                        + Ajukan Pinjam
                    </button>
                @endif

                <!-- Tombol Lonceng Notifikasi (Membuka Popup Modal Foto 2) -->
                @php($hasUnreadNotif = auth()->user()?->unreadNotifications()->exists())
                <button type="button" onclick="openModal('modalNotifications')" style="position: relative; width: 40px; height: 40px; border-radius: 9999px; border: 1px solid #e2e8f0; background: #ffffff; display: flex; align-items: center; justify-content: center; color: #64748b; cursor: pointer; transition: all 0.15s ease;" title="Notifikasi Sistem" aria-label="Notifikasi">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($hasUnreadNotif)
                        <span id="unreadNotifDot" style="position: absolute; top: 8px; right: 9px; width: 7px; height: 7px; border-radius: 9999px; background: #7f112e; border: 1.5px solid #ffffff;"></span>
                    @endif
                </button>
            </div>
        </header>

        <!-- MAIN BODY SELALU CENTER SESUAI FOTO 3 -->
        <main class="oi-main page-container" id="room-content" style="flex: 1; padding: 2rem 2.25rem 3.5rem 2.25rem; max-width: 1240px; width: 100%; margin-left: auto; margin-right: auto; box-sizing: border-box;">
            @if($showFeedback)<x-feedback />@endif
            {{ $slot }}
        </main>
    </div>

    <!-- NOTIFICATION SYSTEM POPUP MODAL (SESUAI FOTO 2) -->
    <x-notification-modal />
</div>
