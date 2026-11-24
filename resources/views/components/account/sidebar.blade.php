@php
    $userName = auth()->user()->name ?? 'User';
    $userEmail = auth()->user()->email ?? 'user@orginventory.id';
    $initials = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('');
@endphp

<aside class="app-sidebar oi-sidebar" id="account-sidebar" style="height: 100vh; width: 240px; padding: 1.25rem 0.875rem 1rem 0.875rem; box-sizing: border-box; display: flex; flex-direction: column;" @click="if (window.innerWidth <= 1024 && $event.target.closest('a, button')) toggleSidebar()" aria-label="Navigasi akun pribadi">
    
    <div style="flex: 1; overflow-y: auto; padding-right: 2px;">
        <!-- BRAND LOGO (SESUAI FOTO 1) -->
        <div style="padding: 0 0.5rem 1.5rem 0.5rem;">
            <a href="{{ route('landing') }}" style="display: flex; align-items: center; text-decoration: none;">
                <img src="{{ asset('images/logo.png') }}" alt="Organizing Inventory" style="height: 38px; width: auto; object-fit: contain;">
            </a>
        </div>

        <!-- SECTION 1: MENU UTAMA -->
        <div style="margin-bottom: 1.5rem;">
            <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.5rem 0.75rem;">
                MENU UTAMA
            </div>
            <nav style="display: flex; flex-direction: column; gap: 0.25rem;">
                <!-- 1. Dashboard Saya -->
                <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: {{ request()->routeIs('dashboard') ? '800' : '700' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('dashboard') ? 'background: #fff1f2; color: #7f112e; border: 1.5px solid #fecdd3;' : 'color: #334155; border: 1.5px solid transparent;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard Saya</span>
                    </div>
                </a>

                <!-- 2. Organization Saya -->
                <a href="{{ route('personal.organizations') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: {{ request()->routeIs('personal.organizations*') ? '800' : '700' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('personal.organizations*') ? 'background: #fff1f2; color: #7f112e; border: 1.5px solid #fecdd3;' : 'color: #334155; border: 1.5px solid transparent;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Organization Saya</span>
                    </div>
                </a>

                <!-- 3. Peminjaman Saya -->
                <a href="{{ route('personal.borrowings') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: {{ request()->routeIs('personal.borrowings*') ? '800' : '700' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('personal.borrowings*') ? 'background: #fff1f2; color: #7f112e; border: 1.5px solid #fecdd3;' : 'color: #334155; border: 1.5px solid transparent;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Peminjaman Saya</span>
                    </div>
                    <span style="background: #7f112e; color: #ffffff; border-radius: 9999px; padding: 0.15rem 0.5rem; font-size: 0.6875rem; font-weight: 800;">1</span>
                </a>

                <!-- 4. Tagihan & Denda -->
                <a href="{{ route('personal.charges') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: {{ request()->routeIs('personal.charges*') ? '800' : '700' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('personal.charges*') ? 'background: #fff1f2; color: #7f112e; border: 1.5px solid #fecdd3;' : 'color: #334155; border: 1.5px solid transparent;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Tagihan & Denda</span>
                    </div>
                </a>

                <!-- 5. Profil Saya -->
                <a href="{{ route('profile') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: {{ request()->routeIs('profile*') ? '800' : '700' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('profile*') ? 'background: #fff1f2; color: #7f112e; border: 1.5px solid #fecdd3;' : 'color: #334155; border: 1.5px solid transparent;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profil Saya</span>
                    </div>
                </a>
            </nav>
        </div>

        <!-- SECTION 2: AKSES CEPAT (SESUAI FOTO 1) -->
        <div>
            <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.5rem 0.75rem;">
                AKSES CEPAT
            </div>
            <nav style="display: flex; flex-direction: column; gap: 0.25rem;">
                <a href="{{ route('organizations') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: 700; color: #334155; text-decoration: none; transition: all 0.15s ease;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Jelajahi Organization Lain</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- BOTTOM USER CARD (SESUAI FOTO 1) -->
    <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; margin-top: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
            <div style="width: 36px; height: 36px; border-radius: 9999px; background: #1e1b4b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.8125rem; font-weight: 800; flex-shrink: 0; letter-spacing: -0.02em;">
                {{ $initials ?: 'WA' }}
            </div>
            <div style="min-width: 0; overflow: hidden;">
                <span style="display: block; font-size: 0.8125rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                    {{ $userName }}
                </span>
                <small style="display: block; font-size: 0.6875rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;">
                    {{ $userEmail }}
                </small>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin: 0; flex-shrink: 0;">
            @csrf
            <button type="submit" title="Keluar" style="background: none; border: none; padding: 6px; cursor: pointer; color: #94a3b8; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: color 0.15s ease;" onmouseover="this.style.color='#7f112e'" onmouseout="this.style.color='#94a3b8'">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>

</aside>
