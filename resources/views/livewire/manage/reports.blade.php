<?php

use Livewire\Volt\Component;
use App\Models\Organization;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\DamageReport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Gate;
use function Livewire\Volt\layout;

layout('components.layouts.manage');

new class extends Component {
    public Organization $organization;

    // Filter states
    public $borrowingDateStart;
    public $borrowingDateEnd;
    public $borrowingStatus = '';

    public $damageDateStart;
    public $damageDateEnd;

    public function mount(Organization $organization)
    {
        if (\Illuminate\Support\Facades\Gate::denies('manageReports', $organization)) {
            $userId = auth()->id();
            $orgId = $organization->id;
            throw new \Exception("Gate denied in component! User: {$userId}, Org: {$orgId}");
        }
        Gate::authorize('manageReports', $organization);
        
        $this->organization = $organization;
        
        // Default to last 30 days
        $this->borrowingDateStart = now()->subDays(30)->format('Y-m-d');
        $this->borrowingDateEnd = now()->format('Y-m-d');
        $this->damageDateStart = now()->subDays(30)->format('Y-m-d');
        $this->damageDateEnd = now()->format('Y-m-d');
    }

    public function exportAssets()
    {
        try {
            Gate::authorize('manageReports', $this->organization);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            throw new \Exception("Auth Exception in exportAssets! User: " . auth()->id() . " Org: " . $this->organization->id);
        }

        $filename = "assets_{$this->organization->slug}_" . now()->format('Ymd_His') . ".csv";

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Kode Aset', 'Nama Aset', 'Kategori', 'Lokasi', 'Status', 'Tanggal Beli']);

            Asset::where('organization_id', $this->organization->id)
                ->with('category')
                ->chunk(100, function ($assets) use ($handle) {
                    foreach ($assets as $asset) {
                        fputcsv($handle, [
                            $asset->code,
                            $asset->name,
                            $asset->category->name ?? '-',
                            $asset->location ?? '-',
                            $asset->status->label(),
                            $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '-'
                        ]);
                    }
                });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportBorrowings()
    {
        Gate::authorize('manageReports', $this->organization);

        $filename = "borrowings_{$this->organization->slug}_" . now()->format('Ymd_His') . ".csv";

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['ID', 'Aset', 'Peminjam', 'Tanggal Pinjam', 'Jatuh Tempo', 'Tanggal Kembali', 'Status', 'Kondisi Kembali', 'Denda']);

            $query = Borrowing::where('organization_id', $this->organization->id)
                ->with(['asset', 'user']);

            if ($this->borrowingDateStart) {
                $query->whereDate('borrow_date', '>=', $this->borrowingDateStart);
            }
            if ($this->borrowingDateEnd) {
                $query->whereDate('borrow_date', '<=', $this->borrowingDateEnd);
            }
            if ($this->borrowingStatus) {
                $query->where('status', $this->borrowingStatus);
            }

            $query->chunk(100, function ($borrowings) use ($handle) {
                foreach ($borrowings as $b) {
                    fputcsv($handle, [
                        $b->id,
                        $b->asset->name,
                        $b->user->name,
                        $b->borrow_date->format('Y-m-d'),
                        $b->due_date->format('Y-m-d'),
                        $b->returned_at ? $b->returned_at->format('Y-m-d H:i') : '-',
                        $b->status->label(),
                        $b->return_condition ? $b->return_condition->label() : '-',
                        $b->fine_amount > 0 ? "Rp " . number_format($b->fine_amount, 0, ',', '.') : '-'
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportDamageReports()
    {
        Gate::authorize('manageReports', $this->organization);

        $filename = "damage_reports_{$this->organization->slug}_" . now()->format('Ymd_His') . ".csv";

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['ID', 'Aset', 'Pelapor', 'Tanggal Lapor', 'Deskripsi', 'Tingkat Keparahan', 'Status', 'Tanggal Selesai']);

            $query = DamageReport::where('organization_id', $this->organization->id)
                ->with(['asset', 'reporter']);

            if ($this->damageDateStart) {
                $query->whereDate('created_at', '>=', $this->damageDateStart);
            }
            if ($this->damageDateEnd) {
                $query->whereDate('created_at', '<=', $this->damageDateEnd);
            }

            $query->chunk(100, function ($reports) use ($handle) {
                foreach ($reports as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->asset->name,
                        $r->reporter->name,
                        $r->created_at->format('Y-m-d H:i'),
                        $r->description,
                        $r->severity ? $r->severity->label() : '-',
                        $r->status->label(),
                        $r->resolved_at ? $r->resolved_at->format('Y-m-d H:i') : '-'
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}; ?>

<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-4">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Laporan & Ekspor</h1>
                    <p class="text-sm text-zinc-500 mt-1">Ekspor data organisasi dalam format CSV (bisa dibuka dengan Excel/Spreadsheet).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Assets Report -->
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-lg">
                            <flux:icon.cube class="size-6" />
                        </div>
                        <h3 class="font-bold text-lg text-zinc-900 dark:text-zinc-100">Daftar Aset</h3>
                    </div>
                    <p class="text-sm text-zinc-500 mb-6 flex-1">Ekspor seluruh data aset yang terdaftar dalam organisasi beserta kategorinya.</p>
                    
                    <flux:button wire:click="exportAssets" icon="document-arrow-down" variant="primary" class="w-full">
                        Ekspor CSV
                    </flux:button>
                </div>

                <!-- Borrowings Report -->
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg">
                            <flux:icon.clipboard-document-list class="size-6" />
                        </div>
                        <h3 class="font-bold text-lg text-zinc-900 dark:text-zinc-100">Riwayat Peminjaman</h3>
                    </div>
                    <p class="text-sm text-zinc-500 mb-4">Ekspor data peminjaman berdasarkan filter tanggal peminjaman dan status.</p>
                    
                    <div class="space-y-3 mb-6 flex-1">
                        <div class="grid grid-cols-2 gap-3">
                            <flux:input type="date" wire:model="borrowingDateStart" label="Dari" size="sm" />
                            <flux:input type="date" wire:model="borrowingDateEnd" label="Sampai" size="sm" />
                        </div>
                        <flux:select wire:model="borrowingStatus" label="Status" size="sm">
                            <flux:select.option value="">Semua Status</flux:select.option>
                            <flux:select.option value="pending">Pending</flux:select.option>
                            <flux:select.option value="borrowed">Dipinjam</flux:select.option>
                            <flux:select.option value="overdue">Overdue</flux:select.option>
                            <flux:select.option value="returned">Dikembalikan</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:button wire:click="exportBorrowings" icon="document-arrow-down" variant="primary" class="w-full">
                        Ekspor CSV
                    </flux:button>
                </div>

                <!-- Damage Reports -->
                <div class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 rounded-lg">
                            <flux:icon.exclamation-triangle class="size-6" />
                        </div>
                        <h3 class="font-bold text-lg text-zinc-900 dark:text-zinc-100">Laporan Kerusakan</h3>
                    </div>
                    <p class="text-sm text-zinc-500 mb-4">Ekspor data riwayat pelaporan kerusakan beserta status perbaikannya.</p>
                    
                    <div class="space-y-3 mb-6 flex-1">
                        <div class="grid grid-cols-2 gap-3">
                            <flux:input type="date" wire:model="damageDateStart" label="Dari" size="sm" />
                            <flux:input type="date" wire:model="damageDateEnd" label="Sampai" size="sm" />
                        </div>
                    </div>

                    <flux:button wire:click="exportDamageReports" icon="document-arrow-down" variant="primary" class="w-full">
                        Ekspor CSV
                    </flux:button>
                </div>
            </div>
            
        </div>
    </div>
</div>
