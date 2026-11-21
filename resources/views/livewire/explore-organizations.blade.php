<div class="oi-explore oi-public-explore">
    <div class="oi-page-head">
        <div>
            <h1>Jelajahi Komunitas</h1>
            <p>Temukan komunitas yang tersedia dan jelajahi katalog inventaris publiknya.</p>
        </div>
    </div>
    <x-organization.filters />
    <div wire:loading.delay class="oi-loading" role="status">Memuat Komunitas…</div>
    <div class="oi-grid oi-cols-2 oi-org-grid">
        @forelse($organizations as $organization)
            <x-organization-card :org="$organization">
                <a class="oi-btn oi-secondary oi-sm" style="border-radius: 9999px;" href="{{ route('organization.public', $organization) }}">Lihat Komunitas →</a>
            </x-organization-card>
        @empty
            <div class="panel-card oi-panel oi-empty oi-full-width">
                <x-icon name="grid" :size="28" />
                <h3>Belum ada Komunitas yang sesuai</h3>
                <p>Coba sesuaikan kata kunci pencarian atau kategori filter.</p>
            </div>
        @endforelse
    </div>
    @if($organizations->hasPages())
        <div class="oi-pagination" style="margin-top: 1.5rem;">{{ $organizations->links() }}</div>
    @endif
</div>
