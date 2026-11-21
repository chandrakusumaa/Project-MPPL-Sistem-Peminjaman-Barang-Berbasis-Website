@props(['org', 'operator', 'admin', 'tab', 'livewireNavigation' => true])
<aside class="oi-sidebar" id="room-sidebar" :class="{ 'is-open': menuOpen }" @click="if ($event.target.closest('a, button')) menuOpen = false" aria-label="Navigasi Komunitas">
    <div class="oi-side-org" style="gap: 12px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 14px;">
        <x-avatar :name="$org->name" type="community" style="width: 42px; height: 42px;" />
        <div class="oi-side-identity" style="min-width: 0;">
            <b title="{{ $org->name }}" style="font-size: 13px; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">{{ $org->name }}</b>
            <span class="micro-badge {{ $admin ? 'micro-badge-red' : '' }}" style="margin-top: 3px; font-size: 10px; font-weight: 800;">
                {{ $admin ? 'ADMIN' : ($operator ? 'STAFF' : 'MEMBER') }}
            </span>
        </div>
    </div>
    <nav class="oi-side-group" aria-label="Workspace">
        <div class="oi-side-label" style="font-size: 10px; font-weight: 800; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 6px;">RUANG KERJA</div>
        @if($operator)<x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'dashboard'])" label="Ringkasan Operasional" icon="grid" :active="$tab === 'dashboard'" wire:click="$set('tab', 'dashboard')" />@endif
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'inventory'])" label="Katalog & Aset Fisik" icon="box" :active="$tab === 'inventory'" wire:click="$set('tab', 'inventory')" />
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'borrowings'])" label="Peminjaman" icon="swap" :active="$tab === 'borrowings'" wire:click="$set('tab', 'borrowings')" />
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'damage-reports'])" label="Laporan Kerusakan" icon="warning" :active="$tab === 'damage-reports'" wire:click="showDamageReports" />
        @if($operator)<x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'maintenance'])" label="Maintenance" icon="tool" :active="$tab === 'maintenance'" wire:click="showMaintenance" />@endif
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'charges'])" label="Riwayat Tagihan" icon="layers" :active="$tab === 'charges'" wire:click="$set('tab', 'charges')" />
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'audit'])" label="Audit Aktivitas" icon="clock" :active="$tab === 'audit'" :disabled="!$admin" wire:click="$set('tab', 'audit')" />
    </nav>
    @if($operator)
    <nav class="oi-side-group" aria-label="Pengelolaan">
        <div class="oi-side-label" style="font-size: 10px; font-weight: 800; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 6px;">PENGELOLAAN KHUSUS</div>
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'members'])" label="Manajemen Anggota" icon="users" :active="$tab === 'members'" :disabled="!$admin" wire:click="$set('tab', 'members')" />
        @if($admin)
            <a class="oi-side-link {{ $tab === 'reports' ? 'is-active' : '' }}" href="{{ route('reports', $org) }}"><x-icon name="report" /><span>Ekspor 6 CSV</span></a>
        @else
            <x-room.nav-item label="Ekspor Laporan (Admin)" icon="report" disabled />
        @endif
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'settings'])" label="Pengaturan Komunitas" icon="tool" :active="$tab === 'settings'" :disabled="!$admin" wire:click="$set('tab', 'settings')" />
    </nav>
    @endif
    <nav class="oi-side-group" aria-label="Akun">
        <div class="oi-side-label" style="font-size: 10px; font-weight: 800; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 6px;">NAVIGASI GLOBAL</div>
        <livewire:organization-menu :current-id="$org->id" />
        <a class="oi-side-link" href="{{ route('dashboard') }}"><x-icon name="home" /><span>Dashboard Pribadi</span></a>
        <a class="oi-side-link" href="{{ route('organization.public', $org) }}"><x-icon name="external" /><span>Lihat Halaman Publik</span></a>
        <form method="POST" action="{{ route('logout') }}" class="oi-mobile-only">@csrf<button class="oi-side-link" type="submit"><x-icon name="external" /><span>Keluar</span></button></form>
    </nav>
</aside>
