@props(['icon' => 'inbox', 'title', 'description' => ''])
<div class="flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl">
    <flux:icon :icon="$icon" class="w-12 h-12 text-zinc-400 mb-4" />
    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
    @if($slot->isNotEmpty())
    <div class="mt-6">
        {{ $slot }}
    </div>
    @endif
</div>
