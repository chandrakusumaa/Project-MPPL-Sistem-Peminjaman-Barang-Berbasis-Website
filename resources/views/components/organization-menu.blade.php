<details class="oi-org-menu" @if($current || (request()->routeIs('organizations') && request('listing') === 'mine')) open @endif>
    <summary class="oi-side-link"><x-icon name="grid" /><span>Organization</span><span class="oi-menu-count">{{ $organizations->count() }}</span><x-icon name="chevron-down" class="oi-menu-chevron" /></summary>
    <nav class="oi-org-links" aria-label="Organization Anda">
        <a class="oi-side-link" href="{{ route('organizations', ['listing' => 'mine']) }}">Semua Organization saya</a>
        @forelse($organizations as $organization)
            <a class="oi-side-link oi-org-link {{ $current?->id === $organization->id ? 'is-current' : '' }}" href="{{ route('workspace', $organization) }}">
                <x-avatar :name="$organization->name" />
                <span><b>{{ $organization->name }}</b><small>{{ strtoupper($organization->memberships->first()->role) }}{{ $organization->archived_at ? ' · Diarsipkan' : '' }}</small></span>
            </a>
        @empty
            <p class="oi-menu-empty">Belum mengikuti Organization.</p>
        @endforelse
        <a class="oi-side-link" href="{{ route('organizations', ['listing' => 'mine', 'create' => 1]) }}"><x-icon name="plus" /><span>Buat Organization</span></a>
    </nav>
</details>
