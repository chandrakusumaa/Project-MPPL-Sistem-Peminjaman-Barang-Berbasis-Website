<?php

use App\Actions\AssetCategory\CreateAssetCategory;
use App\Actions\AssetCategory\DeleteAssetCategory;
use App\Actions\AssetCategory\UpdateAssetCategory;
use App\Models\AssetCategory;
use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.manage')] class extends Component {
    public Organization $organization;
    public $categories;

    // Form
    public string $name = '';
    public ?int $editId = null;

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = $this->organization->assetCategories()->withCount('assets')->get();
    }

    public function save(CreateAssetCategory $create, UpdateAssetCategory $update)
    {
        if ($this->organization->archived_at) {
            $this->addError('name', 'Organisasi diarsipkan, tidak dapat mengubah kategori.');
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255|unique:asset_categories,name,' . $this->editId . ',id,organization_id,' . $this->organization->id,
        ]);

        try {
            if ($this->editId) {
                $cat = AssetCategory::findOrFail($this->editId);
                $update->execute($cat, ['name' => $this->name]);
                session()->flash('success', 'Kategori diperbarui.');
            } else {
                $create->execute($this->organization, ['name' => $this->name]);
                session()->flash('success', 'Kategori ditambahkan.');
            }

            $this->resetForm();
            $this->loadCategories();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        $cat = AssetCategory::findOrFail($id);
        $this->editId = $cat->id;
        $this->name = $cat->name;
    }

    public function delete(DeleteAssetCategory $action, int $id)
    {
        if ($this->organization->archived_at) {
            session()->flash('error', 'Organisasi diarsipkan.');
            return;
        }

        try {
            $cat = AssetCategory::findOrFail($id);
            $action->execute($cat);
            session()->flash('success', 'Kategori dihapus.');
            $this->loadCategories();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->editId = null;
        $this->resetErrorBag();
    }
}; ?>


    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        
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

        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Kategori Aset</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Kelola kategori aset untuk pengelompokan yang lebih baik.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="shadow sm:rounded-md sm:overflow-hidden bg-white dark:bg-gray-800 p-6">
                    <form wire:submit="save" class="mb-8 flex gap-4 items-end">
                        <div class="flex-1">
                            <x-input-label for="name" value="{{ $editId ? 'Edit Kategori' : 'Kategori Baru' }}" />
                            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required placeholder="Nama Kategori" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <x-primary-button>{{ $editId ? 'Simpan' : 'Tambah' }}</x-primary-button>
                            @if($editId)
                                <button type="button" wire:click="resetForm" class="ml-2 inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">Batal</button>
                            @endif
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah Aset</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($categories as $category)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $category->assets_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button wire:click="edit({{ $category->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 mr-3">Edit</button>
                                            <button wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus kategori ini?" class="text-red-600 hover:text-red-900 dark:text-red-400">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">Belum ada kategori.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

