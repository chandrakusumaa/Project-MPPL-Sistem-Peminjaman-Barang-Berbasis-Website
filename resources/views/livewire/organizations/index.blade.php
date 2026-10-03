<?php

use App\Actions\Membership\SubmitJoinRequest;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $tab = 'all'; // 'all' or 'my'
    public string $search = '';
    public string $category = '';
    public ?string $joinMessage = null;
    public ?int $selectedOrgId = null;

    protected $queryString = ['tab', 'search', 'category'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedTab()
    {
        $this->resetPage();
        $this->search = '';
        $this->category = '';
    }

    public function getCategoriesProperty()
    {
        return Organization::whereNull('archived_at')->distinct()->pluck('category');
    }

    public function getAllOrganizationsProperty()
    {
        return Organization::query()
            ->whereNull('archived_at')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->with(['users' => fn($q) => $q->where('user_id', Auth::id())]) // To know if joined
            ->with(['membershipRequests' => fn($q) => $q->where('user_id', Auth::id())->where('status', 'pending')])
            ->paginate(9);
    }

    public function getMyOrganizationsProperty()
    {
        return Auth::user()->organizations()
            ->whereNull('archived_at')
            ->withPivot('role')
            ->paginate(9);
    }

    public function selectOrg(int $orgId)
    {
        $this->selectedOrgId = $orgId;
        $this->joinMessage = null;
        $this->dispatch('open-modal', 'join-modal');
    }

    public function submitJoinRequest(SubmitJoinRequest $action)
    {
        $org = Organization::findOrFail($this->selectedOrgId);
        
        // Ensure not already joined
        if (Auth::user()->isMemberOf($org)) {
            session()->flash('error', 'Anda sudah bergabung dengan organisasi ini.');
            $this->dispatch('close-modal', 'join-modal');
            return;
        }

        // Ensure no pending request
        $hasPending = $org->membershipRequests()->where('user_id', Auth::id())->where('status', 'pending')->exists();
        if ($hasPending) {
            session()->flash('error', 'Anda sudah memiliki permintaan bergabung yang pending.');
            $this->dispatch('close-modal', 'join-modal');
            return;
        }

        $action->execute(Auth::user(), $org, $this->joinMessage);
        
        session()->flash('success', 'Permintaan bergabung berhasil dikirim.');
        $this->dispatch('close-modal', 'join-modal');
    }

    public function cancelJoinRequest($requestId)
    {
        $req = \App\Models\MembershipRequest::where('id', $requestId)->where('user_id', Auth::id())->firstOrFail();
        $req->update(['status' => 'cancelled']);
        session()->flash('success', 'Permintaan bergabung berhasil dibatalkan.');
    }
}; ?>

<x-layouts.app>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Organisasi</h1>
            <a href="{{ route('organizations.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Buat Organisasi
            </a>
        </div>

        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('tab', 'all')" class="{{ $tab === 'all' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Semua Organisasi
                </button>
                <button wire:click="$set('tab', 'my')" class="{{ $tab === 'my' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Organisasi Saya
                </button>
            </nav>
        </div>

        @if($tab === 'all')
            <div class="mb-6 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari organisasi..." class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                </div>
                <div class="sm:w-64">
                    <select wire:model.live="category" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($this->categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($this->allOrganizations as $org)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden flex flex-col">
                        <div class="p-6 flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ $org->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $org->category }}</p>
                            
                            @php
                                $isMember = $org->users->isNotEmpty();
                                $pendingReq = $org->membershipRequests->first();
                            @endphp

                            @if($isMember)
                                @php $role = $org->users->first()->pivot->role; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mb-4">
                                    {{ ucfirst($role) }}
                                </span>
                            @elseif($pendingReq)
                                <div class="text-sm text-yellow-600 dark:text-yellow-400 mb-4 font-medium">Menunggu persetujuan</div>
                            @endif
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                            @if($isMember)
                                <a href="{{ in_array($role, ['admin', 'staff']) ? route('manage.dashboard', $org->slug) : route('organization.catalog', $org->slug) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Buka
                                </a>
                            @elseif($pendingReq)
                                <button wire:click="cancelJoinRequest({{ $pendingReq->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Batalkan
                                </button>
                            @else
                                <button wire:click="selectOrg({{ $org->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    Ajukan Bergabung
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-10 text-gray-500 dark:text-gray-400">
                        Tidak ada organisasi yang ditemukan.
                    </div>
                @endforelse
            </div>
            <div class="mt-6">
                {{ $this->allOrganizations->links() }}
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($this->myOrganizations as $org)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden flex flex-col">
                        <div class="p-6 flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ $org->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $org->category }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mb-4">
                                {{ ucfirst($org->pivot->role) }}
                            </span>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                            <a href="{{ in_array($org->pivot->role, ['admin', 'staff']) ? route('manage.dashboard', $org->slug) : route('organization.catalog', $org->slug) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Buka
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-10 text-gray-500 dark:text-gray-400">
                        Anda belum bergabung dengan organisasi manapun.
                    </div>
                @endforelse
            </div>
            <div class="mt-6">
                {{ $this->myOrganizations->links() }}
            </div>
        @endif

        <x-modal name="join-modal" :show="false" focusable>
            <form wire:submit="submitJoinRequest" class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Ajukan Bergabung ke Organisasi
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Anda dapat menyertakan pesan opsional untuk Admin organisasi.
                </p>

                <div class="mt-6">
                    <x-input-label for="joinMessage" value="Pesan (Opsional)" />
                    <x-text-input id="joinMessage" wire:model="joinMessage" type="text" class="mt-1 block w-full" placeholder="Misal: Saya dari divisi IT" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Batal
                    </x-secondary-button>

                    <x-primary-button class="ms-3">
                        Kirim Permintaan
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
