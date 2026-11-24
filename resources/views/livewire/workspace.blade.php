<div wire:poll.30s>
    <x-room.frame :org="$org" :operator="$operator" :admin="$admin" :tab="$tab"
        :show-feedback="!$selectedMaintenance && !$selectedBorrowing && !($tab === 'inventory' && (($canBorrow && $borrowAssetId) || ($operator && ($assetFormOpen || $categoryFormOpen))))">
    @if($org->archived_at)<div class="notice">Organization diarsipkan. Data hanya dapat dibaca.</div>@endif
    @php
        $titles = ['dashboard' => $org->name, 'inventory' => 'Inventaris', 'borrowings' => 'Peminjaman', 'charges' => 'Riwayat Tagihan & Pembayaran', 'members' => 'Anggota Organization', 'audit' => 'Audit Aktivitas', 'damage-reports' => 'Laporan Kerusakan', 'maintenance' => 'Maintenance', 'settings' => 'Pengaturan Organization'];
        $statusLabels = ['open' => 'Laporan Baru', 'noted' => 'Sudah Dicatat', 'in_maintenance' => 'Dalam Perbaikan', 'resolved' => 'Selesai'];
        $sourceLabels = ['member_report' => 'Laporan Member', 'staff_report' => 'Laporan Pengelola', 'return_inspection' => 'Inspeksi Pengembalian'];
    @endphp
    <!-- WORKSPACE HEADER CARD (SESUAI FOTO 3) -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.25rem 1.75rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 1.15rem; min-width: 0;">
            <div style="width: 48px; height: 48px; border-radius: 9999px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                </svg>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <h1 style="font-size: 1.375rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2;">{{ $org->name }}</h1>
                    <span style="font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px; text-transform: uppercase; {{ $admin ? 'background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3;' : ($operator ? 'background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe;' : 'background: #f1f5f9; color: #475467;') }}">
                        {{ $admin ? 'ADMINISTRATOR' : ($operator ? 'STAFF OPERASIONAL' : 'MEMBER') }}
                    </span>
                </div>
                <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0.35rem 0 0 0;">
                    {{ $org->description ? Str::limit($org->description, 60) . ' · ' : '' }}Maks Pinjam: <strong style="font-weight: 700; color: #0f172a;">{{ $org->max_loan_days ?? 7 }} Hari</strong> · Denda: <strong style="font-weight: 700; color: #0f172a;">Rp {{ number_format($org->late_fee_per_day ?? 25000, 0, ',', '.') }}/hari</strong>
                </p>
            </div>
        </div>

        <!-- ACTION BUTTONS: SESUAI FOTO 3 -->
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <button type="button" style="padding: 0.625rem 1.25rem; font-size: 0.8125rem; font-weight: 700; border-radius: 0.625rem; background: #7f112e; color: #ffffff; border: none; cursor: pointer; transition: background 0.15s ease;" wire:click="$set('tab', 'inventory')" onmouseover="this.style.background='#680e2c'" onmouseout="this.style.background='#7f112e'">
                Ajukan Peminjaman Unit &rarr;
            </button>
            @if($operator)
                <button type="button" style="padding: 0.625rem 1.15rem; font-size: 0.8125rem; font-weight: 700; border-radius: 0.625rem; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; cursor: pointer; transition: all 0.15s ease;" wire:click="openAssetForm" wire:loading.attr="disabled" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                    + Tambah Unit Fisik
                </button>
            @endif
        </div>
    </div>
    <div wire:loading.delay role="status" class="muted">Memproses...</div>

    @if($tab==='dashboard')
        @include('livewire.room-dashboard')
    @elseif($tab==='inventory')
        @if($operator && $assetFormOpen)
            <x-room.form-dialog id="oi-asset-form" :title="$editingAsset ? 'Edit aset' : 'Tambah aset'"
                description="Isi nama, kategori, dan lokasi penyimpanan untuk satu unit barang." close="closeAssetForm">
                <form wire:submit="asset" novalidate>
                    <label>Nama aset<input id="oi-assetName" wire:model="assetName" placeholder="Contoh: Proyektor Epson EB-X06" maxlength="180" required autofocus></label>
                    <label><span id="oi-asset-category-label">Kategori</span>
                        <select wire:model="categoryId" aria-labelledby="oi-asset-category-label">
                            <option value="0">Pilih kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if($categories->isEmpty())
                        <p class="muted">Tutup formulir ini, lalu gunakan Tambah kategori aset untuk membuat kategori pertama.</p>
                    @endif
                    <label>Lokasi<input wire:model="location" placeholder="Contoh: Lemari 2, Ruang Multimedia" maxlength="180" required></label>
                    <label>Deskripsi (opsional)<textarea wire:model="assetDescription" placeholder="Contoh: Dilengkapi kabel HDMI dan remote."></textarea></label>
                    <label>Spesifikasi (opsional)<textarea wire:model="assetSpecifications" placeholder="Contoh: Resolusi 1024 × 768, 3.600 lumen."></textarea></label>
                    <label>Foto aset (opsional, JPG/PNG maksimal 2 MB)<input type="file" wire:model="assetPhoto" accept="image/jpeg,image/png"></label>
                    <div class="oi-modal-actions">
                        <button type="button" class="secondary" wire:click="closeAssetForm">Batal</button>
                        <button type="submit" class="primary" wire:loading.attr="disabled" wire:target="asset,assetPhoto" @disabled($categories->isEmpty())>
                            {{ $editingAsset ? 'Perbarui aset' : 'Simpan aset' }}
                        </button>
                    </div>
                </form>
            </x-room.form-dialog>
        @elseif($operator && $categoryFormOpen)
            <x-room.form-dialog id="oi-category-form" title="Tambah kategori aset"
                description="Beri nama kelompok untuk aset sejenis. Kategori ini bisa dipilih saat menambah atau mengedit aset." close="closeCategoryForm">
                <form wire:submit="category" novalidate>
                    <label>Nama kategori<input id="oi-categoryName" wire:model="categoryName" placeholder="Contoh: Peralatan audio" maxlength="120" required autofocus></label>
                    <button type="submit" class="primary" wire:loading.attr="disabled">Simpan kategori</button>
                </form>
                @if($categories->isNotEmpty())
                    <section class="oi-modal-actions">
                        <div class="oi-inventory-category-list">
                            <h3>Kategori yang tersedia</h3>
                            @foreach($categories as $cat)
                                <div class="list-row" wire:key="inventory-category-{{ $cat->id }}">
                                    <span>{{ $cat->name }}</span>
                                    <div class="oi-action-group">
                                        <button type="button" class="secondary" wire:click="renameCategory({{ $cat->id }})" wire:loading.attr="disabled">Ubah ke nama di atas</button>
                                        <button type="button" class="secondary" wire:click="deleteCategory({{ $cat->id }})" wire:loading.attr="disabled"
                                            wire:confirm="Hapus kategori ini jika belum digunakan?">Hapus</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </x-room.form-dialog>
        @elseif($canBorrow && $borrowAssetId)
            @include('livewire.borrow-request-form')
        @endif

        @if($focusedAsset && $assets->first())
            <section class="panel-card oi-panel" style="padding: 1.5rem 1.75rem; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0 0 0.5rem 0;">{{ $assets->first()->name }}</h2>
                <p>{{ $assets->first()->description }}</p>
                <p class="muted">{{ $assets->first()->specifications }}</p>
                <p>Lokasi: <strong>{{ $assets->first()->location }}</strong></p>
            </section>
        @endif

        @if($reportAsset)
            @include('livewire.damage-report-form')
        @endif

        <section class="panel-card oi-panel" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; overflow: hidden; margin-bottom: 2rem;">
            <div class="panel-header" style="padding: 1.5rem 1.75rem 1.25rem 1.75rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: flex-start; gap: 1.25rem; flex-wrap: wrap;">
                <div>
                    <h2 class="panel-title" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">
                        {{ $admin ? 'Master Inventaris Fisik (Administrator)' : ($operator ? 'Master Inventaris Fisik (Staff)' : 'Katalog Inventaris Fisik') }}
                    </h2>
                    <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0;">
                        {{ $operator ? 'Akses penuh: Daftarkan unit baru, mutasi lokasi simpan, atau tinjau riwayat aset.' : 'Daftar unit fisik barang yang terdaftar di Organization ini. Setiap unit memiliki kode unik stabil.' }}
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="position: relative; min-width: 240px;">
                        <input class="oi-input" placeholder="Cari nama atau kode unit..." wire:model.live.debounce.300ms="search" style="width: 100%; padding: 0.5rem 0.85rem 0.5rem 2.25rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; font-size: 0.8125rem;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    @if($operator)
                        <button type="button" wire:click="openAssetForm" style="background: #05214c; color: #ffffff; border: none; border-radius: 0.5rem; padding: 0.55rem 1.1rem; font-size: 0.8125rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; transition: background 0.15s ease;" onmouseover="this.style.background='#021430'" onmouseout="this.style.background='#05214c'">
                            + Tambah Aset Baru
                        </button>
                    @endif
                </div>
            </div>

            <div class="oi-table-wrap" style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid #f1f5f9; background: #fafbfc;">
                            <th style="padding: 0.85rem 1.75rem; font-size: 0.6875rem; font-weight: 800; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">KODE ASET FISIK</th>
                            <th style="padding: 0.85rem 1.25rem; font-size: 0.6875rem; font-weight: 800; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">NAMA BARANG FISIK</th>
                            <th style="padding: 0.85rem 1.25rem; font-size: 0.6875rem; font-weight: 800; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">KATEGORI</th>
                            <th style="padding: 0.85rem 1.25rem; font-size: 0.6875rem; font-weight: 800; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">LOKASI RAK</th>
                            <th style="padding: 0.85rem 1.25rem; font-size: 0.6875rem; font-weight: 800; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase;">STATUS KETERSEDIAAN</th>
                            <th style="padding: 0.85rem 1.75rem; font-size: 0.6875rem; font-weight: 800; color: #64748b; letter-spacing: 0.05em; text-transform: uppercase; text-align: right;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $a)
                            @php
                                $activeBrw = \App\Models\Borrowing::where('asset_id', $a->id)->whereIn('status', ['borrowed', 'overdue'])->with('borrower')->first();
                                $borrower = $activeBrw?->borrower;
                                $borrowerName = $borrower ? ($borrower->id === auth()->id() ? 'SAYA' : explode(' ', trim($borrower->name))[0]) : null;
                            @endphp
                            <tr wire:key="asset-{{ $a->id }}" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#fcfdfd'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.1rem 1.75rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.8125rem; font-weight: 700; color: #0f172a; white-space: nowrap;">
                                    {{ $a->code }}
                                </td>
                                <td style="padding: 1.1rem 1.25rem;">
                                    <a href="{{ route('asset.show', [$org, $a]) }}" style="font-weight: 700; color: #0f172a; text-decoration: none; transition: color 0.15s ease;" onmouseover="this.style.color='#7f112e'" onmouseout="this.style.color='#0f172a'">
                                        {{ $a->name }}
                                    </a>
                                </td>
                                <td style="padding: 1.1rem 1.25rem; color: #475569; font-weight: 500;">
                                    {{ $a->category->name }}
                                </td>
                                <td style="padding: 1.1rem 1.25rem; color: #475569; font-weight: 500;">
                                    {{ $a->location }}
                                </td>
                                <td style="padding: 1.1rem 1.25rem; white-space: nowrap;">
                                    @if($a->status === 'available')
                                        <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 800; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; text-transform: uppercase;">
                                            <span style="font-size: 0.6rem; color: #10b981;">●</span> TERSEDIA
                                        </span>
                                    @elseif($a->status === 'borrowed')
                                        <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 800; background: #fefce8; color: #b45309; border: 1px solid #fde047; text-transform: uppercase;">
                                            <span style="font-size: 0.6rem; color: #f59e0b;">●</span> DIPINJAM {{ $borrowerName ? '('.strtoupper($borrowerName).')' : '' }}
                                        </span>
                                    @elseif($a->status === 'maintenance')
                                        <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 800; background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; text-transform: uppercase;">
                                            <span style="font-size: 0.6rem; color: #e11d48;">●</span> MAINTENANCE
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 800; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; text-transform: uppercase;">
                                            <span style="font-size: 0.6rem; color: #64748b;">●</span> {{ strtoupper($a->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 1.1rem 1.75rem; text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: flex-end;">
                                        @if($a->status === 'available' && !$org->archived_at)
                                            <button type="button" class="btn-maroon" style="padding: 0.45rem 1rem; font-size: 0.75rem; font-weight: 700; border-radius: 0.5rem; background: #7f112e; color: #ffffff; border: none; cursor: pointer; transition: background 0.15s ease;" wire:click="openBorrowForm({{ $a->id }})" wire:loading.attr="disabled" onmouseover="this.style.background='#680e2c'" onmouseout="this.style.background='#7f112e'">
                                                Ajukan Pinjam
                                            </button>
                                        @elseif($a->status === 'borrowed')
                                            <span style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; letter-spacing: 0.04em;">
                                                SEDANG DIPINJAM
                                            </span>
                                        @elseif($a->status === 'maintenance')
                                            <span style="font-size: 0.75rem; font-weight: 800; color: #be123c; letter-spacing: 0.04em;">
                                                DI BENGKEL SERVIS
                                            </span>
                                        @else
                                            <span style="font-size: 0.75rem; font-weight: 800; color: #94a3b8;">
                                                TIDAK TERSEDIA
                                            </span>
                                        @endif

                                        @if($operator)
                                            <button type="button" title="Edit Aset" style="padding: 0.4rem 0.65rem; font-size: 0.75rem; font-weight: 700; border-radius: 0.5rem; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.15s ease;" wire:click="editAsset({{ $a->id }})" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 2.5rem 1rem; text-align: center; color: #94a3b8;">
                                    <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada aset fisik yang sesuai</p>
                                    <p style="font-size: 0.75rem; margin: 0;">Coba sesuaikan kata kunci pencarian aset fisik.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assets->hasPages())
                <div class="oi-pagination" style="padding: 1rem 1.75rem; border-top: 1px solid #f1f5f9;">
                    {{ $assets->links() }}
                </div>
            @endif
        </section>

    @elseif($tab==='borrowings')
        @include('livewire.borrowing-list')

    @elseif($tab==='charges')
        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <div>
                    <h2 class="panel-title">Daftar Tagihan & Pembayaran</h2>
                    <p class="oi-panel-caption">Untuk menetapkan biaya kerusakan baru, buka menu <strong>Laporan Kerusakan</strong> lalu klik salah satu laporan.</p>
                </div>
                @if($operator)<button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:click="showDamageReports">Buka Laporan Kerusakan →</button>@endif
            </div>
            <div class="oi-filters" style="padding: 1.25rem 1.75rem; border-bottom: 1px solid #f1f5f9; background: #fafbfc;">
                <label>Status tagihan
                    <select class="oi-select" wire:model.live="chargeStatus" style="margin-left: 0.5rem;">
                        <option value="">Semua status</option>
                        <option value="draft">Draft</option>
                        <option value="issued">Diterbitkan (Aktif)</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </label>
            </div>

            <div class="panel-body oi-panel-body" style="display: flex; flex-direction: column; gap: 1.25rem;">
                @forelse($charges as $c)
                    <article class="panel-card" style="margin-bottom: 0; box-shadow: none; border-color: #e2e8f0;" wire:key="charge-{{ $c->id }}">
                        <div class="panel-header oi-panel-head">
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 800; margin: 0; color: #0f172a;">
                                    Tagihan #{{ $c->id }} · {{ $c->charge_type === 'damage' ? 'Biaya Kerusakan' : ($c->charge_type === 'loss' ? 'Penggantian Kehilangan' : 'Denda Keterlambatan') }}
                                </h3>
                                <p class="muted" style="margin: 0.25rem 0 0 0;">
                                    Peminjam: <strong style="color: #0f172a;">{{ $c->borrower?->name ?? 'User #'.$c->borrower_id }}</strong>@if($c->borrower?->email) ({{ $c->borrower->email }})@endif
                                    · Aset: <strong style="color: #0f172a;">{{ $c->asset?->name ?? '-' }}</strong>
                                    @if($c->borrowing_id) · Peminjaman #{{ $c->borrowing_id }}@endif
                                </p>
                            </div>
                            <span class="oi-tag {{ $c->status === 'issued' ? 'oi-tag-blue' : ($c->status === 'draft' ? 'oi-tag-amber' : 'oi-tag-neutral') }}">
                                {{ strtoupper($c->status) }} / {{ strtoupper($c->settlement) }}
                            </span>
                        </div>
                        <div class="panel-body oi-panel-body">
                            <p style="margin: 0 0 0.5rem 0;">{{ $c->reason }}</p>
                            <p class="muted" style="font-size: 0.8125rem;">Dasar kebijakan: {{ $c->policy_basis }}</p>
                            @if($c->borrower_id === auth()->id() && $c->status === 'draft' && !$c->liability_acknowledged_at)
                                <div style="margin: 1rem 0;">
                                    <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'acknowledge')" wire:confirm="Saya menyetujui dasar tanggung jawab dan kebijakan pada tagihan ini.">Setujui dasar tanggung jawab</button>
                                </div>
                            @endif
                            @if($c->evidence_path)
                                <div style="margin: 0.75rem 0;">
                                    <a class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" href="{{ route('evidence', $c) }}">Unduh bukti kerusakan ↗</a>
                                </div>
                            @endif
                            <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin: 1.25rem 0; padding: 1rem; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
                                <div><span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Nominal Tagihan</span><div style="font-size: 1.125rem; font-weight: 800; color: #0f172a;">Rp{{ number_format($c->amount, 0, ',', '.') }}</div></div>
                                <div><span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Pembebasan / Diskon</span><div style="font-size: 1.125rem; font-weight: 800; color: #10b981;">Rp{{ number_format($c->credits->sum('amount'), 0, ',', '.') }}</div></div>
                                <div><span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Sisa Tagihan</span><div style="font-size: 1.125rem; font-weight: 900; color: #dc2626;">Rp{{ number_format($c->balance, 0, ',', '.') }}</div></div>
                            </div>
                            @if($operator)
                                <div class="oi-action-group" style="margin-top: 1rem;">
                                    @if($c->status === 'draft' && $admin)
                                        <button type="button" class="oi-btn oi-btn-primary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'issue')" wire:confirm="Terbitkan tagihan ini?">Terbitkan Tagihan</button>
                                    @endif
                                    @if($c->status === 'issued' && $c->balance > 0)
                                        <button type="button" class="oi-btn oi-btn-primary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="openChargeModal({{ $c->id }}, 'payment')">Catat Pembayaran</button>
                                        @if($admin)
                                            <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="openChargeModal({{ $c->id }}, 'credit')">Beri Pembebasan</button>
                                        @endif
                                    @endif
                                    @if($admin && $c->status !== 'cancelled' && $c->payments->where('status', 'confirmed')->isEmpty() && $c->credits->isEmpty())
                                        <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="openChargeModal({{ $c->id }}, 'cancel')">Batalkan Tagihan</button>
                                    @endif
                                </div>
                            @elseif(!$org->archived_at && $c->borrower_id === auth()->id() && $c->status === 'issued' && $c->balance > 0)
                                <div class="oi-action-group" style="margin-top: 1rem;">
                                    <button type="button" class="oi-btn oi-btn-primary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="openChargeModal({{ $c->id }}, 'payment')">Bayar Tagihan</button>
                                </div>
                            @endif

                            @if($c->payments->isNotEmpty())
                                <div style="margin-top: 1.25rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                                    <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.5rem;">Riwayat Pembayaran:</span>
                                    @foreach($c->payments as $p)
                                        <div class="list-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0; border-bottom: 1px dashed #f1f5f9;">
                                            <div>
                                                <strong>Pembayaran #{{ $p->id }}</strong> · Rp{{ number_format($p->amount, 0, ',', '.') }} · {{ $p->method === 'bank_transfer' ? 'Transfer Bank' : ($p->method === 'cash' ? 'Tunai' : 'Lainnya') }}
                                                <span class="oi-tag {{ $p->status === 'confirmed' ? 'oi-tag-green' : ($p->status === 'pending' ? 'oi-tag-amber' : 'oi-tag-red') }}" style="margin-left: 0.5rem;">
                                                    {{ strtoupper($p->status) }}
                                                </span>
                                                @if($p->reference || $p->notes || $p->evidence_path)
                                                    <div class="oi-table-sub" style="margin-top: 0.25rem;">
                                                        @if($p->reference)<span>Ref: {{ $p->reference }}</span>@endif
                                                        @if($p->reference && $p->notes)<span> · </span>@endif
                                                        @if($p->notes)<span>{{ $p->notes }}</span>@endif
                                                        @if($p->evidence_path)
                                                            <a href="{{ route('evidence', [$c, $p->id]) }}" style="margin-left: 0.5rem; font-weight: 700; text-decoration: underline;">Bukti Bayar ↗</a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            @if($admin && $p->status === 'pending')
                                                <div class="oi-action-group">
                                                    <button type="button" class="oi-btn oi-btn-primary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'confirm',{{ $p->id }})">Konfirmasi Lunas</button>
                                                    <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'reject',{{ $p->id }})">Tolak</button>
                                                </div>
                                            @elseif($admin && $p->status === 'confirmed')
                                                <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'void',{{ $p->id }})" wire:confirm="Void pembayaran ini tanpa menghapus histori?">Void</button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="oi-empty">
                        <x-icon name="clock" :size="28" />
                        <h3>Belum ada tagihan</h3>
                        <p>Tagihan keterlambatan atau ganti rugi fisik akan tercantum di sini.</p>
                    </div>
                @endforelse
            </div>
            @if($charges->hasPages())
                <div class="oi-pagination" style="padding: 1rem 1.75rem;">
                    {{ $charges->links() }}
                </div>
            @endif
        </section>

        @if($selectedCharge)
            <div class="oi-modal-backdrop" wire:click.self="closeChargeModal" role="dialog" aria-modal="true" aria-labelledby="oi-charge-modal-title">
                <div class="oi-modal-card">
                    <div class="oi-modal-head">
                        <div>
                            <span class="oi-tag oi-tag-blue">Tagihan #{{ $selectedCharge->id }} · Sisa Rp{{ number_format($selectedCharge->balance, 0, ',', '.') }}</span>
                            <h2 id="oi-charge-modal-title">
                                @if($chargeModalAction === 'payment')
                                    Catat Pembayaran Tagihan
                                @elseif($chargeModalAction === 'credit')
                                    Beri Pembebasan (Potongan) Tagihan
                                @else
                                    Batalkan Tagihan
                                @endif
                            </h2>
                            <p class="muted">Peminjam: <strong>{{ $selectedCharge->borrower?->name ?? 'User #'.$selectedCharge->borrower_id }}</strong> · Aset: <strong>{{ $selectedCharge->asset?->name ?? '-' }}</strong> · Total Tagihan: Rp{{ number_format($selectedCharge->amount, 0, ',', '.') }}</p>
                        </div>
                        <button type="button" class="oi-modal-close" wire:click="closeChargeModal" aria-label="Tutup popup">✕</button>
                    </div>

                    <div class="oi-modal-body">
                        @if($errors->any())
                            <div class="errors" role="alert">
                                @foreach($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="oi-report-detail-box">
                            <h3>Keterangan Tagihan</h3>
                            <p>{{ $selectedCharge->reason }}</p>
                            <p class="muted">Sisa tagihan saat ini: <strong>Rp{{ number_format($selectedCharge->balance, 0, ',', '.') }}</strong></p>
                        </div>

                        <form wire:submit="submitChargeModal" class="oi-modal-form">
                            @if($chargeModalAction === 'payment')
                                <div class="grid two">
                                    <label>Nominal Pembayaran (Rp)
                                        <input type="number" min="1" max="{{ max(1, (int) $selectedCharge->balance) }}" wire:model="amount" placeholder="Contoh: {{ $selectedCharge->balance }}" required>
                                    </label>
                                    <label>Metode Pembayaran
                                        <select wire:model.live="method">
                                            <option value="cash">Tunai (Cash)</option>
                                            <option value="bank_transfer">Transfer Bank</option>
                                            <option value="other">Lainnya</option>
                                        </select>
                                    </label>
                                </div>

                                <div class="grid two">
                                    <label>Referensi Transfer {{ $method === 'bank_transfer' && !$operator ? '(wajib jika tidak unggah bukti)' : '(opsional untuk tunai)' }}
                                        <input wire:model="reference" placeholder="No. referensi / rekening">
                                    </label>
                                    <label>Waktu Pembayaran Aktual (kosong = sekarang)
                                        <input type="datetime-local" wire:model="paidAt">
                                    </label>
                                </div>

                                <label>Bukti Pembayaran (JPG, PNG, atau PDF maks 2 MB)
                                    <input type="file" wire:model="evidence" accept="image/jpeg,image/png,application/pdf">
                                </label>

                                <label>Catatan Penerimaan / Pembayaran
                                    <textarea wire:model="paymentNotes" rows="2" placeholder="Contoh: Sudah transfer lunas / diterima tunai di sekretariat..."></textarea>
                                </label>
                            @elseif($chargeModalAction === 'credit')
                                <label>Nominal Pembebasan / Diskon (Rp)
                                    <input type="number" min="1" max="{{ max(1, (int) $selectedCharge->balance) }}" wire:model="amount" required>
                                </label>
                                <label>Alasan Pembebasan (minimal 3 karakter)
                                    <textarea wire:model="notes" rows="2" placeholder="Jelaskan alasan pengurangan/pembebasan biaya..." required></textarea>
                                </label>
                            @else
                                <label>Alasan Pembatalan Tagihan (minimal 3 karakter)
                                    <textarea wire:model="notes" rows="2" placeholder="Jelaskan alasan tagihan ini dibatalkan..." required></textarea>
                                </label>
                            @endif

                            <div class="oi-modal-actions">
                                <button type="submit" class="primary" wire:loading.attr="disabled" wire:target="submitChargeModal,evidence">
                                    @if($chargeModalAction === 'payment')
                                        {{ $operator ? 'Simpan Pembayaran' : 'Kirim Pembayaran' }}
                                    @elseif($chargeModalAction === 'credit')
                                        Simpan Pembebasan
                                    @else
                                        Konfirmasi Batalkan Tagihan
                                    @endif
                                </button>
                                <button type="button" class="secondary" wire:click="closeChargeModal">Tutup</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    @elseif($tab==='members')
        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <div>
                    <h2 class="panel-title">Anggota Organization</h2>
                    <p class="oi-panel-caption">Pengguna terverifikasi dapat langsung bergabung sebagai Member. Admin mengatur role dan akses anggota di sini.</p>
                </div>
                <span class="oi-tag oi-tag-blue">{{ $members->count() }} Anggota</span>
            </div>
            <div class="panel-body oi-panel-body" style="padding: 0;">
                <div class="oi-table-wrap">
                    <table class="oi-table">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Role & Status</th>
                                <th>Bergabung Pada</th>
                                <th>Tindakan Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                <tr wire:key="member-{{ $member->id }}">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <x-avatar :name="$member->user->name" size="sm" type="user" />
                                            <div>
                                                <div class="oi-table-title">{{ $member->user->name }}</div>
                                                <div class="oi-table-sub">{{ $member->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="oi-tag {{ $member->role === 'admin' ? 'oi-tag-red' : ($member->role === 'staff' ? 'oi-tag-blue' : 'oi-tag-green') }}">
                                            {{ strtoupper($member->role) }}
                                        </span>
                                        <span class="oi-tag {{ $member->status === 'active' ? 'oi-tag-green' : 'oi-tag-red' }}" style="margin-left: 0.25rem;">
                                            {{ $member->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="oi-table-sub">{{ $member->joined_at ? \Carbon\Carbon::parse($member->joined_at)->format('d M Y') : '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="oi-action-group">
                                            @foreach(['admin', 'staff', 'member', 'revoked'] as $role)
                                                @if($member->role !== $role)
                                                    <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px; text-transform: capitalize;"
                                                        wire:click="member({{ $member->id }},'{{ $role }}')" wire:loading.attr="disabled"
                                                        wire:confirm="Ubah keanggotaan {{ $member->user->name }} menjadi {{ $role }}?">
                                                        Jadikan {{ $role }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    @elseif($tab==='maintenance')
        @include('livewire.maintenance')

    @elseif($tab==='damage-reports')
        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <div>
                    <h2 class="panel-title">Daftar Laporan Kerusakan</h2>
                    <p class="oi-panel-caption">{{ $operator ? 'Tinjau laporan masuk dan riwayat tindak lanjut. Buka laporan untuk menilai kerusakan atau mengelola biaya.' : 'Lihat laporan yang kamu kirim dan status tindak lanjutnya.' }}</p>
                </div>
            </div>
            <div class="oi-filters" style="display: flex; flex-wrap: wrap; gap: 0.75rem; padding: 1.25rem 1.75rem; border-bottom: 1px solid #f1f5f9; background: #fafbfc;">
                <label>Status Laporan
                    <select class="oi-select" wire:model.live="reportStatus" style="margin-left: 0.375rem;">
                        <option value="">Semua status</option>
                        @foreach(['open' => 'Laporan Baru', 'noted' => 'Sudah Dicatat', 'in_maintenance' => 'Dalam Perbaikan', 'resolved' => 'Selesai'] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Tingkat Kerusakan
                    <select class="oi-select" wire:model.live="reportSeverity" style="margin-left: 0.375rem;">
                        <option value="">Semua tingkat</option>
                        <option value="minor">Ringan (Minor)</option>
                        <option value="major">Berat (Major)</option>
                    </select>
                </label>
                <label>Sumber Laporan
                    <select class="oi-select" wire:model.live="reportSource" style="margin-left: 0.375rem;">
                        <option value="">Semua sumber</option>
                        <option value="member_report">Laporan Member</option>
                        <option value="staff_report">Laporan Pengelola</option>
                        <option value="return_inspection">Inspeksi Pengembalian</option>
                    </select>
                </label>
            </div>

            <div class="oi-table-wrap">
                <table class="oi-table">
                    <thead>
                        <tr>
                            <th>ID & Aset</th>
                            <th>Pelapor / Peminjam</th>
                            <th>Ringkasan Kerusakan</th>
                            <th>Status</th>
                            <th>Biaya Kerusakan</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $r)
                            <tr wire:key="report-row-{{ $r->id }}" class="oi-report-row" wire:click="openReportModal({{ $r->id }})">
                                <td>
                                    <div class="oi-table-title">#{{ $r->id }} · {{ $r->asset?->name ?? 'Aset #'.$r->asset_id }}</div>
                                    <div class="oi-table-sub">{{ $r->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                                </td>
                                <td>
                                    <div class="oi-table-title">{{ $r->reporter?->name ?? 'User #'.$r->reported_by }}</div>
                                    <div class="oi-table-sub">{{ $sourceLabels[$r->source] ?? $r->source }}</div>
                                </td>
                                <td>
                                    <div class="oi-report-snippet">{{ $r->description }}</div>
                                    @if($r->severity)
                                        <span class="oi-tag {{ $r->severity === 'major' ? 'oi-tag-red' : 'oi-tag-amber' }}">Tingkat: {{ $r->severity === 'major' ? 'Berat' : 'Ringan' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="oi-tag {{ $r->status === 'open' ? 'oi-tag-red' : ($r->status === 'in_maintenance' ? 'oi-tag-amber' : 'oi-tag-green') }}">
                                        {{ $statusLabels[$r->status] ?? $r->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($r->charge)
                                        @php
                                            $reportSettlement = $r->charge->settlement;
                                            [$chargeBadgeLabel, $chargeBadgeClass] = match ($reportSettlement) {
                                                'paid' => ['Sudah Terbayar (Lunas)', 'oi-tag-green'],
                                                'waived' => ['Dibebaskan', 'oi-tag-green'],
                                                'partial' => ['Sebagian Dibayar', 'oi-tag-amber'],
                                                'unpaid' => ['Tagihan Aktif', 'oi-tag-blue'],
                                                default => ['Draft', 'oi-tag-amber'],
                                            };
                                        @endphp
                                        <div class="oi-table-title">Rp{{ number_format($r->charge->amount, 0, ',', '.') }}</div>
                                        <span class="oi-tag {{ $chargeBadgeClass }}">{{ $chargeBadgeLabel }}</span>
                                    @else
                                        <span class="muted">Belum ditetapkan</span>
                                    @endif
                                </td>
                                <td wire:click.stop>
                                    <button type="button" class="oi-btn oi-btn-sm" style="border-radius: 9999px;" wire:click="openReportModal({{ $r->id }})">
                                        {{ $operator && $r->status === 'open' && !$org->archived_at ? 'Tinjau Laporan' : 'Lihat Detail' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="oi-empty">
                                        <x-icon name="warning" :size="28" />
                                        <h3>Belum ada laporan kerusakan</h3>
                                        <p>Laporan kerusakan dari pengguna atau hasil inspeksi akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())<div class="oi-pagination" style="padding: 1rem 1.75rem;">{{ $reports->links() }}</div>@endif
        </section>

        @if($selectedReport)
            <div class="oi-modal-backdrop" wire:click.self="closeReportModal" role="dialog" aria-modal="true" aria-labelledby="oi-report-modal-title">
                <div class="oi-modal-card">
                    <div class="oi-modal-head">
                        <div>
                            <span class="oi-tag {{ $selectedReport->status === 'open' ? 'oi-tag-red' : ($selectedReport->status === 'in_maintenance' ? 'oi-tag-amber' : 'oi-tag-green') }}">
                                {{ $statusLabels[$selectedReport->status] ?? $selectedReport->status }}
                            </span>
                            <h2 id="oi-report-modal-title">Laporan Kerusakan #{{ $selectedReport->id }} · {{ $selectedReport->asset?->name }}</h2>
                            <p class="muted">Dilaporkan oleh <strong>{{ $selectedReport->reporter?->name }}</strong> pada {{ $selectedReport->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
                        </div>
                        <button type="button" class="oi-modal-close" wire:click="closeReportModal" aria-label="Tutup popup">✕</button>
                    </div>

                    <div class="oi-modal-body">
                        @if($errors->any())
                            <div class="errors" role="alert">
                                @foreach($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="oi-report-detail-box">
                            <h3>Deskripsi Kerusakan dari Pelapor</h3>
                            <p>{{ $selectedReport->description }}</p>
                            @if($selectedReport->borrowing)
                                <p class="muted">Terkait Peminjaman: <strong>#{{ $selectedReport->borrowing->id }}</strong> oleh {{ $selectedReport->borrowing->borrower?->name }} ({{ $selectedReport->borrowing->borrow_date->format('d M Y') }} – {{ $selectedReport->borrowing->due_date->format('d M Y') }})</p>
                            @endif
                            @if($selectedReport->photo)
                                <div class="oi-report-photo-wrap">
                                    <a href="{{ route('damage.photo', $selectedReport) }}" target="_blank" rel="noopener" class="secondary button">Lihat Foto Bukti Kerusakan ↗</a>
                                </div>
                            @endif
                        </div>

                        @if($selectedReport->assessment_notes)
                            <div class="oi-report-detail-box">
                                <h3>Catatan penilaian</h3>
                                <p>{{ $selectedReport->assessment_notes }}</p>
                            </div>
                        @endif
                        @if($selectedReport->status === 'resolved' && $selectedReport->resolution_notes)
                            <div class="oi-report-detail-box">
                                <h3>Catatan penyelesaian</h3>
                                <p>{{ $selectedReport->resolution_notes }}</p>
                            </div>
                        @endif

                        @php
                            $selectedChargeSettlement = $selectedReport->charge?->settlement;
                            $chargeLocked = $selectedReport->charge !== null && $selectedReport->charge->status === 'issued';
                            $chargeSettled = $chargeLocked && in_array($selectedChargeSettlement, ['paid', 'waived'], true);
                            $selectedChargeStatusText = match ($selectedChargeSettlement) {
                                'paid' => 'Sudah Terbayar (Lunas)',
                                'waived' => 'Dibebaskan (Waived)',
                                'partial' => 'Sebagian Dibayar · Sisa Rp'.number_format($selectedReport->charge->balance, 0, ',', '.'),
                                'unpaid' => 'Sudah Diterbitkan ke Peminjam',
                                default => 'Draft - Menunggu Penerbitan',
                            };
                        @endphp

                        @if($selectedReport->charge)
                            <div class="notice">
                                <strong>Biaya Kerusakan Terdaftar:</strong> Rp{{ number_format($selectedReport->charge->amount, 0, ',', '.') }}
                                ({{ $selectedChargeStatusText }})
                                <br><small>Keterangan: {{ $selectedReport->charge->reason }}</small>
                            </div>
                        @endif

                        @if($operator && !$org->archived_at)
                            <form wire:submit="saveReportDecision" class="oi-modal-form">
                                @if($selectedReport->status === 'open' || ($giveChargeOption && !$chargeLocked))
                                    <label>Tingkat Kerusakan (Severity)
                                        <select wire:model="severity">
                                            <option value="minor">Ringan (Minor)</option>
                                            <option value="major">Berat (Major)</option>
                                        </select>
                                    </label>
                                @endif
                                @if($selectedReport->status === 'open')
                                    <div class="grid two">
                                        <label class="oi-checkbox-card">
                                            <input type="checkbox" wire:model.live="startMaintenanceOption">
                                            <span>Masukkan aset ke status <strong>Maintenance (Perbaikan)</strong></span>
                                        </label>
                                        <label>Catatan Penilaian Admin / Pengelola
                                            <textarea wire:model="notes" rows="2" placeholder="Catatan pemeriksaan kondisi barang..."></textarea>
                                        </label>
                                    </div>
                                @endif

                                @if($chargeLocked)
                                    <div class="oi-report-detail-box">
                                        <h3>Status Tagihan Terkunci</h3>
                                        <p class="muted" style="margin: 0;">
                                            @if($chargeSettled)
                                                Tagihan untuk laporan ini <strong>sudah terbayar / dibebaskan</strong> dan tidak dapat diubah atau ditagihkan ulang.
                                            @else
                                                Tagihan untuk laporan ini <strong>sudah diterbitkan</strong> sehingga nominalnya terkunci. Kelola pembayaran atau pembebasan melalui menu <strong>Tagihan</strong>.
                                            @endif
                                        </p>
                                    </div>
                                @else
                                    <div class="oi-charge-toggle-box">
                                        <label class="oi-checkbox-card">
                                            <input type="checkbox" wire:model.live="giveChargeOption">
                                            <span><strong>Tetapkan Biaya Kerusakan / Ganti Rugi kepada Peminjam</strong></span>
                                        </label>

                                        @if($giveChargeOption)
                                            <div class="oi-charge-form-fields">
                                                <div class="grid two">
                                                    <label>Peminjaman Terkait
                                                        <select wire:model="borrowingId">
                                                            <option value="0">Otomatis kaitkan peminjaman aset ini</option>
                                                            @foreach($selectedReportBorrowings as $b)
                                                                <option value="{{ $b->id }}">#{{ $b->id }} · {{ $b->borrower?->name }} ({{ $b->borrow_date->format('d M Y') }})</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label>Jenis Tagihan
                                                        <select wire:model="chargeType">
                                                            <option value="damage">Biaya Kerusakan (Damage)</option>
                                                            <option value="loss">Ganti Rugi Kehilangan (Loss)</option>
                                                        </select>
                                                    </label>
                                                </div>

                                                <label>Nominal Biaya Kerusakan (Rp)
                                                    <input type="number" min="1" wire:model="amount" placeholder="Contoh: 150000" required>
                                                </label>

                                                <label>Rincian / Alasan Biaya Kerusakan (minimal 10 karakter)
                                                    <textarea wire:model="reason" rows="2" placeholder="Jelaskan rincian komponen yang rusak dan dasar perhitungan biaya..." required></textarea>
                                                </label>

                                                <label>Lampiran Bukti Tambahan (opsional jika foto laporan sudah ada, JPG/PNG/PDF maks 2 MB)
                                                    <input type="file" wire:model="evidence" accept="image/jpeg,image/png,application/pdf">
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="oi-modal-actions">
                                    @if($selectedReport->status === 'open' || ($giveChargeOption && !$chargeLocked))
                                        <button type="submit" class="primary" wire:loading.attr="disabled" wire:target="saveReportDecision,evidence">
                                            <span wire:loading.remove wire:target="saveReportDecision">
                                                {{ $giveChargeOption && !$chargeLocked ? 'Simpan & Tetapkan Biaya Kerusakan' : 'Simpan Penilaian Laporan' }}
                                            </span>
                                            <span wire:loading wire:target="saveReportDecision">Menyimpan...</span>
                                        </button>
                                    @endif
                                    @if($selectedReport->status === 'in_maintenance')
                                        <button type="button" class="secondary" wire:click="openMaintenanceModal({{ $selectedReport->id }})" wire:loading.attr="disabled">Buka Perbaikan</button>
                                    @endif
                                    <button type="button" class="secondary" wire:click="closeReportModal">Tutup</button>
                                </div>
                            </form>
                        @else
                            <div class="oi-modal-actions">
                                <button type="button" class="secondary" wire:click="closeReportModal">Tutup</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    @elseif($tab==='settings')
        <div style="display: flex; flex-direction: column; gap: 1.75rem;">
            <!-- PARAMETER OPERASIONAL ORGANIZATION -->
            <section style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">Parameter Operasional Organization</h2>
                    <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0;">Perubahan parameter langsung berlaku untuk seluruh transaksi baru.</p>
                </div>
                <div>
                    <form wire:submit="settings" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <label>
                            <span style="font-weight: 700; font-size: 0.84rem; color: #0f172a; display: block; margin-bottom: 0.4rem;">Nama Organization</span>
                            <input class="oi-input" wire:model="settingName" style="max-width: 460px; width: 100%; border-radius: 0.625rem; border: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;" required>
                        </label>
                        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                            <label style="flex: 0 1 180px;">
                                <span style="font-weight: 700; font-size: 0.84rem; color: #0f172a; display: block; margin-bottom: 0.4rem;">Batas Maksimal Pinjam (Hari)</span>
                                <input type="number" class="oi-input" wire:model="maxDays" min="1" style="width: 140px; border-radius: 0.625rem; border: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;" required>
                            </label>
                            <label style="flex: 0 1 240px;">
                                <span style="font-weight: 700; font-size: 0.84rem; color: #0f172a; display: block; margin-bottom: 0.4rem;">Tarif Denda Telat per Hari (Rp)</span>
                                <input type="number" class="oi-input" wire:model="dailyFine" min="0" style="width: 200px; border-radius: 0.625rem; border: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;" required>
                            </label>
                        </div>
                        <label>
                            <span style="font-weight: 700; font-size: 0.84rem; color: #0f172a; display: block; margin-bottom: 0.4rem;">Rekening Bank untuk Pembayaran Denda Manual</span>
                            <input class="oi-input" placeholder="BCA 827-019-2819 a.n Media Kreatif Lab" style="max-width: 460px; width: 100%; border-radius: 0.625rem; border: 1px solid #e2e8f0; padding: 0.65rem 0.85rem;">
                        </label>
                        <div style="display: flex; justify-content: flex-start; margin-top: 0.5rem;">
                            <button type="submit" class="pill-btn btn-solid-black" style="border-radius: 9999px; padding: 0.65rem 1.75rem; font-size: 0.84rem; font-weight: 700; cursor: pointer; border: none;">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- ZONA BAHAYA ADMIN (SESUAI FOTO 1 & 2) -->
            <section style="background: #ffffff; border: 1px solid #fecdd3; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <div style="margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">Zona Bahaya Admin</h2>
                    <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0;">Tindakan berikut bersifat permanen dan memerlukan otorisasi penuh pemilik Organization.</p>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button type="button" class="pill-btn btn-secondary-light" style="padding: 0.55rem 1.25rem; font-size: 0.8125rem; font-weight: 700;" wire:click="archive" wire:confirm="Ubah status arsip Organization?">
                        {{ $org->archived_at ? 'Buka Kembali Organization' : 'Arsipkan Organization' }}
                    </button>
                    <button type="button" class="pill-btn" style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; padding: 0.55rem 1.25rem; font-size: 0.8125rem; font-weight: 700; border-radius: 9999px; cursor: pointer;" wire:click="deleteOrganization" wire:confirm="Hapus Organization secara soft delete? Histori akan dipertahankan.">
                        Hapus Organization
                    </button>
                </div>
            </section>
        </div>

    @elseif($tab==='audit')
        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <div>
                    <h2 class="panel-title">Audit Aktivitas & Ekspor Laporan</h2>
                    <p class="oi-panel-caption">Rekaman audit kepatuhan 100% tak terhapus dan fasilitas ekspor data tabular untuk kebutuhan LPJ.</p>
                </div>
                <a class="oi-btn oi-btn-primary" style="border-radius: 9999px;" href="{{ route('reports', $org) }}">Laporan dengan filter periode →</a>
            </div>
            <div class="panel-body oi-panel-body" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 1.25rem;">
                <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.75rem;">Unduh Data CSV:</span>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach(['assets' => 'Aset', 'borrowings' => 'Peminjaman', 'damage' => 'Kerusakan', 'charges' => 'Tagihan', 'payments' => 'Pembayaran', 'credits' => 'Pembebasan'] as $type => $label)
                        <a class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" href="{{ route('export', [$org, $type]) }}">CSV {{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div class="oi-table-wrap">
                <table class="oi-table">
                    <thead>
                        <tr>
                            <th>Waktu (WIB)</th>
                            <th>Aksi</th>
                            <th>Objek</th>
                            <th>Detail ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $a)
                            <tr wire:key="audit-{{ $a->id }}">
                                <td style="white-space: nowrap;">
                                    <div class="oi-table-sub">{{ $a->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</div>
                                </td>
                                <td>
                                    <span class="oi-tag oi-tag-blue" style="font-weight: 700;">{{ $a->action }}</span>
                                </td>
                                <td>
                                    <div class="oi-table-title">{{ class_basename($a->subject_type) }}</div>
                                </td>
                                <td>
                                    <span class="oi-table-sub">#{{ $a->subject_id }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="oi-empty">
                                        <x-icon name="clock" :size="28" />
                                        <h3>Belum ada rekaman audit</h3>
                                        <p>Semua perubahan data penting akan tercatat di sini secara permanen.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
    </x-room.frame>
</div>
