<div class="app-wrapper oi-room oi-account" style="background: transparent; min-height: 100vh;">
    <!-- Elemen Backdrop untuk menutup sidebar saat klik di luar (khusus mobile) -->
    <div class="sidebar-backdrop" onclick="toggleSidebar()"></div>
    
    <!-- LEFT SIDEBAR (STICKY / FULL HEIGHT) -->
    <x-account.sidebar />

    <!-- RIGHT MAIN CONTENT WRAPPER -->
    <div class="app-main" style="background-color: transparent;">
        <!-- TOPBAR -->
        <header class="app-topbar" style="height: 64px; background: #ffffff; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; position: sticky; top: 0; z-index: 20;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button onclick="toggleSidebar()" class="btn-toggle-sidebar" title="Buka / Tutup Sidebar">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.015em;">
                    {{ request()->routeIs('dashboard') ? 'Dashboard Saya' : (request()->routeIs('personal.borrowings') ? 'Peminjaman Saya' : (request()->routeIs('personal.charges') ? 'Tagihan & Denda' : (request()->routeIs('profile') ? 'Profil Saya' : (request()->routeIs('personal.organizations*') ? 'Organization Saya' : 'Dashboard')))) }}
                </h1>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.875rem;">
                <!-- Notification Bell with Red Dot (Opens Popup Modal) -->
                @php($hasUnreadNotif = auth()->user()?->unreadNotifications()->exists())
                <button type="button" onclick="openModal('modalNotifications')" style="position: relative; width: 38px; height: 38px; border-radius: 9999px; border: 1px solid #e2e8f0; background: #ffffff; display: flex; align-items: center; justify-content: center; color: #64748b; cursor: pointer; transition: all 0.15s ease;" title="Notifikasi Sistem" aria-label="Notifikasi">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($hasUnreadNotif)
                        <span id="unreadNotifDot" style="position: absolute; top: 7px; right: 8px; width: 7px; height: 7px; border-radius: 9999px; background: #7f112e; border: 1.5px solid #ffffff;"></span>
                    @endif
                </button>
            </div>
        </header>

        <!-- MAIN BODY (CENTERED & PROPORTIONAL) -->
        <main class="oi-main page-container" id="account-content" style="flex: 1; padding: 2rem 2.25rem 3.5rem 2.25rem; max-width: 1240px; width: 100%; margin-left: auto; margin-right: auto; box-sizing: border-box;">
            @if($showFeedback)<x-feedback />@endif
            {{ $slot }}
        </main>
    </div>

    <!-- NOTIFICATION SYSTEM POPUP MODAL (SESUAI FOTO 2) -->
    <x-notification-modal />
</div>
