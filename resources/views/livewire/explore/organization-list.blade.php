<?php

use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Organization::query()
            ->whereNull('archived_at');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        $categories = Organization::whereNull('archived_at')
            ->whereNotNull('category')
            ->select('category')
            ->distinct()
            ->pluck('category');

        return [
            'organizations' => $query->latest()->paginate(9),
            'categories' => $categories,
        ];
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
                {{ __('Explore Organizations') }}
            </h2>
            <a href="{{ route('home') }}" class="text-sm text-zinc-600 dark:text-zinc-400 hover:underline">
                &larr; Kembali ke Home
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Search & Filter -->
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama organisasi..." />
                    </div>
                    <div class="w-full md:w-64">
                        <select wire:model.live="category" class="block w-full border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm h-10">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($organizations as $org)
                    <a href="{{ route('explore.show', $org->slug) }}" class="block bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                @if($org->logo)
                                    <img src="{{ Storage::url($org->logo) }}" alt="{{ $org->name }}" class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xl">
                                        {{ substr($org->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $org->name }}</h3>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $org->category }}</p>
                                </div>
                            </div>
                            <p class="text-zinc-600 dark:text-zinc-300 text-sm line-clamp-2">
                                {{ $org->description ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                        <p class="text-zinc-500 dark:text-zinc-400">Tidak ada organisasi yang ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $organizations->links() }}
            </div>
        </div>
    </div>
</div>
