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
            @php($firstPayableCharge = $charges->first(fn ($c) => $c->status === 'issued' && $c->balance > 0 && ! $c->organization->archived_at))
            @if($firstPayableCharge)
                <button type="button" wire:click="openPaymentModal({{ $firstPayableCharge->id }})" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.5rem 1.15rem; font-weight: 700; cursor: pointer;">
                    + Kirim Bukti Transfer
                </button>
            @endif
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

            <div style="flex: 1; min-width: 280px; max-width: 34rem; font-size: 0.8125rem; color: #9a3412; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 1rem; padding: 1.125rem; display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #7c2d12;">
                    Rekening Tujuan per Organization ({{ $outstandingByOrg->count() }} Organization)
                </div>
                @foreach($outstandingByOrg as $orgItem)
                    <div style="background: #ffffff; border: 1px solid #fed7aa; border-radius: 0.75rem; padding: 0.75rem 0.875rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <div style="min-width: 0;">
                            <div style="font-weight: 800; color: #0f172a; font-size: 0.8125rem;">
                                {{ $orgItem->organization->name }}
                                <span style="color: #c2410c; font-weight: 800; margin-left: 0.35rem;">(Rp{{ number_format($orgItem->outstanding, 0, ',', '.') }})</span>
                            </div>
                            @if($orgItem->organization->payment_account)
                                <div style="margin-top: 0.25rem; font-family: monospace; font-size: 0.8125rem; font-weight: 700; color: #7c2d12; word-break: break-word;">
                                    {{ $orgItem->organization->payment_account }}
                                </div>
                            @else
                                <div style="margin-top: 0.2rem; font-size: 0.75rem; color: #64748b;">
                                    Rekening belum diatur oleh Admin Organization — hubungi pengelola atau bayar tunai.
                                </div>
                            @endif
                        </div>
                        @if($orgItem->organization->payment_account)
                            <button type="button" onclick="copyBankAccount(@js($orgItem->organization->payment_account))" class="pill-btn btn-primary-red" style="font-size: 0.6875rem; padding: 0.3rem 0.7rem; cursor: pointer; flex-shrink: 0;">
                                Salin Rekening
                            </button>
                        @endif
                    </div>
                @endforeach
                <div style="font-size: 0.75rem; color: #7c2d12;">
                    Transfer sesuai nominal masing-masing Organization, lalu klik <strong>Bayar / Kirim Bukti</strong> pada baris tagihan terkait.
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
    @if(session('status'))
        <div role="status" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #166534; font-size: 0.875rem; font-weight: 600;">
            {{ session('status') }}
        </div>
    @endif
    @error('action')
        <div role="alert" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; color: #b91c1c; font-size: 0.875rem; font-weight: 600;">
            {{ $message }}
        </div>
    @enderror
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
                                @if($charge->status === 'issued' && $charge->balance > 0 && !$charge->organization->archived_at)
                                    <button type="button" wire:click="openPaymentModal({{ $charge->id }})" wire:loading.attr="disabled" class="pill-btn btn-primary-red btn-sm" style="white-space: nowrap; padding: 0.4rem 0.85rem; font-weight: 700; cursor: pointer;">
                                        Bayar / Kirim Bukti
                                    </button>
                                @elseif(in_array($settlement, ['paid', 'waived'], true))
                                    <span class="micro-badge micro-badge-green" style="white-space: nowrap;">Lunas Terverifikasi</span>
                                @else
                                    <span style="color: #94a3b8;">—</span>
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
                                @if($payment->evidence_path)
                                    <a href="{{ route('evidence', [$payment->charge_id, $payment->id]) }}" style="display: inline-block; margin-top: 0.25rem; font-size: 0.75rem; font-weight: 600; color: #80142B; text-decoration: underline;">Lihat Bukti ↗</a>
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

    @if($selectedCharge)
        <div wire:click.self="closePaymentModal" role="dialog" aria-modal="true" aria-labelledby="oi-personal-payment-title"
            style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(2px); z-index: 60; display: flex; align-items: center; justify-content: center; padding: 1.25rem; overflow-y: auto;">
            <div style="width: 100%; max-width: 36rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; box-shadow: 0 24px 50px rgba(15, 23, 42, 0.22); overflow: hidden;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <span class="micro-badge" style="background: #eff6ff; color: #1d4ed8;">INV-{{ str_pad($selectedCharge->id, 4, '0', STR_PAD_LEFT) }} · Sisa Rp{{ number_format($selectedCharge->balance, 0, ',', '.') }}</span>
                        <h2 id="oi-personal-payment-title" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0 0.25rem 0;">Kirim Pembayaran Tagihan</h2>
                        <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">{{ $selectedCharge->organization->name }} · Aset: <strong style="color: #0f172a;">{{ $selectedCharge->asset?->name ?? '-' }}</strong></p>
                    </div>
                    <button type="button" wire:click="closePaymentModal" aria-label="Tutup popup"
                        style="background: #f1f5f9; color: #334155; border: none; width: 32px; height: 32px; border-radius: 9999px; font-weight: 700; cursor: pointer; flex-shrink: 0;">✕</button>
                </div>

                <div style="padding: 1.25rem 1.5rem 1.5rem 1.5rem;">
                    @if($errors->any())
                        <div class="errors" role="alert" style="margin-bottom: 1rem;">
                            @foreach($errors->all() as $err)
                                <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.875rem 1rem; margin-bottom: 1rem;">
                        <strong style="display: block; font-size: 0.8125rem; color: #0f172a; margin-bottom: 0.25rem;">Rincian Tagihan</strong>
                        <p style="font-size: 0.8125rem; color: #334155; margin: 0 0 0.35rem 0;">{{ $selectedCharge->reason }}</p>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Sisa tagihan saat ini: <strong style="color: #dc2626;">Rp{{ number_format($selectedCharge->balance, 0, ',', '.') }}</strong>. Pembayaran akan berstatus menunggu verifikasi hingga Admin mengonfirmasinya.</p>
                    </div>

                    <div style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 0.75rem; padding: 0.875rem 1rem; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <div style="min-width: 0;">
                            <span style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #9a3412;">Rekening Tujuan · {{ $selectedCharge->organization->name }}</span>
                            @if($selectedCharge->organization->payment_account)
                                <div style="font-family: monospace; font-size: 0.875rem; font-weight: 800; color: #0f172a; margin-top: 0.2rem; word-break: break-word;">
                                    {{ $selectedCharge->organization->payment_account }}
                                </div>
                            @else
                                <div style="font-size: 0.78rem; color: #7c2d12; margin-top: 0.2rem;">
                                    Admin Organization ini belum mengisi rekening bank. Silakan hubungi pengelola atau pilih metode Tunai.
                                </div>
                            @endif
                        </div>
                        @if($selectedCharge->organization->payment_account)
                            <button type="button" onclick="copyBankAccount(@js($selectedCharge->organization->payment_account))" class="pill-btn btn-primary-red" style="font-size: 0.6875rem; padding: 0.3rem 0.7rem; cursor: pointer; flex-shrink: 0;">
                                Salin Rekening
                            </button>
                        @endif
                    </div>

                    <form wire:submit="submitPayment">
                        <div class="grid two">
                            <label>Nominal Pembayaran (Rp)
                                <input type="number" min="1" max="{{ max(1, (int) $selectedCharge->balance) }}" wire:model="amount" required>
                            </label>
                            <label>Metode Pembayaran
                                <select wire:model.live="method">
                                    <option value="bank_transfer">Transfer Bank Manual</option>
                                    <option value="cash">Tunai (Cash)</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </label>
                        </div>

                        <label>Nomor Referensi / Nama Pengirim {{ $method === 'bank_transfer' ? '(wajib jika tidak unggah bukti)' : '(opsional)' }}
                            <input wire:model="reference" placeholder="Contoh: BCA a.n Nadia / No. Ref 123456">
                        </label>

                        <label>Bukti Pembayaran (JPG, PNG, atau PDF maks 2 MB)
                            <input type="file" wire:model="evidence" accept="image/jpeg,image/png,application/pdf">
                        </label>

                        <label>Catatan Pembayaran {{ $method === 'bank_transfer' ? '(opsional)' : '' }}
                            <textarea wire:model="paymentNotes" rows="2" placeholder="Contoh: Sudah transfer lunas / diserahkan tunai ke pengelola..."></textarea>
                        </label>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.25rem;">
                            <button type="button" class="secondary" wire:click="closePaymentModal">Batal</button>
                            <button type="submit" class="primary" wire:loading.attr="disabled" wire:target="submitPayment,evidence">
                                <span wire:loading.remove wire:target="submitPayment">Kirim Pembayaran</span>
                                <span wire:loading wire:target="submitPayment">Mengirim...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        function copyBankAccount(num) {
            navigator.clipboard.writeText(num).then(() => {
                if (typeof showToast === 'function') {
                    showToast('Nomor rekening ' + num + ' disalin ke clipboard!', 'success');
                }
            }).catch(() => {});
        }
    </script>
</div>
