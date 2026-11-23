@php
    $statuses = [
        'pending' => ['Menunggu persetujuan', 'amber'],
        'borrowed' => ['Sedang dipinjam', 'blue'],
        'overdue' => ['Terlambat', 'red'],
        'returned' => ['Dikembalikan', 'green'],
        'rejected' => ['Ditolak', 'red'],
        'cancelled' => ['Dibatalkan', ''],
    ];
@endphp

<section class="oi-panel" aria-labelledby="oi-borrowing-list">
    <div class="oi-panel-head">
        <div>
            <h2 id="oi-borrowing-list" tabindex="-1">{{ $operator ? 'Pengelolaan peminjaman' : 'Peminjaman saya' }}</h2>
            @if($operator)
                <p class="oi-panel-caption">Pengajuan yang menunggu ditampilkan di atas. Pilih tindakan pada baris pengajuan atau pinjaman.</p>
            @endif
        </div>
    </div>
    <div class="oi-panel-body grid four">
        <label>Filter status<select wire:model.live="borrowStatus">
            <option value="">Semua status</option>
            @foreach($statuses as $key => [$label, $color])
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select></label>
        <label>Tanggal mulai dari<input type="date" wire:model.live="borrowFrom"></label>
        <label>Tanggal mulai sampai<input type="date" wire:model.live="borrowTo"></label>
        <label>Cari peminjam / aset<input wire:model.live.debounce.300ms="borrowSearch" placeholder="Nama peminjam atau aset"></label>
    </div>
    <div class="oi-table-wrap">
        <table class="oi-table">
            <thead><tr><th scope="col">ID / Aset</th><th scope="col">Peminjam</th><th scope="col">Jadwal</th><th scope="col">Status</th><th scope="col">Tindakan</th></tr></thead>
            <tbody>
                @forelse($borrowings as $borrowing)
                    @php([$statusLabel, $statusColor] = $statuses[$borrowing->status] ?? [$borrowing->status, ''])
                    <tr id="oi-borrowing-{{ $borrowing->id }}" wire:key="borrowing-{{ $borrowing->id }}">
                        <td><span class="oi-table-title">{{ $borrowing->asset->name }}</span><small>#{{ $borrowing->id }}</small></td>
                        <td>{{ $borrowing->borrower->name }}</td>
                        <td>{{ $borrowing->borrow_date->format('d M Y') }} – {{ $borrowing->due_date->format('d M Y') }}</td>
                        <td>
                            <span @class(['oi-tag', 'oi-tag-'.$statusColor => $statusColor !== ''])>{{ $statusLabel }}</span>
                            @if($borrowing->status === 'rejected' && $borrowing->rejection_reason)
                                <small>{{ $borrowing->rejection_reason }}</small>
                            @endif
                        </td>
                        <td><div class="oi-action-group">
                            @if(!$org->archived_at && $borrowing->status === 'pending')
                                @if($operator)
                                    <button type="button" id="oi-borrowing-approve-{{ $borrowing->id }}" class="oi-btn oi-btn-sm"
                                        wire:click="openBorrowingAction({{ $borrowing->id }}, 'approve')" wire:loading.attr="disabled" aria-haspopup="dialog">Setujui</button>
                                    <button type="button" id="oi-borrowing-reject-{{ $borrowing->id }}" class="oi-btn oi-btn-secondary oi-btn-sm"
                                        wire:click="openBorrowingAction({{ $borrowing->id }}, 'reject')" wire:loading.attr="disabled" aria-haspopup="dialog">Tolak</button>
                                @endif
                                @if($borrowing->user_id === auth()->id())
                                    <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" wire:click="cancelBorrowing({{ $borrowing->id }})"
                                        wire:confirm="Batalkan pengajuan ini?" wire:loading.attr="disabled">Batalkan</button>
                                @endif
                            @elseif(!$org->archived_at && $operator && in_array($borrowing->status, ['borrowed', 'overdue'], true))
                                <button type="button" id="oi-borrowing-inspect-{{ $borrowing->id }}" class="oi-btn oi-btn-sm"
                                    wire:click="openBorrowingAction({{ $borrowing->id }}, 'inspect')" wire:loading.attr="disabled" aria-haspopup="dialog">Catat Pengembalian</button>
                            @elseif(!$org->archived_at && ! $operator && $borrowing->user_id === auth()->id() && in_array($borrowing->status, ['borrowed', 'overdue'], true))
                                <button type="button" class="oi-btn oi-btn-sm"
                                    wire:click="requestReturn({{ $borrowing->id }})" wire:confirm="Kirim pemberitahuan pengembalian ke Admin/Staff? Serahkan barang secara fisik agar pengelola mencatat inspeksi pengembalian." wire:loading.attr="disabled">Kembalikan Barang</button>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada peminjaman yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($borrowings->hasPages())
        <div class="oi-pagination">{{ $borrowings->links() }}</div>
    @endif
</section>

@if($selectedBorrowing)
    @include('livewire.borrowing-action')
@endif
