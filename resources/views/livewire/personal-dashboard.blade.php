<div class="oi-personal-dashboard" wire:poll.30s style="max-width: 72rem; margin: 0 auto; padding-bottom: 3rem;">
    <!-- PAGE HEADER -->
    <div class="oi-page-head" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.375rem 0;">
                Halo, {{ auth()->user()->name }}
            </h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                Ringkasan inventaris komunitas dan aktivitas peminjaman akun Anda.
            </p>
        </div>
        <div class="oi-head-actions">
            <a class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.625rem 1.25rem; gap: 0.5rem;" href="{{ route('personal.organizations', ['create' => 1]) }}">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                Buat Komunitas Baru
            </a>
        </div>
    </div>

    <!-- 4 STAT METRIC CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
        <!-- Stat 1: Komunitas Aktif -->
        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b;">Komunitas Aktif</span>
                <div class="card-icon-box icon-black" style="width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">{{ $summary['organizations'] }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Komunitas yang Anda ikuti atau kelola</p>
        </div>

        <!-- Stat 2: Total Peminjaman -->
        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b;">Total Peminjaman</span>
                <div class="card-icon-box icon-black" style="width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">{{ $summary['borrowings'] }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Seluruh riwayat pengajuan peminjaman</p>
        </div>

        <!-- Stat 3: Sedang Dipinjam -->
        <div class="feature-card card-accent-red" style="padding: 1.5rem; border-radius: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.8125rem; font-weight: 700; color: #dc2626;">Pinjaman Berjalan</span>
                <div class="card-icon-box icon-red" style="width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 900; color: #dc2626; letter-spacing: -0.03em;">{{ $summary['active'] }}</div>
            <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0 0;">Unit fisik sedang Anda bawa</p>
        </div>

        <!-- Stat 4: Menunggu Persetujuan -->
        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b;">Menunggu Approval</span>
                <div class="card-icon-box icon-black" style="width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div style="font-size: 2rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">{{ $summary['pending'] }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Menunggu konfirmasi Staff</p>
        </div>
    </div>

    <!-- SECTION 1: KOMUNITAS SAYA -->
    <section aria-labelledby="my-organizations-heading" style="margin-bottom: 2.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h2 id="my-organizations-heading" style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Komunitas Saya</h2>
                <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">Komunitas yang Anda buat maupun ikuti, beserta peran aktif Anda.</p>
            </div>
            <a class="pill-btn btn-secondary-light" style="font-size: 0.75rem; padding: 0.5rem 1rem;" href="{{ route('personal.organizations') }}">Lihat Semua &rarr;</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
            @forelse($organizations->take(4) as $organization)
                <x-organization-card :org="$organization" :membership="$organization->memberships->first()">
                    <a class="pill-btn btn-solid-black" style="font-size: 0.75rem; padding: 0.4rem 0.875rem;" href="{{ route('workspace', $organization) }}">Buka Komunitas &rarr;</a>
                </x-organization-card>
            @empty
                <div class="feature-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem 1.5rem;">
                    <div style="width: 48px; height: 48px; margin: 0 auto 1rem auto; color: #94a3b8;">
                        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Belum mengikuti Komunitas</h3>
                    <p style="font-size: 0.8125rem; color: #64748b; max-width: 24rem; margin: 0 auto 1.5rem auto;">Buat komunitas sendiri atau jelajahi komunitas publik untuk mulai meminjam barang.</p>
                    <a class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.625rem 1.25rem;" href="{{ route('personal.organizations', ['listing' => 'all']) }}">Jelajahi Komunitas &rarr;</a>
                </div>
            @endforelse
        </div>
    </section>

    <!-- SECTION 2: PINJAMAN BERJALAN (JIKA ADA) -->
    @if($activeBorrowings->isNotEmpty())
        <section class="feature-card" style="margin-bottom: 2.5rem; padding: 1.75rem;" aria-labelledby="active-borrowings-heading">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <div>
                    <h2 id="active-borrowings-heading" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Pinjaman Berjalan</h2>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Barang fisik yang sedang Anda bawa saat ini.</p>
                </div>
                <a class="pill-btn btn-secondary-light" style="font-size: 0.75rem; padding: 0.4rem 0.875rem;" href="{{ route('personal.borrowings') }}">Semua Peminjaman &rarr;</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                @foreach($activeBorrowings as $borrowing)
                    <div style="border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1rem; background: #ffffff; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                        <div>
                            <strong style="display: block; font-size: 0.875rem; color: #0f172a;">{{ $borrowing->asset->name }}</strong>
                            <small style="color: #64748b;">{{ $borrowing->organization->name }}</small>
                        </div>
                        <div style="text-align: right;">
                            <span class="micro-badge {{ $borrowing->status === 'overdue' || $borrowing->due_date->lt(today('Asia/Jakarta')) ? 'micro-badge-red' : '' }}" style="margin-bottom: 0.25rem;">
                                {{ $borrowing->status === 'overdue' || $borrowing->due_date->lt(today('Asia/Jakarta')) ? 'Terlambat' : 'Dipinjam' }}
                            </span>
                            <small style="display: block; font-size: 0.6875rem; color: #94a3b8;">Jatuh tempo {{ $borrowing->due_date->format('d M Y') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- SECTION 3: RIWAYAT PEMINJAMAN TERBARU -->
    <section class="feature-card" style="padding: 1.75rem;" aria-labelledby="recent-borrowings-heading">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
            <div>
                <h2 id="recent-borrowings-heading" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Riwayat Peminjaman Terbaru</h2>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Pengajuan, persetujuan, dan pengembalian barang Anda.</p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <select class="form-input" style="padding: 0.4rem 0.75rem; font-size: 0.8125rem; width: auto;" wire:model.live="status" aria-label="Filter status peminjaman">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu Approval</option>
                    <option value="borrowed">Sedang Dipinjam</option>
                    <option value="overdue">Terlambat</option>
                    <option value="returned">Dikembalikan</option>
                    <option value="rejected">Ditolak</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
                <a class="pill-btn btn-secondary-light" style="font-size: 0.75rem; padding: 0.45rem 0.875rem;" href="{{ route('personal.borrowings') }}">Lihat Semua &rarr;</a>
            </div>
        </div>

        <div wire:loading.delay role="status" style="font-size: 0.8125rem; color: #dc2626; margin-bottom: 0.5rem; font-weight: 600;">Memuat riwayat...</div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem;">
                        <th style="padding: 0.75rem 1rem;">Aset Fisik</th>
                        <th style="padding: 0.75rem 1rem;">Komunitas</th>
                        <th style="padding: 0.75rem 1rem;">Tanggal Pinjam</th>
                        <th style="padding: 0.75rem 1rem;">Jatuh Tempo</th>
                        <th style="padding: 0.75rem 1rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $borrowing)
                        <tr wire:key="personal-borrowing-{{ $borrowing->id }}" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.875rem 1rem;">
                                <strong style="color: #0f172a; display: block;">{{ $borrowing->asset->name }}</strong>
                                <small style="color: #94a3b8;">Diperbarui {{ $borrowing->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</small>
                            </td>
                            <td style="padding: 0.875rem 1rem; color: #334155;">{{ $borrowing->organization->name }}</td>
                            <td style="padding: 0.875rem 1rem; color: #64748b;">{{ $borrowing->borrow_date->format('d M Y') }}</td>
                            <td style="padding: 0.875rem 1rem; color: #64748b;">{{ $borrowing->due_date->format('d M Y') }}</td>
                            <td style="padding: 0.875rem 1rem;">
                                <x-room.status :value="$borrowing->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada riwayat peminjaman{{ $status ? ' dengan status ini' : '' }}</p>
                                <p style="font-size: 0.75rem; margin: 0;">Pengajuan pinjaman baru Anda akan muncul di tabel ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($borrowings->hasPages())
            <div style="margin-top: 1.25rem;">{{ $borrowings->links() }}</div>
        @endif
    </section>
</div>
