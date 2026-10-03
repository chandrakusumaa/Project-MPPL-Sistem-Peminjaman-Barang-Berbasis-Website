<?php

use App\Models\Organization;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('components.layouts.app')] class extends Component {
    use WithPagination;

    public Organization $organization;
    public string $search = '';
    public string $category = '';
    public string $status = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'status' => ['except' => '']
    ];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function getCategoriesProperty()
    {
        return $this->organization->assetCategories;
    }

    public function with()
    {
        $query = $this->organization->assets()->with('category');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->category) {
            $query->where('asset_category_id', $this->category);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return [
            'assets' => $query->latest()->paginate(12)
        ];
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Katalog {{ $organization->name }}</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Jelajahi dan temukan aset yang Anda butuhkan.</p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <x-text-input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari aset..." class="w-full" />
                </div>
                <div>
                    <select wire:model.live="category" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($this->categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="status" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($assets as $asset)
                <a href="{{ route('organization.asset.show', ['organization' => $organization->slug, 'asset' => $asset->code]) }}" class="block group">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow h-full flex flex-col">
                        <div class="relative pb-[70%] bg-gray-100 dark:bg-gray-900">
                            @if($asset->photo)
                                <img src="{{ Storage::url($asset->photo) }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $asset->name }}">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                    <x-flux::icon.photo class="size-12" />
                                </div>
                            @endif
                            <div class="absolute top-2 right-2">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm
                                    {{ $asset->status->value === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $asset->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $asset->status->value === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $asset->status->value === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($asset->status->value) }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 line-clamp-1">{{ $asset->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2 flex-1">{{ $asset->description }}</p>
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-3">
                                <span class="flex items-center"><x-flux::icon.tag class="size-3 mr-1" /> {{ $asset->category->name }}</span>
                                <span class="flex items-center"><x-flux::icon.map-pin class="size-3 mr-1" /> {{ $asset->location }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <x-flux::icon.cube class="size-12 mx-auto text-gray-400 mb-3" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tidak ada aset</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Belum ada aset yang sesuai kriteria pencarian.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $assets->links() }}
        </div>
    </div>
</div>
