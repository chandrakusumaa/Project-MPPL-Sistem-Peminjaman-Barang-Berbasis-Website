<div class="oi-public">
    <header class="oi-public-header">
        <a class="oi-public-brand" href="/" aria-label="Organization Inventory"><span>O</span>OrgInventory</a>
        <nav aria-label="Navigasi publik">
            <a href="/">Beranda</a>
            <a href="{{ route('organizations') }}">Jelajahi Ruang</a>
        </nav>
        <div class="oi-public-actions">
            @auth
                <a class="secondary button" href="{{ route('dashboard') }}">Dashboard pribadi</a>
            @else
                <a href="{{ route('login') }}">Masuk</a>
                <a class="primary button" href="{{ route('register') }}">Daftar</a>
            @endauth
        </div>
    </header>
    <main class="oi-public-content">
        <x-feedback />
        {{ $slot }}
    </main>
</div>
