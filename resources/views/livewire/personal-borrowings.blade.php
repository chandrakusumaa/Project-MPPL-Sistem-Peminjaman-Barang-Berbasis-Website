<div class="oi-personal-borrowings" style="max-width: 1240px; margin: 0 auto; padding-bottom: 3.5rem;">
    <!-- PAGE HEAD -->
    <div class="oi-page-head" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.35rem 0;">
                Riwayat Peminjaman Pribadi
            </h1>
            <p style="font-size: 0.84rem; font-weight: 400; color: #64748b; margin: 0;">
                Daftar seluruh unit fisik yang sedang Anda bawa, ajukan, atau telah selesai di semua organisasi.
            </p>
        </div>
        <div class="oi-head-actions">
            <a class="pill-btn btn-primary-red" style="font-size: 0.8125rem; font-weight: 700; padding: 0.6rem 1.25rem; gap: 0.5rem; box-shadow: 0 4px 12px rgba(127, 17, 46, 0.22);" href="{{ route('organizations') }}">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                + Pinjam Barang Baru
            </a>
        </div>
    </div>

    <!-- MAIN CARD TABLE -->
    @if(session('status'))
        <div role="status" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 1.25rem; color: #166534; font-size: 0.875rem; font-weight: 600;">
            {{ session('status') }}
        </div>
    @endif
    @error('action')
        <div role="alert" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 1.25rem; color: #b91c1c; font-size: 0.875rem; font-weight: 600;">
            {{ $message }}
        </div>
    @enderror
    <div class="panel-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        <!-- TOOLBAR FILTER PILLS -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: center;">
            <button type="button" class="pill-btn {{ empty($status) ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.45rem 1rem;" wire:click="$set('status', '')">
                Semua
            </button>
            <button type="button" class="pill-btn {{ $status === 'pending' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.45rem 1rem;" wire:click="$set('status', 'pending')">
                Menunggu Approval
            </button>
            <button type="button" class="pill-btn {{ $status === 'approved' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.45rem 1rem;" wire:click="$set('status', 'approved')">
                Disetujui (Siap Ambil)
            </button>
            <button type="button" class="pill-btn {{ in_array($status, ['borrowed', 'overdue']) ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.45rem 1rem;" wire:click="$set('status', 'borrowed')">
                Sedang Dipinjam
            </button>
            <button type="button" class="pill-btn {{ $status === 'returned' ? 'btn-solid-black' : 'btn-secondary-light' }}" style="font-size: 0.75rem; font-weight: 700; padding: 0.45rem 1rem;" wire:click="$set('status', 'returned')">
                Selesai
            </button>

            <div wire:loading.delay role="status" style="font-size: 0.75rem; color: #7f112e; font-weight: 700; margin-left: auto;">Memuat riwayat…</div>
        </div>

        <!-- TABLE OF BORROWINGS -->
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">ID PINJAM</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">KODE FISIK</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; min-width: 220px;">NAMA BARANG</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">ORGANIZATION</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">RENTANG WAKTU</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap; text-align: center;">STATUS</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap; text-align: right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $b)
                        <tr wire:key="borrowing-row-{{ $b->id }}" style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 1rem; font-family: monospace; font-size: 0.78rem; color: #64748b; font-weight: 700; white-space: nowrap;">
                                BRW-2026-{{ str_pad($b->id, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td style="padding: 1rem; white-space: nowrap;">
                                <span class="micro-badge" style="font-family: monospace; font-size: 0.75rem; font-weight: 800; color: #1e293b; background: #f1f5f9;">
                                    {{ $b->asset->code }}
                                </span>
                            </td>
                            <td style="padding: 1rem; font-weight: 700; color: #0f172a;">
                                {{ $b->asset->name }}
                            </td>
                            <td style="padding: 1rem; color: #334155; font-weight: 600; white-space: nowrap;">
                                {{ $b->organization->name }}
                            </td>
                            <td style="padding: 1rem; color: #475467; font-size: 0.78rem; font-weight: 600; white-space: nowrap;">
                                {{ $b->borrow_date ? $b->borrow_date->format('d M Y') : '—' }} &mdash; <strong>{{ $b->due_date ? $b->due_date->format('d M Y') : '—' }}</strong>
                            </td>
                            <td style="padding: 1rem; white-space: nowrap; text-align: center;">
                                @if($b->status === 'pending')
                                    <span class="micro-badge micro-badge-blue">
                                        <span class="status-dot status-dot-blue"></span> Menunggu Approval
                                    </span>
                                @elseif($b->status === 'approved')
                                    <span class="micro-badge micro-badge-green">
                                        <span class="status-dot status-dot-green"></span> Disetujui (Siap Ambil)
                                    </span>
                                @elseif($b->status === 'borrowed')
                                    <span class="micro-badge micro-badge-amber">
                                        <span class="status-dot status-dot-amber"></span> Sedang Dipinjam
                                    </span>
                                @elseif($b->status === 'overdue')
                                    <span class="micro-badge micro-badge-red">
                                        <span class="status-dot status-dot-red"></span> Terlambat
                                    </span>
                                @elseif($b->status === 'returned')
                                    <span class="micro-badge micro-badge-green">
                                        <span class="status-dot status-dot-green"></span> Selesai Terverifikasi
                                    </span>
                                @else
                                    <span class="micro-badge">
                                        {{ strtoupper($b->status) }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1rem; white-space: nowrap; text-align: right;">
                                @if($b->status === 'pending' && !$b->organization->archived_at)
                                    <button type="button" class="pill-btn btn-danger-light btn-sm" style="color: #9f1239; border: 1px solid #fecdd3; padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap; cursor: pointer;"
                                        wire:click="cancelBorrowing({{ $b->id }})" wire:confirm="Batalkan pengajuan peminjaman ini?" wire:loading.attr="disabled">
                                        Batalkan
                                    </button>
                                @elseif(in_array($b->status, ['borrowed', 'overdue'], true) && !$b->organization->archived_at)
                                    <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                        <button type="button" class="pill-btn btn-primary-red btn-sm" style="padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap; cursor: pointer;"
                                            wire:click="requestReturn({{ $b->id }})" wire:confirm="Kirim pemberitahuan pengembalian ke Admin/Staff? Serahkan barang secara fisik agar pengelola mencatat inspeksi pengembalian." wire:loading.attr="disabled">
                                            Kembalikan Barang
                                        </button>
                                        <a href="{{ route('workspace', $b->organization) }}" class="pill-btn btn-secondary-light btn-sm" style="padding: 0.35rem 0.75rem; font-size: 0.72rem; font-weight: 700; text-decoration: none; white-space: nowrap;">
                                            Buka Organization
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('asset.timeline', [$b->organization, $b->asset]) }}" class="pill-btn btn-ghost btn-sm" style="padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-decoration: none; white-space: nowrap;">
                                        Timeline
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada riwayat peminjaman{{ $status ? ' dengan status ini' : '' }}</p>
                                <p style="font-size: 0.75rem; margin: 0;">Ajukan peminjaman unit fisik dari katalog organisasi yang Anda ikuti.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($borrowings->hasPages())
            <div style="margin-top: 1.5rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                {{ $borrowings->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: LAPORAN KERUSAKAN MANDIRI (MEMBER FLOW) -->
    <div id="modalReportDamageMember" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 540px;">
            <div class="modal-header">
                <div>
                    <span class="micro-badge micro-badge-amber" style="font-size: 0.6875rem;">LAPORAN MANDIRI PEMINJAM</span>
                    <h3 class="modal-title" style="margin-top: 0.25rem;">Laporkan Kendala / Kerusakan Fisik</h3>
                </div>
                <button type="button" onclick="closeModal('modalReportDamageMember')" class="pill-btn btn-ghost" style="padding: 0.25rem 0.5rem;">✕</button>
            </div>
            <form onsubmit="handleMemberDamageReport(event)">
                <div class="modal-body">
                    <div style="background: #fdf2f4; border: 1px solid #fecdd6; border-radius: 0.75rem; padding: 0.875rem; margin-bottom: 1rem; font-size: 0.8125rem; color: #7f112e;">
                        <strong>Aset Terkait (Terkunci):</strong> <span id="damageReportAssetName">Tripod Manfrotto 055 Carbon Fiber (AST-0025)</span><br>
                        <strong>ID Peminjaman Aktif:</strong> <span id="damageReportBorrowId">BRW-2026-001 · Media Kreatif Lab</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.8125rem;">Jenis Kendala / Kerusakan Fisik</label>
                        <select class="form-input" required id="damageReportType">
                            <option value="">Pilih jenis kendala...</option>
                            <option value="minor">Kerusakan Fisik Ringan (Baret Dalam / Lecet Kasar / Kendor)</option>
                            <option value="major">Kerusakan Fungsi / Patah / Retak Kaca Lensa</option>
                            <option value="missing_parts">Komponen / Kelengkapan Hilang (Tutup Lensa, Baterai, Kabel)</option>
                            <option value="electronic">Mati Total / Error Sistem Elektronik</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.8125rem;">Deskripsi Kronologi Kerusakan</label>
                        <textarea class="form-input" rows="3" placeholder="Ceritakan bagaimana kendala ini terjadi secara jujur dan transparan..." required id="damageReportDesc"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.8125rem;">Foto Bukti Fisik Bagian Rusak</label>
                        <div style="border: 2px dashed #cbd5e1; border-radius: 0.75rem; padding: 1.25rem; text-align: center; background: #f8fafc; cursor: pointer;" onclick="document.getElementById('damagePhotoInput').click()">
                            <input type="file" id="damagePhotoInput" accept="image/*" style="display: none;" onchange="previewDamagePhoto(this)" required>
                            <div id="damagePlaceholder">
                                <svg width="32" height="32" fill="none" stroke="#64748b" viewBox="0 0 24 24" style="margin: 0 auto 0.5rem auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="4"/></svg>
                                <div style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">Ambil foto atau unggah dokumentasi kerusakan</div>
                                <div style="font-size: 0.6875rem; color: #64748b; margin-top: 0.25rem;">Format JPG, PNG, WEBP (Maks. 5MB)</div>
                            </div>
                            <div id="damagePreviewBox" style="display: none; align-items: center; justify-content: center; gap: 0.75rem;">
                                <img id="damagePreviewImg" src="" style="max-height: 120px; border-radius: 0.5rem; border: 1px solid #cbd5e1; object-fit: contain;">
                                <div style="text-align: left; font-size: 0.75rem; color: #059669; font-weight: 700;">Foto berhasil dimuat ✓</div>
                            </div>
                        </div>
                    </div>

                    <div style="background: #fffbeb; border: 1px solid #fed7aa; border-radius: 0.75rem; padding: 0.75rem; font-size: 0.75rem; color: #9a3412;">
                        ⚠️ Laporan kerusakan mandiri akan masuk ke meja inspeksi staf. Jika ada estimasi biaya perbaikan atau penggantian, Anda akan diberitahu secara transparan pada menu Tagihan.
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" onclick="closeModal('modalReportDamageMember')" class="pill-btn btn-secondary-light">Batal</button>
                    <button type="submit" class="pill-btn btn-primary-red" style="font-weight: 800;">Kirim Laporan Kerusakan →</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openMemberDamageModal(assetName, assetCode, borrowId, orgName) {
            const nameEl = document.getElementById('damageReportAssetName');
            const idEl = document.getElementById('damageReportBorrowId');
            if (nameEl) nameEl.textContent = assetName + ' (' + assetCode + ')';
            if (idEl) idEl.textContent = borrowId + ' · ' + orgName;
            if (typeof openModal === 'function') {
                openModal('modalReportDamageMember');
            } else {
                const m = document.getElementById('modalReportDamageMember');
                if (m) m.style.display = 'flex';
            }
        }

        function previewDamagePhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const placeholder = document.getElementById('damagePlaceholder');
                    const previewBox = document.getElementById('damagePreviewBox');
                    const previewImg = document.getElementById('damagePreviewImg');
                    if (placeholder) placeholder.style.display = 'none';
                    if (previewBox) previewBox.style.display = 'flex';
                    if (previewImg) previewImg.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function handleMemberDamageReport(e) {
            e.preventDefault();
            if (typeof closeModal === 'function') {
                closeModal('modalReportDamageMember');
            } else {
                const m = document.getElementById('modalReportDamageMember');
                if (m) m.style.display = 'none';
            }
            if (typeof showToast === 'function') {
                showToast('Laporan kerusakan fisik berhasil dikirim ke meja inspeksi staf!', 'warning');
            } else {
                alert('Laporan kerusakan fisik berhasil dikirim ke meja inspeksi staf!');
            }
        }
    </script>
</div>
