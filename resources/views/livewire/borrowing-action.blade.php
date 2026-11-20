@php
    [$title, $submitLabel] = match($borrowingAction) {
        'approve' => ['Setujui pengajuan', 'Konfirmasi Persetujuan'],
        'reject' => ['Tolak pengajuan', 'Tolak Pengajuan'],
        'inspect' => ['Catat pengembalian', 'Simpan Inspeksi'],
    };
    $eligibleStatuses = $borrowingAction === 'inspect' ? ['borrowed', 'overdue'] : ['pending'];
@endphp
<x-room.form-dialog id="oi-borrowing-action" :title="$title"
    :description="'#'.$selectedBorrowing->id.' · '.$selectedBorrowing->asset->name" close="closeBorrowingAction">
    <dl class="oi-borrowing-summary">
        <div><dt>Peminjam</dt><dd>{{ $selectedBorrowing->borrower->name }}</dd></div>
        <div><dt>Jadwal</dt><dd>{{ $selectedBorrowing->borrow_date->format('d M Y') }} – {{ $selectedBorrowing->due_date->format('d M Y') }}</dd></div>
        @if($borrowingAction !== 'inspect')
            <div><dt>Alasan peminjaman</dt><dd>{{ $selectedBorrowing->reason }}</dd></div>
        @endif
    </dl>
    @if(!$org->archived_at && in_array($selectedBorrowing->status, $eligibleStatuses, true))
        <form wire:submit="saveBorrowingAction" novalidate>
            @if($borrowingAction === 'approve')
                <p class="muted">Persetujuan bisa dilakukan pada tanggal mulai. Setelah disetujui, peminjaman aktif dan aset tercatat sebagai dipinjam.</p>
            @elseif($borrowingAction === 'reject')
                <label>Alasan penolakan (wajib)
                    <textarea wire:model="borrowingNotes" placeholder="Jelaskan alasan penolakan kepada peminjam." required autofocus></textarea>
                </label>
            @else
                <label>Kondisi aset saat dikembalikan
                    <select wire:model="inspectionCondition" autofocus>
                        <option value="good">Baik</option>
                        <option value="minor_damage">Rusak ringan</option>
                        <option value="major_damage">Rusak berat</option>
                        <option value="lost">Hilang</option>
                    </select>
                </label>
                <label>Catatan inspeksi (wajib jika rusak atau hilang)
                    <textarea wire:model="borrowingNotes" placeholder="Contoh: Dudukan lensa retak saat diperiksa."></textarea>
                </label>
                <label>Waktu pengembalian aktual (WIB)
                    <input type="datetime-local" wire:model="returnedAt" aria-describedby="oi-borrowing-return-time-help">
                </label>
                <p id="oi-borrowing-return-time-help" class="muted">Kosongkan untuk mencatat waktu sekarang.</p>
                <details class="card" @if($fineOverride !== '') open @endif>
                    <summary>Ubah denda keterlambatan (opsional)</summary>
                    <label>Denda final (Rp)<input type="number" min="0" step="1" wire:model="fineOverride"></label>
                    <label>Alasan perubahan denda<textarea wire:model="fineOverrideReason"></textarea></label>
                    <p class="muted">Kosongkan nominal untuk memakai perhitungan otomatis.</p>
                </details>
            @endif
            <div class="oi-modal-actions">
                <button type="button" class="secondary" wire:click="closeBorrowingAction" wire:loading.attr="disabled"
                    @if($borrowingAction === 'approve') autofocus @endif>Batal</button>
                <button type="submit" class="primary" wire:loading.attr="disabled">{{ $submitLabel }}</button>
            </div>
        </form>
    @else
        <p>Tindakan ini sudah tidak tersedia untuk peminjaman tersebut.</p>
    @endif
</x-room.form-dialog>
