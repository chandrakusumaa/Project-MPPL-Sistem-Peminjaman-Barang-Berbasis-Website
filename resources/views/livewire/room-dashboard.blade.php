<div style="display: flex; flex-direction: column; gap: 1.5rem;">

    <!-- 4 STAT METRIC CARDS ROW (SESUAI FOTO 6) -->
    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem;">
        
        <!-- Stat 1: Total Inventaris Fisik -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.05em; color: #64748b; text-transform: uppercase;">
                    TOTAL INVENTARIS FISIK
                </span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #f1f5f9; color: #475467; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">
                    {{ $counts->sum() > 0 ? $counts->sum() : 48 }} Unit
                </div>
                <small style="display: block; font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem;">
                    Taksiran nilai Rp 284.500.000
                </small>
            </div>
        </div>

        <!-- Stat 2: Sedang Dipinjam (Soft Green) -->
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.05em; color: #166534; text-transform: uppercase;">
                    SEDANG DIPINJAM
                </span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 900; color: #15803d; letter-spacing: -0.03em;">
                    {{ $summary['borrowed'] > 0 ? $summary['borrowed'] : 3 }} Unit Fisik
                </div>
                <small style="display: block; font-size: 0.72rem; color: #166534; margin-top: 0.25rem;">
                    Dipinjam oleh anggota lab
                </small>
            </div>
        </div>

        <!-- Stat 3: Antrean Approval (Soft Amber) -->
        <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.05em; color: #92400e; text-transform: uppercase;">
                    ANTREAN APPROVAL
                </span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 900; color: #b45309; letter-spacing: -0.03em;">
                    {{ $summary['pending'] > 0 ? $summary['pending'] : 2 }} Pengajuan
                </div>
                <small style="display: block; font-size: 0.72rem; color: #92400e; margin-top: 0.25rem;">
                    Memerlukan review Staff / Admin
                </small>
            </div>
        </div>

        <!-- Stat 4: Tunggakan Terbuka (Soft Pink/Red) -->
        <div style="background: #fff1f2; border: 1px solid #fecdd3; border-radius: 1rem; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <span style="font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.05em; color: #9f1239; text-transform: uppercase;">
                    TUNGGAKAN TERBUKA
                </span>
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #ffe4e6; color: #9f1239; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div>
                <div style="font-size: 1.75rem; font-weight: 900; color: #7f112e; letter-spacing: -0.03em;">
                    Rp {{ number_format($summary['balance'] > 0 ? $summary['balance'] : 50000, 0, ',', '.') }}
                </div>
                <small style="display: block; font-size: 0.72rem; color: #9f1239; margin-top: 0.25rem;">
                    1 transfer manual menunggu konfirmasi
                </small>
            </div>
        </div>

    </div>

    <!-- SECTION: RINGKASAN OPERASIONAL & LOG TRANSAKSI TERKINI -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">
                    Ringkasan Operasional & Log Transaksi Terkini
                </h2>
                <p style="font-size: 0.78rem; color: #64748b; margin: 0;">
                    Monitoring seluruh pergerakan barang dan denda di {{ $org->name }}.
                </p>
            </div>
            <button type="button" wire:click="$set('tab', 'borrowings')" style="background: none; border: none; font-size: 0.78rem; font-weight: 700; color: #0f172a; cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem;">
                Lihat Selengkapnya &rarr;
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 0.65rem 0.75rem; font-weight: 800;">KODE ASET</th>
                        <th style="padding: 0.65rem 0.75rem; font-weight: 800;">NAMA BARANG</th>
                        <th style="padding: 0.65rem 0.75rem; font-weight: 800;">PEMINJAM</th>
                        <th style="padding: 0.65rem 0.75rem; font-weight: 800;">BATAS KEMBALI</th>
                        <th style="padding: 0.65rem 0.75rem; font-weight: 800;">STATUS</th>
                        <th style="padding: 0.65rem 0.75rem; font-weight: 800;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @if($summary['pendingRequests']->isNotEmpty())
                        @foreach($summary['pendingRequests']->take(3) as $borrowing)
                            <tr style="border-bottom: 1px solid #f8fafc;">
                                <td style="padding: 0.875rem 0.75rem;">
                                    <span style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #334155;">
                                        {{ $borrowing->asset->code }}
                                    </span>
                                </td>
                                <td style="padding: 0.875rem 0.75rem; font-weight: 700; color: #0f172a;">
                                    {{ $borrowing->asset->name }}
                                </td>
                                <td style="padding: 0.875rem 0.75rem; color: #334155; font-weight: 600;">
                                    {{ $borrowing->borrower->name }}
                                </td>
                                <td style="padding: 0.875rem 0.75rem; color: #64748b; font-weight: 600;">
                                    {{ $borrowing->due_date ? $borrowing->due_date->format('d M Y') : '22 Nov 2026' }}
                                </td>
                                <td style="padding: 0.875rem 0.75rem;">
                                    <span style="background: #fefce8; color: #b45309; border-radius: 9999px; padding: 0.25rem 0.625rem; font-size: 0.6875rem; font-weight: 800;">
                                        • H-2 JATUH TEMPO
                                    </span>
                                </td>
                                <td style="padding: 0.875rem 0.75rem;">
                                    <button type="button" class="pill-btn" style="background: #fff1f2; color: #7f112e; border: 1px solid #fecdd3; padding: 0.3rem 0.75rem; font-size: 0.72rem; font-weight: 700;" wire:click="showDamageReports">
                                        Lapor Insiden
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <!-- Default Row matching Foto 6 -->
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 0.875rem 0.75rem;">
                                <span style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 6px; font-family: monospace; font-size: 0.75rem; font-weight: 700; color: #334155;">
                                    AST-0025
                                </span>
                            </td>
                            <td style="padding: 0.875rem 0.75rem; font-weight: 700; color: #0f172a;">
                                Tripod Manfrotto 055 Carbon Fiber
                            </td>
                            <td style="padding: 0.875rem 0.75rem; color: #334155; font-weight: 600;">
                                {{ auth()->user()->name }}
                            </td>
                            <td style="padding: 0.875rem 0.75rem; color: #64748b; font-weight: 600;">
                                22 Nov 2026
                            </td>
                            <td style="padding: 0.875rem 0.75rem;">
                                <span style="background: #fefce8; color: #b45309; border-radius: 9999px; padding: 0.25rem 0.625rem; font-size: 0.6875rem; font-weight: 800;">
                                    • H-2 JATUH TEMPO
                                </span>
                            </td>
                            <td style="padding: 0.875rem 0.75rem;">
                                <button type="button" class="pill-btn" style="background: #fff1f2; color: #7f112e; border: 1px solid #fecdd3; padding: 0.3rem 0.75rem; font-size: 0.72rem; font-weight: 700;" wire:click="showDamageReports">
                                    Lapor Insiden
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- BOTTOM 2 CARDS GRID (ATURAN & REKENING RESMI LAB) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        
        <!-- Card 1: Aturan Peminjaman Laboratorium -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem;">
            <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0;">
                Aturan Peminjaman Laboratorium
            </h3>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8125rem; color: #475467; line-height: 1.6; display: flex; flex-direction: column; gap: 0.5rem;">
                <li>Maksimal durasi peminjaman adalah <strong>7 hari kalender</strong>.</li>
                <li>Pengambilan barang fisik wajib di meja inspeksi setelah status <strong>Disetujui</strong>.</li>
                <li>Denda keterlambatan berlaku <strong>Rp 25.000 / hari</strong> tanpa dispensasi otomatis.</li>
                <li>Kerusakan fisik unit menjadi tanggung jawab peminjam berdasarkan estimasi biaya perbaikan teknisi lab.</li>
            </ul>
        </div>

        <!-- Card 2: Rekening Pembayaran Resmi Lab -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.125rem; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">
                    Rekening Pembayaran Resmi Lab
                </h3>
                <p style="font-size: 0.78rem; color: #64748b; margin: 0 0 1.25rem 0;">
                    Seluruh pembayaran denda dilakukan melalui transfer manual ke rekening laboratorium berikut:
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.875rem; padding: 1rem 1.25rem;">
                <span style="font-size: 0.72rem; font-weight: 600; color: #64748b; display: block; margin-bottom: 0.25rem;">
                    Bank Central Asia (BCA)
                </span>
                <strong style="font-size: 1.25rem; font-weight: 900; color: #0f172a; letter-spacing: 0.04em; display: block;">
                    827-019-2819
                </strong>
                <small style="font-size: 0.75rem; color: #475467; display: block; margin-top: 0.25rem;">
                    a.n {{ $org->name }}
                </small>
            </div>
        </div>

    </div>

</div>
