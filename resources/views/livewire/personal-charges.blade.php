<div class="oi-personal-charges" wire:poll.30s>
    @php
        $chargeTypes = ['late_fee' => 'Denda keterlambatan', 'damage' => 'Biaya kerusakan', 'loss' => 'Penggantian kehilangan'];
        $settlements = ['unpaid' => 'Belum dibayar', 'partial' => 'Sebagian dibayar', 'paid' => 'Lunas', 'waived' => 'Dibebaskan', 'draft' => 'Draft', 'cancelled' => 'Dibatalkan'];
        $paymentLabels = ['pending' => 'Menunggu verifikasi', 'confirmed' => 'Terkonfirmasi', 'rejected' => 'Ditolak', 'voided' => 'Dibatalkan'];
        $methods = ['cash' => 'Tunai', 'bank_transfer' => 'Transfer bank', 'other' => 'Lainnya'];
    @endphp
    <div class="oi-page-head"><div><h1>Tagihan Saya</h1><p>Ringkasan tagihan, denda, dan riwayat pembayaranmu di semua Organization.</p></div><a class="oi-btn oi-secondary" href="{{ route('personal.borrowings') }}">Lihat peminjaman →</a></div>
    <div class="oi-stats oi-finance-stats">
        <x-room.stat label="Total tagihan" :value="$summary->total" prefix="Rp" note="Tagihan yang sudah diterbitkan" icon="layers" />
        <x-room.stat label="Sudah dibayar" :value="$summary->paid" prefix="Rp" note="Pembayaran terkonfirmasi" icon="check" />
        <x-room.stat label="Sisa tagihan" :value="$summary->outstanding" prefix="Rp" note="Jumlah yang belum lunas" icon="clock" />
        <x-room.stat label="Pembebasan" :value="$summary->credited" prefix="Rp" note="Pengurangan tagihan oleh pengelola" icon="report" />
    </div>
    @if($pendingPayments > 0)<div class="oi-payment-notice" role="status"><x-icon name="clock" /><span>Pembayaran <strong>Rp{{ number_format($pendingPayments, 0, ',', '.') }}</strong> menunggu verifikasi pengelola. Sisa tagihan berkurang setelah pembayaran dikonfirmasi.</span></div>@endif
    <section class="oi-panel" aria-labelledby="charges-heading">
        <div class="oi-panel-head"><div><h2 id="charges-heading">Daftar tagihan & denda</h2><p class="oi-panel-caption">Lihat keterangan serta status pembayaran setiap tagihan.</p></div></div>
        <div class="oi-filters">
            <label>Status tagihan<select class="oi-select" aria-label="Status tagihan" wire:model.live="status"><option value="">Semua status</option><option value="outstanding">Belum lunas</option><option value="paid">Lunas</option><option value="waived">Dibebaskan</option><option value="draft">Draft</option><option value="cancelled">Dibatalkan</option></select></label>
            <label>Jenis tagihan<select class="oi-select" aria-label="Jenis tagihan" wire:model.live="type"><option value="">Semua jenis</option>@foreach($chargeTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
        </div>
        <div wire:loading.delay role="status" class="oi-loading">Memuat tagihan…</div>
        <div class="oi-table-wrap"><table class="oi-table oi-charges-table"><thead><tr><th>Tagihan & keterangan</th><th>Organization / aset</th><th>Nominal</th><th>Dibayar</th><th>Sisa</th><th>Status</th></tr></thead><tbody>
            @forelse($charges as $charge)
                @php($settlement = $charge->settlement)
                <tr wire:key="personal-charge-{{ $charge->id }}">
                    <td><div class="oi-table-title">#{{ $charge->id }} · {{ $chargeTypes[$charge->charge_type] }}</div><p class="oi-charge-description">{{ $charge->reason }}</p><details class="oi-charge-details"><summary>Dasar tagihan</summary><p>{{ $charge->policy_basis }}</p>@foreach($charge->credits as $credit)<p>Pembebasan Rp{{ number_format($credit->amount, 0, ',', '.') }}: {{ $credit->reason }}</p>@endforeach @if($charge->cancellation_reason)<p>Alasan pembatalan: {{ $charge->cancellation_reason }}</p>@endif</details></td>
                    <td><div class="oi-table-title">{{ $charge->organization->name }}</div><div class="oi-table-sub">{{ $charge->asset->name }}</div></td>
                    <td class="oi-money">Rp{{ number_format($charge->amount, 0, ',', '.') }}</td><td class="oi-money">Rp{{ number_format($charge->payments->where('status', 'confirmed')->sum('amount'), 0, ',', '.') }}</td>
                    <td class="oi-money">{{ $charge->status === 'issued' ? 'Rp'.number_format($charge->balance, 0, ',', '.') : '—' }}</td>
                    <td><span class="oi-tag {{ in_array($settlement, ['paid', 'waived']) ? 'oi-tag-green' : (in_array($settlement, ['unpaid', 'partial']) ? 'oi-tag-amber' : '') }}">{{ $settlements[$settlement] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="oi-empty"><x-icon name="layers" :size="28" /><h3>Belum ada tagihan{{ $status || $type ? ' yang sesuai filter' : '' }}</h3><p>Tagihan denda, kerusakan, atau kehilangan akan tampil beserta keterangannya.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
        @if($charges->hasPages())<div class="oi-pagination">{{ $charges->links() }}</div>@endif
    </section>
    <section class="oi-panel" aria-labelledby="payments-heading">
        <div class="oi-panel-head"><div><h2 id="payments-heading">Riwayat pembayaran</h2><p class="oi-panel-caption">Pembayaran terkonfirmasi, menunggu verifikasi, ditolak, dan dibatalkan.</p></div></div>
        <div class="oi-filters"><label>Status pembayaran<select class="oi-select" aria-label="Status pembayaran" wire:model.live="paymentStatus"><option value="">Semua pembayaran</option>@foreach($paymentLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label></div>
        <div class="oi-table-wrap"><table class="oi-table oi-payments-table"><thead><tr><th>Tanggal pembayaran</th><th>Tagihan / Organization</th><th>Nominal</th><th>Metode & keterangan</th><th>Status</th></tr></thead><tbody>
            @forelse($payments as $payment)
                <tr wire:key="personal-payment-{{ $payment->id }}"><td>{{ $payment->paid_at->timezone('Asia/Jakarta')->format('d M Y') }}<div class="oi-table-sub">{{ $payment->paid_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</div></td><td><div class="oi-table-title">#{{ $payment->charge_id }} · {{ $chargeTypes[$payment->charge->charge_type] }}</div><div class="oi-table-sub">{{ $payment->charge->organization->name }} · {{ $payment->charge->asset->name }}</div></td><td class="oi-money">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td><td>{{ $methods[$payment->method] }}<p class="oi-charge-description">{{ $payment->notes ?: $payment->reference }}</p>@if($payment->rejection_reason || $payment->void_reason)<div class="oi-table-sub">{{ $payment->rejection_reason ?: $payment->void_reason }}</div>@endif</td><td><span class="oi-tag {{ $payment->status === 'confirmed' ? 'oi-tag-green' : ($payment->status === 'pending' ? 'oi-tag-amber' : 'oi-tag-red') }}">{{ $paymentLabels[$payment->status] }}</span></td></tr>
            @empty
                <tr><td colspan="5"><div class="oi-empty"><x-icon name="clock" :size="28" /><h3>Belum ada pembayaran{{ $paymentStatus ? ' dengan status ini' : '' }}</h3><p>Pembayaran yang dicatat pengelola Organization akan muncul di sini.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
        @if($payments->hasPages())<div class="oi-pagination">{{ $payments->links() }}</div>@endif
    </section>
</div>
