@props(['sidebarId' => null, 'menuLabel' => 'Buka menu akun'])

@php
    $user = auth()->user();
    $initials = $user ? collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('') : 'U';
@endphp

<div style="position: sticky; top: 14px; z-index: 50; max-width: 64rem; width: 100%; margin: 14px auto 1.5rem auto; padding: 0 1rem; box-sizing: border-box;">
    <header style="height: auto; width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; padding: 0.75rem 1.75rem; background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(226, 232, 240, 0.9); border-radius: 9999px; box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04); transition: all 0.2s ease;">

        <!-- BRAND LOGO (KIRI) -->
        <a href="{{ route('landing') }}" style="display: flex; align-items: center; text-decoration: none; flex-shrink: 0;" aria-label="Organization Inventory">
            <img src="{{ asset('images/logo.png') }}" alt="Organization Inventory" style="height: 38px; width: auto; object-fit: contain;">
        </a>

        <!-- NAV LINKS (TENGAH - SAMA DENGAN LANDING PAGE) -->
        <nav aria-label="Navigasi Utama" style="display: flex; align-items: center; gap: 2rem; font-size: 0.875rem; font-weight: 600;">
            <a href="{{ route('landing') }}#fitur" style="color: #64748b; text-decoration: none; transition: color 0.15s ease;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
                Fitur Utama
            </a>
            <a href="{{ route('organizations') }}" style="color: {{ request()->routeIs('organizations') ? '#7f112e' : '#64748b' }}; font-weight: {{ request()->routeIs('organizations') ? '700' : '600' }}; text-decoration: none; transition: color 0.15s ease;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='{{ request()->routeIs('organizations') ? '#7f112e' : '#64748b' }}'">
                Jelajahi Organization
            </a>
            <a href="{{ route('landing') }}#tentang" style="color: #64748b; text-decoration: none; transition: color 0.15s ease;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
                Tentang
            </a>
        </nav>

        <!-- AUTH / USER ACTIONS (KANAN - SAMA DENGAN LANDING PAGE) -->
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
            @auth
                <div style="display: inline-flex; align-items: center; gap: 0.65rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.25rem 0.35rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <div style="width: 28px; height: 28px; border-radius: 9999px; background: #1e1b4b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.6875rem; font-weight: 800;">
                        {{ $initials ?: 'U' }}
                    </div>
                    <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; padding-right: 0.25rem;">
                        {{ $user->name }}
                    </span>
                    <a href="{{ route('dashboard') }}" style="background: #0f172a; color: #ffffff; border-radius: 9999px; padding: 0.3rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-decoration: none; transition: background 0.15s ease;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='#0f172a'">
                        Dashboard &rarr;
                    </a>
                </div>

                @if($sidebarId)
                    <button type="button" class="oi-mobile-toggle" @click="menuOpen = !menuOpen" style="background: none; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; cursor: pointer; color: #64748b;">
                        <x-icon name="menu" size="18" />
                    </button>
                @endif
            @else
                <a href="{{ route('login') }}" style="color: #0f172a; font-weight: 700; text-decoration: none; padding: 0.4rem 0.75rem; font-size: 0.875rem; transition: color 0.15s ease;" onmouseover="this.style.color='#7f112e'" onmouseout="this.style.color='#0f172a'">
                    Masuk
                </a>
                <a href="{{ route('register') }}" style="display: inline-flex; align-items: center; justify-content: center; background: #0f172a; color: #ffffff; border-radius: 9999px; font-weight: 700; font-size: 0.8125rem; padding: 0.5rem 1.15rem; text-decoration: none; transition: background 0.15s ease;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='#0f172a'">
                    Buat Akun
                </a>
            @endauth
        </div>

    </header>
</div>
