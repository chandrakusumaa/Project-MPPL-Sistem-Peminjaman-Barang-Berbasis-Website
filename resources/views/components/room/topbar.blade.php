<header class="oi-topbar">
    <div class="oi-wrap oi-topbar-inner">
        <a href="/" class="oi-brand" aria-label="Organization Inventory"><span class="oi-brand-mark">O</span><span>OrgInventory</span></a>
        <nav class="oi-topnav" aria-label="Navigasi utama">
            <a href="/">Beranda</a>
            <a href="{{ route('organizations') }}">Jelajahi Ruang</a>
            <a href="{{ route('dashboard') }}">Dashboard</a>
        </nav>
        <div class="oi-top-actions">
            <livewire:notification-bell variant="workspace" />
            <form method="POST" action="{{ route('logout') }}" class="oi-logout">@csrf<button class="oi-btn oi-secondary oi-sm oi-hide-mobile" type="submit">Keluar</button></form>
            <a href="/profile" class="oi-avatar oi-user-avatar" aria-label="Profil {{ auth()->user()->name }}">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/'.auth()->user()->avatar) }}" alt="">
                @else
                    {{ collect(preg_split('/\s+/', trim(auth()->user()->name)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('') }}
                @endif
            </a>
            <button type="button" class="oi-btn oi-secondary oi-sm oi-mobile-toggle" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-controls="room-sidebar" aria-label="Buka menu ruang"><x-icon name="menu" /></button>
        </div>
    </div>
</header>
