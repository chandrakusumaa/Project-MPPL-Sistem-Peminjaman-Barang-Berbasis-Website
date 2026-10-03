<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Notifications\DatabaseNotification;

new class extends Component {
    use WithPagination;

    public $filter = 'all'; // all, unread

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-marked-as-read'); // Optional, to trigger UI updates if necessary, but we can rely on poll
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        session()->flash('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function getNotificationsProperty()
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        return $query->paginate(20);
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-200">Notifikasi</h2>
                        
                        <div class="flex items-center gap-4">
                            <flux:select wire:model.live="filter" class="w-40">
                                <flux:select.option value="all">Semua</flux:select.option>
                                <flux:select.option value="unread">Belum Dibaca</flux:select.option>
                            </flux:select>
                            
                            <flux:button wire:click="markAllAsRead" size="sm" variant="outline">
                                Tandai Semua Dibaca
                            </flux:button>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 bg-green-50 text-green-700 p-3 rounded text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        @forelse($this->notifications as $notification)
                            <div class="p-4 rounded-lg border {{ is_null($notification->read_at) ? 'bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-900' : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700' }} flex justify-between items-start">
                                <div class="flex gap-4">
                                    <div class="shrink-0 mt-1">
                                        @if(($notification->data['category'] ?? '') === 'borrowing')
                                            <flux:icon.clipboard-document-list class="size-6 text-indigo-500" />
                                        @elseif(($notification->data['category'] ?? '') === 'membership')
                                            <flux:icon.users class="size-6 text-emerald-500" />
                                        @elseif(($notification->data['category'] ?? '') === 'damage')
                                            <flux:icon.exclamation-triangle class="size-6 text-amber-500" />
                                        @else
                                            <flux:icon.bell class="size-6 text-zinc-500" />
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('notifications.show', $notification->id) }}" class="block font-semibold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400" wire:navigate>
                                            {{ $notification->data['title'] ?? 'Notifikasi' }}
                                        </a>
                                        <p class="text-zinc-600 dark:text-zinc-400 text-sm mt-1">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        <p class="text-zinc-400 text-xs mt-2">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                
                                @if(is_null($notification->read_at))
                                    <button wire:click="markAsRead('{{ $notification->id }}')" class="shrink-0 text-xs text-indigo-600 hover:text-indigo-800 font-medium" title="Tandai sudah dibaca">
                                        <flux:icon.check-circle class="size-5" />
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-12 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                <flux:icon.bell-slash class="size-12 mx-auto text-zinc-400 mb-3" />
                                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">Tidak ada notifikasi</h3>
                                <p class="text-zinc-500 mt-1">Anda sudah membaca semua notifikasi.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $this->notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
