@props(['sidebarId' => null, 'menuLabel' => 'Buka menu akun'])
<header class="oi-topbar">
    <div class="oi-topbar-inner">
        <a href="{{ route('landing') }}" class="oi-brand" aria-label="Organization Inventory — Beranda">
            <img src="{{ asset('images/organizing-inventory-logo.png') }}" alt="Organizing Inventory" class="oi-brand-logo" width="1817" height="866" fetchpriority="high" decoding="async">
        </a>
        <nav class="oi-topnav" aria-label="Navigasi utama">
            <a href="{{ route('landing') }}" @if(request()->routeIs('landing')) aria-current="page" @endif>Beranda</a>
            <a href="{{ route('organizations') }}" @if(request()->routeIs('organizations')) aria-current="page" @endif>Jelajahi Organization</a>
            <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
        </nav>
        <div class="oi-top-actions">
            @auth
                <form method="POST" action="{{ route('logout') }}" class="oi-logout">@csrf<button class="oi-btn oi-secondary oi-sm oi-hide-mobile" type="submit">Keluar</button></form>
                @if(auth()->user()->hasVerifiedEmail())<livewire:notification-bell />@endif
                <a href="{{ route('profile') }}" aria-label="Profil {{ auth()->user()->name }}"><x-avatar :name="auth()->user()->name" :image="auth()->user()->avatar" class="oi-user-avatar" /></a>
                @if($sidebarId)
                    <button type="button" class="oi-btn oi-secondary oi-sm oi-mobile-toggle" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-controls="{{ $sidebarId }}" aria-label="{{ $menuLabel }}"><x-icon name="menu" /></button>
                @endif
            @else
                <a class="oi-btn oi-ghost oi-sm" href="{{ route('login') }}">Masuk</a>
                <a class="oi-btn oi-sm" href="{{ route('register') }}">Daftar</a>
            @endauth
        </div>
    </div>
</header>
