<div wire:poll.30s>
    <x-room.frame :org="$org" :operator="$operator" :admin="$admin" :tab="$tab"
        :show-feedback="!$selectedMaintenance && !$selectedBorrowing && !($tab === 'inventory' && (($canBorrow && $borrowAssetId) || ($operator && ($assetFormOpen || $categoryFormOpen))))">
    @if($org->archived_at)<div class="notice">Komunitas diarsipkan. Data hanya dapat dibaca.</div>@endif
    @php
        $titles = ['dashboard' => $org->name, 'inventory' => 'Inventaris', 'borrowings' => 'Peminjaman', 'charges' => 'Riwayat Tagihan & Pembayaran', 'members' => 'Anggota Komunitas', 'audit' => 'Audit Aktivitas', 'damage-reports' => 'Laporan Kerusakan', 'maintenance' => 'Maintenance', 'settings' => 'Pengaturan Komunitas'];
        $statusLabels = ['open' => 'Laporan Baru', 'noted' => 'Sudah Dicatat', 'in_maintenance' => 'Dalam Perbaikan', 'resolved' => 'Selesai'];
        $sourceLabels = ['member_report' => 'Laporan Member', 'staff_report' => 'Laporan Pengelola', 'return_inspection' => 'Inspeksi Pengembalian'];
    @endphp
    <div class="workspace-header-card oi-page-head">
        <div class="org-identity">
            <x-avatar :url="$org->logo" :name="$org->name" size="lg" type="community" />
            <div class="org-title-group">
                <h1 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0; line-height: 1.2;">{{ $titles[$tab] }}</h1>
                <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0;">{{ $tab === 'dashboard' ? 'Peran berlaku pada Komunitas ini. Kembali ke dashboard pribadi tidak mengubah role.' : $org->name }}</p>
            </div>
        </div>
        <div class="oi-head-actions" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <select class="oi-select" style="border-radius: 9999px; padding: 0.5rem 1rem;" aria-label="Komunitas aktif" wire:change="switchWorkspace($event.target.value)">
                @foreach($workspaces as $workspace)<option value="{{ $workspace->slug }}" @selected($workspace->id === $org->id)>{{ $workspace->name }}</option>@endforeach
            </select>
            @if($operator && $tab === 'inventory')
                <button type="button" id="oi-add-category" class="oi-btn oi-btn-secondary" style="border-radius: 9999px;" wire:click="openCategoryForm"
                    wire:loading.attr="disabled" aria-haspopup="dialog">+ Tambah kategori aset</button>
            @endif
            @if($operator && in_array($tab, ['dashboard', 'inventory']))
                <button type="button" id="oi-add-asset" class="oi-btn oi-btn-primary" style="border-radius: 9999px;" wire:click="openAssetForm"
                    wire:loading.attr="disabled" aria-haspopup="dialog">+ Tambah aset</button>
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

        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <div>
                    <h2 class="panel-title">Katalog Inventaris</h2>
                    <p class="oi-panel-caption">Daftar unit fisik barang yang terdaftar di Komunitas ini. Setiap unit memiliki kode fisik unik.</p>
                </div>
            </div>
            <div class="oi-filters" style="display: flex; flex-wrap: wrap; gap: 0.75rem; padding: 1.25rem 1.75rem; border-bottom: 1px solid #f1f5f9; background: #fafbfc;">
                <div style="flex: 1; min-width: 200px;">
                    <input class="oi-input" placeholder="Cari nama atau kode aset..." wire:model.live.debounce.300ms="search" style="width: 100%;">
                </div>
                <select class="oi-select" wire:model.live="filterCategory">
                    <option value="0">Semua kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select class="oi-select" wire:model.live="filterStatus">
                    <option value="">Semua status aset</option>
                    @foreach(['available' => 'Tersedia', 'borrowed' => 'Dipinjam', 'maintenance' => 'Maintenance', 'lost' => 'Hilang'] as $s => $label)
                        <option value="{{ $s }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select class="oi-select" wire:model.live="assetSort">
                    <option value="name">Urut: Nama A-Z</option>
                    <option value="newest">Urut: Terbaru</option>
                </select>
            </div>

            <div class="oi-table-wrap">
                <table class="oi-table">
                    <thead>
                        <tr>
                            <th>Aset / Kode</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Status / Kondisi</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $a)
                            <tr wire:key="asset-{{ $a->id }}">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.875rem;">
                                        @if($a->photo)
                                            <img src="{{ asset('storage/'.$a->photo) }}" alt="{{ $a->name }}" style="width: 48px; height: 48px; border-radius: 0.625rem; object-fit: cover; border: 1px solid #e2e8f0;" loading="lazy">
                                        @else
                                            <div style="width: 48px; height: 48px; border-radius: 0.625rem; background: #f1f5f9; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                                <x-icon name="box" :size="22" />
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('asset.show', [$org, $a]) }}" class="oi-table-title">{{ $a->name }}</a>
                                            <div class="oi-table-sub" style="font-family: monospace;">{{ $a->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="oi-tag oi-tag-neutral">{{ $a->category->name }}</span>
                                </td>
                                <td>
                                    <div class="oi-table-sub">{{ $a->location }}</div>
                                </td>
                                <td>
                                    <x-room.status :value="$a->status" />
                                    <div class="oi-table-sub" style="margin-top: 0.25rem;">{{ $a->physical_condition }}</div>
                                </td>
                                <td>
                                    <div class="oi-action-group">
                                        @if($canBorrow && $a->status === 'available' && !$org->archived_at)
                                            <button type="button" id="oi-borrow-asset-{{ $a->id }}" class="oi-btn oi-btn-primary oi-btn-sm" style="border-radius: 9999px;" wire:click="openBorrowForm({{ $a->id }})" wire:loading.attr="disabled" aria-haspopup="dialog">Ajukan Pinjam</button>
                                        @endif
                                        <button type="button" id="oi-report-button-{{ $a->id }}" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:click="openDamageReport({{ $a->id }})" wire:loading.attr="disabled" aria-expanded="{{ $reportAssetId === $a->id ? 'true' : 'false' }}" aria-controls="oi-damage-report-form">Laporkan Kerusakan</button>
                                        <a class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" href="{{ route('asset.timeline', [$org, $a]) }}">Riwayat</a>
                                        @if($operator)
                                            <button type="button" id="oi-edit-asset-{{ $a->id }}" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:click="editAsset({{ $a->id }})" aria-haspopup="dialog">Edit</button>
                                            <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:click="deleteAsset({{ $a->id }})" wire:confirm="Hapus aset ini secara soft delete?">Hapus</button>
                                            @if($a->status === 'lost')
                                                <button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" wire:click="found({{ $a->id }})">Tandai ditemukan</button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="oi-empty">
                                        <x-icon name="box" :size="28" />
                                        <h3>Belum ada aset yang sesuai</h3>
                                        <p>Coba sesuaikan kata kunci pencarian atau filter kategori.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assets->hasPages())
                <div class="oi-pagination" style="padding: 1rem 1.75rem;">
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
                                <p class="muted" style="margin: 0.25rem 0 0 0;">Aset: <strong>{{ $c->asset?->name ?? '-' }}</strong></p>
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
                            <p class="muted">Aset: <strong>{{ $selectedCharge->asset?->name ?? '-' }}</strong> · Total Tagihan: Rp{{ number_format($selectedCharge->amount, 0, ',', '.') }}</p>
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
                                    <label>Referensi Transfer (opsional untuk tunai)
                                        <input wire:model="reference" placeholder="No. referensi / rekening">
                                    </label>
                                    <label>Waktu Pembayaran Aktual (kosong = sekarang)
                                        <input type="datetime-local" wire:model="paidAt">
                                    </label>
                                </div>

                                <label>Catatan Penerimaan
                                    <textarea wire:model="paymentNotes" rows="2" placeholder="Contoh: Diterima tunai oleh admin di sekretariat..."></textarea>
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
                                <button type="submit" class="primary" wire:loading.attr="disabled">
                                    @if($chargeModalAction === 'payment')
                                        Simpan Pembayaran
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
                    <h2 class="panel-title">Anggota Komunitas</h2>
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
                                        <div class="oi-table-title">Rp{{ number_format($r->charge->amount, 0, ',', '.') }}</div>
                                        <span class="oi-tag {{ $r->charge->status === 'issued' ? 'oi-tag-blue' : 'oi-tag-amber' }}">{{ $r->charge->status === 'issued' ? 'Tagihan Aktif' : 'Draft' }}</span>
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

                        @if($selectedReport->charge)
                            <div class="notice">
                                <strong>Biaya Kerusakan Terdaftar:</strong> Rp{{ number_format($selectedReport->charge->amount, 0, ',', '.') }}
                                ({{ $selectedReport->charge->status === 'issued' ? 'Sudah Diterbitkan ke Peminjam' : 'Draft - Menunggu Penerbitan' }})
                                <br><small>Keterangan: {{ $selectedReport->charge->reason }}</small>
                            </div>
                        @endif

                        @if($operator && !$org->archived_at)
                            <form wire:submit="saveReportDecision" class="oi-modal-form">
                                @if($selectedReport->status === 'open' || $giveChargeOption)
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

                                <div class="oi-modal-actions">
                                    @if($selectedReport->status === 'open' || $giveChargeOption)
                                        <button type="submit" class="primary" wire:loading.attr="disabled" wire:target="saveReportDecision,evidence">
                                            <span wire:loading.remove wire:target="saveReportDecision">
                                                {{ $giveChargeOption ? 'Simpan & Tetapkan Biaya Kerusakan' : 'Simpan Penilaian Laporan' }}
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
            <section class="panel-card oi-panel">
                <div class="panel-header oi-panel-head">
                    <div>
                        <h2 class="panel-title">Pengaturan Kebijakan & Komunitas</h2>
                        <p class="oi-panel-caption">Atur nama komunitas, batas durasi peminjaman, tarif denda keterlambatan, dan kebijakan ganti rugi fisik.</p>
                    </div>
                </div>
                <div class="panel-body oi-panel-body">
                    <form wire:submit="settings" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <label>
                            <span style="font-weight: 700; font-size: 0.875rem;">Nama Komunitas</span>
                            <input class="oi-input" wire:model="settingName" style="width: 100%; margin-top: 0.375rem;" required>
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
                            <label>
                                <span style="font-weight: 700; font-size: 0.875rem;">Maksimum Hari Pinjam</span>
                                <input type="number" class="oi-input" wire:model="maxDays" min="1" style="width: 100%; margin-top: 0.375rem;" required>
                            </label>
                            <label>
                                <span style="font-weight: 700; font-size: 0.875rem;">Denda Keterlambatan per Hari (Rp)</span>
                                <input type="number" class="oi-input" wire:model="dailyFine" min="0" style="width: 100%; margin-top: 0.375rem;" required>
                            </label>
                        </div>
                        <label>
                            <span style="font-weight: 700; font-size: 0.875rem;">Kebijakan Ganti Rugi Kerusakan / Kehilangan</span>
                            <textarea class="oi-input" wire:model="settingPolicy" rows="4" style="width: 100%; margin-top: 0.375rem;" placeholder="Jelaskan aturan pertanggungjawaban anggota jika barang rusak atau hilang..."></textarea>
                        </label>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" class="oi-btn oi-btn-primary" style="border-radius: 9999px; padding: 0.75rem 2rem;">Simpan Pengaturan</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="panel-card oi-panel" style="border-left: 4px solid #ef4444;">
                <div class="panel-header oi-panel-head">
                    <div>
                        <h2 class="panel-title" style="color: #ef4444;">Zona Berbahaya (Danger Zone)</h2>
                        <p class="oi-panel-caption">Arsipkan komunitas atau hapus secara soft delete. Riwayat transaksi dan audit tetap tersimpan.</p>
                    </div>
                </div>
                <div class="panel-body oi-panel-body" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <h3 style="font-size: 0.9375rem; font-weight: 700; margin: 0;">Status Arsip Komunitas</h3>
                            <p style="font-size: 0.8125rem; color: #64748b; margin: 0.25rem 0 0 0;">{{ $org->archived_at ? 'Komunitas ini sedang diarsipkan. Pengajuan baru dinonaktifkan.' : 'Mengarsipkan komunitas akan menghentikan transaksi baru tanpa menghapus data.' }}</p>
                        </div>
                        <button type="button" class="oi-btn oi-btn-secondary" style="border-radius: 9999px;" wire:click="archive" wire:confirm="Ubah status arsip Komunitas?">
                            {{ $org->archived_at ? 'Buka Kembali Komunitas' : 'Arsipkan Komunitas' }}
                        </button>
                    </div>

                    <div>
                        <h3 style="font-size: 0.9375rem; font-weight: 700; color: #ef4444; margin: 0 0 0.5rem 0;">Hapus Komunitas (Soft Delete)</h3>
                        <p style="font-size: 0.8125rem; color: #64748b; margin: 0 0 1rem 0;">Ketik nama komunitas <strong>{{ $org->name }}</strong> untuk mengonfirmasi penghapusan.</p>
                        <div style="display: flex; gap: 0.75rem; max-width: 480px;">
                            <input class="oi-input" wire:model="deleteConfirmation" placeholder="Ketik nama komunitas di sini" style="flex: 1;">
                            <button type="button" class="oi-btn" style="background-color: #ef4444; color: #fff; border-radius: 9999px; white-space: nowrap;" wire:click="deleteOrganization" wire:confirm="Hapus Komunitas secara soft delete? Histori akan dipertahankan.">Hapus Komunitas</button>
                        </div>
                    </div>
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
