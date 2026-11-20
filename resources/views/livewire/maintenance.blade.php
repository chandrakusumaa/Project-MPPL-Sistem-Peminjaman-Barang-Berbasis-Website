<section aria-labelledby="oi-maintenance-heading">
    <div class="oi-page-head">
        <div>
            <h2 id="oi-maintenance-heading">Aset dalam perbaikan</h2>
            <p>{{ $maintenanceReports->total() }} aset sedang ditangani</p>
        </div>
        <button type="button" class="oi-btn oi-btn-secondary" wire:click="showDamageReports">Lihat Laporan Kerusakan</button>
    </div>

    <div class="oi-grid oi-cols-2">
        @foreach($maintenanceReports as $repair)
            <article class="oi-panel oi-maintenance-card" wire:key="maintenance-{{ $repair->id }}">
                <div class="oi-maintenance-photo">
                    @if($repair->asset?->photo)
                        <img src="{{ asset('storage/'.$repair->asset->photo) }}" alt="Foto {{ $repair->asset->name }}" loading="lazy">
                    @elseif($repair->photo)
                        <img src="{{ route('damage.photo', $repair) }}" alt="Foto bukti kerusakan {{ $repair->asset?->name }}" loading="lazy">
                    @else
                        <div class="oi-maintenance-photo-placeholder"><x-icon name="camera" :size="30" /> Belum ada foto</div>
                    @endif
                </div>
                <div class="oi-panel-body">
                    <div>
                        <h3>{{ $repair->asset?->name ?? 'Aset #'.$repair->asset_id }}</h3>
                        <p class="muted">{{ $repair->asset?->code }}</p>
                    </div>
                    <dl class="oi-maintenance-meta">
                        <div><dt>Lokasi aset</dt><dd>{{ $repair->asset?->location ?? '—' }}</dd></div>
                        <div><dt>Tingkat kerusakan</dt><dd>{{ match($repair->severity) { 'major' => 'Berat', 'minor' => 'Ringan', default => 'Belum dinilai' } }}</dd></div>
                        <div>
                            <dt>Mulai perbaikan</dt>
                            <dd>{{ $repair->maintenance_started_at ? \Carbon\CarbonImmutable::parse($repair->maintenance_started_at, 'UTC')->setTimezone('Asia/Jakarta')->format('d/m/Y, H:i').' WIB' : '—' }}</dd>
                        </div>
                        <div><dt>Ditangani oleh</dt><dd>{{ $repair->handler?->name ?? '—' }}</dd></div>
                    </dl>
                    <div class="oi-report-detail-box">
                        <h4>Deskripsi kerusakan</h4>
                        <p>{{ $repair->description }}</p>
                    </div>
                    @if($repair->assessment_notes)
                        <div><h4>Catatan penilaian</h4><p>{{ $repair->assessment_notes }}</p></div>
                    @endif
                    <div class="oi-action-group oi-maintenance-actions">
                        @unless($org->archived_at)
                            <button type="button" id="oi-finish-maintenance-{{ $repair->id }}" class="oi-btn" wire:click="openMaintenanceModal({{ $repair->id }})" wire:loading.attr="disabled"
                                aria-haspopup="dialog">Selesaikan Perbaikan</button>
                        @endunless
                        <button type="button" class="oi-btn oi-btn-secondary" wire:click="openReportModal({{ $repair->id }})" wire:loading.attr="disabled">Laporan #{{ $repair->id }}</button>
                        @if($repair->photo)
                            <a class="oi-btn oi-btn-secondary" href="{{ route('damage.photo', $repair) }}" target="_blank" rel="noopener">Buka Foto Bukti</a>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    @if($maintenanceReports->isEmpty())
        <div class="oi-panel oi-empty">
            <x-icon name="tool" :size="28" />
            <h3>Tidak ada aset dalam perbaikan</h3>
            <p>Setelah perbaikan dimulai dari laporan atau inspeksi pengembalian, asetnya akan muncul di sini.</p>
        </div>
    @endif
    @if($maintenanceReports->hasPages())
        <div class="oi-pagination">{{ $maintenanceReports->links() }}</div>
    @endif
</section>

@if($selectedMaintenance)
    <x-room.form-dialog id="oi-maintenance-form" title="Selesaikan perbaikan"
        :description="$selectedMaintenance->asset?->name ?? 'Aset #'.$selectedMaintenance->asset_id" close="closeMaintenanceModal">
        @if($selectedMaintenance->status === 'in_maintenance' && !$org->archived_at)
            <form wire:submit="finishMaintenance" novalidate>
                <label>Kondisi akhir aset
                    <select wire:model="maintenanceCondition" required>
                        <option value="good">Baik</option>
                        <option value="minor_damage">Kerusakan ringan</option>
                    </select>
                </label>
                <label>Catatan perbaikan
                    <textarea wire:model="resolutionNotes" placeholder="Contoh: Kabel daya diganti dan aset sudah diuji." required autofocus></textarea>
                </label>
                <p class="muted">Setelah disimpan, aset kembali tersedia dan catatan tersimpan di laporan kerusakan.</p>
                <div class="oi-modal-actions">
                    <button type="button" class="secondary" wire:click="closeMaintenanceModal">Batal</button>
                    <button type="submit" class="primary" wire:loading.attr="disabled">Simpan Penyelesaian</button>
                </div>
            </form>
        @else
            <p>Perbaikan ini sudah tidak aktif.</p>
        @endif
    </x-room.form-dialog>
@endif
