@props(['org', 'membership' => null])
<article class="oi-org-card">
    <div class="oi-org-identity">
        <x-avatar :name="$org->name" />
        <div><h3><a href="{{ $membership ? route('workspace', $org) : route('organization.public', $org) }}">{{ $org->name }}</a></h3><p>{{ ucfirst($org->category) }}@if($membership) · {{ strtoupper($membership->role) }}@endif</p></div>
    </div>
    @if($org->description)<p class="oi-org-description">{{ $org->description }}</p>@endif
    <div class="oi-org-tags">
        @if($membership)<span class="oi-tag {{ $org->created_by === auth()->id() ? 'oi-tag-red' : 'oi-tag-blue' }}">{{ $org->created_by === auth()->id() ? 'Dibuat olehmu' : 'Diikuti' }}</span>@endif
        @if($org->archived_at)<span class="oi-tag">Diarsipkan</span>@endif
    </div>
    <div class="oi-org-foot"><span>{{ $org->assets_count }} aset</span><div>{{ $slot }}</div></div>
</article>
