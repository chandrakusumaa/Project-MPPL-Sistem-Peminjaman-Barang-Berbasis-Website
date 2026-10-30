<div class="oi-stats" aria-label="Ringkasan inventaris">
    <x-room.stat label="Total aset" :value="$counts->sum()" note="Semua inventaris" icon="box" />
    <x-room.stat label="Tersedia" :value="$counts['available'] ?? 0" note="Siap dipinjam" icon="check" />
    <x-room.stat label="Sedang dipinjam" :value="$summary['borrowed']" note="Perlu dipantau" icon="swap" />
    <x-room.stat label="Menunggu persetujuan" :value="$summary['pending']" note="Butuh tindakan" icon="clock" />
</div>
<div class="oi-grid oi-cols-2">
    <section class="oi-panel">
        <div class="oi-panel-head"><h2>Permintaan peminjaman</h2><button class="oi-btn oi-ghost oi-sm" wire:click="$set('tab', 'borrowings')">Lihat semua →</button></div>
        @if($summary['pendingRequests']->isNotEmpty())
        <div class="oi-table-wrap" tabindex="0" role="region" aria-label="Tabel permintaan peminjaman"><table class="oi-table">
            <thead><tr><th scope="col">Peminjam</th><th scope="col">Aset</th><th scope="col"><span class="sr-only">Tindakan</span></th></tr></thead>
            <tbody>@foreach($summary['pendingRequests'] as $borrowing)
            <tr wire:key="dashboard-request-{{ $borrowing->id }}"><td><div class="oi-table-title">{{ $borrowing->borrower->name }}</div><div class="oi-table-sub">{{ $borrowing->borrow_date->locale('id')->translatedFormat('d M Y') }}</div></td><td>{{ $borrowing->asset->name }}</td><td><button class="oi-btn oi-secondary oi-sm" wire:click="reviewBorrowing({{ $borrowing->id }})">Tinjau</button></td></tr>
            @endforeach</tbody>
        </table></div>
        @else
        <div class="oi-empty"><x-icon name="clock" size="28" /><h3>Tidak ada permintaan</h3><p>Permintaan baru akan muncul di sini.</p></div>
        @endif
    </section>
    <section class="oi-panel">
        <div class="oi-panel-head"><h2>Inventaris terbaru</h2><button class="oi-btn oi-ghost oi-sm" wire:click="$set('tab', 'inventory')">Lihat semua →</button></div>
        @if($summary['recentAssets']->isNotEmpty())
        <div class="oi-table-wrap" tabindex="0" role="region" aria-label="Tabel inventaris terbaru"><table class="oi-table">
            <thead><tr><th scope="col">Nama aset</th><th scope="col">Status</th></tr></thead>
            <tbody>@foreach($summary['recentAssets'] as $asset)
            <tr wire:key="dashboard-asset-{{ $asset->id }}"><td><a class="oi-table-title" href="{{ route('asset.show', [$org, $asset]) }}">{{ $asset->name }}</a><div class="oi-table-sub">{{ $asset->code }}</div></td><td><x-room.status :value="$asset->status" /></td></tr>
            @endforeach</tbody>
        </table></div>
        @else
        <div class="oi-empty"><x-icon name="box" size="28" /><h3>Belum ada aset</h3><p>Tambahkan aset pertama.</p></div>
        @endif
    </section>
</div>
<div class="oi-grid oi-cols-3">
    <button type="button" class="oi-shortcut" wire:click="showDamageReports"><span class="oi-shortcut-icon"><x-icon name="warning" size="22" /></span><h3>Laporan kerusakan</h3><p>{{ $summary['reports'] }} laporan perlu dipantau</p></button>
    <button type="button" class="oi-shortcut" wire:click="showMaintenance"><span class="oi-shortcut-icon"><x-icon name="tool" size="22" /></span><h3>Maintenance</h3><p>{{ $summary['maintenance'] }} jadwal aktif</p></button>
    <button type="button" class="oi-shortcut" wire:click="$set('tab', 'members')" @disabled(!$admin) @if(!$admin) title="Hanya untuk Admin" @endif><span class="oi-shortcut-icon"><x-icon name="users" size="22" /></span><h3>Anggota</h3><p>{{ $summary['members'] }} anggota terdaftar</p></button>
</div>
<section class="oi-panel">
    <div class="oi-panel-head"><h2>Tagihan & Pembayaran</h2><button class="oi-btn oi-secondary oi-sm" wire:click="$set('tab', 'charges')">Lihat tagihan →</button></div>
    <div class="oi-panel-body"><b>Rp{{ number_format($summary['balance'], 0, ',', '.') }}</b> sisa tagihan · {{ $summary['pendingPayments'] }} pembayaran menunggu verifikasi</div>
</section>
<div class="oi-secondary-metrics" aria-label="Status operasional lainnya">
    <span>{{ $counts['maintenance'] ?? 0 }} aset maintenance</span><span>{{ $counts['lost'] ?? 0 }} aset hilang</span><span>{{ $summary['overdue'] }} pinjaman terlambat</span>
</div>
@if($summary['overdue'] > 0)
<section class="oi-panel"><div class="oi-panel-head"><h2>Pinjaman terlambat</h2><button class="oi-btn oi-ghost oi-sm" wire:click="showOverdue">Lihat semua →</button></div>
<div class="oi-table-wrap" tabindex="0" role="region" aria-label="Pinjaman terlambat"><table class="oi-table"><thead><tr><th>Peminjam</th><th>Aset</th><th>Jatuh tempo</th></tr></thead><tbody>@foreach($summary['overdueBorrowings'] as $borrowing)<tr><td>{{ $borrowing->borrower->name }}</td><td>{{ $borrowing->asset->name }}</td><td>{{ $borrowing->due_date->format('d M Y') }}</td></tr>@endforeach</tbody></table></div></section>
@endif
<details class="oi-panel oi-analytics">
    <summary>Analitik inventaris</summary>
    <div class="oi-panel-body oi-grid oi-cols-3">
        <section wire:ignore.self><h3>Komposisi aset</h3><div class="oi-chart"><canvas data-composition='@json($counts)'></canvas></div></section>
        <section wire:ignore.self><h3>Pinjaman dimulai · 6 bulan</h3><div class="oi-chart"><canvas data-series='@json($monthly)'></canvas></div></section>
        <section wire:ignore.self><h3>Aset paling sering dipinjam</h3><div class="oi-chart"><canvas data-series='@json($top)'></canvas></div></section>
    </div>
</details>
