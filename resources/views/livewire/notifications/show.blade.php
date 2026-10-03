<?php

use Livewire\Volt\Component;

new class extends Component {
    public $notification;

    public function mount($id)
    {
        $this->notification = auth()->user()->notifications()->findOrFail($id);
        
        if (is_null($this->notification->read_at)) {
            $this->notification->markAsRead();
        }
    }
}; ?>

<div>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('notifications.index') }}" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 flex items-center gap-1" wire:navigate>
                    <flux:icon.arrow-left class="size-4" /> Kembali ke Notifikasi
                </a>
            </div>

            <div class="bg-white dark:bg-zinc-800 shadow sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
                            @if(($notification->data['category'] ?? '') === 'borrowing')
                                <flux:icon.clipboard-document-list class="size-6" />
                            @elseif(($notification->data['category'] ?? '') === 'membership')
                                <flux:icon.users class="size-6" />
                            @elseif(($notification->data['category'] ?? '') === 'damage')
                                <flux:icon.exclamation-triangle class="size-6" />
                            @else
                                <flux:icon.bell class="size-6" />
                            @endif
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $notification->data['title'] ?? 'Notifikasi' }}</h2>
                            <p class="text-xs text-zinc-500">{{ $notification->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="py-4 border-y border-zinc-200 dark:border-zinc-700">
                        <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-line text-lg">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end">
                        @if(!empty($notification->data['url']))
                            <flux:button href="{{ $notification->data['url'] }}" variant="primary">
                                Buka Halaman Terkait
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
