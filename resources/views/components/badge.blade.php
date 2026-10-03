@props(['color' => 'gray'])
<flux:badge :color="$color" {{ $attributes }}>
    {{ $slot }}
</flux:badge>
