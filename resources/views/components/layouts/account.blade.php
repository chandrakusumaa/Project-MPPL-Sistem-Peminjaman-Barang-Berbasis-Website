<div class="oi-room oi-account" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
    <x-navigation.topbar sidebar-id="account-sidebar" />
    <button type="button" class="oi-sidebar-backdrop" x-cloak x-show="menuOpen" @click="menuOpen = false" aria-label="Tutup menu akun" tabindex="-1"></button>
    <div class="oi-app-layout">
        <x-account.sidebar />
        <main class="oi-main" id="account-content">
            @if($showFeedback)<x-feedback />@endif
            {{ $slot }}
        </main>
    </div>
</div>
