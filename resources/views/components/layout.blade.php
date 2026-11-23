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

    <!-- Script Interaktivitas Toggle Sidebar (Desktop Collapse & Mobile Drawer) -->
    <script>
    function toggleSidebar() {
      const wrapper = document.querySelector('.app-wrapper');
      const sidebar = document.querySelector('.app-sidebar');
      const backdrop = document.querySelector('.sidebar-backdrop');

      if (window.innerWidth > 1024) {
        // Mode Desktop: ciutkan / lebarkan sidebar
        if (wrapper) wrapper.classList.toggle('sidebar-collapsed');
      } else {
        // Mode Mobile: buka / tutup drawer
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
    </script>

    @livewireScripts
    @fluxScripts
</body>
</html>
