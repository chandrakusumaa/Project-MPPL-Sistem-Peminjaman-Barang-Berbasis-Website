<?php

use App\Models\Asset;
use App\Models\Organization;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    public Organization $organization;
    public Asset $asset;

    public $borrow_date;
    public $due_date;
    public $reason;

    public function mount(Organization $organization, Asset $asset)
    {
        if ($asset->organization_id !== $organization->id) {
            abort(404);
        }

        $this->organization = $organization;
        $this->asset = $asset;
        
        $this->borrow_date = date('Y-m-d');
        $this->due_date = date('Y-m-d', strtotime('+' . min(1, $organization->max_borrow_days) . ' days'));
    }

    public function submitBorrowRequest(\App\Actions\Borrowing\RequestBorrowing $action)
    {
        $this->validate([
            'borrow_date' => 'required|date',
            'due_date' => 'required|date',
            'reason' => 'required|string|min:10',
        ]);

        try {
            $action->execute($this->organization, $this->asset, auth()->user(), [
                'borrow_date' => $this->borrow_date,
                'due_date' => $this->due_date,
                'reason' => $this->reason,
            ]);

            session()->flash('success', 'Pengajuan peminjaman berhasil dikirim.');
            $this->redirect(route('my-borrowings.index', ['tab' => 'pending']), navigate: true);
        } catch (\Exception $e) {
            $this->addError('reason', $e->getMessage());
        }
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('organization.catalog', $organization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 flex items-center">
                <x-flux::icon.arrow-left class="size-5 mr-2" />
                Kembali ke Katalog
            </a>
            
            @if(auth()->user()->canManage($organization))
                <a href="{{ route('manage.inventory.show', ['organization' => $organization->slug, 'asset' => $asset->code]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none transition ease-in-out duration-150">
                    <x-flux::icon.cog-6-tooth class="size-4 mr-2" />
                    Kelola Aset Ini
                </a>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="md:flex">
                <div class="md:w-1/2 p-6 md:p-8 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                    @if($asset->photo)
                        <img src="{{ Storage::url($asset->photo) }}" alt="{{ $asset->name }}" class="max-h-[400px] object-contain rounded-lg shadow-sm">
                    @else
                        <div class="w-full aspect-square max-h-[400px] bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center text-gray-400">
                            <x-flux::icon.photo class="size-32" />
                        </div>
                    @endif
                </div>
                <div class="md:w-1/2 p-6 md:p-8">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-1">{{ $asset->name }}</h1>
                            <p class="text-sm font-mono text-gray-500 dark:text-gray-400">{{ $asset->code }}</p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full shadow-sm
                            {{ $asset->status->value === 'available' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $asset->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $asset->status->value === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $asset->status->value === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst($asset->status->value) }}
                        </span>
                    </div>

                    <div class="mb-6 flex gap-3 text-sm text-gray-600 dark:text-gray-400">
                        <span class="flex items-center"><x-flux::icon.tag class="size-4 mr-1.5" /> {{ $asset->category->name }}</span>
                        <span class="flex items-center"><x-flux::icon.map-pin class="size-4 mr-1.5" /> {{ $asset->location }}</span>
                    </div>

                    <div class="prose dark:prose-invert max-w-none mb-8">
                        <h4 class="text-lg font-medium">Deskripsi</h4>
                        <p class="whitespace-pre-line">{{ $asset->description }}</p>
                        
                        <h4 class="text-lg font-medium mt-6">Spesifikasi</h4>
                        <p class="whitespace-pre-line">{{ $asset->specifications }}</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        @if($asset->status->value === 'available' && !$organization->archived_at)
                            <flux:modal.trigger name="borrow-modal">
                                <button class="flex-1 inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 transition-colors">
                                    Ajukan Peminjaman
                                </button>
                            </flux:modal.trigger>
                        @else
                            <button disabled class="flex-1 inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest opacity-50 cursor-not-allowed">
                                Ajukan Peminjaman (Tidak Tersedia)
                            </button>
                        @endif

                        <button disabled class="sm:flex-none inline-flex justify-center items-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm opacity-50 cursor-not-allowed">
                            Lapor Kerusakan (Segera Hadir)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Pinjam -->
    <flux:modal name="borrow-modal" class="md:w-96">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Ajukan Peminjaman Aset</h2>
            
            <form wire:submit="submitBorrowRequest" class="space-y-4">
                <div>
                    <x-input-label for="borrow_date" value="Tanggal Peminjaman" />
                    <x-text-input wire:model="borrow_date" id="borrow_date" type="date" class="mt-1 block w-full" min="{{ date('Y-m-d') }}" required />
                    <x-input-error :messages="$errors->get('borrow_date')" class="mt-2" />
                </div>
                
                <div>
                    <x-input-label for="due_date" value="Estimasi Tanggal Pengembalian" />
                    <x-text-input wire:model="due_date" id="due_date" type="date" class="mt-1 block w-full" min="{{ date('Y-m-d') }}" required />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    <p class="text-xs text-gray-500 mt-1">Maksimal {{ $organization->max_borrow_days }} hari.</p>
                </div>

                <div>
                    <x-input-label for="reason" value="Alasan Peminjaman" />
                    <textarea wire:model="reason" id="reason" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Minimal 10 karakter" required></textarea>
                    <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:modal.close>
                        <x-secondary-button>Batal</x-secondary-button>
                    </flux:modal.close>
                    <x-primary-button>Ajukan</x-primary-button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
