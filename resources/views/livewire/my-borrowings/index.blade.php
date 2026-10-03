<?php

use App\Models\Borrowing;
use App\Models\Organization;
use App\Enums\BorrowingStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $tab = 'active'; // active, pending, history
    
    #[Url]
    public string $search = '';
    
    #[Url]
    public string $organization_id = '';

    public function getOrganizationsProperty()
    {
        return auth()->user()->organizations;
    }

    public function with()
    {
        $query = Borrowing::with(['asset', 'organization'])
            ->where('user_id', auth()->id());

        if ($this->search) {
            $query->whereHas('asset', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->organization_id) {
            $query->where('organization_id', $this->organization_id);
        }

        if ($this->tab === 'active') {
            $query->whereIn('status', [BorrowingStatus::BORROWED, BorrowingStatus::OVERDUE]);
        } elseif ($this->tab === 'pending') {
            $query->where('status', BorrowingStatus::PENDING);
        } else {
            $query->whereIn('status', [BorrowingStatus::RETURNED, BorrowingStatus::REJECTED, BorrowingStatus::CANCELLED]);
        }

        return [
            'borrowings' => $query->latest()->paginate(10)
        ];
    }

    public function setTab($tab)
    {
        $this->tab = $tab;
        $this->resetPage();
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Peminjaman Saya</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Pantau status peminjaman aset Anda.</p>
        </div>

        @if (session()->has('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex">
                    <button wire:click="setTab('active')" class="{{ $tab === 'active' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Aktif
                    </button>
                    <button wire:click="setTab('pending')" class="{{ $tab === 'pending' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Menunggu Persetujuan
                    </button>
                    <button wire:click="setTab('history')" class="{{ $tab === 'history' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                        Riwayat
                    </button>
                </nav>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-text-input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari nama atau kode aset..." class="w-full" />
                <select wire:model.live="organization_id" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                    <option value="">Semua Organisasi</option>
                    @foreach($this->organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($borrowings as $borrowing)
                    <li>
                        <a href="{{ route('my-borrowings.show', $borrowing) }}" class="block hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    @if($borrowing->asset->photo)
                                        <img src="{{ Storage::url($borrowing->asset->photo) }}" class="h-12 w-12 rounded-lg object-cover shadow-sm">
                                    @else
                                        <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                                            <x-flux::icon.photo class="size-6" />
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">{{ $borrowing->asset->name }}</p>
                                        <div class="mt-1 sm:flex sm:items-center sm:gap-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center"><x-flux::icon.building-office-2 class="size-3 mr-1" /> {{ $borrowing->organization->name }}</span>
                                            <span class="flex items-center mt-1 sm:mt-0"><x-flux::icon.calendar class="size-3 mr-1" /> {{ $borrowing->borrow_date->format('d M Y') }} - {{ $borrowing->due_date->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex flex-col items-end gap-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $borrowing->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $borrowing->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $borrowing->status->value === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $borrowing->status->value === 'returned' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $borrowing->status->value === 'rejected' || $borrowing->status->value === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        {{ ucfirst($borrowing->status->value) }}
                                    </span>
                                    <x-flux::icon.chevron-right class="size-5 text-gray-400" />
                                </div>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        Tidak ada riwayat peminjaman.
                    </li>
                @endforelse
            </ul>
            @if($borrowings->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $borrowings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
