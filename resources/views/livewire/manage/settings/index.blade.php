<?php

use App\Actions\Organization\ArchiveOrganization;
use App\Actions\Organization\DeleteOrganization;
use App\Actions\Organization\UpdateOrganizationProfile;
use App\Actions\Organization\UpdateOrganizationRules;
use App\Http\Requests\Organization\UpdateProfileRequest;
use App\Http\Requests\Organization\UpdateRulesRequest;
use App\Models\Organization;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Organization $organization;
    
    // Profile
    public string $name = '';
    public string $description = '';
    public string $category = '';
    public $logo;

    // Rules
    public int $max_borrow_days = 7;
    public int $late_fine_per_day = 0;

    // Delete
    public string $deleteConfirm = '';

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
        $this->name = $organization->name;
        $this->description = $organization->description;
        $this->category = $organization->category;
        $this->max_borrow_days = $organization->max_borrow_days;
        $this->late_fine_per_day = $organization->late_fine_per_day;
    }

    public function updateProfile(UpdateOrganizationProfile $action)
    {
        $request = new UpdateProfileRequest();
        $validated = $this->validate($request->rules());

        if ($this->logo) {
            $validated['logo'] = $this->logo->store('organizations', 'public');
        }

        try {
            $action->execute($this->organization, $validated);
            session()->flash('profile_success', 'Profil organisasi berhasil diperbarui.');
            $this->logo = null;
        } catch (\Exception $e) {
            session()->flash('profile_error', $e->getMessage());
        }
    }

    public function updateRules(UpdateOrganizationRules $action)
    {
        $request = new UpdateRulesRequest();
        $validated = $this->validate($request->rules());

        try {
            $action->execute($this->organization, $validated);
            session()->flash('rules_success', 'Aturan peminjaman berhasil diperbarui.');
        } catch (\Exception $e) {
            session()->flash('rules_error', $e->getMessage());
        }
    }

    public function archive(ArchiveOrganization $action)
    {
        try {
            $isArchived = $this->organization->archived_at !== null;
            $action->execute($this->organization, !$isArchived);
            
            $status = !$isArchived ? 'diarsipkan' : 'diaktifkan kembali';
            session()->flash('archive_success', "Organisasi berhasil $status.");
            $this->redirectRoute('manage.settings.index', ['organization' => $this->organization->slug], navigate: true);
        } catch (\Exception $e) {
            session()->flash('archive_error', $e->getMessage());
        }
    }

    public function delete(DeleteOrganization $action)
    {
        if ($this->deleteConfirm !== $this->organization->name) {
            $this->addError('deleteConfirm', 'Nama organisasi tidak cocok.');
            return;
        }

        try {
            $action->execute($this->organization);
            session()->flash('success', 'Organisasi berhasil dihapus.');
            $this->redirectRoute('organizations.index', navigate: true);
        } catch (\Exception $e) {
            $this->addError('deleteConfirm', $e->getMessage());
        }
    }
}; ?>

<x-layouts.manage>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        
        @if($organization->archived_at)
            <div class="mb-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p class="font-bold">Perhatian</p>
                <p>Organisasi ini sedang diarsipkan. Fitur peminjaman dinonaktifkan.</p>
            </div>
        @endif

        <div class="md:grid md:grid-cols-3 md:gap-6 mb-8">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Profil Organisasi</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Perbarui nama, kategori, deskripsi, dan logo organisasi.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form wire:submit="updateProfile">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white dark:bg-gray-800 space-y-6 sm:p-6">
                            
                            @if (session()->has('profile_success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                    <span class="block sm:inline">{{ session('profile_success') }}</span>
                                </div>
                            @endif

                            <div>
                                <x-input-label for="name" value="Nama Organisasi" />
                                <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="category" value="Kategori" />
                                <x-text-input wire:model="category" id="category" type="text" class="mt-1 block w-full" required />
                                <x-input-error :messages="$errors->get('category')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="description" value="Deskripsi" />
                                <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="logo" value="Logo" />
                                <input type="file" wire:model="logo" id="logo" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 text-right sm:px-6">
                            <x-primary-button wire:loading.attr="disabled" wire:target="updateProfile, logo">
                                Simpan Profil
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="hidden sm:block" aria-hidden="true">
            <div class="py-5">
                <div class="border-t border-gray-200 dark:border-gray-700"></div>
            </div>
        </div>

        <div class="md:grid md:grid-cols-3 md:gap-6 mb-8">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Aturan Peminjaman</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Atur durasi maksimal pinjam dan denda keterlambatan per hari (Rp).
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form wire:submit="updateRules">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="px-4 py-5 bg-white dark:bg-gray-800 space-y-6 sm:p-6">
                            
                            @if (session()->has('rules_success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                    <span class="block sm:inline">{{ session('rules_success') }}</span>
                                </div>
                            @endif

                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <x-input-label for="max_borrow_days" value="Maksimal Hari Pinjam" />
                                    <x-text-input wire:model="max_borrow_days" id="max_borrow_days" type="number" class="mt-1 block w-full" required min="1" />
                                    <x-input-error :messages="$errors->get('max_borrow_days')" class="mt-2" />
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <x-input-label for="late_fine_per_day" value="Denda per Hari (Rp)" />
                                    <x-text-input wire:model="late_fine_per_day" id="late_fine_per_day" type="number" class="mt-1 block w-full" required min="0" />
                                    <x-input-error :messages="$errors->get('late_fine_per_day')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 text-right sm:px-6">
                            <x-primary-button wire:loading.attr="disabled" wire:target="updateRules">
                                Simpan Aturan
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="hidden sm:block" aria-hidden="true">
            <div class="py-5">
                <div class="border-t border-gray-200 dark:border-gray-700"></div>
            </div>
        </div>

        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Zona Bahaya</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Archive atau hapus organisasi secara permanen.
                    </p>
                </div>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white dark:bg-gray-800 space-y-6 sm:p-6">
                        
                        @if (session()->has('archive_success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ session('archive_success') }}</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $organization->archived_at ? 'Aktifkan Kembali Organisasi' : 'Archive Organisasi' }}
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $organization->archived_at ? 'Membuka kembali akses penuh ke organisasi ini.' : 'Organisasi akan menjadi read-only dan tidak muncul di Explore.' }}
                                </p>
                            </div>
                            <button wire:click="archive" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $organization->archived_at ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $organization->archived_at ? 'Unarchive' : 'Archive' }}
                            </button>
                        </div>

                        <hr class="my-6 border-gray-200 dark:border-gray-700">

                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 text-red-600">Hapus Organisasi</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 mb-4">
                                Penghapusan tidak dapat dibatalkan. Hanya bisa dilakukan jika tidak ada peminjaman yang sedang aktif. Ketik <strong>{{ $organization->name }}</strong> untuk mengonfirmasi.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <x-text-input wire:model="deleteConfirm" type="text" class="block w-full" placeholder="Ketik nama organisasi" />
                                    <x-input-error :messages="$errors->get('deleteConfirm')" class="mt-2" />
                                </div>
                                <div class="sm:w-auto">
                                    <button wire:click="delete" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.manage>
