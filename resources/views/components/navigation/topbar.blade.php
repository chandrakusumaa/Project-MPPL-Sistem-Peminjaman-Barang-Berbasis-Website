@props(['sidebarId' => null, 'menuLabel' => 'Buka menu akun'])

@php
    $user = auth()->user();
    $initials = $user ? collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('') : 'U';
@endphp

<header style="height: 70px; background: #ffffff; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; position: sticky; top: 0; z-index: 30; width: 100%;">
    <div style="max-width: 1200px; width: 100%; margin: 0 auto; padding: 0 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem;">
        
        <!-- BRAND LOGO (KIRI) -->
        <a href="{{ route('landing') }}" style="display: flex; align-items: center; text-decoration: none; flex-shrink: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="Organizing Inventory" style="height: 38px; width: auto; object-fit: contain;">
        </a>

        <!-- NAV LINKS (TENGAH - SESUAI FOTO 4 & 5) -->
        <nav style="display: flex; align-items: center; gap: 2rem; font-size: 0.84rem; font-weight: 700;">
            <a href="{{ route('organizations') }}" style="color: {{ request()->routeIs('organizations') ? '#80142B' : '#475467' }}; text-decoration: none; transition: color 0.15s ease;" onmouseover="this.style.color='#80142B'" onmouseout="this.style.color='{{ request()->routeIs('organizations') ? '#80142B' : '#475467' }}'">
                Jelajahi Organization
            </a>
            <a href="{{ route('scan.qr') }}" style="color: {{ request()->routeIs('scan.qr') ? '#80142B' : '#475467' }}; text-decoration: none; transition: color 0.15s ease;" onmouseover="this.style.color='#80142B'" onmouseout="this.style.color='{{ request()->routeIs('scan.qr') ? '#80142B' : '#475467' }}'">
                Scan QR Cepat
            </a>
            <a href="{{ route('landing') }}#features" style="color: #475467; text-decoration: none; transition: color 0.15s ease;" onmouseover="this.style.color='#80142B'" onmouseout="this.style.color='#475467'">
                Fitur Utama
            </a>
        </nav>

        <!-- USER PILL / AUTH (KANAN - SESUAI FOTO 4 & 5) -->
        <div style="display: flex; align-items: center; gap: 0.875rem;">
            @auth
                <!-- User Pill matching Foto 4 & 5 -->
                <div style="display: inline-flex; align-items: center; gap: 0.65rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.25rem 0.35rem 0.25rem 0.35rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="width: 28px; height: 28px; border-radius: 9999px; background: #1e1b4b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.6875rem; font-weight: 800;">
                        {{ $initials ?: 'WA' }}
                    </div>
                    <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; padding-right: 0.25rem;">
                        {{ $user->name }}
                    </span>
                    <a href="{{ route('dashboard') }}" style="background: #fff1f2; color: #80142B; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.72rem; font-weight: 800; text-decoration: none; transition: all 0.15s ease;">
                        Dashboard &rarr;
                    </a>
                </div>

                @if($sidebarId)
                    <button type="button" class="oi-mobile-toggle" @click="menuOpen = !menuOpen" style="background: none; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; cursor: pointer; color: #64748b;">
                        <x-icon name="menu" size="18" />
                    </button>
                @endif
            @else
                <a href="{{ route('login') }}" class="pill-btn btn-secondary-light" style="padding: 0.45rem 1rem; font-size: 0.8125rem;">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="btn-maroon" style="padding: 0.45rem 1.15rem; font-size: 0.8125rem;">
                    Daftar
                </a>
            @endauth
        </div>

    </div>
</header>
