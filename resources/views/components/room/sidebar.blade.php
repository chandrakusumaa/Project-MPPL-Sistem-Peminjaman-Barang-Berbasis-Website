@props(['org', 'operator', 'admin', 'tab', 'livewireNavigation' => true])

@php
    $userName = auth()->user()->name ?? 'User';
    $userEmail = auth()->user()->email ?? 'user@orginventory.id';
    $initials = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn($part) => mb_substr($part, 0, 1))->implode('');
@endphp

<aside class="app-sidebar oi-sidebar" id="room-sidebar" style="height: 100vh; padding: 1.5rem 1rem 1rem 1rem; box-sizing: border-box;" @click="if (window.innerWidth <= 1024 && $event.target.closest('a, button')) toggleSidebar()" aria-label="Navigasi Komunitas">
    
    <div style="flex: 1; overflow-y: auto; padding-right: 2px;">
        <!-- BRAND LOGO -->
        <div style="padding: 0 0.5rem 1.25rem 0.5rem;">
            <a href="{{ route('landing') }}" style="display: flex; align-items: center; text-decoration: none;">
                <img src="{{ asset('images/logo.png') }}" alt="Organizing Inventory" style="height: 38px; width: auto; object-fit: contain;">
            </a>
        </div>

        <!-- 1. NAVIGASI KOMUNITAS -->
        <div style="margin-bottom: 1.25rem;">
            <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.4rem 0.75rem;">
                NAVIGASI KOMUNITAS
            </div>
            <nav style="display: flex; flex-direction: column; gap: 0.2rem;">
                @if($operator)
                    <!-- Ringkasan Komunitas (khusus Admin & Staff sesuai otorisasi dashboard manajemen) -->
                    <button type="button" wire:click="$set('tab', 'dashboard')" style="width: 100%; border: none; background: {{ $tab === 'dashboard' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'dashboard' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'dashboard' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left; transition: all 0.15s ease;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Ringkasan Komunitas</span>
                    </button>
                @endif

                <!-- Peminjaman & Tagihan Pribadi (HANYA DITAMPILKAN JIKA MEMBER, KARENA ADMIN ADALAH PENGELOLA) -->
                @if(!$operator)
                    <button type="button" wire:click="$set('tab', 'inventory')" style="width: 100%; border: none; background: {{ $tab === 'inventory' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'inventory' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'inventory' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Katalog Aset (Pinjam)</span>
                    </button>
                    <button type="button" wire:click="$set('tab', 'borrowings')" style="width: 100%; border: none; background: {{ $tab === 'borrowings' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'borrowings' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'borrowings' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Peminjaman Saya</span>
                    </button>
                    <button type="button" wire:click="$set('tab', 'charges')" style="width: 100%; border: none; background: {{ $tab === 'charges' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'charges' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'charges' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Tagihan Saya</span>
                    </button>
                @endif
            </nav>
        </div>

        <!-- 2. OPERASIONAL STAFF (HANYA UNTUK STAFF & ADMIN) -->
        @if($operator)
            <div style="margin-bottom: 1.25rem;">
                <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.4rem 0.75rem;">
                    OPERASIONAL STAFF
                </div>
                <nav style="display: flex; flex-direction: column; gap: 0.2rem;">
                    <!-- Master Aset Fisik -->
                    <button type="button" wire:click="$set('tab', 'inventory')" style="width: 100%; border: none; background: {{ $tab === 'inventory' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'inventory' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'inventory' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Master Aset Fisik</span>
                    </button>

                    <!-- Persetujuan Pinjam -->
                    <button type="button" wire:click="$set('tab', 'borrowings')" style="width: 100%; border: none; background: {{ $tab === 'borrowings' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'borrowings' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'borrowings' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Persetujuan Pinjam</span>
                    </button>

                    <!-- Meja Inspeksi Kembali -->
                    <button type="button" wire:click="showDamageReports" style="width: 100%; border: none; background: {{ $tab === 'damage-reports' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'damage-reports' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'damage-reports' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Meja Inspeksi Kembali</span>
                    </button>

                    <!-- Inspeksi & Servis -->
                    <button type="button" wire:click="showMaintenance" style="width: 100%; border: none; background: {{ $tab === 'maintenance' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'maintenance' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'maintenance' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Inspeksi & Servis</span>
                    </button>
                </nav>
            </div>
        @endif

        <!-- 3. OTORITAS ADMIN (HANYA UNTUK ADMIN KOMUNITAS) -->
        @if($admin)
            <div style="margin-bottom: 1.25rem;">
                <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.4rem 0.75rem;">
                    OTORITAS ADMIN
                </div>
                <nav style="display: flex; flex-direction: column; gap: 0.2rem;">
                    <!-- Otoritas Finansial -->
                    <button type="button" wire:click="$set('tab', 'charges')" style="width: 100%; border: none; background: {{ $tab === 'charges' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'charges' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'charges' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Otoritas Finansial</span>
                    </button>

                    <!-- Kelola Anggota -->
                    <button type="button" wire:click="$set('tab', 'members')" style="width: 100%; border: none; background: {{ $tab === 'members' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'members' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'members' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Kelola Anggota</span>
                    </button>

                    <!-- Pusat Unduh CSV -->
                    <button type="button" wire:click="$set('tab', 'audit')" style="width: 100%; border: none; background: {{ $tab === 'audit' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'audit' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'audit' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Pusat Unduh CSV</span>
                    </button>

                    <!-- Pengaturan Komunitas -->
                    <button type="button" wire:click="$set('tab', 'settings')" style="width: 100%; border: none; background: {{ $tab === 'settings' ? '#fff1f2' : 'transparent' }}; color: {{ $tab === 'settings' ? '#80142B' : '#334155' }}; font-weight: {{ $tab === 'settings' ? '800' : '600' }}; display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; cursor: pointer; text-align: left;">
                        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Pengaturan Komunitas</span>
                    </button>
                </nav>
            </div>
        @endif

        <!-- 4. NAVIGASI GLOBAL -->
        <div>
            <div style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em; color: #94a3b8; text-transform: uppercase; padding: 0 0.75rem 0.4rem 0.75rem;">
                NAVIGASI GLOBAL
            </div>
            <nav style="display: flex; flex-direction: column; gap: 0.2rem;">
                <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: 700; color: #334155; text-decoration: none;">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard Saya</span>
                </a>
                <a href="{{ route('personal.organizations') }}" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: 700; color: #334155; text-decoration: none;">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Semua Organization</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- BOTTOM USER CARD -->
    <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; margin-top: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
            <div style="width: 36px; height: 36px; border-radius: 9999px; background: #1e1b4b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.8125rem; font-weight: 800; flex-shrink: 0; letter-spacing: -0.02em;">
                {{ $initials ?: 'WA' }}
            </div>
            <div style="min-width: 0; overflow: hidden;">
                <strong style="display: block; font-size: 0.8125rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                    {{ $userName }}
                </strong>
                <small style="display: block; font-size: 0.6875rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;">
                    Peran: {{ $admin ? 'Admin Komunitas' : ($operator ? 'Staff Lab' : 'Member') }}
                </small>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin: 0; flex-shrink: 0;">
            @csrf
            <button type="submit" title="Keluar" style="background: none; border: none; padding: 6px; cursor: pointer; color: #94a3b8; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: color 0.15s ease;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            </button>
        </form>
    </div>

</aside>
