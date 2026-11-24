@php
    $userName = auth()->user()->name ?? 'User';
    $firstName = explode(' ', trim($userName))[0] ?? 'User';

    // Financial check for Open Charges (compute real outstanding balance, not raw amount)
    $openCharges = \App\Models\BorrowerCharge::where('borrower_id', auth()->id())
        ->where('status', 'issued')
        ->with('organization', 'payments', 'credits')
        ->get()
        ->filter(fn ($charge) => $charge->balance > 0);
    $totalOpenCharge = $openCharges->sum(fn ($charge) => $charge->balance);
    $firstOpenCharge = $openCharges->first();
    $openOrgCount = $openCharges->pluck('organization_id')->unique()->count();
@endphp

<div class="oi-personal-dashboard" wire:poll.30s style="width: 100%; box-sizing: border-box; max-width: 1240px; margin: 0 auto; padding-bottom: 3.5rem;">
    
    <!-- GREETING HEADER: RAMAH, JELAS & ELEGAN -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 class="page-title" style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                Halo, {{ $firstName }} <span style="font-size: 1.4rem;">👋</span>
            </h1>
            <p class="page-subtitle" style="color: #64748b; font-size: 0.875rem; margin-top: 0.375rem; margin-bottom: 0;">
                Pantau barang fisik yang sedang Anda pinjam dan ajukan peminjaman baru dengan mudah.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('personal.borrowings') }}" class="pill-btn btn-secondary-light btn-sm" style="font-size: 0.8125rem; padding: 0.55rem 1.15rem; font-weight: 700; text-decoration: none;">
                Lihat Peminjaman &rarr;
            </a>
            <a href="{{ route('organizations') }}" class="pill-btn btn-primary-red btn-sm" style="font-size: 0.8125rem; padding: 0.55rem 1.25rem; font-weight: 700; box-shadow: 0 4px 12px rgba(127, 17, 46, 0.22); text-decoration: none;">
                + Pinjam Lagi
            </a>
        </div>
    </div>

    <!-- 2-COLUMN BALANCED DASHBOARD GRID -->
    <div class="dashboard-split-grid" style="display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.75rem; align-items: start;">
        
        <!-- LEFT / MAIN WORK AREA -->
        <div class="dashboard-main-col" style="display: flex; flex-direction: column; gap: 1.75rem; min-width: 0;">
            
            <!-- 3 OPERATIONAL METRICS -->
            <div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 0;">
                
                <!-- Metric 1: Organization Diikuti -->
                <div class="metric-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.375rem 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div class="metric-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span class="metric-label" style="color: #475467; font-weight: 700; font-size: 0.8125rem;">Organization Diikuti</span>
                        <div class="metric-icon" style="width: 32px; height: 32px; border-radius: 8px; background-color: #e2e8f0; color: #1e293b; display: flex; align-items: center; justify-content: center;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <div class="metric-value" style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em;">
                        {{ $summary['organizations'] }} Organization
                    </div>
                </div>

                <!-- Metric 2: Barang Sedang Dipinjam -->
                <div class="metric-card" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 1rem; padding: 1.375rem 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div class="metric-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span class="metric-label" style="color: #166534; font-weight: 700; font-size: 0.8125rem;">Barang Sedang Dipinjam</span>
                        <div class="metric-icon" style="width: 32px; height: 32px; border-radius: 8px; background-color: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                    <div class="metric-value" style="font-size: 1.625rem; font-weight: 800; color: #15803d; letter-spacing: -0.025em;">
                        {{ $summary['active'] }} Unit Fisik
                    </div>
                </div>

                <!-- Metric 3: Status Pengajuan -->
                <div class="metric-card" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 1rem; padding: 1.375rem 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div class="metric-top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <span class="metric-label" style="color: #92400e; font-weight: 700; font-size: 0.8125rem;">Status Pengajuan</span>
                        <div class="metric-icon" style="width: 32px; height: 32px; border-radius: 8px; background-color: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="metric-value" style="font-size: 1.625rem; font-weight: 800; color: #b45309; letter-spacing: -0.025em;">
                        {{ $summary['pending'] }} Pending
                    </div>
                </div>

            </div>

            <!-- ACTIVE PHYSICAL BORROWINGS TABLE -->
            <div class="panel-card" style="margin-bottom: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem;">
                <div class="panel-header" style="padding: 1.375rem 1.75rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <h2 class="panel-title" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0;">Barang yang Sedang Anda Pinjam</h2>
                        <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0;">Unit fisik yang saat ini berada di tangan Anda dan wajib dikembalikan tepat waktu.</p>
                    </div>
                    <a href="{{ route('personal.borrowings') }}" class="pill-btn btn-secondary-light btn-sm" style="white-space: nowrap; font-size: 0.78rem; text-decoration: none;">
                        Lihat Semua Pinjaman &rarr;
                    </a>
                </div>

                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                <th style="padding: 0.85rem 1.25rem; font-weight: 700;">Kode Aset</th>
                                <th style="padding: 0.85rem 1.25rem; font-weight: 700; min-width: 200px;">Nama Barang</th>
                                <th style="padding: 0.85rem 1.25rem; font-weight: 700;">Organization</th>
                                <th style="padding: 0.85rem 1.25rem; font-weight: 700;">Batas Kembali</th>
                                <th style="padding: 0.85rem 1.25rem; font-weight: 700; text-align: center;">Status</th>
                                <th style="padding: 0.85rem 1.25rem; font-weight: 700; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeBorrowings as $b)
                                <tr style="border-bottom: 1px solid #f8fafc;">
                                    <td style="padding: 1rem 1.25rem; white-space: nowrap;">
                                        <span class="micro-badge" style="font-family: monospace; background-color: #f1f5f9; color: #1e293b; font-weight: 700; white-space: nowrap; font-size: 0.75rem; padding: 0.3125rem 0.625rem;">
                                            {{ $b->asset->code }}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem 1.25rem;">
                                        <div style="color: #0f172a; font-weight: 700; font-size: 0.875rem; line-height: 1.4;">{{ $b->asset->name }}</div>
                                    </td>
                                    <td style="padding: 1rem 1.25rem; white-space: nowrap;">
                                        <a href="{{ route('workspace', $b->organization) }}" style="color: #0f172a; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                            {{ $b->organization->name }} ↗
                                        </a>
                                    </td>
                                    <td style="padding: 1rem 1.25rem; white-space: nowrap;">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 0.8125rem;">{{ $b->due_date ? $b->due_date->format('d M Y') : '—' }}</div>
                                    </td>
                                    <td style="padding: 1rem 1.25rem; text-align: center; white-space: nowrap;">
                                        @if($b->status === 'overdue')
                                            <span class="micro-badge micro-badge-red" style="font-weight: 700;">
                                                <span class="status-dot status-dot-red"></span> Terlambat
                                            </span>
                                        @else
                                            <span class="micro-badge micro-badge-amber" style="font-weight: 700;">
                                                <span class="status-dot status-dot-amber"></span> Sedang Dipinjam
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding: 1rem 1.25rem; text-align: right; white-space: nowrap;">
                                        <a href="{{ route('personal.borrowings') }}" class="pill-btn btn-danger-light btn-sm" style="white-space: nowrap; font-size: 0.72rem; padding: 0.35rem 0.75rem; text-decoration: none;">
                                            Lapor Insiden
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2.25rem 1rem; color: #94a3b8;">
                                        <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Tidak ada barang fisik yang sedang dipinjam saat ini.</p>
                                        <p style="font-size: 0.75rem; margin: 0;">Jelajahi Organization dan ajukan peminjaman sesuai kebutuhan Anda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ORGANIZATION SAYA -->
            <div class="panel-card" style="margin-bottom: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem;">
                <div class="panel-header" style="padding: 1.375rem 1.75rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                    <div>
                        <h2 class="panel-title" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0;">Organization Saya</h2>
                    </div>
                    <a href="{{ route('personal.organizations') }}" class="pill-btn btn-secondary-light btn-sm" style="white-space: nowrap; font-size: 0.78rem; text-decoration: none;">
                        Kelola Organization &rarr;
                    </a>
                </div>

                <div style="padding: 1.5rem 1.75rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.25rem;">
                    @forelse($organizations as $org)
                        @php
                            $role = $org->memberships->first()?->role ?? 'member';
                        @endphp
                        <div class="panel-card" style="margin-bottom: 0; padding: 1.375rem; display: flex; flex-direction: column; justify-content: space-between; min-height: 155px; box-shadow: none; border: 1px solid #e2e8f0; border-radius: 1rem;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.875rem;">
                                    <div class="community-avatar" style="width: 44px; height: 44px; border-radius: 9999px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                        </svg>
                                    </div>
                                    <span class="micro-badge {{ $role === 'admin' ? 'badge-role-admin' : ($role === 'staff' ? 'badge-role-staff' : 'badge-role-member') }}" style="font-size: 0.625rem; font-weight: 800;">
                                        {{ $role === 'admin' ? 'ADMIN ORGANIZATION' : ($role === 'staff' ? 'STAFF OPERASIONAL' : 'MEMBER AKTIF') }}
                                    </span>
                                </div>
                                <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">{{ $org->name }}</h3>
                                <small style="font-size: 0.72rem; color: #94a3b8;">{{ $org->assets_count }} Unit Fisik</small>
                            </div>
                            <div style="margin-top: 1.25rem;">
                                <a href="{{ route('workspace', $org) }}" class="pill-btn {{ $role === 'admin' ? 'btn-primary-red' : 'btn-solid-black' }} btn-sm" style="width: 100%; justify-content: center; text-decoration: none; padding: 0.45rem 1rem;">
                                    {{ $role === 'admin' ? 'Kelola Organization' : 'Pinjam Barang' }} &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <p style="grid-column: 1 / -1; color: #94a3b8; font-size: 0.8125rem; margin: 0;">Anda belum bergabung ke organisasi manapun.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT / SIDE RAIL (FINANCIAL & QUICK ACTIONS) -->
        <div class="dashboard-side-col" style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- FINANCIAL CHARGE CARD (ELEGANT WINE CRIMSON ACCENT) -->
            <div class="info-rail-card" style="border: 1px solid #fecdd6; border-top: 3px solid #7f112e; background: #ffffff; border-radius: 1.125rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="info-rail-header" style="border-bottom: 1px solid #fdf2f4; padding-bottom: 0.75rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div class="info-rail-title" style="color: #05214c; font-weight: 800; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Tagihan Terbuka
                    </div>
                    <span class="micro-badge badge-role-admin" style="font-weight: 800; font-size: 0.6875rem;">DENDA</span>
                </div>
                
                <div style="font-size: 1.75rem; font-weight: 900; color: #7f112e; line-height: 1.1;">
                    {{ $totalOpenCharge > 0 ? 'Rp ' . number_format($totalOpenCharge, 0, ',', '.') : 'Rp 0 (Nihil)' }}
                </div>
                <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0.5rem; line-height: 1.5;">
                    @if($totalOpenCharge > 0)
                        @if($openOrgCount > 1)
                            Sisa kewajiban tersebar di <strong>{{ $openOrgCount }} Organization</strong>.
                        @else
                            Tagihan denda atau penggantian fisik di <strong>{{ $firstOpenCharge?->organization?->name ?? 'Organization' }}</strong>.
                        @endif
                    @else
                        Tidak ada kewajiban denda keterlambatan yang tertunggak.
                    @endif
                </p>

                <div style="margin-top: 1.25rem;">
                    <a href="{{ route('personal.charges') }}" class="pill-btn btn-primary-red btn-sm" style="width: 100%; justify-content: center; box-shadow: 0 4px 12px rgba(127, 17, 46, 0.22); text-decoration: none;">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- QUICK SHORTCUTS CARD (CLEAN LIST ITEMS) -->
            <div class="info-rail-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="info-rail-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                    <div class="info-rail-title" style="font-weight: 800; font-size: 0.9rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Pintasan Cepat
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.625rem;">
                    <a href="{{ route('organizations') }}" class="shortcut-action-item" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.75rem; text-decoration: none; background: #f8fafc; transition: all 0.15s ease; color: #1e293b; font-size: 0.8125rem; font-weight: 600;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="action-icon" style="color: #64748b;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <span>Jelajahi Organization & Barang</span>
                        </div>
                        <span style="color: #94a3b8; font-size: 0.875rem;">&rarr;</span>
                    </a>

                    <a href="{{ route('personal.borrowings') }}" class="shortcut-action-item" style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.75rem; border-radius: 0.75rem; text-decoration: none; background: #f8fafc; transition: all 0.15s ease; color: #1e293b; font-size: 0.8125rem; font-weight: 600;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="action-icon" style="color: #64748b;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <span>Riwayat Lengkap Pinjam</span>
                        </div>
                        <span style="color: #94a3b8; font-size: 0.875rem;">&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- AKTIVITAS AKUN -->
            <div class="info-rail-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div class="info-rail-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 1rem;">
                    <div class="info-rail-title" style="font-weight: 800; font-size: 0.9rem; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Status Sistem
                    </div>
                </div>

                <div style="font-size: 0.8125rem; color: #64748b; line-height: 1.6;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="width: 8px; height: 8px; border-radius: 9999px; background: #059669;"></span>
                        <strong style="color: #0f172a;">Sistem Operasional Normal</strong>
                    </div>
                    Audit trail dan pelacakan aset fisik aktif secara stabil.
                </div>
            </div>

        </div>

    </div>

</div>
