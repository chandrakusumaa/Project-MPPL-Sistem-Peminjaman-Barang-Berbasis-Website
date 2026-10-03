<?php

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Organization;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
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
            'assets' => $query->latest()->paginate(10)
        ];
    }
}; ?>

<x-layouts.manage>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Inventory Aset</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Kelola semua aset milik organisasi.</p>
            </div>
            
            @if(!$organization->archived_at)
                <a href="{{ route('manage.inventory.create', $organization->slug) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Tambah Aset
                </a>
            @endif
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

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <x-text-input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari nama atau kode aset..." class="w-full" />
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

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aset</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($assets as $asset)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($asset->photo)
                                                <img class="h-10 w-10 rounded-md object-cover" src="{{ Storage::url($asset->photo) }}" alt="{{ $asset->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center text-gray-400">
                                                    <x-flux::icon.photo class="size-6" />
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $asset->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $asset->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $asset->category->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $asset->location }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $asset->status->value === 'available' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $asset->status->value === 'borrowed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $asset->status->value === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $asset->status->value === 'lost' ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ ucfirst($asset->status->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('manage.inventory.show', ['organization' => $organization->slug, 'asset' => $asset->code]) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada aset ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $assets->links() }}
            </div>
        </div>
    </div>
</x-layouts.manage>
