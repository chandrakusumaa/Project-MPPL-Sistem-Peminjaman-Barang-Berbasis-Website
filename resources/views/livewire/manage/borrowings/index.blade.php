<?php

use App\Models\Borrowing;
use App\Models\Organization;
use App\Enums\BorrowingStatus;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.manage')] class extends Component {
    use WithPagination;

    public Organization $organization;
    public string $tab = 'pending'; // pending, active, history
    public string $search = '';

    protected $queryString = [
        'tab' => ['except' => 'pending'],
        'search' => ['except' => '']
    ];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function with()
    {
        $query = Borrowing::with(['asset', 'user'])
            ->where('organization_id', $this->organization->id);

        if ($this->search) {
            $query->whereHas('asset', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            })->orWhereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tab === 'pending') {
            $query->where('status', BorrowingStatus::PENDING);
        } elseif ($this->tab === 'active') {
            $query->whereIn('status', [BorrowingStatus::BORROWED, BorrowingStatus::OVERDUE]);
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


    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Manajemen Peminjaman</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Kelola permohonan dan sirkulasi peminjaman aset.</p>
        </div>

        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-start md:items-center">
                <nav class="-mb-px flex w-full md:w-auto">
                    <button wire:click="setTab('pending')" class="{{ $tab === 'pending' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 md:w-auto py-4 px-4 text-center border-b-2 font-medium text-sm">
                        Pending Request
                    </button>
                    <button wire:click="setTab('active')" class="{{ $tab === 'active' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 md:w-auto py-4 px-4 text-center border-b-2 font-medium text-sm">
                        Active Borrowing
                    </button>
                    <button wire:click="setTab('history')" class="{{ $tab === 'history' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} w-1/3 md:w-auto py-4 px-4 text-center border-b-2 font-medium text-sm">
                        History
                    </button>
                </nav>
                <div class="p-2 w-full md:w-auto">
                    <x-text-input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari aset / peminjam..." class="w-full md:w-64 text-sm" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aset & Peminjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($borrowings as $borrowing)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($borrowing->asset->photo)
                                                <img class="h-10 w-10 rounded-md object-cover" src="{{ Storage::url($borrowing->asset->photo) }}">
                                            @else
                                                <div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center text-gray-400">
                                                    <x-flux::icon.photo class="size-6" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $borrowing->asset->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                                <x-flux::icon.user class="size-3" /> {{ $borrowing->user->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div class="text-gray-900 dark:text-gray-100">{{ $borrowing->borrow_date->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">Batas: {{ $borrowing->due_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $borrowing->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $borrowing->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $borrowing->status->value === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $borrowing->status->value === 'returned' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $borrowing->status->value === 'rejected' || $borrowing->status->value === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                    ">
                                        {{ ucfirst($borrowing->status->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('manage.borrowings.show', ['organization' => $organization->slug, 'borrowing' => $borrowing->id]) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Kelola</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($borrowings->hasPages())
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $borrowings->links() }}
                </div>
            @endif
        </div>
    </div>

