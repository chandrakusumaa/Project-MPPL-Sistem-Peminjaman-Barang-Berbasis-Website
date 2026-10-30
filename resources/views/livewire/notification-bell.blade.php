<div wire:poll.30s>
@if($variant === 'workspace')
<details class="oi-bell"><summary aria-label="Notifikasi"><x-icon name="bell" />@if($count)<span class="oi-bell-count">{{ $count }}</span>@endif</summary>
<div class="oi-bell-menu">@forelse($recent as $n)<p>{{ $n->data['title'] ?? 'Aktivitas ruang' }}</p>@empty<p>Belum ada notifikasi.</p>@endforelse<a href="/notifications">Semua notifikasi →</a></div></details>
@else
<details><summary aria-label="Notifikasi">♧ {{ $count }}</summary><div class="bell-menu card">@forelse($recent as $n)<p>{{ $n->data['title']??'Aktivitas ruang' }}</p>@empty<p>Belum ada notifikasi.</p>@endforelse<a href="/notifications">Semua notifikasi →</a></div></details>
@endif
</div>
