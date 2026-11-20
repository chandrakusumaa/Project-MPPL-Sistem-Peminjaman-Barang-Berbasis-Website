<x-room.form-dialog id="oi-borrow-form" title="Pengajuan peminjaman"
    :description="$borrowAsset ? $borrowAsset->name . ' · ' . $borrowAsset->code : 'Aset yang dipilih sudah tidak tersedia.'" close="closeBorrowForm">
    @if($borrowAsset && $borrowAsset->status === 'available' && !$org->archived_at)
        <form wire:submit="requestBorrow" novalidate>
            <div class="grid two">
                <label>Tanggal mulai<input type="date" wire:model="borrowDate" required autofocus></label>
                <label>Tanggal kembali<input type="date" wire:model="dueDate" required></label>
            </div>
            <p class="muted">Pilih tanggal mulai hari ini atau setelahnya. Durasi maksimal {{ $org->max_borrow_days }} hari.</p>
            <label>Alasan peminjaman (minimal 10 karakter)
                <textarea wire:model="borrowReason" placeholder="Contoh: Dipakai untuk presentasi pada rapat anggota." maxlength="3000" required></textarea>
            </label>
            @if($org->damage_charge_policy)
                <h3>Kebijakan biaya kerusakan</h3>
                <p>{{ $org->damage_charge_policy }}</p>
                <label><input type="checkbox" wire:model="acknowledged" required> Saya memahami dan menyetujui kebijakan di atas.</label>
            @endif
            <p class="muted">Pengajuan menunggu persetujuan pengelola. Pengajuan tidak menjadi reservasi aset.</p>
            <div class="oi-modal-actions">
                <button type="button" class="secondary" wire:click="closeBorrowForm" wire:loading.attr="disabled">Batal</button>
                <button type="submit" class="primary" wire:loading.attr="disabled">Kirim pengajuan</button>
            </div>
        </form>
    @elseif($borrowAsset)
        <p>Aset ini sudah tidak tersedia untuk dipinjam.</p>
    @endif
</x-room.form-dialog>
