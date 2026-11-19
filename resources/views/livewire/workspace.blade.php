<div wire:poll.30s>
    <x-room.frame :org="$org" :operator="$operator" :admin="$admin" :tab="$tab" :report-status="$reportStatus">
    @if($org->archived_at)<div class="notice">Organization diarsipkan. Data hanya dapat dibaca.</div>@endif
    @php
        $titles = ['dashboard' => $org->name, 'inventory' => 'Inventaris', 'borrowings' => 'Peminjaman', 'charges' => 'Riwayat Tagihan & Pembayaran', 'members' => 'Anggota', 'audit' => 'Audit Aktivitas', 'maintenance' => $reportStatus === 'in_maintenance' ? 'Maintenance' : 'Laporan Kerusakan', 'settings' => 'Pengaturan Organization'];
        $statusLabels = ['open' => 'Laporan Baru', 'noted' => 'Sudah Dicatat', 'in_maintenance' => 'Dalam Perbaikan', 'resolved' => 'Selesai'];
        $sourceLabels = ['member_report' => 'Laporan Member', 'staff_report' => 'Laporan Pengelola', 'return_inspection' => 'Inspeksi Pengembalian'];
    @endphp
    <div class="oi-page-head">
        <div><h1>{{ $titles[$tab] }}</h1><p>{{ $tab === 'dashboard' ? 'Peran berlaku pada Organization ini. Kembali ke dashboard pribadi tidak mengubah role.' : $org->name }}</p></div>
        <div class="oi-head-actions">
            <select class="oi-select" aria-label="Organization aktif" wire:change="switchWorkspace($event.target.value)">
                @foreach($workspaces as $workspace)<option value="{{ $workspace->slug }}" @selected($workspace->id === $org->id)>{{ $workspace->name }}</option>@endforeach
            </select>
            @if($operator && in_array($tab, ['dashboard', 'inventory']))<button class="oi-btn" wire:click="openAssetForm">+ Tambah aset</button>@endif
        </div>
    </div>
    <div wire:loading.delay role="status" class="muted">Memproses...</div>
    @if($tab==='dashboard')
    @include('livewire.room-dashboard')
    @elseif($tab==='inventory')
    <div class="page-heading"><h2>Katalog aset</h2><select wire:model.live="assetSort"><option value="name">Nama A-Z</option><option value="newest">Terbaru</option></select><select wire:model.live="filterCategory"><option value="0">Semua kategori</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select><select wire:model.live="filterStatus"><option value="">Semua status aset</option>@foreach(['available','borrowed','maintenance','lost'] as $s)<option>{{ $s }}</option>@endforeach</select><input placeholder="Cari aset..." wire:model.live.debounce.300ms="search"></div>
    @if($operator)<details id="oi-asset-form" class="card" @if($editingAsset || $assetFormOpen) open @endif><summary>Tambah kategori dan aset</summary><div class="grid two"><div><form wire:submit="category"><label>Nama kategori<input id="oi-categoryName" wire:model="categoryName" required></label><button class="secondary">Tambah kategori</button></form>@foreach($categories as $cat)<div class="list-row"><span>{{ $cat->name }}</span><div><button class="secondary" wire:click="renameCategory({{ $cat->id }})">Ubah ke nama di atas</button><button class="secondary" wire:click="deleteCategory({{ $cat->id }})" wire:confirm="Hapus kategori ini jika belum digunakan?">Hapus</button></div></div>@endforeach</div><form wire:submit="asset"><label>Nama aset<input id="oi-assetName" wire:model="assetName" required></label><label>Kategori<select wire:model="categoryId"><option value="0">Pilih kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label><label>Lokasi<input id="oi-location" wire:model="location" required></label><label>Deskripsi<textarea wire:model="assetDescription"></textarea></label><label>Spesifikasi<textarea wire:model="assetSpecifications"></textarea></label><label>Foto aset (JPG/PNG, maksimal 2 MB)<input type="file" wire:model="assetPhoto"></label><button class="primary">{{ $editingAsset?'Perbarui aset':'Simpan aset' }}</button></form></div></details>@endif
    <section class="card"><h3>Pengajuan peminjaman</h3><div class="grid three"><label>Tanggal mulai<input type="date" wire:model="borrowDate"></label><label>Tanggal kembali<input type="date" wire:model="dueDate"></label><label>Alasan (minimal 10 karakter)<input wire:model="reason"></label></div><p>{{ $org->damage_charge_policy ?: 'Organization belum menetapkan kebijakan biaya kerusakan.' }}</p>@if($org->damage_charge_policy)<label class="inline"><input type="checkbox" wire:model="acknowledged"> Saya memahami dan menyetujui kebijakan di atas.</label>@endif<p class="muted">Isi formulir lalu pilih Ajukan pada aset yang tersedia. Pengajuan bukan reservasi.</p></section>
    @if($focusedAsset && $assets->first())<section class="card"><h2>{{ $assets->first()->name }}</h2><p>{{ $assets->first()->description }}</p><p class="muted">{{ $assets->first()->specifications }}</p><p>Lokasi: {{ $assets->first()->location }}</p></section>@endif
    @if($reportAsset)
    @include('livewire.damage-report-form')
    @endif
    <div class="table-wrap card"><table><thead><tr><th>Aset / Kode</th><th>Kategori</th><th>Lokasi</th><th>Status / Kondisi</th><th>Tindakan</th></tr></thead><tbody>@forelse($assets as $a)<tr wire:key="asset-{{ $a->id }}"><td>@if($a->photo)<img src="{{ asset('storage/'.$a->photo) }}" alt="{{ $a->name }}" width="64" height="64" loading="lazy">@endif<strong><a href="{{ route('asset.show',[$org,$a]) }}">{{ $a->name }}</a></strong><small>{{ $a->code }}</small></td><td>{{ $a->category->name }}</td><td>{{ $a->location }}</td><td><span class="badge">{{ $a->status }}</span><small>{{ $a->physical_condition }}</small></td><td><div class="oi-action-group">@if($a->status==='available')<button class="primary" wire:click="requestBorrow({{ $a->id }})" wire:loading.attr="disabled">Ajukan Pinjam</button>@endif<button type="button" id="oi-report-button-{{ $a->id }}" class="secondary" wire:click="openDamageReport({{ $a->id }})" wire:loading.attr="disabled" aria-expanded="{{ $reportAssetId === $a->id ? 'true' : 'false' }}" aria-controls="oi-damage-report-form">Laporkan Kerusakan</button><a class="secondary button" href="{{ route('asset.timeline',[$org,$a]) }}">Riwayat</a>@if($operator)<button class="secondary" wire:click="editAsset({{ $a->id }})">Edit</button><button class="secondary" wire:click="deleteAsset({{ $a->id }})" wire:confirm="Hapus aset ini secara soft delete?">Hapus</button>@if($a->status==='lost')<button class="secondary" wire:click="found({{ $a->id }})">Tandai ditemukan</button>@endif @endif</div></td></tr>@empty<tr><td colspan="5" class="empty">Belum ada aset yang sesuai.</td></tr>@endforelse</tbody></table></div>{{ $assets->links() }}
    @elseif($tab==='borrowings')
    <section class="card"><h2>{{ $operator?'Pengelolaan peminjaman':'Peminjaman saya' }}</h2><label>Filter status<select wire:model.live="borrowStatus"><option value="">Semua status</option>@foreach(['pending','borrowed','overdue','returned','rejected','cancelled'] as $s)<option>{{ $s }}</option>@endforeach</select></label><div class="grid three"><label>Dari tanggal<input type="date" wire:model.live="borrowFrom"></label><label>Sampai tanggal<input type="date" wire:model.live="borrowTo"></label><label>Cari peminjam / aset<input wire:model.live.debounce.300ms="borrowSearch"></label></div><label>Catatan penolakan / inspeksi<textarea wire:model="notes"></textarea></label>@if($operator)<label>Waktu pengembalian aktual (WIB, kosong = sekarang)<input type="datetime-local" wire:model="returnedAt"></label><label>Override denda (opsional, Rupiah)<input type="number" wire:model="fineOverride" min="0"></label><label>Alasan override<input wire:model="fineOverrideReason"></label><label>Kondisi hasil inspeksi<select wire:model="condition">@foreach(['good','minor_damage','major_damage','lost'] as $v)<option>{{ $v }}</option>@endforeach</select></label>@endif</section>
    <div class="table-wrap card"><table><thead><tr><th>ID / Aset</th><th>Peminjam</th><th>Jadwal</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>@forelse($borrowings as $b)<tr id="oi-borrowing-{{ $b->id }}" wire:key="borrowing-{{ $b->id }}"><td>#{{ $b->id }} {{ $b->asset->name }}</td><td>{{ $b->borrower->name }}</td><td>{{ $b->borrow_date->format('d M Y') }} – {{ $b->due_date->format('d M Y') }}</td><td><span class="badge">{{ $b->status }}</span></td><td>@if($b->status==='pending')@if($operator)<button class="primary" wire:click="decide({{ $b->id }},'approve')" wire:confirm="Setujui dan mulai peminjaman ini?">Setujui</button><button class="secondary" wire:click="decide({{ $b->id }},'reject')">Tolak</button>@endif @if($b->user_id===auth()->id())<button class="secondary" wire:click="decide({{ $b->id }},'cancel')">Batalkan</button>@endif @elseif($operator && in_array($b->status,['borrowed','overdue']))<button class="primary" wire:click="inspect({{ $b->id }})" wire:confirm="Simpan hasil inspeksi? Inspeksi final hanya dapat dilakukan sekali.">Simpan inspeksi</button>@endif</td></tr>@empty<tr><td colspan="5" class="empty">Belum ada peminjaman.</td></tr>@endforelse</tbody></table></div>
    {{ $borrowings->links() }}@elseif($tab==='charges')
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
    @if($operator)
    <details class="card">
        <summary>Panel Pencatatan Pembayaran & Pembebasan Tagihan</summary>
        <div class="grid three"><label>Nominal Rupiah<input type="number" min="1" wire:model="amount" placeholder="Contoh: 50000"></label><label>Metode Pembayaran<select wire:model="method"><option value="cash">Tunai (Cash)</option><option value="bank_transfer">Transfer Bank</option><option value="other">Lainnya</option></select></label><label>Referensi Transfer<input wire:model="reference" placeholder="No. referensi / rekening"></label></div>
        <div class="grid two"><label>Waktu pembayaran aktual (WIB, kosong = sekarang)<input type="datetime-local" wire:model="paidAt"></label><label>Catatan penerimaan / alasan keputusan<input wire:model="paymentNotes" placeholder="Catatan kasir atau keterangan"></label></div>
        <p class="muted">Isi nominal di atas lalu klik tombol tindakan (Catat pembayaran / Beri pembebasan) pada kartu tagihan di bawah.</p>
    </details>
    @endif
    @forelse($charges as $c)<section class="card" wire:key="charge-{{ $c->id }}"><div class="page-heading"><div><h2>Tagihan #{{ $c->id }} · {{ $c->charge_type === 'damage' ? 'Biaya Kerusakan' : ($c->charge_type === 'loss' ? 'Penggantian Kehilangan' : 'Denda Keterlambatan') }}</h2><small>Aset: {{ $c->asset?->name ?? '-' }}</small></div><span class="badge">{{ $c->status }} / {{ $c->settlement }}</span></div><p>{{ $c->reason }}</p><p class="muted">Dasar kebijakan: {{ $c->policy_basis }}</p>@if($c->borrower_id===auth()->id() && $c->status==='draft' && !$c->liability_acknowledged_at)<button class="secondary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'acknowledge')" wire:confirm="Saya menyetujui dasar tanggung jawab dan kebijakan pada tagihan ini.">Setujui dasar tanggung jawab</button>@endif@if($c->evidence_path)<a class="secondary button" href="{{ route('evidence',$c) }}">Unduh bukti kerusakan</a>@endif<div class="grid three"><p>Nominal <strong>Rp{{ number_format($c->amount,0,',','.') }}</strong></p><p>Pembebasan <strong>Rp{{ number_format($c->credits->sum('amount'),0,',','.') }}</strong></p><p>Sisa <strong>Rp{{ number_format($c->balance,0,',','.') }}</strong></p></div>
    @if($operator)<div class="oi-action-group">@if($c->status==='draft' && $admin)<button class="primary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'issue')" wire:confirm="Terbitkan tagihan ini?">Terbitkan Tagihan</button>@endif @if($c->status==='issued')<button class="primary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'payment')">Catat Pembayaran</button>@if($admin)<button class="secondary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'credit')" wire:confirm="Beri pembebasan sebesar nominal yang diisi?">Beri Pembebasan</button>@endif @endif @if($admin && $c->status!=='cancelled')<button class="secondary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'cancel')" wire:confirm="Batalkan tagihan ini?">Batalkan Tagihan</button>@endif</div>@endif
    @foreach($c->payments as $p)<div class="list-row"><span>Pembayaran #{{ $p->id }} · Rp{{ number_format($p->amount,0,',','.') }} · {{ $p->status }}</span>@if($admin && $p->status==='pending')<div><button class="primary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'confirm',{{ $p->id }})">Konfirmasi</button><button class="secondary" wire:click="charge({{ $c->id }},'reject',{{ $p->id }})">Tolak</button></div>@elseif($admin && $p->status==='confirmed')<button class="secondary" wire:loading.attr="disabled" wire:click="charge({{ $c->id }},'void',{{ $p->id }})" wire:confirm="Void pembayaran ini tanpa menghapus histori?">Void</button>@endif</div>@endforeach</section>@empty<div class="card empty">Belum ada tagihan.</div>@endforelse
    {{ $charges->links() }}@elseif($tab==='members')
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
    <section class="oi-panel">
        <div class="oi-panel-head">
            <div>
                <h2>Daftar Laporan Kerusakan</h2>
                <p class="oi-panel-caption">Klik laporan di bawah untuk melihat detail kerusakan, foto bukti, dan menetapkan biaya kerusakan kepada peminjam.</p>
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
                                    {{ $operator ? ($r->charge ? 'Detail & Biaya' : 'Buka & Beri Biaya') : 'Lihat Detail' }}
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

                    @if($selectedReport->charge)
                        <div class="notice">
                            <strong>Biaya Kerusakan Terdaftar:</strong> Rp{{ number_format($selectedReport->charge->amount, 0, ',', '.') }}
                            ({{ $selectedReport->charge->status === 'issued' ? 'Sudah Diterbitkan ke Peminjam' : 'Draft - Menunggu Penerbitan' }})
                            <br><small>Keterangan: {{ $selectedReport->charge->reason }}</small>
                        </div>
                    @endif

                    @if($operator)
                        <form wire:submit="saveReportDecision" class="oi-modal-form">
                            <div class="grid two">
                                <label>Tingkat Kerusakan (Severity)
                                    <select wire:model="severity">
                                        <option value="minor">Ringan (Minor)</option>
                                        <option value="major">Berat (Major)</option>
                                    </select>
                                </label>
                                @if($selectedReport->status === 'in_maintenance')
                                    <label>Kondisi Akhir Setelah Perbaikan
                                        <select wire:model="condition">
                                            <option value="good">Baik (Good)</option>
                                            <option value="minor_damage">Kerusakan Ringan</option>
                                        </select>
                                    </label>
                                @else
                                    <label class="oi-checkbox-card">
                                        <input type="checkbox" wire:model.live="startMaintenanceOption">
                                        <span>Masukkan aset ke status <strong>Maintenance (Perbaikan)</strong></span>
                                    </label>
                                @endif
                            </div>

                            <label>Catatan Penilaian Admin / Pengelola
                                <textarea wire:model="notes" rows="2" placeholder="Catatan pemeriksaan kondisi barang..."></textarea>
                            </label>
                            @error('notes')<p class="errors">{{ $message }}</p>@enderror

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
                                                    <option value="0">Otomatis gunakan peminjaman terakhir aset ini</option>
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
                                            <input type="number" min="1" step="1000" wire:model="amount" placeholder="Contoh: 150000" required>
                                        </label>
                                        @error('amount')<p class="errors">{{ $message }}</p>@enderror

                                        <label>Rincian / Alasan Biaya Kerusakan (minimal 10 karakter)
                                            <textarea wire:model="reason" rows="2" placeholder="Jelaskan rincian komponen yang rusak dan dasar perhitungan biaya..." required></textarea>
                                        </label>
                                        @error('reason')<p class="errors">{{ $message }}</p>@enderror

                                        <label>Lampiran Bukti Tambahan (opsional jika foto laporan sudah ada, JPG/PNG/PDF maks 2 MB)
                                            <input type="file" wire:model="evidence" accept="image/jpeg,image/png,application/pdf">
                                        </label>
                                        @error('evidence')<p class="errors">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                            </div>

                            <div class="oi-modal-actions">
                                <button type="submit" class="primary" wire:loading.attr="disabled">
                                    {{ $giveChargeOption ? 'Simpan & Tetapkan Biaya Kerusakan' : ($selectedReport->status === 'in_maintenance' ? 'Selesaikan Perbaikan' : 'Simpan Penilaian Laporan') }}
                                </button>
                                @if($selectedReport->status === 'in_maintenance')
                                    <button type="button" class="secondary" wire:click="maintain({{ $selectedReport->id }}, 'finish')">Tandai Selesai Perbaikan</button>
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
