<section id="oi-damage-report-form" class="card" aria-labelledby="oi-damage-report-title" wire:key="damage-report-{{ $reportAsset->id }}">
    <h2 id="oi-damage-report-title">Laporkan Kerusakan</h2>
    <p><strong>{{ $reportAsset->name }}</strong> · {{ $reportAsset->code }}</p>
    <p class="muted">Jelaskan kerusakan yang ditemukan. Foto dan peminjaman terkait bersifat opsional.</p>
    <form wire:submit="report">
        <label for="oi-reportDescription">Uraian kerusakan (minimal 10 karakter)</label>
        <textarea id="oi-reportDescription" wire:model="reportDescription" rows="4" required minlength="10" @error('reportDescription') aria-invalid="true" aria-describedby="oi-report-description-error" @enderror></textarea>
        @error('reportDescription')<p id="oi-report-description-error" class="errors" role="alert">{{ $message }}</p>@enderror

        <label for="oi-report-borrowing">Peminjaman terkait (opsional)</label>
        <select id="oi-report-borrowing" wire:model="reportBorrowingId">
            <option value="0">Tidak terkait peminjaman</option>
            @foreach($reportBorrowings as $borrowing)
                <option value="{{ $borrowing->id }}">#{{ $borrowing->id }} · {{ $borrowing->borrower->name }} · {{ $borrowing->borrow_date->format('d M Y') }}</option>
            @endforeach
        </select>
        <label>Foto laporan (opsional, JPG/PNG, maksimal 2 MB)
            <input type="file" wire:model="reportPhoto" accept="image/jpeg,image/png">
        </label>
        @error('reportPhoto')<p class="errors" role="alert">{{ $message }}</p>@enderror
        <p class="muted" wire:loading wire:target="reportPhoto" role="status">Mengunggah foto…</p>
        <div class="oi-head-actions">
            <button type="submit" class="primary" wire:loading.attr="disabled" wire:target="report,reportPhoto">
                <span wire:loading.remove wire:target="report">Kirim Laporan</span>
                <span wire:loading wire:target="report">Mengirim…</span>
            </button>
            <button type="button" class="secondary" wire:click="closeDamageReport" wire:loading.attr="disabled" wire:target="report,reportPhoto">Batal</button>
        </div>
    </form>
</section>
