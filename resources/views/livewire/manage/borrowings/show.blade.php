<?php

use App\Actions\Borrowing\ApproveBorrowing;
use App\Actions\Borrowing\RejectBorrowing;
use App\Actions\Borrowing\ProcessReturn;
use App\Models\Borrowing;
use App\Models\Organization;
use App\Enums\BorrowingStatus;
use Livewire\Volt\Component;

new class extends Component {
    public Organization $organization;
    public Borrowing $borrowing;

    // Reject Form
    public string $rejectionReason = '';

    // Return Form
    public string $returnCondition = 'good'; // good, minor_damage, major_damage, lost
    public string $returnNotes = '';
    public int $fineAmount = 0;

    public function mount(Organization $organization, Borrowing $borrowing)
    {
        if ($borrowing->organization_id !== $organization->id) {
            abort(404);
        }

        $this->organization = $organization;
        $this->borrowing = $borrowing->load(['asset', 'user', 'approvedBy', 'processedBy']);
        
        // Auto hitung denda default
        $this->fineAmount = $this->calculateFine();
    }

    public function getDaysLateProperty()
    {
        if (in_array($this->borrowing->status, [BorrowingStatus::BORROWED, BorrowingStatus::OVERDUE])) {
            $days = now()->startOfDay()->diffInDays($this->borrowing->due_date->startOfDay(), false);
            return $days < 0 ? abs($days) : 0;
        }
        return 0;
    }

    public function calculateFine()
    {
        return $this->daysLate * $this->organization->late_fine_per_day;
    }

    public function approve(ApproveBorrowing $action)
    {
        try {
            $action->execute($this->borrowing, auth()->user());
            session()->flash('success', 'Peminjaman disetujui.');
            $this->redirectRoute('manage.borrowings.show', ['organization' => $this->organization->slug, 'borrowing' => $this->borrowing->id], navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function reject(RejectBorrowing $action)
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5'
        ]);

        try {
            $action->execute($this->borrowing, auth()->user(), $this->rejectionReason);
            session()->flash('success', 'Peminjaman ditolak.');
            $this->redirectRoute('manage.borrowings.show', ['organization' => $this->organization->slug, 'borrowing' => $this->borrowing->id], navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function processReturn(ProcessReturn $action)
    {
        $this->validate([
            'returnCondition' => 'required|string',
            'returnNotes' => 'required_unless:returnCondition,good|string|nullable',
            'fineAmount' => 'required|numeric|min:0'
        ]);

        try {
            $action->execute($this->borrowing, auth()->user(), [
                'return_condition' => $this->returnCondition,
                'return_notes' => $this->returnNotes,
                'fine_amount' => $this->fineAmount,
            ]);
            
            session()->flash('success', 'Pengembalian aset berhasil diproses.');
            $this->redirectRoute('manage.borrowings.show', ['organization' => $this->organization->slug, 'borrowing' => $this->borrowing->id], navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}; ?>

<x-layouts.manage>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('manage.borrowings.index', ['organization' => $organization->slug, 'tab' => $borrowing->status->value]) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <x-flux::icon.arrow-left class="size-5" />
                </a>
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Detail Peminjaman</h2>
                </div>
            </div>
            
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

        @if (session()->has('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Info Aset & Peminjam -->
            <div class="lg:col-span-2 space-y-6">
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
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $borrowing->asset->name }}</h3>
                            <p class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $borrowing->asset->code }}</p>
                            <p class="text-sm mt-2 text-gray-600 dark:text-gray-300">
                                Kategori: <strong>{{ $borrowing->asset->category->name }}</strong>
                            </p>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Pengajuan</h4>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Peminjam</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ $borrowing->user->name }}</dd>
                                <dd class="text-xs text-gray-500">{{ $borrowing->user->email }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pengajuan</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->created_at->format('d M Y H:i') }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Peminjaman</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->borrow_date->format('d M Y') }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Batas Pengembalian</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->due_date->format('d M Y') }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alasan</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->reason }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($borrowing->status->value === 'returned')
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Hasil Inspeksi</h4>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Kondisi Saat Dikembalikan</dt>
                                <dd class="text-sm font-medium mt-1">
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
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Tanggal Pengembalian</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->returned_at->format('d M Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Denda</dt>
                                <dd class="mt-1 text-sm text-red-600 font-bold">Rp {{ number_format($borrowing->fine_amount, 0, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Diproses Oleh</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->processedBy?->name ?? '-' }}</dd>
                            </div>
                            @if($borrowing->return_notes)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">Catatan Inspeksi</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $borrowing->return_notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
                
                @if($borrowing->status->value === 'rejected')
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                        <h4 class="text-lg font-medium text-red-800 dark:text-red-400 mb-2">Penolakan</h4>
                        <p class="text-sm text-red-700 dark:text-red-300 mb-2">{{ $borrowing->rejection_reason }}</p>
                        <p class="text-xs text-red-600 dark:text-red-400">Oleh: {{ $borrowing->approvedBy?->name ?? '-' }} ({{ $borrowing->updated_at->format('d M Y H:i') }})</p>
                    </div>
                @endif
            </div>

            <!-- Kolom Eksekusi (Sidebar Kanan) -->
            <div class="space-y-6">
                
                @if(in_array($borrowing->status->value, ['borrowed', 'overdue']))
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Berjalan</h4>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Disetujui Oleh</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $borrowing->approvedBy?->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $borrowing->approved_at?->format('d M Y H:i') }}</p>
                            </div>
                            
                            @if($borrowing->status->value === 'overdue')
                                <div class="bg-red-50 border border-red-200 rounded-md p-3">
                                    <p class="text-xs text-red-600 font-semibold uppercase tracking-wider mb-1">Terlambat</p>
                                    <p class="text-lg font-bold text-red-700">{{ $this->daysLate }} Hari</p>
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

                @if($borrowing->status->value === 'pending')
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Aksi Pengajuan</h4>
                        
                        <div class="mb-4">
                            @if($borrowing->asset->status->value === 'available')
                                <button wire:click="approve" wire:confirm="Yakin ingin menyetujui peminjaman ini?" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Approve Peminjaman
                                </button>
                            @else
                                <div class="p-3 bg-red-50 text-red-700 text-sm rounded-md mb-2">
                                    Aset sedang tidak tersedia (Status: {{ $borrowing->asset->status->value }}). Anda tidak dapat menyetujui pengajuan ini.
                                </div>
                            @endif
                        </div>
                        
                        <hr class="my-4 border-gray-200 dark:border-gray-700">
                        
                        <form wire:submit="reject">
                            <div class="mb-3">
                                <x-input-label for="rejectionReason" value="Alasan Penolakan" />
                                <textarea wire:model="rejectionReason" id="rejectionReason" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-md shadow-sm" required placeholder="Minimal 5 karakter"></textarea>
                                <x-input-error :messages="$errors->get('rejectionReason')" class="mt-2" />
                            </div>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Reject Pengajuan
                            </button>
                        </form>
                    </div>
                @endif

                @if(in_array($borrowing->status->value, ['borrowed', 'overdue']))
                    <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Proses Pengembalian</h4>
                        
                        <form wire:submit="processReturn">
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="returnCondition" value="Kondisi Aset" />
                                    <select wire:model="returnCondition" id="returnCondition" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-md shadow-sm" required>
                                        <option value="good">Good (Baik)</option>
                                        <option value="minor_damage">Minor Damage (Rusak Ringan)</option>
                                        <option value="major_damage">Major Damage (Rusak Berat)</option>
                                        <option value="lost">Lost (Hilang)</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('returnCondition')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="returnNotes" value="Catatan Inspeksi" />
                                    <textarea wire:model="returnNotes" id="returnNotes" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 rounded-md shadow-sm" placeholder="Wajib jika kondisi tidak baik"></textarea>
                                    <x-input-error :messages="$errors->get('returnNotes')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="fineAmount" value="Denda Keterlambatan / Ganti Rugi (Rp)" />
                                    <x-text-input wire:model="fineAmount" id="fineAmount" type="number" class="mt-1 block w-full text-red-600 font-bold" min="0" required />
                                    <x-input-error :messages="$errors->get('fineAmount')" class="mt-2" />
                                    @if($this->daysLate > 0)
                                        <p class="text-xs text-gray-500 mt-1">Sistem menyarankan Rp {{ number_format($this->daysLate * $organization->late_fine_per_day, 0, ',', '.') }} berdasarkan keterlambatan {{ $this->daysLate }} hari x Rp {{ number_format($organization->late_fine_per_day, 0, ',', '.') }}. Anda dapat mengubahnya.</p>
                                    @endif
                                </div>

                                <button type="submit" wire:confirm="Konfirmasi hasil inspeksi dan selesaikan peminjaman ini?" class="w-full inline-flex justify-center items-center px-4 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Konfirmasi & Selesai
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.manage>
