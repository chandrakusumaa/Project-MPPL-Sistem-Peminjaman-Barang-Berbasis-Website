<div class="oi-personal-borrowings" style="max-width: 72rem; margin: 0 auto; padding-bottom: 3rem;">
    <!-- PAGE HEAD -->
    <div class="oi-page-head" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.375rem 0;">
                Peminjaman Saya
            </h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                Seluruh riwayat pengajuan, persetujuan, dan pengembalian barang fisik Anda di semua komunitas.
            </p>
        </div>
        <div class="oi-head-actions">
            <a class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.5rem 1rem;" href="{{ route('dashboard') }}">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- MAIN CARD TABLE -->
    <div class="feature-card" style="padding: 1.75rem;">
        <!-- TOOLBAR FILTER -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <label style="font-size: 0.8125rem; font-weight: 700; color: #334155;">Filter Status:</label>
                <select class="form-input" style="width: auto; padding: 0.4rem 0.875rem; font-size: 0.8125rem;" wire:model.live="status" aria-label="Status peminjaman">
                    <option value="">Semua Status</option>
                    @foreach(['pending' => 'Menunggu Approval', 'borrowed' => 'Sedang Dipinjam', 'overdue' => 'Terlambat', 'returned' => 'Dikembalikan', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div wire:loading.delay role="status" style="font-size: 0.8125rem; color: #dc2626; font-weight: 600;">Memuat riwayat…</div>
        </div>

        <!-- TABLE OF BORROWINGS -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem;">
                        <th style="padding: 0.75rem 1rem;">Aset Fisik</th>
                        <th style="padding: 0.75rem 1rem;">Komunitas</th>
                        <th style="padding: 0.75rem 1rem;">Periode Pinjam</th>
                        <th style="padding: 0.75rem 1rem;">Inspeksi & Catatan</th>
                        <th style="padding: 0.75rem 1rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $b)
                        <tr wire:key="borrowing-row-{{ $b->id }}" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;">
                                <strong style="font-size: 0.875rem; color: #0f172a; display: block;">{{ $b->asset->name }}</strong>
                                <span style="font-family: monospace; font-size: 0.6875rem; color: #64748b; background: #f8fafc; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid #e2e8f0;">
                                    {{ $b->asset->code }}
                                </span>
                            </td>
                            <td style="padding: 1rem; color: #334155; font-weight: 600;">
                                {{ $b->organization->name }}
                            </td>
                            <td style="padding: 1rem; color: #64748b;">
                                <div>Pinjam: <strong style="color: #334155;">{{ $b->borrow_date->format('d M Y') }}</strong></div>
                                <div>Kembali: <strong style="color: #334155;">{{ $b->due_date->format('d M Y') }}</strong></div>
                            </td>
                            <td style="padding: 1rem; color: #64748b; max-width: 260px;">
                                @if($b->return_condition)
                                    <div style="font-size: 0.75rem; margin-bottom: 0.25rem;">
                                        Kondisi kembali: <strong style="color: #0f172a;">{{ ucfirst($b->return_condition) }}</strong>
                                    </div>
                                    @if($b->fine_amount > 0)
                                        <div style="font-size: 0.75rem; color: #dc2626; font-weight: 600;">
                                            Denda telat: Rp{{ number_format($b->fine_amount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                @else
                                    <span style="color: #94a3b8; font-size: 0.75rem;">Belum diinspeksi</span>
                                @endif

                                @foreach($b->charges as $c)
                                    <div style="margin-top: 0.35rem; font-size: 0.75rem; color: #7f1d1d; background: #fef2f2; padding: 0.25rem 0.5rem; border-radius: 0.375rem; border: 1px solid #fecdd6;">
                                        Tagihan: Rp{{ number_format($c->amount, 0, ',', '.') }} (Sisa: Rp{{ number_format($c->balance, 0, ',', '.') }})
                                    </div>
                                @endforeach
                            </td>
                            <td style="padding: 1rem;">
                                <x-room.status :value="$b->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 3rem 1rem; color: #94a3b8;">
                                <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada peminjaman yang sesuai</p>
                                <p style="font-size: 0.75rem; margin: 0;">Pengajuan peminjaman baru Anda akan muncul di tabel ini.</p>
                            </td>
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
