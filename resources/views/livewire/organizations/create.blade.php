<?php

use App\Actions\Organization\CreateOrganization;
use App\Http\Requests\CreateOrganizationRequest;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $description = '';
    public string $category = '';
    public $logo;

    public function rules()
    {
        $request = new CreateOrganizationRequest();
        $rules = $request->rules();
        
        // Remove slug, max_borrow_days, late_fine_per_day from rules as they are set by default
        unset($rules['slug']);
        unset($rules['max_borrow_days']);
        unset($rules['late_fine_per_day']);
        
        return $rules;
    }

    public function messages()
    {
        return (new CreateOrganizationRequest())->messages();
    }

    public function save(CreateOrganization $action)
    {
        $validated = $this->validate();

        if ($this->logo) {
            $validated['logo'] = $this->logo->store('organizations', 'public');
        }

        try {
            $action->execute(auth()->user(), $validated);
            session()->flash('success', 'Organisasi berhasil dibuat.');
            $this->redirect(route('organizations.index', ['tab' => 'my']), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat organisasi: ' . $e->getMessage());
        }
    }
}; ?>

<x-layouts.app>
    <div class="max-w-2xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="text-2xl font-semibold mb-6">Buat Organisasi Baru</h2>

                @if (session()->has('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <form wire:submit="save">
                    <div class="mb-4">
                        <x-input-label for="name" value="Nama Organisasi *" />
                        <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="category" value="Kategori *" />
                        <x-text-input wire:model="category" id="category" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('category')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="description" value="Deskripsi *" />
                        <textarea wire:model="description" id="description" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" required></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="logo" value="Logo (Opsional)" />
                        <input type="file" wire:model="logo" id="logo" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept="image/jpeg,image/png,image/webp">
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        <div wire:loading wire:target="logo" class="text-sm text-gray-500 mt-2">Mengunggah...</div>
                        @if ($logo)
                            <div class="mt-2">
                                <img src="{{ $logo->temporaryUrl() }}" class="h-20 w-auto rounded">
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('organizations.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                            Batal
                        </a>
                        <x-primary-button wire:loading.attr="disabled" wire:target="save, logo">
                            Simpan
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
