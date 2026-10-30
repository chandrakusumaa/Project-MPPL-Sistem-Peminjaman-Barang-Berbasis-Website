<aside class="oi-sidebar" id="account-sidebar" :class="{ 'is-open': menuOpen }" @click="if ($event.target.closest('a, button')) menuOpen = false" aria-label="Navigasi akun pribadi">
    <div class="oi-side-org">
        <x-avatar :name="auth()->user()->name" :image="auth()->user()->avatar" />
        <div class="oi-side-identity"><b>{{ auth()->user()->name }}</b><span>Akun pribadi</span></div>
    </div>
    <nav class="oi-side-group" aria-label="Menu akun">
        <div class="oi-side-label">MENU PENGGUNA</div>
        <a class="oi-side-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><x-icon name="home" /><span>Dashboard Pribadi</span></a>
        <x-organization-menu />
        <a class="oi-side-link {{ request()->routeIs('personal.borrowings') ? 'is-active' : '' }}" href="{{ route('personal.borrowings') }}" @if(request()->routeIs('personal.borrowings')) aria-current="page" @endif><x-icon name="swap" /><span>Peminjaman Saya</span></a>
        <a class="oi-side-link {{ request()->routeIs('personal.charges') ? 'is-active' : '' }}" href="{{ route('personal.charges') }}" @if(request()->routeIs('personal.charges')) aria-current="page" @endif><x-icon name="layers" /><span>Tagihan Saya</span></a>
        <a class="oi-side-link {{ request()->is('scan') ? 'is-active' : '' }}" href="/scan"><x-icon name="qr" /><span>Scan QR</span></a>
        <a class="oi-side-link {{ request()->routeIs('profile') ? 'is-active' : '' }}" href="{{ route('profile') }}" @if(request()->routeIs('profile')) aria-current="page" @endif><x-icon name="user" /><span>Profil Saya</span></a>
    </nav>
    <p class="oi-account-note">Satu akun, banyak Organization.<br>Peran mengikuti ruang yang sedang dibuka.</p>
    <form method="POST" action="{{ route('logout') }}" class="oi-mobile-only">@csrf<button class="oi-side-link" type="submit"><x-icon name="external" /><span>Keluar</span></button></form>
</aside>
