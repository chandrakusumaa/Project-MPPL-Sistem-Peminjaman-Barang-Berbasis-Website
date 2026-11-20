<div wire:poll.30s>
    <x-room.frame :org="$org" :operator="$operator" :admin="$admin" :tab="$tab"
        :show-feedback="!$selectedMaintenance && !$selectedBorrowing && !($tab === 'inventory' && (($canBorrow && $borrowAssetId) || ($operator && ($assetFormOpen || $categoryFormOpen))))">
    @if($org->archived_at)<div class="notice">Organization diarsipkan. Data hanya dapat dibaca.</div>@endif
    @php
        $titles = ['dashboard' => $org->name, 'inventory' => 'Inventaris', 'borrowings' => 'Peminjaman', 'charges' => 'Riwayat Tagihan & Pembayaran', 'members' => 'Anggota', 'audit' => 'Audit Aktivitas', 'damage-reports' => 'Laporan Kerusakan', 'maintenance' => 'Maintenance', 'settings' => 'Pengaturan Organization'];
        $statusLabels = ['open' => 'Laporan Baru', 'noted' => 'Sudah Dicatat', 'in_maintenance' => 'Dalam Perbaikan', 'resolved' => 'Selesai'];
        $sourceLabels = ['member_report' => 'Laporan Member', 'staff_report' => 'Laporan Pengelola', 'return_inspection' => 'Inspeksi Pengembalian'];
    @endphp
    <div class="oi-page-head">
        <div><h1>{{ $titles[$tab] }}</h1><p>{{ $tab === 'dashboard' ? 'Peran berlaku pada Organization ini. Kembali ke dashboard pribadi tidak mengubah role.' : $org->name }}</p></div>
        <div class="oi-head-actions">
            <select class="oi-select" aria-label="Organization aktif" wire:change="switchWorkspace($event.target.value)">
                @foreach($workspaces as $workspace)<option value="{{ $workspace->slug }}" @selected($workspace->id === $org->id)>{{ $workspace->name }}</option>@endforeach
            </select>
            @if($operator && $tab === 'inventory')
                <button type="button" id="oi-add-category" class="oi-btn oi-btn-secondary" wire:click="openCategoryForm"
                    wire:loading.attr="disabled" aria-haspopup="dialog">+ Tambah kategori aset</button>
            @endif
            @if($operator && in_array($tab, ['dashboard', 'inventory']))
                <button type="button" id="oi-add-asset" class="oi-btn" wire:click="openAssetForm"
                    wire:loading.attr="disabled" aria-haspopup="dialog">+ Tambah aset</button>
            @endif
        </div>
    </div>
    <div wire:loading.delay role="status" class="muted">Memproses...</div>
    @if($tab==='dashboard')
    @include('livewire.room-dashboard')
    @elseif($tab==='inventory')
    <div class="page-heading"><h2>Katalog aset</h2><select wire:model.live="assetSort"><option value="name">Nama A-Z</option><option value="newest">Terbaru</option></select><select wire:model.live="filterCategory"><option value="0">Semua kategori</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select><select wire:model.live="filterStatus"><option value="">Semua status aset</option>@foreach(['available','borrowed','maintenance','lost'] as $s)<option>{{ $s }}</option>@endforeach</select><input placeholder="Cari aset..." wire:model.live.debounce.300ms="search"></div>
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
    @if($focusedAsset && $assets->first())<section class="card"><h2>{{ $assets->first()->name }}</h2><p>{{ $assets->first()->description }}</p><p class="muted">{{ $assets->first()->specifications }}</p><p>Lokasi: {{ $assets->first()->location }}</p></section>@endif
    @if($reportAsset)
    @include('livewire.damage-report-form')
    @endif
    <div class="table-wrap card"><table><thead><tr><th>Aset / Kode</th><th>Kategori</th><th>Lokasi</th><th>Status / Kondisi</th><th>Tindakan</th></tr></thead><tbody>@forelse($assets as $a)<tr wire:key="asset-{{ $a->id }}"><td>@if($a->photo)<img src="{{ asset('storage/'.$a->photo) }}" alt="{{ $a->name }}" width="64" height="64" loading="lazy">@endif<strong><a href="{{ route('asset.show',[$org,$a]) }}">{{ $a->name }}</a></strong><small>{{ $a->code }}</small></td><td>{{ $a->category->name }}</td><td>{{ $a->location }}</td><td><span class="badge">{{ $a->status }}</span><small>{{ $a->physical_condition }}</small></td><td><div class="oi-action-group">@if($canBorrow && $a->status === 'available' && !$org->archived_at)<button type="button" id="oi-borrow-asset-{{ $a->id }}" class="primary" wire:click="openBorrowForm({{ $a->id }})" wire:loading.attr="disabled" aria-haspopup="dialog">Ajukan Pinjam</button>@endif<button type="button" id="oi-report-button-{{ $a->id }}" class="secondary" wire:click="openDamageReport({{ $a->id }})" wire:loading.attr="disabled" aria-expanded="{{ $reportAssetId === $a->id ? 'true' : 'false' }}" aria-controls="oi-damage-report-form">Laporkan Kerusakan</button><a class="secondary button" href="{{ route('asset.timeline',[$org,$a]) }}">Riwayat</a>@if($operator)<button type="button" id="oi-edit-asset-{{ $a->id }}" class="secondary" wire:click="editAsset({{ $a->id }})" aria-haspopup="dialog">Edit</button><button class="secondary" wire:click="deleteAsset({{ $a->id }})" wire:confirm="Hapus aset ini secara soft delete?">Hapus</button>@if($a->status==='lost')<button class="secondary" wire:click="found({{ $a->id }})">Tandai ditemukan</button>@endif @endif</div></td></tr>@empty<tr><td colspan="5" class="empty">Belum ada aset yang sesuai.</td></tr>@endforelse</tbody></table></div>{{ $assets->links() }}
    @elseif($tab==='borrowings')
        @include('livewire.borrowing-list')
    @elseif($tab==='charges')
    <section class="oi-panel">
        <div class="oi-panel-head">
            <div>
                <h2>Daftar Tagihan & Pembayaran</h2>
                <p class="oi-panel-caption">Untuk menetapkan biaya kerusakan baru, buka menu <strong>Laporan Kerusakan</strong> lalu klik salah satu laporan.</p>
            </div>
            @if($operator)<button type="button" class="oi-btn oi-btn-secondary oi-btn-sm" wire:click="showDamageReports">Buka Laporan Kerusakan →</button>@endif
        </div>
        <div class="oi-filters">
            <label>Status tagihan<select class="oi-select" wire:model.live="chargeStatus"><option value="">Semua status</option><option value="draft">Draft</option><option value="issued">Diterbitkan (Aktif)</option><option value="cancelled">Dibatalkan</option></select></label>
        </div>
    </section>

    @forelse($charges as $c)
        <section class="card" wire:key="charge-{{ $c->id }}">
            <div class="page-heading">
                <div>
                    <h2>Tagihan #{{ $c->id }} · {{ $c->charge_type === 'damage' ? 'Biaya Kerusakan' : ($c->charge_type === 'loss' ? 'Penggantian Kehilangan' : 'Denda Keterlambatan') }}</h2>
                    <small>Aset: {{ $c->asset?->name ?? '-' }}</small>
                </div>
                <span class="badge">{{ $c->status }} / {{ $c->settlement }}</span>
            </div>
            <p>{{ $c->reason }}</p>
            <p class="muted">Dasar kebijakan: {{ $c->policy_basis }}</p>
            @if($c->borrower_id===auth()->id() && $c->status==='draft' && !$c->liability_acknowledged_at)
                <button class="secondary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'acknowledge')" wire:confirm="Saya menyetujui dasar tanggung jawab dan kebijakan pada tagihan ini.">Setujui dasar tanggung jawab</button>
            @endif
            @if($c->evidence_path)
                <a class="secondary button" href="{{ route('evidence',$c) }}">Unduh bukti kerusakan</a>
            @endif
            <div class="grid three">
                <p>Nominal <strong>Rp{{ number_format($c->amount,0,',','.') }}</strong></p>
                <p>Pembebasan <strong>Rp{{ number_format($c->credits->sum('amount'),0,',','.') }}</strong></p>
                <p>Sisa <strong>Rp{{ number_format($c->balance,0,',','.') }}</strong></p>
            </div>
            @if($operator)
                <div class="oi-action-group">
                    @if($c->status==='draft' && $admin)
                        <button class="primary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'issue')" wire:confirm="Terbitkan tagihan ini?">Terbitkan Tagihan</button>
                    @endif
                    @if($c->status==='issued' && $c->balance > 0)
                        <button type="button" class="primary" wire:loading.attr="disabled" wire:click="openChargeModal({{ $c->id }}, 'payment')">Catat Pembayaran</button>
                        @if($admin)
                            <button type="button" class="secondary" wire:loading.attr="disabled" wire:click="openChargeModal({{ $c->id }}, 'credit')">Beri Pembebasan</button>
                        @endif
                    @endif
                    @if($admin && $c->status!=='cancelled' && $c->payments->where('status', 'confirmed')->isEmpty() && $c->credits->isEmpty())
                        <button type="button" class="secondary" wire:loading.attr="disabled" wire:click="openChargeModal({{ $c->id }}, 'cancel')">Batalkan Tagihan</button>
                    @endif
                </div>
            @endif
            @foreach($c->payments as $p)
                <div class="list-row">
                    <span>Pembayaran #{{ $p->id }} · Rp{{ number_format($p->amount,0,',','.') }} · {{ $p->method === 'bank_transfer' ? 'Transfer Bank' : ($p->method === 'cash' ? 'Tunai' : 'Lainnya') }} · <span class="badge">{{ $p->status }}</span></span>
                    @if($admin && $p->status==='pending')
                        <div class="oi-action-group">
                            <button class="primary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'confirm',{{ $p->id }})">Konfirmasi Lunas</button>
                            <button class="secondary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'reject',{{ $p->id }})">Tolak</button>
                        </div>
                    @elseif($admin && $p->status==='confirmed')
                        <button class="secondary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'void',{{ $p->id }})" wire:confirm="Void pembayaran ini tanpa menghapus histori?">Void</button>
                    @endif
                </div>
            @endforeach
        </section>
    @empty
        <div class="card empty">Belum ada tagihan.</div>
    @endforelse
    {{ $charges->links() }}

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
    <section class="card"><h2>Anggota Organization</h2><p class="muted">Pengguna terverifikasi dapat langsung bergabung sebagai Member. Admin mengatur role dan akses anggota di sini.</p>
    @foreach($members as $member)
    <div class="list-row" wire:key="member-{{ $member->id }}"><span>{{ $member->user->name }}</span><span class="badge">{{ $member->role }} / {{ $member->status }}</span><div>
    @foreach(['admin','staff','member','revoked'] as $role)
    <button class="secondary" wire:click="member({{ $member->id }},'{{ $role }}')" wire:loading.attr="disabled" wire:confirm="Ubah keanggotaan menjadi {{ $role }}?">{{ $role }}</button>
    @endforeach
    </div></div>
    @endforeach
    </section>
    @elseif($tab==='maintenance')
        @include('livewire.maintenance')
    @elseif($tab==='damage-reports')
    <section class="oi-panel">
        <div class="oi-panel-head">
            <div>
                <h2>Daftar Laporan Kerusakan</h2>
                <p class="oi-panel-caption">{{ $operator ? 'Tinjau laporan masuk dan riwayat tindak lanjut. Buka laporan untuk menilai kerusakan atau mengelola biaya.' : 'Lihat laporan yang kamu kirim dan status tindak lanjutnya.' }}</p>
            </div>
        </div>
        <div class="oi-filters">
            <label>Status Laporan
                <select class="oi-select" wire:model.live="reportStatus">
                    <option value="">Semua status</option>
                    @foreach(['open' => 'Laporan Baru', 'noted' => 'Sudah Dicatat', 'in_maintenance' => 'Dalam Perbaikan', 'resolved' => 'Selesai'] as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Tingkat Kerusakan
                <select class="oi-select" wire:model.live="reportSeverity">
                    <option value="">Semua tingkat</option>
                    <option value="minor">Ringan (Minor)</option>
                    <option value="major">Berat (Major)</option>
                </select>
            </label>
            <label>Sumber Laporan
                <select class="oi-select" wire:model.live="reportSource">
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
                                <button type="button" class="oi-btn oi-btn-sm" wire:click="openReportModal({{ $r->id }})">
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
        @if($reports->hasPages())<div class="oi-pagination">{{ $reports->links() }}</div>@endif
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
    @elseif($tab==='settings')<section class="card"><h2>Pengaturan Organization</h2><form wire:submit="settings"><label>Nama Organization<input wire:model="settingName"></label><label>Maksimum hari pinjam<input type="number" wire:model="maxDays" min="1"></label><label>Denda per hari (Rp)<input type="number" wire:model="dailyFine" min="0"></label><label>Kebijakan kerusakan<textarea wire:model="settingPolicy"></textarea></label><button class="primary">Simpan pengaturan</button></form><label>Konfirmasi nama Organization untuk hapus<input wire:model="deleteConfirmation"></label><button class="secondary" wire:click="deleteOrganization" wire:confirm="Hapus Organization secara soft delete? Histori akan dipertahankan.">Hapus Organization</button><button class="secondary" wire:click="archive" wire:confirm="Ubah status arsip Organization?">{{ $org->archived_at?'Buka kembali Organization':'Arsipkan Organization' }}</button></section>
    @elseif($tab==='audit')<section class="card"><h2>Audit aktivitas & Laporan</h2><a class="primary button" href="{{ route('reports',$org) }}">Laporan dengan filter periode</a><p>@foreach(['assets','borrowings','damage','charges','payments','credits'] as $type)<a class="secondary button" href="{{ route('export',[$org,$type]) }}">CSV {{ $type }}</a>@endforeach</p>@foreach($audits as $a)<div class="list-row"><span>{{ $a->action }} · {{ $a->subject_type }} #{{ $a->subject_id }}</span><small>{{ $a->created_at }}</small></div>@endforeach</section>@endif
    </x-room.frame>
</div>
