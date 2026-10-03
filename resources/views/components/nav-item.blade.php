@props(['disabled' => false, 'icon' => null, 'href' => '#', 'current' => false])

@if($disabled)
    <div title="Hanya untuk Admin" class="opacity-50 cursor-not-allowed" x-data x-tooltip="'Hanya untuk Admin'">
        <flux:navlist.item :icon="$icon" href="#" class="pointer-events-none">
            {{ $slot }}
        </flux:navlist.item>
    </div>
@else
    <flux:navlist.item :icon="$icon" :href="$href" :current="$current" wire:navigate>
        {{ $slot }}
    </flux:navlist.item>
@endif
