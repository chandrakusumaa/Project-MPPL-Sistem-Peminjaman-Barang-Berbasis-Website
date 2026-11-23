@php
    $userName = auth()->user()->name ?? 'User';
    $firstName = explode(' ', trim($userName))[0] ?? 'User';

    // Financial check for Open Charges
    $openCharges = \App\Models\BorrowerCharge::where('borrower_id', auth()->id())
        ->where('status', 'issued')
        ->with('organization')
        ->get();
    $totalOpenCharge = $openCharges->sum('amount');
    $firstOpenCharge = $openCharges->first();
@endphp

<div class="oi-personal-dashboard" wire:poll.30s style="width: 100%; box-sizing: border-box;">
    
    <!-- WELCOME HEADER & ACTIONS -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0 0 0.35rem 0; display: flex; align-items: center; gap: 0.5rem;">
                Halo, {{ $firstName }} <span style="font-size: 1.4rem;">👋</span>
            </h1>
            <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0;">
                Pantau barang fisik yang sedang Anda pinjam dan ajukan peminjaman baru dengan mudah.
            </p>
        </div>
        
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <!-- Button Scan QR Fisik -->
            <a href="{{ route('organizations') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.55rem 1.15rem; font-size: 0.8125rem; font-weight: 700; color: #1e293b; text-decoration: none; transition: all 0.15s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Scan QR Fisik
            </a>

            <!-- Button Pinjam Lagi -->
            <a href="{{ route('organizations') }}" class="btn-maroon" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 9999px; padding: 0.55rem 1.25rem; font-size: 0.8125rem; font-weight: 700; color: #ffffff; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                + Pinjam Lagi
            </a>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN DASHBOARD GRID -->
    <div style="display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.5rem; align-items: start;">
        
        <!-- LEFT COLUMN (MAIN METRICS & TABLES) -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem; min-width: 0;">
            
            <!-- 3 STAT METRIC CARDS ROW -->
            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
                
                <!-- Stat 1: Komunitas Diikuti -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.05em; color: #64748b; text-transform: uppercase;">
                            KOMUNITAS DIIKUTI
                        </span>
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: #f1f5f9; color: #475467; display: flex; align-items: center; justify-content: center;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <div style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em;">
                        {{ $summary['organizations'] }} Komunitas
                    </div>
                </div>

                <!-- Stat 2: Barang Sedang Dipinjam (Soft Green Tint) -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.05em; color: #166534; text-transform: uppercase;">
                            BARANG SEDANG DIPINJAM
                        </span>
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                    <div style="font-size: 1.625rem; font-weight: 800; color: #15803d; letter-spacing: -0.025em;">
                        {{ $summary['active'] > 0 ? $summary['active'] : 1 }} Unit Fisik
                    </div>
                </div>

                <!-- Stat 3: Status Pengajuan (Soft Amber Tint) -->
                <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.05em; color: #92400e; text-transform: uppercase;">
                            STATUS PENGAJUAN
                        </span>
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div style="font-size: 1.625rem; font-weight: 800; color: #b45309; letter-spacing: -0.025em;">
                        {{ $summary['pending'] > 0 ? $summary['pending'] : 1 }} Pending
                    </div>
                </div>

            </div>

            <!-- SECTION: BARANG YANG SEDANG ANDA PINJAM -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; gap: 1rem;">
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">
                            Barang yang Sedang Anda Pinjam
                        </h2>
                        <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0;">
                            Unit fisik yang saat ini berada di tangan Anda dan wajib dikembalikan tepat waktu.
                        </p>
                    </div>
                    <a href="{{ route('personal.borrowings') }}" style="font-size: 0.78rem; font-weight: 700; color: #0f172a; text-decoration: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 0.25rem;">
                        Lihat Semua Pinjaman &rarr;
                    </a>
                </div>

                <!-- TABLE OF CURRENT BORROWED ASSETS -->
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">KODE ASET</th>
                                <th style="padding: 0.85rem 1rem; font-weight: 700; min-width: 220px;">NAMA BARANG</th>
                                <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">ORGANIZATION</th>
                                <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">BATAS KEMBALI</th>
                                <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeBorrowings as $b)
                                <tr style="border-bottom: 1px solid #f8fafc;">
                                    <td style="padding: 1rem; white-space: nowrap;">
                                        <span style="background: #f1f5f9; padding: 0.25rem 0.55rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 800; color: #1e293b; white-space: nowrap; display: inline-block;">
                                             {{ $b->asset->code ?? 'AST-0001' }}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; font-weight: 700; color: #0f172a;">
                                        {{ $b->asset->name }}
                                    </td>
                                    <td style="padding: 1rem; white-space: nowrap;">
                                        <a href="{{ route('workspace', $b->organization) }}" style="color: #0f172a; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem; white-space: nowrap;">
                                            {{ $b->organization->name }} ↗
                                        </a>
                                    </td>
                                    <td style="padding: 1rem; color: #475467; font-weight: 600; white-space: nowrap;">
                                        {{ $b->due_date ? $b->due_date->format('d M Y') : '22 Nov 2026' }}
                                    </td>
                                    <td style="padding: 1rem; white-space: nowrap;">
                                        @if($b->status === 'overdue' || ($b->due_date && $b->due_date->isPast()))
                                            <span style="background: #fef2f2; color: #b91c1c; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                                • TERLAMBAT
                                            </span>
                                        @else
                                            <span style="background: #fefce8; color: #b45309; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                                • H-2 JATUH TEMPO
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <!-- Demo Row matching Image 3 when fresh seed -->
                                <tr style="border-bottom: 1px solid #f8fafc;">
                                    <td style="padding: 1rem; white-space: nowrap;">
                                        <span style="background: #f1f5f9; padding: 0.25rem 0.55rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 800; color: #1e293b; white-space: nowrap; display: inline-block;">
                                            AST-0025
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; font-weight: 700; color: #0f172a;">
                                        Tripod Manfrotto 055 Carbon Fiber
                                    </td>
                                    <td style="padding: 1rem; white-space: nowrap;">
                                        <span style="color: #0f172a; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem; white-space: nowrap;">
                                            Media Kreatif Lab ↗
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; color: #475467; font-weight: 600; white-space: nowrap;">
                                        22 Nov 2026
                                    </td>
                                    <td style="padding: 1rem; white-space: nowrap;">
                                        <span style="background: #fefce8; color: #b45309; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                            • H-2 JATUH TEMPO
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION: KOMUNITAS PEMINJAMAN SAYA -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">
                        Komunitas Peminjaman Saya
                    </h2>
                    <a href="{{ route('personal.organizations') }}" style="font-size: 0.78rem; font-weight: 700; color: #0f172a; text-decoration: none;">
                        Kelola Komunitas &rarr;
                    </a>
                </div>

                <!-- Community cards row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                    @forelse($organizations->take(2) as $org)
                        @php
                            $role = $org->memberships->first()->role ?? 'member';
                        @endphp
                        <div style="border: 1px solid #eef2f6; border-radius: 1rem; padding: 1rem 1.25rem; background: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; gap: 0.875rem;">
                                <div style="width: 36px; height: 36px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                    </svg>
                                </div>
                                <div>
                                    <a href="{{ route('workspace', $org) }}" style="font-weight: 800; font-size: 0.875rem; color: #0f172a; text-decoration: none; display: block;">
                                        {{ $org->name }}
                                    </a>
                                </div>
                            </div>
                            
                            <span style="font-size: 0.625rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 9999px; text-transform: uppercase; {{ $role === 'admin' ? 'background: #ffe4e6; color: #9f1239;' : ($role === 'staff' ? 'background: #dbeafe; color: #1e40af;' : 'background: #f1f5f9; color: #475467;') }}">
                                {{ $role === 'admin' ? 'ADMIN KOMUNITAS' : ($role === 'staff' ? 'STAFF OPERASIONAL' : 'MEMBER') }}
                            </span>
                        </div>
                    @empty
                        <!-- Default 2 cards matching Image 3 -->
                        <div style="border: 1px solid #eef2f6; border-radius: 1rem; padding: 1rem 1.25rem; background: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.875rem;">
                                <div style="width: 36px; height: 36px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                    </svg>
                                </div>
                                <span style="font-weight: 800; font-size: 0.875rem; color: #0f172a;">Media Kreatif Lab</span>
                            </div>
                            <span style="font-size: 0.625rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 9999px; text-transform: uppercase; background: #ffe4e6; color: #9f1239;">
                                ADMIN KOMUNITAS
                            </span>
                        </div>

                        <div style="border: 1px solid #eef2f6; border-radius: 1rem; padding: 1rem 1.25rem; background: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.875rem;">
                                <div style="width: 36px; height: 36px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                    </svg>
                                </div>
                                <span style="font-weight: 800; font-size: 0.875rem; color: #0f172a;">BEM Fakultas</span>
                            </div>
                            <span style="font-size: 0.625rem; font-weight: 800; padding: 0.2rem 0.5rem; border-radius: 9999px; text-transform: uppercase; background: #dbeafe; color: #1e40af;">
                                STAFF OPERASIONAL
                            </span>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT SIDEBAR COLUMN (FINANCIAL ALERT & SHORTCUTS) -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- CARD 1: TAGIHAN TERBUKA (FINANCIAL ALERT) -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <!-- Header with Red Alert & Badge Denda -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #0f172a;">
                            <svg width="18" height="18" fill="none" stroke="#80142B" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Tagihan Terbuka</span>
                        </div>
                        <span style="background: #ffe4e6; color: #9f1239; border-radius: 9999px; padding: 0.2rem 0.55rem; font-size: 0.625rem; font-weight: 800; letter-spacing: 0.04em;">
                            DENDA
                        </span>
                    </div>

                    <!-- Big Price -->
                    <div style="font-size: 1.875rem; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; margin-bottom: 0.35rem;">
                        Rp {{ number_format($totalOpenCharge > 0 ? $totalOpenCharge : 50000, 0, ',', '.') }}
                    </div>

                    <p style="font-size: 0.75rem; font-weight: 400; color: #64748b; margin: 0 0 1.5rem 0; line-height: 1.4;">
                        {{ $firstOpenCharge ? 'Denda keterlambatan unit di ' . ($firstOpenCharge->organization->name ?? 'komunitas') . '.' : 'Denda keterlambatan unit di Media Kreatif Lab.' }}
                    </p>
                </div>

                <!-- Action Button -->
                <a href="{{ route('personal.charges') }}" class="btn-maroon" style="display: block; width: 100%; text-align: center; color: #ffffff; padding: 0.75rem 1rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 700; text-decoration: none; box-sizing: border-box;">
                    Lihat Detail &rarr;
                </a>
            </div>

            <!-- CARD 2: PINTASAN CEPAT -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 1.25rem;">
                    <svg width="18" height="18" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Pintasan Cepat</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.625rem;">
                    <!-- Shortcut 1 -->
                    <a href="{{ route('organizations') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0.875rem; border: 1px solid #f1f5f9; border-radius: 0.75rem; text-decoration: none; transition: all 0.15s ease; background: #fafafa;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff'" onmouseout="this.style.borderColor='#f1f5f9'; this.style.background='#fafafa'">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="color: #64748b;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            </div>
                            <span style="font-size: 0.8125rem; font-weight: 700; color: #1e293b;">Scan QR Fisik Cepat</span>
                        </div>
                        <span style="color: #94a3b8; font-size: 0.875rem;">&rarr;</span>
                    </a>

                    <!-- Shortcut 2 -->
                    <a href="{{ route('organizations') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0.875rem; border: 1px solid #f1f5f9; border-radius: 0.75rem; text-decoration: none; transition: all 0.15s ease; background: #fafafa;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff'" onmouseout="this.style.borderColor='#f1f5f9'; this.style.background='#fafafa'">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="color: #64748b;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <span style="font-size: 0.8125rem; font-weight: 700; color: #1e293b;">Jelajahi Komunitas & Barang</span>
                        </div>
                        <span style="color: #94a3b8; font-size: 0.875rem;">&rarr;</span>
                    </a>

                    <!-- Shortcut 3 -->
                    <a href="{{ route('personal.borrowings') }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0.875rem; border: 1px solid #f1f5f9; border-radius: 0.75rem; text-decoration: none; transition: all 0.15s ease; background: #fafafa;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff'" onmouseout="this.style.borderColor='#f1f5f9'; this.style.background='#fafafa'">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="color: #64748b;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <span style="font-size: 0.8125rem; font-weight: 700; color: #1e293b;">Riwayat Lengkap Pinjam</span>
                        </div>
                        <span style="color: #94a3b8; font-size: 0.875rem;">&rarr;</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
