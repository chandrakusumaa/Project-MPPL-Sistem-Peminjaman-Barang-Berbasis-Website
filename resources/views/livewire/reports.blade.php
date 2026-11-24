<div>
    <x-room.frame :org="$org" :operator="true" :admin="true" tab="reports" :livewire-navigation="false">
        <div style="max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="oi-page-head" style="margin-bottom: 0;">
                <div>
                    <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin: 0;">Laporan Organization {{ $org->name }}</h1>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0;">Filter data transaksi dan unduh berkas CSV untuk keperluan audit & LPJ.</p>
                </div>
            </div>

            <section class="panel-card oi-panel">
                <div class="panel-header oi-panel-head">
                    <h2 class="panel-title">Filter Rentang Tanggal & Status</h2>
                </div>
                <div class="panel-body oi-panel-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
                        <label>
                            <span style="font-weight: 700; font-size: 0.875rem;">Dari Tanggal</span>
                            <input type="date" class="oi-input" wire:model.live="from" style="width: 100%; margin-top: 0.375rem;">
                        </label>
                        <label>
                            <span style="font-weight: 700; font-size: 0.875rem;">Sampai Tanggal</span>
                            <input type="date" class="oi-input" wire:model.live="to" style="width: 100%; margin-top: 0.375rem;">
                        </label>
                        <label>
                            <span style="font-weight: 700; font-size: 0.875rem;">Status Transaksi</span>
                            <input class="oi-input" wire:model.live="status" placeholder="Contoh: confirmed" style="width: 100%; margin-top: 0.375rem;">
                        </label>
                    </div>
                    <div style="padding: 0.875rem 1.25rem; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0; font-size: 0.8125rem; color: #64748b; margin-bottom: 1.5rem;">
                        <strong>Catatan:</strong> Hanya pembayaran berstatus <strong>confirmed</strong> yang dihitung sebagai penerimaan kas bersih. Seluruh histori transaksi dapat diunduh dalam berkas CSV terpisah di bawah ini.
                    </div>

                    <h3 style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin: 0 0 0.875rem 0; text-transform: uppercase; letter-spacing: 0.04em;">Unduh Berkas Laporan CSV</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.625rem;">
                        @foreach(['assets'=>'Daftar Aset Fisik','borrowings'=>'Riwayat Peminjaman','damage'=>'Laporan Kerusakan','charges'=>'Tagihan & Saldo','payments'=>'Riwayat Pembayaran','credits'=>'Pembebasan Biaya'] as $key=>$title)
                            <a class="oi-btn oi-btn-secondary oi-btn-sm" style="border-radius: 9999px;" href="{{ route('export',[$org,$key]+array_filter(['from'=>$from,'to'=>$to,'status'=>$status])) }}">
                                Unduh {{ $title }} ↓
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </x-room.frame>
</div>
