<?php

use function Livewire\Volt\{state, mount, layout};
use App\Models\Borrowing;

layout('components.layouts.app');

state(['stats' => [], 'activeBorrowings' => [], 'pendingRequestsCount' => 0, 'unreadNotifications' => []]);

mount(function () {
    $user = auth()->user();
    
    $this->stats = [
        'organizations_count' => $user->organizations()->count(),
    ];

    $this->activeBorrowings = Borrowing::where('user_id', $user->id)
        ->whereIn('status', ['borrowed', 'overdue'])
        ->with(['asset', 'organization'])
        ->orderBy('due_date', 'asc')
        ->take(5)
        ->get();

    $this->pendingRequestsCount = Borrowing::where('user_id', $user->id)
        ->where('status', 'pending')
        ->count();
        
    $this->unreadNotifications = $user->unreadNotifications()->take(3)->get();
});

?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Welcome & Profile Summary -->
        <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-zinc-900 dark:text-zinc-100 flex flex-col md:flex-row items-center gap-6">
                <div class="flex-shrink-0">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-full object-cover border-4 border-indigo-100 dark:border-indigo-900">
                    @else
                        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400 text-2xl font-bold border-4 border-indigo-50 dark:border-indigo-900/20">
                            {{ auth()->user()->initials() }}
                        </span>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold">Selamat datang, {{ auth()->user()->name }}!</h2>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-1">{{ auth()->user()->email }}</p>
                </div>
                <div class="grid grid-cols-1 gap-4 text-center">
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                        <span class="block text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['organizations_count'] }}</span>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">Organisasi Diikuti</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shortcuts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="{{ route('organizations.index') }}" wire:navigate class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center gap-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                <div class="p-3 bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg">
                    <flux:icon.building-office-2 class="size-6" />
                </div>
                <div>
                    <h3 class="font-bold text-lg text-zinc-800 dark:text-zinc-200">Semua Organisasi</h3>
                    <p class="text-sm text-zinc-500">Jelajahi atau kelola organisasi Anda.</p>
                </div>
            </a>
            
            <a href="{{ route('scan') }}" wire:navigate class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center gap-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                <div class="p-3 bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg">
                    <flux:icon.qr-code class="size-6" />
                </div>
                <div>
                    <h3 class="font-bold text-lg text-zinc-800 dark:text-zinc-200">Scan QR Code</h3>
                    <p class="text-sm text-zinc-500">Scan aset untuk melihat detail atau meminjam.</p>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Active Borrowings -->
            <div class="lg:col-span-2 bg-white dark:bg-zinc-800 shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">Peminjaman Berjalan</h3>
                    <a href="{{ route('my-borrowings.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Lihat Semua</a>
                </div>
                <div class="p-6">
                    @if($activeBorrowings->isEmpty())
                        <div class="text-center py-6 text-zinc-500">
                            Belum ada peminjaman aktif.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($activeBorrowings as $borrowing)
                                <div class="flex items-center justify-between p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    <div class="flex gap-4 items-center">
                                        <div class="h-12 w-12 rounded bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center overflow-hidden">
                                            @if($borrowing->asset->photo)
                                                <img src="{{ Storage::url($borrowing->asset->photo) }}" alt="{{ $borrowing->asset->name }}" class="h-full w-full object-cover">
                                            @else
                                                <flux:icon.cube class="size-6 text-zinc-400" />
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('my-borrowings.show', $borrowing->id) }}" wire:navigate class="font-semibold text-zinc-800 dark:text-zinc-200 hover:text-indigo-600 dark:hover:text-indigo-400">
                                                {{ $borrowing->asset->name }}
                                            </a>
                                            <p class="text-xs text-zinc-500">{{ $borrowing->organization->name }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($borrowing->status->value === 'overdue')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                                Dipinjam
                                            </span>
                                        @endif
                                        <p class="text-xs text-zinc-500 mt-1">Kembali: {{ $borrowing->due_date->format('d M Y') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($pendingRequestsCount > 0)
                        <div class="mt-6 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/50 rounded-lg p-4 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <flux:icon.clock class="size-5 text-amber-500" />
                                <span class="text-sm font-medium text-amber-800 dark:text-amber-400">Anda memiliki {{ $pendingRequestsCount }} pengajuan tertunda.</span>
                            </div>
                            <a href="{{ route('my-borrowings.index') }}?tab=pending" wire:navigate class="text-sm text-amber-600 hover:text-amber-800 dark:text-amber-500 font-semibold">Cek Status</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notifications -->
            <div class="bg-white dark:bg-zinc-800 shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-zinc-800 dark:text-zinc-200">Notifikasi Terbaru</h3>
                    <a href="{{ route('notifications.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Semua</a>
                </div>
                <div class="p-0">
                    @forelse($unreadNotifications as $notification)
                        <a href="{{ route('notifications.show', $notification->id) }}" wire:navigate class="block p-4 border-b border-zinc-100 dark:border-zinc-700/50 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                            <p class="font-medium text-sm text-zinc-800 dark:text-zinc-200">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                            <p class="text-xs text-zinc-500 mt-1 line-clamp-1">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="text-xs text-zinc-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                        </a>
                    @empty
                        <div class="text-center py-8 text-zinc-500 text-sm">
                            Tidak ada notifikasi baru.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
