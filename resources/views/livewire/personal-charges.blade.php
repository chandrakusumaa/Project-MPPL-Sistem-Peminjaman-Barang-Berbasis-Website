<div class="oi-personal-charges" wire:poll.30s style="max-width: 72rem; margin: 0 auto; padding-bottom: 3rem;">
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
                Ringkasan kewajiban denda keterlambatan, biaya kerusakan, dan verifikasi pembayaran manual di seluruh komunitas.
            </p>
        </div>
        <div class="oi-head-actions">
            <a class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.5rem 1rem;" href="{{ route('personal.borrowings') }}">
                Lihat Peminjaman &rarr;
            </a>
        </div>
    </div>

    <!-- 4 STAT METRIC CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 0.5rem;">Total Tagihan</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em;">Rp{{ number_format($summary->total, 0, ',', '.') }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Tagihan resmi diterbitkan Admin</p>
        </div>

        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #059669; display: block; margin-bottom: 0.5rem;">Sudah Terkonfirmasi</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #059669; letter-spacing: -0.03em;">Rp{{ number_format($summary->paid, 0, ',', '.') }}</div>
            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;">Pembayaran diverifikasi lunas</p>
        </div>

        <div class="feature-card card-accent-red" style="padding: 1.5rem; border-radius: 1.25rem;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #dc2626; display: block; margin-bottom: 0.5rem;">Sisa Kewajiban</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #dc2626; letter-spacing: -0.03em;">Rp{{ number_format($summary->outstanding, 0, ',', '.') }}</div>
            <p style="font-size: 0.75rem; color: #64748b; margin: 0.25rem 0 0 0;">Jumlah yang belum lunas</p>
        </div>

        <div class="feature-card" style="padding: 1.5rem; border-radius: 1.25rem;">
            <span style="font-size: 0.8125rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 0.5rem;">Pembebasan (Waived)</span>
            <div style="font-size: 1.75rem; font-weight: 900; color: #334155; letter-spacing: -0.03em;">Rp{{ number_format($summary->credited, 0, ',', '.') }}</div>
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
    @if($pendingPayments > 0)
        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem; color: #92400e; font-size: 0.8125rem;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>
                Pembayaran transfer sebesar <strong>Rp{{ number_format($pendingPayments, 0, ',', '.') }}</strong> sedang menunggu verifikasi Admin komunitas. Sisa tagihan Anda akan berkurang secara otomatis begitu Admin mengonfirmasi resi transfer Anda.
            </span>
        </div>
    @endif

    <!-- SECTION 1: DAFTAR TAGIHAN -->
    <section class="feature-card" style="padding: 1.75rem; margin-bottom: 2.5rem;" aria-labelledby="charges-heading">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 id="charges-heading" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Daftar Tagihan & Denda</h2>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Rincian biaya denda dan status pelunasan.</p>
            </div>
            
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <select class="form-input" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.8125rem;" wire:model.live="status" aria-label="Status tagihan">
                    <option value="">Semua Status</option>
                    <option value="outstanding">Belum Lunas</option>
                    <option value="paid">Lunas</option>
                    <option value="waived">Dibebaskan (Waived)</option>
                    <option value="draft">Draft Usulan</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>

                <select class="form-input" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.8125rem;" wire:model.live="type" aria-label="Jenis tagihan">
                    <option value="">Semua Jenis</option>
                    @foreach($chargeTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div wire:loading.delay role="status" style="font-size: 0.8125rem; color: #dc2626; margin-bottom: 0.5rem; font-weight: 600;">Memuat tagihan…</div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem;">
                        <th style="padding: 0.75rem 1rem;">No & Jenis Biaya</th>
                        <th style="padding: 0.75rem 1rem;">Komunitas / Aset</th>
                        <th style="padding: 0.75rem 1rem;">Total Biaya</th>
                        <th style="padding: 0.75rem 1rem;">Terkonfirmasi</th>
                        <th style="padding: 0.75rem 1rem;">Sisa Tagihan</th>
                        <th style="padding: 0.75rem 1rem;">Status</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($charges as $charge)
                        @php($settlement = $charge->settlement)
                        <tr wire:key="personal-charge-{{ $charge->id }}" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;">
                                <strong style="color: #0f172a; display: block;">#{{ $charge->id }} · {{ $chargeTypes[$charge->charge_type] }}</strong>
                                <span style="font-size: 0.75rem; color: #64748b;">{{ $charge->reason }}</span>
                            </td>
                            <td style="padding: 1rem;">
                                <strong style="color: #334155; display: block;">{{ $charge->organization->name }}</strong>
                                <small style="color: #94a3b8;">{{ $charge->asset->name }}</small>
                            </td>
                            <td style="padding: 1rem; font-weight: 700; color: #0f172a;">Rp{{ number_format($charge->amount, 0, ',', '.') }}</td>
                            <td style="padding: 1rem; color: #059669; font-weight: 600;">Rp{{ number_format($charge->payments->where('status', 'confirmed')->sum('amount'), 0, ',', '.') }}</td>
                            <td style="padding: 1rem; font-weight: 800; color: #dc2626;">{{ $charge->status === 'issued' ? 'Rp'.number_format($charge->balance, 0, ',', '.') : '—' }}</td>
                            <td style="padding: 1rem;">
                                <span class="micro-badge {{ in_array($settlement, ['paid', 'waived']) ? 'micro-badge-green' : (in_array($settlement, ['unpaid', 'partial']) ? 'micro-badge-red' : '') }}">
                                    {{ $settlements[$settlement] }}
                                </span>
                            </td>
                            <td style="padding: 1rem; text-align: right; white-space: nowrap;">
                                @if($charge->status === 'issued' && $charge->balance > 0 && !$charge->organization->archived_at)
                                    <button type="button" class="btn-maroon" style="padding: 0.4rem 0.9rem; font-size: 0.75rem; font-weight: 700; border-radius: 9999px; border: none; cursor: pointer;" wire:click="openPaymentModal({{ $charge->id }})" wire:loading.attr="disabled">
                                        Bayar
                                    </button>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Tidak ada tagihan{{ $status || $type ? ' yang sesuai filter' : '' }}</p>
                                <p style="font-size: 0.75rem; margin: 0;">Tagihan denda keterlambatan atau kerusakan akan tercatat di sini.</p>
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
    <section class="feature-card" style="padding: 1.75rem;" aria-labelledby="payments-heading">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 id="payments-heading" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">Riwayat Pembayaran Manual</h2>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Catatan bukti transfer manual yang Anda kirimkan ke Admin.</p>
            </div>
            
            <div>
                <select class="form-input" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.8125rem;" wire:model.live="paymentStatus" aria-label="Status pembayaran">
                    <option value="">Semua Pembayaran</option>
                    @foreach($paymentLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem;">
                        <th style="padding: 0.75rem 1rem;">Waktu Transfer</th>
                        <th style="padding: 0.75rem 1rem;">Tagihan & Komunitas</th>
                        <th style="padding: 0.75rem 1rem;">Nominal Bayar</th>
                        <th style="padding: 0.75rem 1rem;">Metode / Keterangan</th>
                        <th style="padding: 0.75rem 1rem;">Status Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr wire:key="personal-payment-{{ $payment->id }}" style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem; color: #334155;">
                                <strong>{{ $payment->paid_at->timezone('Asia/Jakarta')->format('d M Y') }}</strong>
                                <small style="display: block; color: #94a3b8;">{{ $payment->paid_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</small>
                            </td>
                            <td style="padding: 1rem;">
                                <strong style="color: #0f172a; display: block;">#{{ $payment->charge_id }} · {{ $chargeTypes[$payment->charge->charge_type] }}</strong>
                                <small style="color: #64748b;">{{ $payment->charge->organization->name }} · {{ $payment->charge->asset->name }}</small>
                            </td>
                            <td style="padding: 1rem; font-weight: 800; color: #0f172a;">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td style="padding: 1rem; color: #64748b;">
                                <span>{{ $methods[$payment->method] }}</span>
                                @if($payment->notes || $payment->reference)
                                    <small style="display: block; color: #94a3b8;">{{ $payment->notes ?: $payment->reference }}</small>
                                @endif
                                @if($payment->evidence_path)
                                    <a href="{{ route('evidence', [$payment->charge_id, $payment->id]) }}" style="display: inline-block; margin-top: 0.25rem; font-size: 0.75rem; font-weight: 600; color: #80142B; text-decoration: underline;">Lihat Bukti ↗</a>
                                @endif
                                @if($payment->rejection_reason || $payment->void_reason)
                                    <small style="display: block; color: #dc2626; font-weight: 600;">Alasan: {{ $payment->rejection_reason ?: $payment->void_reason }}</small>
                                @endif
                            </td>
                            <td style="padding: 1rem;">
                                <span class="micro-badge {{ $payment->status === 'confirmed' ? 'micro-badge-green' : ($payment->status === 'pending' ? 'micro-badge-yellow' : 'micro-badge-red') }}">
                                    {{ $paymentLabels[$payment->status] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                                <p style="font-weight: 700; color: #0f172a; margin-bottom: 0.25rem;">Belum ada riwayat pembayaran{{ $paymentStatus ? ' dengan status ini' : '' }}</p>
                                <p style="font-size: 0.75rem; margin: 0;">Pembayaran yang dikonfirmasi Admin akan tercatat di sini.</p>
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
                        <span class="micro-badge" style="background: #eff6ff; color: #1d4ed8;">Tagihan #{{ $selectedCharge->id }} · Sisa Rp{{ number_format($selectedCharge->balance, 0, ',', '.') }}</span>
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
</div>
