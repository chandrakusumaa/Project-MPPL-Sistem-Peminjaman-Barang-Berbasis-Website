@props(['org', 'operator', 'admin', 'tab', 'reportStatus' => '', 'livewireNavigation' => true])
<div class="oi-room" x-data="{ menuOpen: false }" @oi-review-borrowing.window="$nextTick(() => document.getElementById('oi-borrowing-' + $event.detail.id)?.scrollIntoView({ block: 'center', behavior: 'smooth' }))" @keydown.escape.window="menuOpen = false" @oi-focus-field.window="$nextTick(() => { const field = document.getElementById('oi-' + $event.detail.field); if (field) { field.focus(); field.scrollIntoView({ block: 'center', behavior: 'smooth' }); } })">
    <x-room.topbar />
    <button type="button" class="oi-sidebar-backdrop" x-cloak x-show="menuOpen" @click="menuOpen = false" aria-label="Tutup menu ruang" tabindex="-1"></button>
    <div class="oi-app-layout">
        <x-room.sidebar :org="$org" :operator="$operator" :admin="$admin" :tab="$tab" :report-status="$reportStatus" :livewire-navigation="$livewireNavigation" />
        <main class="oi-main" id="room-content">
            <div class="oi-context"><span><b>{{ $org->name }}</b> · Peran {{ $admin ? 'ADMIN' : ($operator ? 'STAFF' : 'MEMBER') }} pada ruang ini</span><a href="{{ route('dashboard') }}">← Dashboard pribadi</a></div>
            <x-feedback />
            {{ $slot }}
        </main>
    </div>
</div>
