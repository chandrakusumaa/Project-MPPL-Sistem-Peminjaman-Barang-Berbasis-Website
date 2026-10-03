<?php

use App\Models\Asset;
use App\Models\Organization;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

new #[Layout('components.layouts.app')] class extends Component {
    use WithFileUploads;

    public Organization $organization;
    public Asset $asset;

    public $borrow_date;
    public $due_date;
    public $reason;

    // Damage Report form
    public $damage_description;
    public $damage_photo;

    public $activeTab = 'info'; // info, history

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

    public function submitDamageReport(\App\Actions\Asset\ReportDamage $action)
    {
        $validated = $this->validate([
            'damage_description' => 'required|string|min:5',
            'damage_photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($this->damage_photo) {
            $photoPath = $this->damage_photo->store('damage-reports', 'public');
        }

        try {
            $action->execute($this->asset, auth()->user(), [
                'description' => $this->damage_description,
                'photo' => $photoPath,
            ]);

            session()->flash('success', 'Laporan kerusakan berhasil dikirim.');
            $this->reset(['damage_description', 'damage_photo']);
            // The modal will close via x-on:click or similar on the close button if needed, but a flash message is shown.
            $this->redirectRoute('organization.catalog.show', ['organization' => $this->organization->slug, 'asset' => $this->asset->code], navigate: true);
        } catch (\Exception $e) {
            $this->addError('damage_description', $e->getMessage());
        }
    }

    public function getAssetLogsProperty()
    {
        return $this->asset->logs()->with('user')->latest()->get();
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

                        @if(session('success'))
                            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                        @endif
                        
                        <!-- Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button wire:click="$set('activeTab', 'info')" class="{{ $activeTab === 'info' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Informasi
                                </button>
                                <button wire:click="$set('activeTab', 'history')" class="{{ $activeTab === 'history' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Riwayat
                                </button>
                            </nav>
                        </div>

                        @if($activeTab === 'info')
                            <div class="prose dark:prose-invert max-w-none mb-8">
                                <h4 class="text-lg font-medium">Deskripsi</h4>
                                <p class="whitespace-pre-line">{{ $asset->description }}</p>
                                
                                <h4 class="text-lg font-medium mt-6">Spesifikasi</h4>
                                <p class="whitespace-pre-line">{{ $asset->specifications }}</p>
                            </div>
                        @else
                            <div class="mb-8">
                                <ul role="list" class="-mb-8">
                                    @forelse($this->assetLogs as $idx => $log)
                                        <li>
                                            <div class="relative pb-8">
                                                @if($idx !== $this->assetLogs->count() - 1)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800 bg-{{ \App\Enums\AssetLogEvent::from($log->event)->color() }}-500">
                                                            <x-flux::icon.clock class="size-5 text-white" />
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ \App\Enums\AssetLogEvent::from($log->event)->label() }}</span>
                                                                oleh <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log->user?->name ?? 'Sistem' }}</span>
                                                            </p>
                                                            <p class="text-sm mt-1 text-gray-600 dark:text-gray-300">{{ $log->description }}</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                            <time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d M Y H:i') }}</time>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <p class="text-sm text-gray-500">Belum ada riwayat tercatat.</p>
                                    @endforelse
                                </ul>
                            </div>
                        @endif

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

                            <flux:modal.trigger name="damage-modal">
                                <button class="sm:flex-none inline-flex justify-center items-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-red-600 dark:text-red-400 uppercase tracking-widest shadow-sm hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    Lapor Kerusakan
                                </button>
                            </flux:modal.trigger>
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

    <!-- Modal Form Damage Report -->
    <flux:modal name="damage-modal" class="md:w-[500px]">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Laporkan Kerusakan Aset</h2>
            
            <form wire:submit="submitDamageReport" class="space-y-4">
                <div>
                    <x-input-label for="damage_description" value="Deskripsi Kerusakan" />
                    <textarea wire:model="damage_description" id="damage_description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Jelaskan secara detail bagian mana yang rusak" required></textarea>
                    <x-input-error :messages="$errors->get('damage_description')" class="mt-2" />
                </div>
                
                <div>
                    <x-input-label for="damage_photo" value="Foto Kerusakan (Opsional, Maks 2MB)" />
                    <input type="file" wire:model="damage_photo" id="damage_photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                    <x-input-error :messages="$errors->get('damage_photo')" class="mt-2" />
                    
                    @if ($damage_photo)
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-2">Preview:</p>
                            <img src="{{ $damage_photo->temporaryUrl() }}" class="h-32 object-contain rounded">
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <flux:modal.close>
                        <x-secondary-button>Batal</x-secondary-button>
                    </flux:modal.close>
                    <x-primary-button>Kirim Laporan</x-primary-button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
