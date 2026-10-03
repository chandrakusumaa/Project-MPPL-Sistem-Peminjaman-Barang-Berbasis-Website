<?php

use App\Models\Borrowing;
use App\Enums\BorrowingStatus;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public Borrowing $borrowing;

    public function mount(Borrowing $borrowing)
    {
        if ($borrowing->user_id !== auth()->id()) {
            abort(403);
        }
        
        $this->borrowing = $borrowing->load(['asset', 'organization', 'approvedBy', 'processedBy']);
    }

    public function getDaysLateProperty()
    {
        if ($this->borrowing->status === BorrowingStatus::OVERDUE && !$this->borrowing->returned_at) {
            return now()->startOfDay()->diffInDays($this->borrowing->due_date->startOfDay());
        }
        return 0;
    }

    public function getCurrentFineProperty()
    {
        return $this->daysLate * $this->borrowing->organization->late_fine_per_day;
    }

    public function cancelRequest(\App\Actions\Borrowing\CancelBorrowing $action)
    {
        try {
            $action->execute($this->borrowing, auth()->user());
            session()->flash('success', 'Pengajuan peminjaman berhasil dibatalkan.');
            $this->redirectRoute('my-borrowings.index', ['tab' => 'history'], navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}; ?>

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('my-borrowings.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 flex items-center">
                <x-flux::icon.arrow-left class="size-5 mr-2" />
                Kembali
            </a>
            
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full shadow-sm
                {{ $borrowing->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $borrowing->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $borrowing->status->value === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                {{ $borrowing->status->value === 'returned' ? 'bg-green-100 text-green-800' : '' }}
                {{ $borrowing->status->value === 'rejected' || $borrowing->status->value === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
            ">
                Status: {{ ucfirst($borrowing->status->value) }}
            </span>
        </div>

        @if (session()->has('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 md:flex items-center gap-6">
                @if($borrowing->asset->photo)
                    <img src="{{ Storage::url($borrowing->asset->photo) }}" class="w-24 h-24 rounded-lg object-cover shadow-sm hidden md:block">
                @else
                    <div class="w-24 h-24 rounded-lg bg-gray-100 dark:bg-gray-700 hidden md:flex items-center justify-center text-gray-400">
                        <x-flux::icon.photo class="size-8" />
                    </div>
                @endif
                
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $borrowing->asset->name }}</h3>
                    <p class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $borrowing->asset->code }}</p>
                    <p class="text-sm mt-2 text-gray-600 dark:text-gray-300">
                        Organisasi: <strong>{{ $borrowing->organization->name }}</strong>
                    </p>
                </div>
            </div>

            <div class="p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Peminjaman</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->borrow_date->format('d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Batas Pengembalian</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->due_date->format('d F Y') }}</dd>
                    </div>
                    
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alasan</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->reason }}</dd>
                    </div>

                    @if($borrowing->status->value === 'rejected')
                        <div class="sm:col-span-2 bg-red-50 p-4 rounded-md mt-2 border border-red-100">
                            <dt class="text-sm font-medium text-red-800">Alasan Penolakan</dt>
                            <dd class="mt-1 text-sm text-red-700">{{ $borrowing->rejection_reason }}</dd>
                            <dd class="mt-2 text-xs text-red-600">Oleh: {{ $borrowing->approvedBy?->name ?? '-' }} ({{ $borrowing->updated_at->format('d M Y H:i') }})</dd>
                        </div>
                    @endif

                    @if($borrowing->status->value === 'pending')
                        <div class="sm:col-span-2 pt-4 flex justify-end">
                            <button wire:click="cancelRequest" wire:confirm="Yakin ingin membatalkan pengajuan ini?" class="inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 transition-colors">
                                Batalkan Pengajuan
                            </button>
                        </div>
                    @endif

                    @if(in_array($borrowing->status->value, ['borrowed', 'overdue']))
                        <div class="sm:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-6 mt-2">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Berjalan</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Disetujui Oleh</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $borrowing->approvedBy?->name ?? '-' }} ({{ $borrowing->approved_at?->format('d M Y') }})</p>
                                </div>
                                
                                @if($borrowing->status->value === 'overdue')
                                    <div class="bg-red-50 border border-red-200 rounded-md p-3">
                                        <p class="text-xs text-red-600 font-semibold uppercase tracking-wider mb-1">Terlambat</p>
                                        <p class="text-lg font-bold text-red-700">{{ $this->daysLate }} Hari</p>
                                        @if($this->currentFine > 0)
                                            <p class="text-sm text-red-600 mt-1">Estimasi Denda: Rp {{ number_format($this->currentFine, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <p class="text-xs text-blue-600 font-semibold uppercase tracking-wider mb-1">Sisa Waktu</p>
                                        <p class="text-lg font-bold text-blue-700">{{ max(0, now()->startOfDay()->diffInDays($borrowing->due_date->startOfDay(), false)) }} Hari</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($borrowing->status->value === 'returned')
                        <div class="sm:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-6 mt-2">
                            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Hasil Inspeksi Pengembalian</h4>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-750 p-4 rounded-lg">
                                <div>
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Dikembalikan</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $borrowing->returned_at?->format('d F Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Kondisi Aset</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $borrowing->return_condition->value === 'good' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $borrowing->return_condition->value === 'minor_damage' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $borrowing->return_condition->value === 'major_damage' || $borrowing->return_condition->value === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $borrowing->return_condition->value)) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Denda / Biaya</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($borrowing->fine_amount, 0, ',', '.') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Diproses Oleh</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $borrowing->processedBy?->name ?? '-' }}</dd>
                                </div>
                                @if($borrowing->return_notes)
                                    <div class="sm:col-span-2 border-t border-gray-200 dark:border-gray-600 pt-3 mt-1">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Catatan Petugas</dt>
                                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $borrowing->return_notes }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
