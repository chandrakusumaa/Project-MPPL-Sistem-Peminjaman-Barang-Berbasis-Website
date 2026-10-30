<div class="oi-personal-dashboard" wire:poll.30s>
    <div class="oi-page-head">
        <div><h1>Halo, {{ auth()->user()->name }}</h1><p>Ringkasan Organization dan aktivitas terbaru akunmu.</p></div>
        <div class="oi-head-actions"><a class="oi-btn" href="{{ route('personal.organizations', ['create' => 1]) }}"><x-icon name="plus" />Buat Organization</a></div>
    </div>
    <div class="oi-stats">
        <x-room.stat label="Organization aktif" :value="$summary['organizations']" note="Organization yang kamu ikuti atau kelola" icon="grid" />
        <x-room.stat label="Total peminjaman" :value="$summary['borrowings']" note="Seluruh riwayat peminjamanmu" icon="swap" />
        <x-room.stat label="Peminjaman berjalan" :value="$summary['active']" note="Sedang dipinjam atau terlambat" icon="box" />
        <x-room.stat label="Menunggu persetujuan" :value="$summary['pending']" note="Pengajuan peminjamanmu" icon="clock" />
    </div>
    <section aria-labelledby="my-organizations-heading">
        <div class="oi-section-head"><div><h2 id="my-organizations-heading">Organization Saya</h2><p>Organization yang kamu buat maupun ikuti, beserta peranmu.</p></div><a class="oi-btn oi-secondary oi-sm" href="{{ route('personal.organizations') }}">Lihat semua →</a></div>
        <div class="oi-grid oi-cols-2 oi-org-grid">
            @forelse($organizations->take(4) as $organization)
                <x-organization-card :org="$organization" :membership="$organization->memberships->first()"><a class="oi-btn oi-sm" href="{{ route('workspace', $organization) }}">Buka Organization →</a></x-organization-card>
            @empty
                <div class="oi-panel oi-empty oi-full-width"><x-icon name="grid" :size="28" /><h3>Belum mengikuti Organization</h3><p>Buat Organization sendiri atau temukan Organization untuk mulai meminjam.</p><a class="oi-btn oi-secondary oi-sm" href="{{ route('personal.organizations', ['listing' => 'all']) }}">Jelajahi Organization →</a></div>
            @endforelse
        </div>
    </section>
    @if($activeBorrowings->isNotEmpty())
        <section class="oi-panel" aria-labelledby="active-borrowings-heading">
            <div class="oi-panel-head"><h2 id="active-borrowings-heading">Pinjaman berjalan</h2><a class="oi-btn oi-ghost oi-sm" href="{{ route('personal.borrowings') }}">Semua peminjaman →</a></div>
            <div class="oi-active-borrowings">
                @foreach($activeBorrowings as $borrowing)
                    <div class="oi-active-borrowing"><div><strong>{{ $borrowing->asset->name }}</strong><small>{{ $borrowing->organization->name }}</small></div><div><span class="oi-tag {{ $borrowing->status === 'overdue' || $borrowing->due_date->lt(today('Asia/Jakarta')) ? 'oi-tag-red' : 'oi-tag-blue' }}">{{ $borrowing->status === 'overdue' || $borrowing->due_date->lt(today('Asia/Jakarta')) ? 'Terlambat' : 'Dipinjam' }}</span><small>Jatuh tempo {{ $borrowing->due_date->format('d M Y') }}</small></div></div>
                @endforeach
            </div>
        </section>
    @endif
    <section class="oi-panel" aria-labelledby="recent-borrowings-heading">
        <div class="oi-panel-head"><div><h2 id="recent-borrowings-heading">Peminjaman terbaru</h2><p class="oi-panel-caption">Pengajuan, persetujuan, dan riwayat peminjamanmu.</p></div><a class="oi-btn oi-ghost oi-sm" href="{{ route('personal.borrowings') }}">Lihat semua →</a></div>
        <div class="oi-filters"><label>Status peminjaman<select class="oi-select" aria-label="Status peminjaman" wire:model.live="status"><option value="">Semua status</option><option value="pending">Menunggu persetujuan</option><option value="borrowed">Dipinjam</option><option value="overdue">Terlambat</option><option value="returned">Dikembalikan</option><option value="rejected">Ditolak</option><option value="cancelled">Dibatalkan</option></select></label><a class="oi-btn oi-secondary oi-sm" href="/scan"><x-icon name="qr" />Scan QR</a></div>
        <div wire:loading.delay role="status" class="oi-loading">Memuat riwayat…</div>
        <div class="oi-table-wrap"><table class="oi-table oi-history-table"><thead><tr><th>Aset</th><th>Organization</th><th>Tanggal pinjam</th><th>Jatuh tempo</th><th>Status</th></tr></thead><tbody>
            @forelse($borrowings as $borrowing)
                <tr wire:key="personal-borrowing-{{ $borrowing->id }}"><td><div class="oi-table-title">{{ $borrowing->asset->name }}</div><div class="oi-table-sub">Diperbarui {{ $borrowing->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</div></td><td>{{ $borrowing->organization->name }}</td><td>{{ $borrowing->borrow_date->format('d M Y') }}</td><td>{{ $borrowing->due_date->format('d M Y') }}</td><td><x-room.status :value="$borrowing->status" /></td></tr>
            @empty
                <tr><td colspan="5"><div class="oi-empty"><x-icon name="swap" :size="28" /><h3>Belum ada peminjaman{{ $status ? ' dengan status ini' : '' }}</h3><p>Pengajuan dan riwayat peminjaman akan tampil di sini.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
        @if($borrowings->hasPages())<div class="oi-pagination">{{ $borrowings->links() }}</div>@endif
    </section>
</div>
