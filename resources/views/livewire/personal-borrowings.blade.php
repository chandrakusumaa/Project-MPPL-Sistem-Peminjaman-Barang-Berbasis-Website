<div class="oi-personal-borrowings" style="width: 100%; box-sizing: border-box; padding-bottom: 3rem;">
    <!-- PAGE HEAD (SESUAI FOTO 2) -->
    <div class="oi-page-head" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0 0 0.35rem 0;">
                Riwayat Peminjaman Pribadi
            </h1>
            <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0;">
                Daftar seluruh unit fisik yang sedang Anda bawa, ajukan, atau telah selesai di semua organisasi.
            </p>
        </div>
        <div class="oi-head-actions">
            <a class="btn-maroon" style="font-size: 0.8125rem; font-weight: 700; padding: 0.6rem 1.25rem; gap: 0.5rem;" href="{{ route('organizations') }}">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                + Pinjam Barang Baru
            </a>
        </div>
    </div>

    <!-- MAIN CARD TABLE -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.75rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        
        <!-- TOOLBAR FILTER PILLS (SESUAI FOTO 2) -->
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

            <div wire:loading.delay role="status" style="font-size: 0.75rem; color: #80142B; font-weight: 700; margin-left: auto;">Memuat riwayat…</div>
        </div>

        <!-- TABLE OF BORROWINGS -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">ID PINJAM</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">KODE FISIK</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; min-width: 220px;">NAMA BARANG</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">ORGANIZATION</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">RENTANG WAKTU</th>
                        <th style="padding: 0.85rem 1rem; font-weight: 700; white-space: nowrap;">STATUS</th>
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
                                <span style="background: #f1f5f9; padding: 0.25rem 0.55rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 800; color: #1e293b; white-space: nowrap; display: inline-block;">
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
                                {{ $b->borrow_date->format('d M Y') }} &mdash; {{ $b->due_date->format('d M Y') }}
                            </td>
                            <td style="padding: 1rem; white-space: nowrap;">
                                @if($b->status === 'pending')
                                    <span style="background: #eff6ff; color: #1d4ed8; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                        • MENUNGGU APPROVAL
                                    </span>
                                @elseif($b->status === 'borrowed')
                                    <span style="background: #fefce8; color: #b45309; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                        • SEDANG DIPINJAM
                                    </span>
                                @elseif($b->status === 'overdue')
                                    <span style="background: #fef2f2; color: #b91c1c; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                        • TERLAMBAT
                                    </span>
                                @elseif($b->status === 'returned')
                                    <span style="background: #f0fdf4; color: #15803d; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                        • SELESAI TERVERIFIKASI
                                    </span>
                                @else
                                    <span style="background: #f1f5f9; color: #475467; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">
                                        • {{ strtoupper($b->status) }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1rem; white-space: nowrap; text-align: right;">
                                @if($b->status === 'pending')
                                    <button type="button" class="pill-btn" style="background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">
                                        Batalkan
                                    </button>
                                @elseif($b->status === 'borrowed')
                                    <a href="{{ route('workspace', $b->organization) }}" class="pill-btn btn-secondary-light" style="padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-decoration: none; white-space: nowrap;">
                                        QR Serah Terima
                                    </a>
                                @else
                                    <a href="{{ route('timeline') }}" class="pill-btn btn-secondary-light" style="padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; text-decoration: none; white-space: nowrap;">
                                        Timeline
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <!-- Default Rows matching Foto 2 -->
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 1rem; font-family: monospace; font-size: 0.78rem; color: #64748b; font-weight: 700; white-space: nowrap;">BRW-2026-001</td>
                            <td style="padding: 1rem; white-space: nowrap;"><span style="background: #f1f5f9; padding: 0.25rem 0.55rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 800; color: #1e293b; white-space: nowrap; display: inline-block;">AST-0025</span></td>
                            <td style="padding: 1rem; font-weight: 700; color: #0f172a;">Tripod Manfrotto 055 Carbon Fiber</td>
                            <td style="padding: 1rem; color: #334155; font-weight: 600; white-space: nowrap;">Media Kreatif Lab</td>
                            <td style="padding: 1rem; color: #475467; font-size: 0.78rem; font-weight: 600; white-space: nowrap;">18 Nov 2026 &mdash; 22 Nov 2026</td>
                            <td style="padding: 1rem; white-space: nowrap;"><span style="background: #fefce8; color: #b45309; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">• SEDANG DIPINJAM</span></td>
                            <td style="padding: 1rem; white-space: nowrap; text-align: right;"><button type="button" class="pill-btn btn-secondary-light" style="padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">QR Serah Terima</button></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 1rem; font-family: monospace; font-size: 0.78rem; color: #64748b; font-weight: 700; white-space: nowrap;">BRW-2026-002</td>
                            <td style="padding: 1rem; white-space: nowrap;"><span style="background: #f1f5f9; padding: 0.25rem 0.55rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 800; color: #1e293b; white-space: nowrap; display: inline-block;">AST-0024</span></td>
                            <td style="padding: 1rem; font-weight: 700; color: #0f172a;">Sony Alpha 7 IV (Body Only)</td>
                            <td style="padding: 1rem; color: #334155; font-weight: 600; white-space: nowrap;">Media Kreatif Lab</td>
                            <td style="padding: 1rem; color: #475467; font-size: 0.78rem; font-weight: 600; white-space: nowrap;">20 Nov 2026 &mdash; 24 Nov 2026</td>
                            <td style="padding: 1rem; white-space: nowrap;"><span style="background: #eff6ff; color: #1d4ed8; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">• MENUNGGU APPROVAL</span></td>
                            <td style="padding: 1rem; white-space: nowrap; text-align: right;"><button type="button" class="pill-btn" style="background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Batalkan</button></td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 1rem; font-family: monospace; font-size: 0.78rem; color: #64748b; font-weight: 700; white-space: nowrap;">BRW-2026-003</td>
                            <td style="padding: 1rem; white-space: nowrap;"><span style="background: #f1f5f9; padding: 0.25rem 0.55rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 800; color: #1e293b; white-space: nowrap; display: inline-block;">AST-0028</span></td>
                            <td style="padding: 1rem; font-weight: 700; color: #0f172a;">Audio Recorder Zoom H6 Black</td>
                            <td style="padding: 1rem; color: #334155; font-weight: 600; white-space: nowrap;">Media Kreatif Lab</td>
                            <td style="padding: 1rem; color: #475467; font-size: 0.78rem; font-weight: 600; white-space: nowrap;">10 Nov 2026 &mdash; 15 Nov 2026</td>
                            <td style="padding: 1rem; white-space: nowrap;"><span style="background: #f0fdf4; color: #15803d; border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; white-space: nowrap; display: inline-flex; align-items: center;">• SELESAI TERVERIFIKASI</span></td>
                            <td style="padding: 1rem; white-space: nowrap; text-align: right;"><button type="button" class="pill-btn btn-secondary-light" style="padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Timeline</button></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($borrowings->hasPages())
            <div style="margin-top: 1.5rem;">{{ $borrowings->links() }}</div>
        @endif
    </div>
</div>
