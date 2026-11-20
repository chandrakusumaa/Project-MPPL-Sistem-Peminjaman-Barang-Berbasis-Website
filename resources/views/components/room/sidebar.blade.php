@props(['org', 'operator', 'admin', 'tab', 'livewireNavigation' => true])
<aside class="oi-sidebar" id="room-sidebar" :class="{ 'is-open': menuOpen }" @click="if ($event.target.closest('a, button')) menuOpen = false" aria-label="Navigasi Organization">
    <div class="oi-side-org">
        <div class="oi-avatar">{{ collect(preg_split('/\\s+/', trim($org->name)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('') }}</div>
        <div class="oi-side-identity"><b title="{{ $org->name }}">{{ $org->name }}</b><span>{{ $admin ? 'ADMIN' : ($operator ? 'STAFF' : 'MEMBER') }}</span></div>
    </div>
    <nav class="oi-side-group" aria-label="Workspace">
        <div class="oi-side-label">WORKSPACE</div>
        @if($operator)<x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'dashboard'])" label="Dashboard" icon="grid" :active="$tab === 'dashboard'" wire:click="$set('tab', 'dashboard')" />@endif
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'inventory'])" label="Inventaris" icon="box" :active="$tab === 'inventory'" wire:click="$set('tab', 'inventory')" />
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'borrowings'])" label="Peminjaman" icon="swap" :active="$tab === 'borrowings'" wire:click="$set('tab', 'borrowings')" />
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'damage-reports'])" label="Laporan Kerusakan" icon="warning" :active="$tab === 'damage-reports'" wire:click="showDamageReports" />
        @if($operator)<x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'maintenance'])" label="Maintenance" icon="tool" :active="$tab === 'maintenance'" wire:click="showMaintenance" />@endif
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'charges'])" label="Riwayat Tagihan" icon="layers" :active="$tab === 'charges'" wire:click="$set('tab', 'charges')" />
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'audit'])" label="Audit Aktivitas" icon="clock" :active="$tab === 'audit'" :disabled="!$admin" wire:click="$set('tab', 'audit')" />
    </nav>
    @if($operator)
    <nav class="oi-side-group" aria-label="Pengelolaan">
        <div class="oi-side-label">PENGELOLAAN</div>
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'members'])" label="Anggota" icon="users" :active="$tab === 'members'" :disabled="!$admin" wire:click="$set('tab', 'members')" />
        @if($admin)
            <a class="oi-side-link {{ $tab === 'reports' ? 'is-active' : '' }}" href="{{ route('reports', $org) }}"><x-icon name="report" /><span>Ekspor Laporan</span></a>
        @else
            <x-room.nav-item label="Ekspor Laporan" icon="report" disabled />
        @endif
        <x-room.nav-item :href="$livewireNavigation ? null : route('workspace', [$org, 'tab' => 'settings'])" label="Pengaturan" icon="tool" :active="$tab === 'settings'" :disabled="!$admin" wire:click="$set('tab', 'settings')" />
    </nav>
    @endif
    <nav class="oi-side-group" aria-label="Akun">
        <div class="oi-side-label">AKUN</div>
        <livewire:organization-menu :current-id="$org->id" />
        <a class="oi-side-link" href="{{ route('dashboard') }}"><x-icon name="home" /><span>Beranda pengguna</span></a>
        <a class="oi-side-link" href="{{ route('organization.public', $org) }}"><x-icon name="external" /><span>Lihat halaman publik</span></a>
        <form method="POST" action="{{ route('logout') }}" class="oi-mobile-only">@csrf<button class="oi-side-link" type="submit"><x-icon name="external" /><span>Keluar</span></button></form>
    </nav>
</aside>
