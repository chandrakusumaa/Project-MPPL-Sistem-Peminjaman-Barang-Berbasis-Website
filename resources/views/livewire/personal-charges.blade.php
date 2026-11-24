<div class="oi-personal-charges" wire:poll.30s style="max-width: 1240px; margin: 0 auto; padding-bottom: 3.5rem;">
    @php
        $chargeTypes = ['late_fee' => 'Denda Keterlambatan', 'damage' => 'Biaya Kerusakan', 'loss' => 'Penggantian Kehilangan'];
        $settlements = ['unpaid' => 'Belum Dibayar', 'partial' => 'Sebagian Dibayar', 'paid' => 'Lunas', 'waived' => 'Dibebaskan', 'draft' => 'Draft Usulan', 'cancelled' => 'Dibatalkan'];
        $paymentLabels = ['pending' => 'Menunggu Verifikasi Admin', 'confirmed' => 'Terkonfirmasi Lunas', 'rejected' => 'Ditolak', 'voided' => 'Dibatalkan'];
        $methods = ['cash' => 'Tunai', 'bank_transfer' => 'Transfer Bank Manual', 'other' => 'Lainnya'];
    @endphp

    <!-- PAGE HEAD -->
    <div class="oi-page-head" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.375rem 0;">
                Tagihan & Denda Saya
            </h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                Ringkasan kewajiban denda keterlambatan, biaya kerusakan, dan verifikasi pembayaran manual di seluruh organisasi.
            </p>
        </div>
        <div class="oi-head-actions" style="display: flex; gap: 0.75rem;">
            <a class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.5rem 1rem;" href="{{ route('personal.borrowings') }}">
                Lihat Peminjaman &rarr;
            </a>
            <button type="button" onclick="openModal('modalUploadReceipt')" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.5rem 1.15rem; font-weight: 700;">
                + Kirim Bukti Transfer
            </button>
        </div>
    </div>

    <!-- SUMMARY BANNER: SISA TAGIHAN & INSTRUKSI REKENING -->
    @if(($summary->outstanding ?? 0) > 0)
        <div style="background-color: #ffffff; border: 1px solid #fed7aa; border-top: 4px solid #ea580c; border-radius: 1.25rem; padding: 1.75rem 2rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div>
                <span class="micro-badge micro-badge-amber" style="margin-bottom: 0.5rem;">
                    <span class="status-dot status-dot-amber"></span> Tagihan Belum Dibayar
                </span>
                <div style="font-size: 2.25rem; font-weight: 900; color: #c2410c; letter-spacing: -0.04em;">
                    Rp{{ number_format($summary->outstanding, 0, ',', '.') }}
                </div>
                <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.25rem;">
                    Sisa kewajiban denda keterlambatan atau ganti rugi fisik yang belum terkonfirmasi.
                </p>
            </div>

            <div style="max-width: 32rem; font-size: 0.8125rem; color: #9a3412; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 1rem; padding: 1.125rem; display: flex; flex-direction: column; gap: 0.625rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div><strong>Rekening Resmi:</strong> Bank BCA: <code style="font-family: monospace; font-size: 0.95rem; font-weight: 800; background: #ffedd5; padding: 0.15rem 0.4rem; border-radius: 4px;">827-019-2819</code> a.n. Kas Komunitas</div>
                    <button type="button" onclick="copyBankAccount('8270192819')" class="pill-btn btn-primary-red" style="font-size: 0.6875rem; padding: 0.25rem 0.6rem; cursor: pointer;">
                        📋 Salin Rekening
                    </button>
                </div>
                <div style="font-size: 0.75rem; color: #7c2d12;">
                    Setelah mentransfer, klik <strong>Kirim Bukti Transfer</strong> agar admin Organization dapat memverifikasi dan memperbarui saldo Anda.
                </div>
            </div>
        </div>
    @endif

    <!-- 4 STAT METRIC CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 0.5rem;">Total Tagihan</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">Rp{{ number_format($summary->total ?? 0, 0, ',', '.') }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Tagihan resmi diterbitkan Admin</p>
        </div>

        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #059669; display: block; margin-bottom: 0.5rem;">Sudah Terkonfirmasi</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #059669; letter-spacing: -0.03em;">Rp{{ number_format($summary->paid ?? 0, 0, ',', '.') }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Pembayaran diverifikasi lunas</p>
        </div>

        <div class="feature-card card-accent-red" style="padding: 1.5rem; border-radius: 1.25rem; background: #ffffff; border: 1px solid #fecdd6;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #7f112e; display: block; margin-bottom: 0.5rem;">Sisa Kewajiban</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #7f112e; letter-spacing: -0.03em;">Rp{{ number_format($summary->outstanding ?? 0, 0, ',', '.') }}</div>
            <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0 0;">Jumlah yang belum lunas</p>
        </div>

        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 0.5rem;">Pembebasan (Waived)</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #334155; letter-spacing: -0.03em;">Rp{{ number_format($summary->credited ?? 0, 0, ',', '.') }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Denda dimaafkan oleh Admin</p>
        </div>
    </div>

    <!-- ALERT BANNER PEMBAYARAN MENUNGGU VERIFIKASI -->
    @if(($pendingPayments ?? 0) > 0)
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; color: #92400e; font-size: 0.8125rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>
                Pembayaran transfer sebesar <strong>Rp{{ number_format($pendingPayments, 0, ',', '.') }}</strong> sedang menunggu verifikasi Admin komunitas. Sisa tagihan Anda akan berkurang secara otomatis begitu Admin mengonfirmasi resi transfer Anda.
            </span>
        </div>
    @endif

    <!-- SECTION 1: DAFTAR TAGIHAN -->
    <section class="panel-card" style="padding: 1.75rem; margin-bottom: 2.5rem; border-radius: 1.25rem;" aria-labelledby="charges-heading">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 id="charges-heading" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Daftar Tagihan Lintas Organization</h2>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Rincian biaya denda, kerusakan, dan status pelunasan.</p>
            </div>
            
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <select class="form-input" style="width: auto; padding: 0.45rem 0.75rem; font-size: 0.8125rem;" wire:model.live="status" aria-label="Status tagihan">
                    <option value="">Semua Status</option>
                    <option value="outstanding">Belum Lunas</option>
                    <option value="paid">Lunas</option>
                    <option value="waived">Dibebaskan (Waived)</option>
                    <option value="draft">Draft Usulan</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>

                <select class="form-input" style="width: auto; padding: 0.45rem 0.75rem; font-size: 0.8125rem;" wire:model.live="type" aria-label="Jenis tagihan">
                    <option value="">Semua Jenis</option>
                    @foreach($chargeTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div wire:loading.delay role="status" style="font-size: 0.8125rem; color: #7f112e; margin-bottom: 0.5rem; font-weight: 600;">Memuat data tagihan…</div>

        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
            <table class="data-table charges-full-table" style="width: 100%; table-layout: auto;">
                <thead>
                    <tr>
                        <th style="white-space: nowrap; width: 110px; padding: 0.75rem 0.625rem;">No Tagihan</th>
                        <th style="white-space: nowrap; min-width: 130px; padding: 0.75rem 0.625rem;">Organization</th>
                        <th style="white-space: nowrap; width: 125px; padding: 0.75rem 0.625rem;">Jenis Tagihan</th>
                        <th style="min-width: 140px; padding: 0.75rem 0.625rem;">Barang Terkait</th>
                        <th style="white-space: nowrap; text-align: right; width: 100px; padding: 0.75rem 0.625rem;">Total Biaya</th>
                        <th style="white-space: nowrap; text-align: right; width: 105px; padding: 0.75rem 0.625rem;">Terkonfirmasi</th>
                        <th style="white-space: nowrap; text-align: right; width: 95px; padding: 0.75rem 0.625rem;">Sisa Tagihan</th>
                        <th style="white-space: nowrap; text-align: center; width: 135px; padding: 0.75rem 0.625rem;">Status</th>
                        <th style="white-space: nowrap; text-align: center; min-width: 130px; padding: 0.75rem 0.625rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charges as $charge)
                        @php($settlement = $charge->settlement)
                        <tr wire:key="personal-charge-{{ $charge->id }}">
                            <td style="padding: 0.75rem 0.625rem;">
                                <span class="micro-badge" style="font-family: monospace;">INV-{{ str_pad($charge->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td style="padding: 0.75rem 0.625rem;">
                                <strong style="color: #0f172a;">{{ $charge->organization->name }}</strong>
                            </td>
                            <td style="padding: 0.75rem 0.625rem;">
                                <span class="micro-badge {{ $charge->charge_type === 'late_fee' ? 'micro-badge-amber' : ($charge->charge_type === 'damage' ? 'micro-badge-blue' : 'micro-badge-red') }}">
                                    {{ $chargeTypes[$charge->charge_type] ?? $charge->charge_type }}
                                </span>
                            </td>
                            <td style="padding: 0.75rem 0.625rem;">
                                <div style="font-weight: 600; color: #1e293b;">{{ $charge->asset->name }}</div>
                                @if($charge->reason)
                                    <small style="color: #64748b; font-size: 0.7rem; display: block;">{{ $charge->reason }}</small>
                                @endif
                            </td>
                            <td style="white-space: nowrap; text-align: right; padding: 0.75rem 0.625rem; font-weight: 600;">
                                Rp{{ number_format($charge->amount, 0, ',', '.') }}
                            </td>
                            <td style="white-space: nowrap; text-align: right; padding: 0.75rem 0.625rem; color: #059669; font-weight: 600;">
                                Rp{{ number_format($charge->payments->where('status', 'confirmed')->sum('amount'), 0, ',', '.') }}
                            </td>
                            <td style="white-space: nowrap; text-align: right; padding: 0.75rem 0.625rem;">
                                <strong style="color: {{ $charge->balance > 0 ? '#b91c1c' : '#15803d' }};">
                                    {{ $charge->status === 'issued' ? 'Rp'.number_format($charge->balance, 0, ',', '.') : '—' }}
                                </strong>
                            </td>
                            <td style="text-align: center; padding: 0.75rem 0.625rem;">
                                <span class="micro-badge {{ in_array($settlement, ['paid', 'waived']) ? 'micro-badge-green' : (in_array($settlement, ['unpaid', 'partial']) ? 'micro-badge-amber' : '') }}">
                                    <span class="status-dot {{ in_array($settlement, ['paid', 'waived']) ? 'status-dot-green' : (in_array($settlement, ['unpaid', 'partial']) ? 'status-dot-amber' : '') }}"></span>
                                    {{ $settlements[$settlement] ?? $settlement }}
                                </span>
                            </td>
                            <td style="text-align: center; padding: 0.75rem 0.625rem;">
                                @if($charge->balance > 0)
                                    <button type="button" onclick="openChargeReceiptModal({{ $charge->id }}, 'INV-{{ str_pad($charge->id, 4, '0', STR_PAD_LEFT) }}', '{{ number_format($charge->balance, 0, ',', '.') }}')" class="pill-btn btn-primary-red btn-sm" style="white-space: nowrap; padding: 0.4rem 0.85rem; font-weight: 700;">
                                        Kirim Bukti Bayar
                                    </button>
                                @else
                                    <span class="micro-badge micro-badge-green" style="white-space: nowrap;">Lunas Terverifikasi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Tidak ada tagihan{{ $status || $type ? ' yang sesuai filter' : '' }}</p>
                                <p style="font-size: 0.75rem; margin: 0;">Tagihan denda keterlambatan atau kerusakan fisik akan tercatat di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($charges->hasPages())
            <div style="margin-top: 1.25rem;">{{ $charges->links() }}</div>
        @endif
    </section>

    <!-- SECTION 2: RIWAYAT PEMBAYARAN TRANSFER MANUAL -->
    <section class="panel-card" style="padding: 1.75rem; border-radius: 1.25rem;" aria-labelledby="payments-heading">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 id="payments-heading" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Riwayat Pembayaran Manual</h2>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Catatan bukti transfer manual yang Anda kirimkan ke Admin.</p>
            </div>
            
            <div>
                <select class="form-input" style="width: auto; padding: 0.45rem 0.75rem; font-size: 0.8125rem;" wire:model.live="paymentStatus" aria-label="Status pembayaran">
                    <option value="">Semua Pembayaran</option>
                    @foreach($paymentLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive" style="width: 100%; overflow-x: auto;">
            <table class="data-table" style="width: 100%; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem;">
                        <th style="padding: 0.75rem 1rem;">Waktu Transfer</th>
                        <th style="padding: 0.75rem 1rem;">Tagihan & Organization</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Nominal Bayar</th>
                        <th style="padding: 0.75rem 1rem;">Metode / Keterangan</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Status Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr wire:key="personal-payment-{{ $payment->id }}" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; color: #334155; white-space: nowrap;">
                                <strong>{{ $payment->paid_at ? $payment->paid_at->timezone('Asia/Jakarta')->format('d M Y') : '—' }}</strong>
                                <small style="display: block; color: #94a3b8;">{{ $payment->paid_at ? $payment->paid_at->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '' }}</small>
                            </td>
                            <td style="padding: 1rem;">
                                <strong style="color: #0f172a; display: block;">#{{ $payment->charge_id }} · {{ $chargeTypes[$payment->charge->charge_type] ?? 'Tagihan' }}</strong>
                                <small style="color: #64748b;">{{ $payment->charge->organization->name }} · {{ $payment->charge->asset->name }}</small>
                            </td>
                            <td style="padding: 1rem; font-weight: 800; color: #0f172a; text-align: right; white-space: nowrap;">
                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                            </td>
                            <td style="padding: 1rem; color: #64748b;">
                                <span>{{ $methods[$payment->method] ?? $payment->method }}</span>
                                @if($payment->notes || $payment->reference)
                                    <small style="display: block; color: #94a3b8;">Ref: {{ $payment->notes ?: $payment->reference }}</small>
                                @endif
                                @if($payment->rejection_reason || $payment->void_reason)
                                    <small style="display: block; color: #dc2626; font-weight: 600;">Alasan: {{ $payment->rejection_reason ?: $payment->void_reason }}</small>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center; white-space: nowrap;">
                                <span class="micro-badge {{ $payment->status === 'confirmed' ? 'micro-badge-green' : ($payment->status === 'pending' ? 'micro-badge-yellow' : 'micro-badge-red') }}">
                                    <span class="status-dot {{ $payment->status === 'confirmed' ? 'status-dot-green' : ($payment->status === 'pending' ? 'status-dot-amber' : 'status-dot-red') }}"></span>
                                    {{ $paymentLabels[$payment->status] ?? $payment->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada riwayat pembayaran{{ $paymentStatus ? ' dengan status ini' : '' }}</p>
                                <p style="font-size: 0.75rem; margin: 0;">Pembayaran manual yang dikonfirmasi Admin akan tercatat di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div style="margin-top: 1.25rem;">{{ $payments->links() }}</div>
        @endif
    </section>

    <!-- MODAL: UPLOAD RESI PEMBAYARAN MANUAL -->
    <div id="modalUploadReceipt" class="modal-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 520px;">
            <div class="modal-header">
                <div>
                    <span class="micro-badge micro-badge-blue" style="font-size: 0.6875rem;">PEMBAYARAN MANUAL ANTARBANK</span>
                    <h3 class="modal-title" style="margin-top: 0.25rem;">Kirim Bukti Transfer Manual</h3>
                </div>
                <button type="button" onclick="closeModal('modalUploadReceipt')" class="pill-btn btn-ghost" style="padding: 0.25rem 0.5rem;">✕</button>
            </div>
            <form onsubmit="handleReceiptSubmit(event)">
                <div class="modal-body">
                    <!-- Bank Account Info with Copy-to-Clipboard Button -->
                    <div style="background: #fdf2f4; border: 1px solid #fecdd6; border-radius: 0.875rem; padding: 1rem; margin-bottom: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.75rem; font-weight: 800; color: #7f112e; text-transform: uppercase;">Rekening Resmi Komunitas:</span>
                            <button type="button" onclick="copyBankAccount('8270192819')" class="pill-btn btn-primary-red btn-sm" style="font-size: 0.6875rem; padding: 0.2rem 0.625rem;">
                                📋 Salin Rekening
                            </button>
                        </div>
                        <div style="font-size: 1.125rem; font-weight: 900; color: #0f172a; font-family: monospace; letter-spacing: 0.05em;">
                            BCA: 827-019-2819
                        </div>
                        <div style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem;">
                            a.n. Kas Bendahara Organization
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8125rem;">No. Tagihan</label>
                            <input type="text" id="receiptInvoiceNo" class="form-input" value="INV-0001" readonly style="background: #f8fafc; font-family: monospace;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-size: 0.8125rem;">Nominal Ditransfer</label>
                            <input type="text" id="receiptAmount" class="form-input" value="Rp 50.000" readonly style="background: #f8fafc; font-weight: 800; color: #7f112e;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.8125rem;">Tanggal Transfer</label>
                        <input type="date" id="receiptDate" class="form-input" required>
                    </div>

                    <!-- File Upload with Live Image Preview -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-size: 0.8125rem;">Unggah Foto Struk / Bukti Mutasi Transfer</label>
                        <div style="border: 2px dashed #cbd5e1; border-radius: 0.75rem; padding: 1.25rem; text-align: center; background: #f8fafc; cursor: pointer;" onclick="document.getElementById('receiptFileInput').click()">
                            <input type="file" id="receiptFileInput" accept="image/*" style="display: none;" onchange="previewReceiptPhoto(this)" required>
                            <div id="receiptPlaceholder">
                                <svg width="32" height="32" fill="none" stroke="#64748b" viewBox="0 0 24 24" style="margin: 0 auto 0.5rem auto;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <div style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">Pilih file foto struk atau screenshot mobile banking</div>
                                <div style="font-size: 0.6875rem; color: #64748b; margin-top: 0.25rem;">Format JPG, PNG, WEBP (Maks. 5MB)</div>
                            </div>
                            <div id="receiptPreviewBox" style="display: none; align-items: center; justify-content: center; gap: 0.75rem;">
                                <img id="receiptPreviewImg" src="" style="max-height: 120px; border-radius: 0.5rem; border: 1px solid #cbd5e1; object-fit: contain;">
                                <div style="text-align: left; font-size: 0.75rem; color: #059669; font-weight: 700;">Foto struk berhasil dimuat ✓</div>
                            </div>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem; font-size: 0.75rem; color: #475467;">
                        ℹ️ Status tagihan akan berubah menjadi <strong>Menunggu Verifikasi Admin</strong>. Saldo Anda lunas setelah dicocokkan dengan mutasi rekening nyata.
                    </div>
                </div>
                <div class="modal-footer" style="display: flex; justify-content: space-between;">
                    <button type="button" onclick="closeModal('modalUploadReceipt')" class="pill-btn btn-secondary-light">Batal</button>
                    <button type="submit" class="pill-btn btn-primary-red" style="font-weight: 800;">Kirim Bukti Pembayaran →</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const todayISO = new Date().toISOString().split('T')[0];
            const dateInp = document.getElementById('receiptDate');
            if (dateInp) dateInp.value = todayISO;
        });

        function copyBankAccount(num) {
            navigator.clipboard.writeText(num).then(() => {
                if (typeof showToast === 'function') {
                    showToast('Nomor rekening ' + num + ' disalin ke clipboard! 📋', 'success');
                } else {
                    alert('Nomor rekening ' + num + ' disalin!');
                }
            }).catch(() => {
                if (typeof showToast === 'function') {
                    showToast('Nomor rekening: ' + num, 'info');
                } else {
                    alert('Nomor rekening: ' + num);
                }
            });
        }

        function openChargeReceiptModal(chargeId, invoiceNo, amountStr) {
            const invInp = document.getElementById('receiptInvoiceNo');
            const amtInp = document.getElementById('receiptAmount');
            if (invInp) invInp.value = invoiceNo;
            if (amtInp) amtInp.value = 'Rp ' + amountStr;
            if (typeof openModal === 'function') {
                openModal('modalUploadReceipt');
            } else {
                const m = document.getElementById('modalUploadReceipt');
                if (m) m.style.display = 'flex';
            }
        }

        function previewReceiptPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const placeholder = document.getElementById('receiptPlaceholder');
                    const previewBox = document.getElementById('receiptPreviewBox');
                    const previewImg = document.getElementById('receiptPreviewImg');
                    if (placeholder) placeholder.style.display = 'none';
                    if (previewBox) previewBox.style.display = 'flex';
                    if (previewImg) previewImg.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function handleReceiptSubmit(e) {
            e.preventDefault();
            if (typeof closeModal === 'function') {
                closeModal('modalUploadReceipt');
            } else {
                const m = document.getElementById('modalUploadReceipt');
                if (m) m.style.display = 'none';
            }
            if (typeof showToast === 'function') {
                showToast('Struk transfer berhasil dikirim! Menunggu verifikasi mutasi oleh Admin.', 'success');
            } else {
                alert('Struk transfer berhasil dikirim! Menunggu verifikasi mutasi oleh Admin.');
            }
        }
    </script>
</div>
