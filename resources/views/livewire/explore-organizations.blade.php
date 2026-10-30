<div class="oi-explore oi-public-explore">
    <div class="oi-page-head"><div><h1>Jelajahi Organization</h1><p>Temukan Organization yang tersedia dan lihat profilnya.</p></div></div>
    <x-organization.filters />
    <div wire:loading.delay class="oi-loading" role="status">Memuat Organization…</div>
    <div class="oi-grid oi-cols-2 oi-org-grid">
        @forelse($organizations as $organization)
            <x-organization-card :org="$organization"><a class="oi-btn oi-secondary oi-sm" href="{{ route('organization.public', $organization) }}">Lihat Organization →</a></x-organization-card>
        @empty
            <div class="oi-panel oi-empty oi-full-width"><x-icon name="grid" :size="28" /><h3>Belum ada Organization yang sesuai</h3><p>Coba nama atau kategori lain.</p></div>
        @endforelse
    </div>
    <div class="oi-pagination">{{ $organizations->links() }}</div>
</div>
