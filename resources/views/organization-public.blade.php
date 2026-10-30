<x-layout>
    <section class="card">
        <span class="badge">{{ $org->category }}</span><h1>{{ $org->name }}</h1><p>{{ $org->description }}</p>
        <p>{{ $memberCount }} anggota · {{ $assetCount }} aset</p>
        <p class="muted">Katalog hanya tersedia untuk anggota aktif Organization ini.</p>
        @auth
            @if(\Illuminate\Support\Facades\Gate::allows('view', $org))
                <a class="primary button" href="{{ route('workspace', $org) }}">Buka Organization</a>
            @elseif(!auth()->user()->hasVerifiedEmail())
                <a class="secondary button" href="{{ route('verification.notice') }}">Verifikasi email untuk bergabung</a>
            @elseif(\Illuminate\Support\Facades\Gate::allows('join', $org))
                <form method="POST" action="{{ route('organization.join', $org) }}" x-data="{ joining: false }" @submit="joining = true">
                    @csrf
                    <button type="submit" class="primary" :disabled="joining"><span x-show="!joining">Gabung</span><span x-cloak x-show="joining">Bergabung…</span></button>
                </form>
            @else
                <p class="muted">Akses bergabung tidak tersedia. Hubungi Admin Organization jika keanggotaanmu telah dicabut.</p>
            @endif
        @else
            <a class="primary button" href="{{ route('login') }}">Masuk untuk bergabung</a>
        @endauth
    </section>
</x-layout>
