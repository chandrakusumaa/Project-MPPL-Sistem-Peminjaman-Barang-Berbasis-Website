<div class="oi-explore">
    <x-feedback />
    <div class="oi-page-head"><div><h1>{{ $listing === 'mine' ? 'Organization Saya' : 'Jelajahi Organization' }}</h1><p>{{ $listing === 'mine' ? 'Ruang inventaris yang kamu buat maupun ikuti.' : 'Temukan Organization dan ajukan bergabung untuk mulai meminjam.' }}</p></div>@auth @if(auth()->user()->hasVerifiedEmail())<button class="oi-btn" wire:click="$toggle('creating')"><x-icon name="plus" />{{ $creating ? 'Tutup formulir' : 'Buat Organization' }}</button>@endif @endauth</div>
    @auth
        @if(!auth()->user()->hasVerifiedEmail())
            <section class="oi-panel oi-panel-body" aria-labelledby="verify-workspace-heading"><h2 id="verify-workspace-heading">Verifikasi email untuk membuat ruang</h2><p>Akun Anda belum terverifikasi. Verifikasi email terlebih dahulu agar bisa membuat Ruang Inventaris atau mengajukan bergabung.</p><a class="oi-btn" href="{{ route('verification.notice') }}">Verifikasi email</a><p class="muted">Anda tetap dapat menjelajahi profil ruang di bawah.</p></section>
        @elseif($creating)
            <section class="oi-panel oi-panel-body oi-create-panel" aria-labelledby="create-organization-heading"><h2 id="create-organization-heading">Buat Ruang Inventaris</h2><p class="muted">Untuk barang pribadi, kelompok, atau komunitas. Kamu otomatis menjadi Admin ruang ini.</p><form wire:submit="create"><div class="oi-grid oi-cols-2"><label>Nama ruang<input wire:model="name" required maxlength="150" autofocus>@error('name')<span class="oi-field-error" role="alert">{{ $message }}</span>@enderror</label><label>Kategori<select wire:model="category">@foreach(['kampus','sekolah','komunitas','perusahaan','lainnya'] as $categoryOption)<option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>@endforeach</select>@error('category')<span class="oi-field-error" role="alert">{{ $message }}</span>@enderror</label></div><label>Deskripsi<textarea wire:model="description" maxlength="3000"></textarea>@error('description')<span class="oi-field-error" role="alert">{{ $message }}</span>@enderror</label><button class="oi-btn" type="submit" wire:loading.attr="disabled">Buat ruang</button></form></section>
        @endif
    @endauth
    @auth<nav class="oi-listing-tabs" aria-label="Daftar Organization"><a href="{{ route('organizations') }}" @if($listing !== 'mine') aria-current="page" @endif>Jelajahi Organization</a><a href="{{ route('organizations', ['listing' => 'mine']) }}" @if($listing === 'mine') aria-current="page" @endif>Organization Saya</a></nav>@endauth
    <div class="oi-filters oi-explore-filters"><label>Cari Organization<input type="search" placeholder="Cari nama Organization…" wire:model.live.debounce.300ms="search"></label><label>Kategori ruang<select wire:model.live="filterCategory"><option value="">Semua kategori</option>@foreach(['kampus','sekolah','komunitas','perusahaan','lainnya'] as $categoryOption)<option value="{{ $categoryOption }}">{{ ucfirst($categoryOption) }}</option>@endforeach</select></label></div>
    <div wire:loading.delay class="oi-loading" role="status">Memuat Organization…</div>
    <div class="oi-grid oi-cols-2 oi-org-grid">
        @forelse($organizations as $org)
            <x-organization-card :org="$org" :membership="$memberships->get($org->id)">
                @if($memberships->has($org->id))
                    <a class="oi-btn oi-sm" href="{{ route('workspace', $org) }}">Buka ruang →</a>
                @elseif(auth()->check() && !auth()->user()->hasVerifiedEmail())
                    <a class="oi-btn oi-secondary oi-sm" href="{{ route('verification.notice') }}">Verifikasi email untuk bergabung</a>
                @elseif(in_array($org->id, $pending))
                    <span class="oi-tag oi-tag-amber">Menunggu persetujuan</span><button class="oi-btn oi-secondary oi-sm" wire:click="cancelJoin({{ $org->id }})" wire:loading.attr="disabled">Batalkan pengajuan</button>
                @elseif(auth()->check())
                    <button class="oi-btn oi-secondary oi-sm" wire:click="join({{ $org->id }})" wire:loading.attr="disabled">Ajukan bergabung</button>
                @else
                    <a href="{{ route('login') }}" class="oi-btn oi-secondary oi-sm">Masuk untuk bergabung</a>
                @endif
            </x-organization-card>
        @empty
            <div class="oi-panel oi-empty oi-full-width"><x-icon name="grid" :size="28" /><h3>Belum ada Organization yang sesuai</h3><p>{{ $listing === 'mine' ? 'Buat Organization sendiri atau jelajahi ruang untuk bergabung.' : 'Coba nama atau kategori lain.' }}</p></div>
        @endforelse
    </div>
    <div class="oi-pagination">{{ $organizations->links() }}</div>
</div>
