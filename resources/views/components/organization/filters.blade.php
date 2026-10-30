<div class="oi-filters oi-explore-filters">
    <label>Cari Organization<input type="search" placeholder="Cari nama Organization…" wire:model.live.debounce.300ms="search"></label>
    <label>Kategori Organization<select aria-label="Kategori Organization" wire:model.live="filterCategory"><option value="">Semua kategori</option>@foreach(['kampus','sekolah','komunitas','perusahaan','lainnya'] as $categoryOption)<option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>@endforeach</select></label>
</div>
