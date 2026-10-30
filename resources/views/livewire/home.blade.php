<div class="oi-explore">
    <x-feedback />
    <div class="oi-page-head"><div><h1>Organization Saya</h1><p>{{ $listing === 'mine' ? 'Organization yang kamu buat maupun ikuti.' : 'Temukan Organization dan gabung langsung sebagai Member untuk mulai meminjam.' }}</p></div>@auth @if(auth()->user()->hasVerifiedEmail())<button class="oi-btn" wire:click="$toggle('creating')"><x-icon name="plus" />{{ $creating ? 'Tutup formulir' : 'Buat Organization' }}</button>@endif @endauth</div>
    @auth
        @if(!auth()->user()->hasVerifiedEmail())
            <section class="oi-panel oi-panel-body" aria-labelledby="verify-workspace-heading"><h2 id="verify-workspace-heading">Verifikasi email untuk membuat Organization</h2><p>Akun Anda belum terverifikasi. Verifikasi email terlebih dahulu agar bisa membuat Organization atau bergabung.</p><a class="oi-btn" href="{{ route('verification.notice') }}">Verifikasi email</a><p class="muted">Anda tetap dapat menjelajahi profil Organization di bawah.</p></section>
        @elseif($creating)
            <section class="oi-panel oi-panel-body oi-create-panel" aria-labelledby="create-organization-heading"><h2 id="create-organization-heading">Buat Organization</h2><p class="muted">Untuk barang pribadi, kelompok, atau komunitas. Kamu otomatis menjadi Admin Organization ini.</p><form wire:submit="create"><div class="oi-grid oi-cols-2"><label>Nama Organization<input wire:model="name" required maxlength="150" autofocus>@error('name')<span class="oi-field-error" role="alert">{{ $message }}</span>@enderror</label><label>Kategori<select wire:model="category">@foreach(['kampus','sekolah','komunitas','perusahaan','lainnya'] as $categoryOption)<option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>@endforeach</select>@error('category')<span class="oi-field-error" role="alert">{{ $message }}</span>@enderror</label></div><label>Deskripsi<textarea wire:model="description" maxlength="3000"></textarea>@error('description')<span class="oi-field-error" role="alert">{{ $message }}</span>@enderror</label><button class="oi-btn" type="submit" wire:loading.attr="disabled">Buat Organization</button></form></section>
        @endif
    @endauth
    <nav class="oi-listing-tabs" aria-label="Daftar Organization">
        <button type="button" wire:click="showListing('all')" @if($listing === 'all') aria-current="page" @endif>Jelajahi Organization</button>
        <button type="button" wire:click="showListing('mine')" @if($listing === 'mine') aria-current="page" @endif>Organization Saya</button>
    </nav>
    <x-organization.filters />
    <div wire:loading.delay class="oi-loading" role="status">Memuat Organization…</div>
    <div class="oi-grid oi-cols-2 oi-org-grid">
        @forelse($organizations as $org)
            <x-organization-card :org="$org" :membership="$memberships->get($org->id)">
                @if($memberships->has($org->id))
                    @if($joinedOrganizationId === $org->id)<span class="oi-tag oi-tag-blue" role="status">Berhasil bergabung</span>@endif
                    <a class="oi-btn oi-sm" href="{{ route('workspace', $org) }}">Buka Organization →</a>
                @elseif(auth()->check() && !auth()->user()->hasVerifiedEmail())
                    <a class="oi-btn oi-secondary oi-sm" href="{{ route('verification.notice') }}">Verifikasi email untuk bergabung</a>
                @elseif(in_array($org->id, $revoked))
                    <span class="oi-tag">Keanggotaan dicabut</span>
                @elseif(auth()->check())
                    <button class="oi-btn oi-secondary oi-sm" wire:click="join({{ $org->id }})" wire:loading.attr="disabled" wire:target="join({{ $org->id }})">
                        <span wire:loading.remove wire:target="join({{ $org->id }})">Gabung</span><span wire:loading wire:target="join({{ $org->id }})">Memproses…</span>
                    </button>
                @else
                    <a href="{{ route('login') }}" class="oi-btn oi-secondary oi-sm">Masuk untuk bergabung</a>
                @endif
            </x-organization-card>
        @empty
            <div class="oi-panel oi-empty oi-full-width"><x-icon name="grid" :size="28" /><h3>Belum ada Organization yang sesuai</h3><p>{{ $listing === 'mine' ? 'Buat Organization sendiri atau jelajahi Organization untuk bergabung.' : 'Coba nama atau kategori lain.' }}</p></div>
        @endforelse
    </div>
    <div class="oi-pagination">{{ $organizations->links() }}</div>
</div>
