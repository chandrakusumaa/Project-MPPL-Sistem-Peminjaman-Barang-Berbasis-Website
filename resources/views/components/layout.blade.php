@props(['context' => null, 'showFeedback' => true])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Organization Inventory</title>
    <!-- Google Fonts: Plus Jakarta Sans for Crisp Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1.7">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @fluxAppearance
</head>
<body style="position: relative; background-color: #f8fafc; color: #0f172a; min-height: 100vh;">
    <!-- Ambient Dotted Grid & Watermark Silhouette Background -->
    <div style="position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden;" aria-hidden="true">
        <svg style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.045;" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="grid-dots-global" width="28" height="28" patternUnits="userSpaceOnUse">
              <circle cx="2" cy="2" r="1.25" fill="#0f172a" />
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#grid-dots-global)" />
        </svg>

        <!-- Faint Silhouette: Kamera (Kiri - digeser agar tetap tampak jelas saat sidebar 260px terbuka) -->
        <svg style="position: absolute; left: 17%; top: 10%; width: 30rem; height: 30rem; color: #0f172a; opacity: 0.085; transform: rotate(-5deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          <circle cx="12" cy="13" r="4" stroke-width="1.4" />
          <circle cx="12" cy="13" r="2.2" stroke-width="1.2" opacity="0.6" />
        </svg>

        <!-- Faint Silhouette: Proyektor (Kanan Atas) -->
        <svg style="position: absolute; right: 3%; top: 14%; width: 32rem; height: 32rem; color: #0f172a; opacity: 0.085; transform: rotate(7deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
          <rect x="2" y="7" width="20" height="10" rx="2" />
          <circle cx="8" cy="12" r="3" stroke-width="1.4" />
          <path d="M16 11h2m-2 2h2" stroke-linecap="round" />
          <path d="M6 17l-2 3M18 17l2 3" stroke-linecap="round" stroke-width="1.4" />
        </svg>

        <!-- Faint Silhouette: Audio Mixer / Gadget (Bawah Tengah) -->
        <svg style="position: absolute; left: 45%; bottom: 4%; width: 26rem; height: 26rem; color: #0f172a; opacity: 0.075; transform: rotate(-4deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3">
          <rect x="3" y="5" width="18" height="14" rx="2" />
          <circle cx="8" cy="10" r="2" />
          <circle cx="16" cy="10" r="2" />
          <path d="M6 15h12M10 15v2M14 15v2" stroke-linecap="round" />
        </svg>
    </div>

    <div style="position: relative; z-index: 1;">
        @if($context === 'workspace')
            {{ $slot }}
        @elseif($context === 'public' || !auth()->check() || request()->routeIs('organization.public'))
            @include('components.layouts.public')
        @else
            @include('components.layouts.account')
        @endif
    </div>

    <!-- Script Interaktivitas Toggle Sidebar & Modal Notifikasi Global -->
    <script>
    function toggleSidebar() {
      const wrapper = document.querySelector('.app-wrapper');
      const sidebar = document.querySelector('.app-sidebar');
      const backdrop = document.querySelector('.sidebar-backdrop');

      if (window.innerWidth > 1024) {
        if (wrapper) wrapper.classList.toggle('sidebar-collapsed');
      } else {
        if (sidebar) sidebar.classList.toggle('is-open');
        if (backdrop) backdrop.classList.toggle('is-active', sidebar.classList.contains('is-open'));
      }
    }
    window.toggleSidebar = toggleSidebar;

    // Shortcut keyboard Ctrl + B untuk buka/tutup
    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
        e.preventDefault();
        toggleSidebar();
      }
    });

    // Universal Modal Handlers
    window.openModal = function(modalId) {
      let modal = document.getElementById(modalId);
      if (!modal && (modalId === 'modalNotifications' || modalId === 'modalNotification')) {
        window.openNotificationModal();
        return;
      }
      if (!modal) {
        console.warn('Modal not found:', modalId);
        return;
      }
      modal.classList.add('is-open', 'is-active');
      modal.style.setProperty('display', 'flex', 'important');
      modal.style.setProperty('opacity', '1', 'important');
      modal.style.setProperty('visibility', 'visible', 'important');
      modal.style.setProperty('pointer-events', 'auto', 'important');
      document.body.style.overflow = 'hidden';
    };

    window.closeModal = function(modalId) {
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.classList.remove('is-open', 'is-active');
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
        modal.style.pointerEvents = 'none';
        modal.style.setProperty('display', 'none', 'important');
        modal.style.setProperty('opacity', '0', 'important');
        modal.style.setProperty('visibility', 'hidden', 'important');
        modal.style.setProperty('pointer-events', 'none', 'important');
      }
      const remaining = document.querySelectorAll('.modal-backdrop.is-open, .modal-backdrop.is-active, .modal-overlay.is-open, .modal-overlay.is-active');
      if (!remaining || remaining.length === 0) {
        document.body.style.overflow = '';
      }
    };

    // Universal Notification Popup Modal (Foto 2)
    window.openNotificationModal = function() {
      let modal = document.getElementById('modalNotifications');
      if (!modal) {
        const container = document.createElement('div');
        container.id = 'modalNotifications';
        container.className = 'modal-backdrop';
        container.style.cssText = 'display: none; position: fixed; inset: 0; z-index: 999999; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;';
        container.onclick = function(e) { if (e.target === container) window.closeModal('modalNotifications'); };
        
        container.innerHTML = `
        <div class="modal-dialog" style="max-width: 520px; width: 100%; background: #ffffff; border-radius: 1.25rem; box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
          <div class="modal-header" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 0.625rem;">
              <svg width="20" height="20" fill="none" stroke="#7f112e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
              <h3 class="modal-title" style="font-size: 1.0625rem; font-weight: 800; color: #0f172a; margin: 0;">Notifikasi Sistem</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <button type="button" onclick="closeModal('modalNotifications')" class="modal-close-btn" style="background: none; border: none; font-size: 1.25rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0.25rem;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
            </div>
          </div>
          <div class="modal-body" style="padding: 2rem 1.5rem; text-align: center; color: #94a3b8;">
            <div style="font-size: 0.9375rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada notifikasi</div>
            <p style="font-size: 0.8125rem; margin: 0;">Aktivitas peminjaman, pengembalian, kerusakan, dan tagihan akan muncul di sini.</p>
          </div>
          <div class="modal-footer" style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <a href="{{ route('notifications') }}" style="font-size: 0.75rem; font-weight: 700; color: #7f112e; text-decoration: none;">Lihat semua notifikasi &rarr;</a>
            <button type="button" onclick="closeModal('modalNotifications')" style="padding: 0.5rem 1.35rem; font-size: 0.8125rem; font-weight: 700; background: #05214c; color: #ffffff; border-radius: 0.5rem; border: none; cursor: pointer;">
              Tutup
            </button>
          </div>
        </div>`;
        document.body.appendChild(container);
        modal = container;
      }
      
      modal.classList.add('is-open', 'is-active');
      modal.style.setProperty('display', 'flex', 'important');
      modal.style.setProperty('opacity', '1', 'important');
      modal.style.setProperty('visibility', 'visible', 'important');
      modal.style.setProperty('pointer-events', 'auto', 'important');
      document.body.style.overflow = 'hidden';
    };

    // Global listener untuk semua tombol notifikasi di halaman manapun
    document.addEventListener('click', function(e) {
      // 1. Jika klik tombol close atau elemen closeModal, JANGAN dicegat! Biarkan closeModal berjalan
      if (e.target.closest('.modal-close-btn, [onclick*="closeModal"], [data-modal-close]')) {
        return;
      }
      
      // 2. Hanya cegat tombol pembuka notifikasi
      const notifBtn = e.target.closest('[data-open-notif], [onclick*="openModal(\'modalNotifications\')"], [onclick*="openModal(\\"modalNotifications\\")"], [onclick*="openNotificationModal"], button[aria-label="Notifikasi"], button[title*="Notifikasi"], .btn-notif-bell');
      if (notifBtn) {
        e.preventDefault();
        e.stopPropagation();
        window.openNotificationModal();
      }
    });

    // Menutup modal dengan tombol Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' || e.key === 'Esc') {
        window.closeModal('modalNotifications');
      }
    });
    </script>

    @livewireScripts
    @fluxScripts
</body>
</html>
