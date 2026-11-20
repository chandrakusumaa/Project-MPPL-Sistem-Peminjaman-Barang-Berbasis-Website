@props(['id', 'title', 'description', 'close'])

<dialog id="{{ $id }}" class="oi-modal-card oi-form-dialog" aria-labelledby="{{ $id }}-title" aria-describedby="{{ $id }}-description"
    x-data x-init="$nextTick(() => { if ($el.isConnected) $el.showModal() })"
    @cancel.prevent="$wire.{{ $close }}()" @click.self="$wire.{{ $close }}()" wire:key="{{ $id }}" wire:ignore.self>
    <div class="oi-modal-head">
        <div>
            <h2 id="{{ $id }}-title">{{ $title }}</h2>
            <p id="{{ $id }}-description" class="muted">{{ $description }}</p>
        </div>
        <button type="button" class="oi-modal-close" wire:click="{{ $close }}" aria-label="Tutup pop-up">×</button>
    </div>
    <div class="oi-modal-body">
        <x-feedback />
        {{ $slot }}
    </div>
</dialog>
