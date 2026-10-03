<?php

use Livewire\Volt\Component;
use Flux\Flux;

new class extends Component {
    public int $unreadCount = 0;
    public $latestNotifications = [];
    public ?string $lastNotifiedId = null;

    public function mount()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $this->unreadCount = $user->unreadNotifications()->count();
            $this->latestNotifications = $user->notifications()->take(5)->get();
            
            // Initial last notified id
            if ($this->latestNotifications->isNotEmpty()) {
                $this->lastNotifiedId = $this->latestNotifications->first()->id;
            }
        }
    }

    public function loadNotifications()
    {
        if (auth()->check()) {
            $user = auth()->user();
            $this->unreadCount = $user->unreadNotifications()->count();
            $this->latestNotifications = $user->notifications()->take(5)->get();

            // Check if there are new unread notifications that we haven't toasted yet
            if ($this->latestNotifications->isNotEmpty()) {
                $latest = $this->latestNotifications->first();
                
                if (is_null($latest->read_at) && $latest->id !== $this->lastNotifiedId) {
                    $this->lastNotifiedId = $latest->id;
                    
                    Flux::toast(
                        heading: $latest->data['title'] ?? 'Notifikasi Baru',
                        text: $latest->data['message'] ?? '',
                    );
                }
            }
        }
    }
}; ?>

<div wire:poll.30s="loadNotifications" class="relative inline-flex">
    <flux:dropdown position="bottom" align="start">
        <flux:button variant="ghost" icon="bell" class="relative rounded-full p-2 h-10 w-10">
            @if($unreadCount > 0)
                <span class="absolute top-1 right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
            @endif
        </flux:button>
        
        <flux:menu class="w-80 p-0">
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center bg-zinc-50 dark:bg-zinc-800 rounded-t-lg">
                <h3 class="font-semibold text-sm">Notifikasi ({{ $unreadCount }})</h3>
                @if($unreadCount > 0)
                    <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400" wire:navigate>Lihat semua</a>
                @endif
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                @forelse($latestNotifications as $notification)
                    <a href="{{ route('notifications.show', $notification->id) }}" class="block px-4 py-3 border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition {{ is_null($notification->read_at) ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : '' }}" wire:navigate>
                        <div class="flex justify-between items-start mb-1">
                            <span class="font-medium text-sm text-zinc-900 dark:text-zinc-100 {{ is_null($notification->read_at) ? 'font-bold' : '' }}">
                                {{ $notification->data['title'] ?? 'Notifikasi' }}
                            </span>
                            <span class="text-xs text-zinc-500 shrink-0 ml-2">{{ $notification->created_at->diffForHumans(null, true, true) }}</span>
                        </div>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2">{{ $notification->data['message'] ?? '' }}</p>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-zinc-500 text-sm">
                        Belum ada notifikasi.
                    </div>
                @endforelse
            </div>
            
            <div class="border-t border-zinc-200 dark:border-zinc-700 p-2 text-center bg-zinc-50 dark:bg-zinc-800 rounded-b-lg">
                <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" wire:navigate>
                    Lihat Semua Notifikasi
                </a>
            </div>
        </flux:menu>
    </flux:dropdown>
</div>
