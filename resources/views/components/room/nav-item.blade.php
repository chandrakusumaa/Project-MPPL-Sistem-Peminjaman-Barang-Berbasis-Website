@props(['label', 'icon', 'active' => false, 'disabled' => false, 'href' => null])
@if($href && !$disabled)
<a href="{{ $href }}" {{ $attributes->except('wire:click')->class(['oi-side-link', 'is-active' => $active]) }} @if($active) aria-current="page" @endif><x-icon :name="$icon" /><span>{{ $label }}</span></a>
@else
<button type="button" {{ $attributes->class(['oi-side-link', 'is-active' => $active]) }} @disabled($disabled) @if($active) aria-current="page" @endif @if($disabled) title="Hanya untuk Admin" aria-disabled="true" @endif>
    <x-icon :name="$icon" /><span>{{ $label }}</span>
</button>
@endif
