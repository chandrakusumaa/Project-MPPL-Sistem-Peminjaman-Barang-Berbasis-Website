@props(['org', 'membership' => null])
<article class="oi-org-card feature-card">
    <div class="oi-org-identity">
        <x-avatar :name="$org->name" type="community" />
        <div>
            <h3 style="font-weight: 800; font-size: 1rem;"><a href="{{ $membership ? route('workspace', $org) : route('organization.public', $org) }}" style="color: #0f172a; text-decoration: none;">{{ $org->name }}</a></h3>
            <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0;">{{ ucfirst($org->category) }}@if($membership) · <span class="micro-badge" style="font-weight: 700;">{{ strtoupper($membership->role) }}</span>@endif</p>
        </div>
    </div>
    @if($org->description)<p class="oi-org-description" style="font-size: 0.8125rem; color: #64748b; line-height: 1.5; margin: 0.75rem 0;">{{ $org->description }}</p>@endif
    <div class="oi-org-tags">
        @if($membership)<span class="micro-badge {{ $org->created_by === auth()->id() ? 'micro-badge-red' : '' }}">{{ $org->created_by === auth()->id() ? 'Dikelola oleh Anda' : 'Diikuti' }}</span>@endif
        @if($org->archived_at)<span class="micro-badge" style="background: #f1f5f9; color: #64748b;">Diarsipkan</span>@endif
    </div>
    <div class="oi-org-foot" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: #64748b;">
        <span style="font-weight: 600;">{{ $org->assets_count }} unit aset</span>
        <div>{{ $slot }}</div>
    </div>
</article>
