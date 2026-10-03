<?php

use App\Models\Organization;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public Organization $organization;

    public function mount(Organization $organization): void
    {
        // Don't show archived
        if ($organization->archived_at) {
            abort(404);
        }

        $this->organization = $organization->loadCount(['members', 'assets']);
    }

    public function applyToJoin(): void
    {
        if (auth()->guest()) {
            session()->put('url.intended', route('explore.show', $this->organization->slug));
            $this->redirect(route('login', absolute: false), navigate: true);
            return;
        }

        session()->flash('status', 'Fitur bergabung akan diimplementasikan pada Fase 3.');
    }
}; ?>

<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-zinc-800 dark:text-zinc-200 leading-tight">
                {{ $organization->name }}
            </h2>
            <div class="space-x-4 text-sm text-zinc-600 dark:text-zinc-400">
                <a href="{{ route('explore.index') }}" class="hover:underline" wire:navigate>&larr; Kembali ke Daftar</a>
                <a href="{{ route('home') }}" class="hover:underline" wire:navigate>Home</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8">
                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="w-32 h-32 shrink-0">
                            @if($organization->logo)
                                <img src="{{ Storage::url($organization->logo) }}" alt="{{ $organization->name }}" class="w-full h-full rounded-xl object-cover shadow-sm">
                            @else
                                <div class="w-full h-full rounded-xl bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-4xl shadow-sm">
                                    {{ substr($organization->name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 w-full">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                                <div>
                                    <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mb-2">{{ $organization->name }}</h1>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                        {{ $organization->category }}
                                    </span>
                                </div>
                                
                                <div>
                                    <flux:button wire:click="applyToJoin" variant="primary">
                                        Ajukan Bergabung
                                    </flux:button>
                                </div>
                            </div>

                            <p class="text-zinc-600 dark:text-zinc-300 text-lg mb-8">
                                {{ $organization->description ?? 'Organisasi ini belum memiliki deskripsi.' }}
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                                <div>
                                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Anggota</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $organization->members_count }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400 mb-1">Total Aset</p>
                                    <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $organization->assets_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
