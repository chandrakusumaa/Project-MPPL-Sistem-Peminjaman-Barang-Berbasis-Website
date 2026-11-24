@props(['org', 'operator', 'admin', 'tab', 'livewireNavigation' => true])

@php
    $userName = auth()->user()->name ?? 'User';
    $userEmail = auth()->user()->email ?? 'user@orginventory.id';
    $initials = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('');
@endphp

<aside class="app-sidebar oi-sidebar" id="room-sidebar" style="height: 100vh; width: 240px; padding: 1.25rem 0.875rem 1rem 0.875rem; box-sizing: border-box; display: flex; flex-direction: column;" @click="if (window.innerWidth <= 1024 && $event.target.closest('a, button')) toggleSidebar()" aria-label="Navigasi Organization">
    
    <div style="flex: 1; overflow-y: auto; padding-right: 2px;">
        <!-- BRAND LOGO (SESUAI FOTO 3) -->
        <div class="sidebar-header" style="padding: 0 0.5rem 1.25rem 0.5rem;">
            <a href="{{ route('landing') }}" class="brand-logo" style="display: flex; align-items: center; text-decoration: none;">
                <img src="{{ asset('images/logo.png') }}" alt="Organization Inventory" class="app-logo" style="height: 38px; width: auto; object-fit: contain;">
            </a>
        </div>

        <nav class="sidebar-nav" style="display: flex; flex-direction: column; gap: 0.25rem; padding: 0;">
            <!-- 1. NAVIGASI KOMUNITAS / ORGANIZATION (SEMUA ROLE - SESUAI FOTO 3) -->
            <div class="nav-section-title" style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0.25rem 0.75rem 0.4rem 0.75rem;">
                Navigasi Komunitas
            </div>
            
            <!-- Ringkasan Komunitas -->
            <button type="button" class="workspace-nav-tab {{ $tab === 'dashboard' ? 'active' : '' }}" wire:click="$set('tab', 'dashboard')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Ringkasan Komunitas</span>
            </button>

            <!-- Katalog Barang (Tab aktif default di Foto 3) -->
            <button type="button" class="workspace-nav-tab {{ $tab === 'inventory' ? 'active' : '' }}" wire:click="$set('tab', 'inventory')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Katalog Barang</span>
            </button>

            <!-- Peminjaman Saya -->
            <button type="button" class="workspace-nav-tab {{ $tab === 'borrowings' && !$operator ? 'active' : '' }}" wire:click="$set('tab', 'borrowings')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Peminjaman Saya</span>
            </button>

            <!-- Tagihan Saya -->
            <button type="button" class="workspace-nav-tab {{ $tab === 'charges' && !$operator ? 'active' : '' }}" wire:click="$set('tab', 'charges')">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Tagihan Saya</span>
            </button>

            <!-- 2. OPERASIONAL STAFF (SESUAI FOTO 3) -->
            @if($operator)
                <div class="nav-section-title" style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0.85rem 0.75rem 0.4rem 0.75rem;">
                    Operasional Staff
                </div>
                
                <button type="button" class="workspace-nav-tab" wire:click="$set('tab', 'inventory')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Master Aset Fisik</span>
                </button>

                <button type="button" class="workspace-nav-tab {{ $tab === 'borrowings' ? 'active' : '' }}" wire:click="$set('tab', 'borrowings')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Persetujuan Pinjam</span>
                </button>

                <button type="button" class="workspace-nav-tab" wire:click="$set('tab', 'borrowings')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Meja Inspeksi Kembali</span>
                </button>

                <button type="button" class="workspace-nav-tab {{ $tab === 'maintenance' ? 'active' : '' }}" wire:click="$set('tab', 'maintenance')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Inspeksi & Servis</span>
                </button>

                <button type="button" class="workspace-nav-tab {{ $tab === 'damage-reports' ? 'active' : '' }}" wire:click="$set('tab', 'damage-reports')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Laporan Kerusakan</span>
                </button>

                <button type="button" class="workspace-nav-tab" wire:click="openCategoryForm">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Kategori Aset</span>
                </button>

                <button type="button" class="workspace-nav-tab" wire:click="$set('tab', 'inventory')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Lokasi Simpan</span>
                </button>
            @endif

            <!-- 3. OTORITAS ADMIN (SESUAI FOTO 3) -->
            @if($admin)
                <div class="nav-section-title" style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0.85rem 0.75rem 0.4rem 0.75rem;">
                    Otoritas Admin
                </div>
                
                <button type="button" class="workspace-nav-tab {{ $tab === 'charges' ? 'active' : '' }}" wire:click="$set('tab', 'charges')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Otoritas Finansial</span>
                </button>

                <button type="button" class="workspace-nav-tab {{ $tab === 'members' ? 'active' : '' }}" wire:click="$set('tab', 'members')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Kelola Anggota</span>
                </button>

                <a href="{{ route('export', [$org, 'assets']) }}" class="workspace-nav-tab" style="text-decoration: none;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Pusat Unduh CSV</span>
                </a>

                <button type="button" class="workspace-nav-tab {{ $tab === 'settings' ? 'active' : '' }}" wire:click="$set('tab', 'settings')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Pengaturan Organization</span>
                </button>

                <button type="button" class="workspace-nav-tab {{ $tab === 'audit' ? 'active' : '' }}" wire:click="$set('tab', 'audit')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Audit Aktivitas</span>
                </button>
            @endif

            <!-- 4. NAVIGASI GLOBAL -->
            <div class="nav-section-title" style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 1rem 0.75rem 0.4rem 0.75rem;">
                Navigasi Global
            </div>
            <a href="{{ route('dashboard') }}" class="workspace-nav-tab" style="text-decoration: none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard Saya</span>
            </a>
            <a href="{{ route('personal.organizations') }}" class="workspace-nav-tab" style="text-decoration: none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Semua Organization</span>
            </a>
        </nav>
    </div>

    <!-- SIDEBAR FOOTER (SESUAI FOTO 3) -->
    <div class="sidebar-footer" style="padding-top: 1rem; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
            <div class="user-avatar" style="width: 36px; height: 36px; border-radius: 9999px; background: #1e1b4b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8125rem; flex-shrink: 0;">
                {{ $initials }}
            </div>
            <div class="user-info" style="min-width: 0; flex: 1;">
                <div class="user-name" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">{{ $userName }}</div>
                <div class="user-email" style="font-size: 0.6875rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;">
                    Peran: {{ $admin ? 'Administrator' : ($operator ? 'Staff' : 'Member') }}
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin: 0; flex-shrink: 0;">
            @csrf
            <button type="submit" title="Keluar" style="background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0.25rem; display: flex; align-items: center; transition: color 0.15s ease;" onmouseover="this.style.color='#7f112e'" onmouseout="this.style.color='#94a3b8'">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>
</aside>
