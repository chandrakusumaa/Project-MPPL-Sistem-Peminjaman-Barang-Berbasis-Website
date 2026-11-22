@php
    $userName = auth()->user()->name ?? 'User';
    $userEmail = auth()->user()->email ?? 'user@orginventory.id';
    $initials = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('');
@endphp

<aside class="app-sidebar oi-sidebar" id="account-sidebar" style="height: 100vh; padding: 1.5rem 1rem 1rem 1rem; box-sizing: border-box;" @click="if (window.innerWidth <= 1024 && $event.target.closest('a, button')) toggleSidebar()" aria-label="Navigasi akun pribadi">
    
    <div style="flex: 1; overflow-y: auto; padding-right: 2px;">
        <!-- BRAND LOGO -->
        <div style="padding: 0 0.5rem 1.75rem 0.5rem;">
            <a href="{{ route('landing') }}" style="display: flex; align-items: center; text-decoration: none;">
                <img src="{{ asset('images/logo.png') }}" alt="Organizing Inventory" style="height: 38px; width: auto; object-fit: contain;">
            </a>
        </div>

        <!-- SECTION 1: MENU UTAMA -->
        <div style="margin-bottom: 1.75rem;">
            <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.5rem 0.75rem;">
                MENU UTAMA
            </div>
            <nav style="display: flex; flex-direction: column; gap: 0.25rem;">
                <!-- 1. Dashboard Saya -->
                <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: {{ request()->routeIs('dashboard') ? '800' : '600' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('dashboard') ? 'background: #fff1f2; color: #80142B;' : 'color: #334155;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard Saya</span>
                    </div>
                </a>

                <!-- 2. Organization Saya -->
                <a href="{{ route('personal.organizations') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: {{ request()->routeIs('personal.organizations*') ? '800' : '600' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('personal.organizations*') ? 'background: #fff1f2; color: #80142B;' : 'color: #334155;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Organization Saya</span>
                    </div>
                </a>

                <!-- 3. Peminjaman Saya -->
                <a href="{{ route('personal.borrowings') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: {{ request()->routeIs('personal.borrowings*') ? '800' : '600' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('personal.borrowings*') ? 'background: #fff1f2; color: #80142B;' : 'color: #334155;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Peminjaman Saya</span>
                    </div>
                    <span style="background: #80142B; color: #ffffff; border-radius: 9999px; padding: 0.15rem 0.5rem; font-size: 0.6875rem; font-weight: 800;">1</span>
                </a>

                <!-- 4. Tagihan & Denda -->
                <a href="{{ route('personal.charges') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: {{ request()->routeIs('personal.charges*') ? '800' : '600' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('personal.charges*') ? 'background: #fff1f2; color: #80142B;' : 'color: #334155;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Tagihan & Denda</span>
                    </div>
                    <span style="background: #f59e0b; color: #ffffff; border-radius: 9999px; padding: 0.15rem 0.5rem; font-size: 0.6875rem; font-weight: 800;">Rp 50rb</span>
                </a>

                <!-- 5. Notifikasi -->
                <a href="{{ route('notifications') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: {{ request()->routeIs('notifications*') ? '800' : '600' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('notifications*') ? 'background: #fff1f2; color: #80142B;' : 'color: #334155;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span>Notifikasi</span>
                    </div>
                </a>

                <!-- 6. Profil Saya -->
                <a href="{{ route('profile') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: {{ request()->routeIs('profile*') ? '800' : '600' }}; text-decoration: none; transition: all 0.15s ease; {{ request()->routeIs('profile*') ? 'background: #fff1f2; color: #80142B;' : 'color: #334155;' }}">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profil Saya</span>
                    </div>
                </a>
            </nav>
        </div>

        <!-- SECTION 2: AKSES CEPAT -->
        <div>
            <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.5rem 0.75rem;">
                AKSES CEPAT
            </div>
            <nav style="display: flex; flex-direction: column; gap: 0.25rem;">
                <a href="{{ route('organizations') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: 700; color: #334155; text-decoration: none; transition: all 0.15s ease;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Scan QR Cepat</span>
                </a>
                <a href="{{ route('organizations') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.75rem; border-radius: 0.625rem; font-size: 0.84rem; font-weight: 700; color: #334155; text-decoration: none; transition: all 0.15s ease;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Jelajahi Komunitas Lain</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- BOTTOM USER CARD -->
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
            <button type="submit" title="Keluar" style="background: none; border: none; padding: 6px; cursor: pointer; color: #94a3b8; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: color 0.15s ease;" onmouseover="this.style.color='#80142B'" onmouseout="this.style.color='#94a3b8'">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>

</aside>
