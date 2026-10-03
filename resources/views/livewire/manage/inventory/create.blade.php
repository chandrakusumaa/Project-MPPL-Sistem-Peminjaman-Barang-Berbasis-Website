<?php

use App\Actions\Asset\CreateAsset;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.manage')] class extends Component {
    use WithFileUploads;

    public Organization $organization;

    public string $name = '';
    public string $asset_category_id = '';
    public string $description = '';
    public string $specifications = '';
    public string $location = '';
    public ?string $purchase_date = null;
    public $photo;

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
        if ($this->organization->archived_at) {
            abort(403, 'Organisasi telah diarsipkan.');
        }
    }

    public function getCategoriesProperty()
    {
        return $this->organization->assetCategories;
    }

    public function save(CreateAsset $action)
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'description' => 'required|string',
            'specifications' => 'required|string',
            'location' => 'required|string|max:255',
            'purchase_date' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($this->photo) {
            $validated['photo'] = $this->photo->store('assets', 'public');
        }

        try {
            $action->execute($this->organization, auth()->user(), $validated);
            session()->flash('success', 'Aset berhasil ditambahkan.');
            $this->redirectRoute('manage.inventory.index', ['organization' => $this->organization->slug], navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }
}; ?>


    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('manage.inventory.index', $organization->slug) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <x-flux::icon.arrow-left class="size-5" />
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Tambah Aset Baru</h2>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <form wire:submit="save" class="p-6 space-y-6">
                
                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="name" value="Nama Aset" />
                        <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="asset_category_id" value="Kategori" />
                        <select wire:model="asset_category_id" id="asset_category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('asset_category_id')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="description" value="Deskripsi" />
                        <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="specifications" value="Spesifikasi" />
                        <textarea wire:model="specifications" id="specifications" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
                        <x-input-error :messages="$errors->get('specifications')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="location" value="Lokasi / Penempatan" />
                        <x-text-input wire:model="location" id="location" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="purchase_date" value="Tanggal Pembelian (Opsional)" />
                        <x-text-input wire:model="purchase_date" id="purchase_date" type="date" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('purchase_date')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="photo" value="Foto Aset (Opsional, Maks 2MB)" />
                        <input type="file" wire:model="photo" id="photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        
                        @if ($photo)
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-2">Preview:</p>
                                <img src="{{ $photo->temporaryUrl() }}" class="h-32 object-contain rounded">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <x-primary-button wire:loading.attr="disabled" wire:target="save, photo">
                        Simpan Aset
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

