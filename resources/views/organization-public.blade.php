<x-layout>
    <div style="max-width: 52rem; margin: 2rem auto; padding: 0 1rem;">
        <div class="feature-card" style="padding: 2.25rem; margin-bottom: 2rem;">
            <div style="display: flex; gap: 1.5rem; align-items: flex-start; flex-wrap: wrap;">
                <x-avatar :name="$org->name" type="community" style="width: 64px; height: 64px; border-radius: 9999px; font-size: 1.5rem; flex-shrink: 0;" />
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.375rem;">
                        <h1 style="font-size: 1.625rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0;">{{ $org->name }}</h1>
                        <span class="micro-badge" style="background: #ecfdf5; border-color: #a7f3d0; color: #047857; font-weight: 700;">
                            <span class="status-dot"></span> Komunitas Publik
                        </span>
                    </div>
                    <p style="font-size: 0.875rem; color: #64748b; margin: 0 0 1rem 0; line-height: 1.6;">{{ $org->description ?: 'Komunitas peminjaman perlengkapan dan inventaris fisik.' }}</p>
                    
                    <div style="display: flex; gap: 1.25rem; font-size: 0.8125rem; color: #475467; flex-wrap: wrap; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                        <span>Kategori: <strong>{{ ucfirst($org->category) }}</strong></span>
                        <span>•</span>
                        <span>Anggota: <strong>{{ $memberCount }} Orang</strong></span>
                        <span>•</span>
                        <span>Inventaris: <strong>{{ $assetCount }} Unit Fisik</strong></span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1.75rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">
                    Katalog lengkap dan pengajuan peminjaman tersedia bagi anggota aktif komunitas ini.
                </p>

                <div>
                    @auth
                        @if(\Illuminate\Support\Facades\Gate::allows('view', $org))
                            <a class="pill-btn btn-solid-black" style="padding: 0.625rem 1.25rem; font-size: 0.8125rem;" href="{{ route('workspace', $org) }}">
                                Buka Workspace Komunitas &rarr;
                            </a>
                        @elseif(!auth()->user()->hasVerifiedEmail())
                            <a class="pill-btn btn-secondary-light" style="padding: 0.625rem 1.25rem; font-size: 0.8125rem;" href="{{ route('verification.notice') }}">
                                Verifikasi Email untuk Bergabung
                            </a>
                        @elseif(\Illuminate\Support\Facades\Gate::allows('join', $org))
                            <form method="POST" action="{{ route('organization.join', $org) }}" x-data="{ joining: false }" @submit="joining = true">
                                @csrf
                                <button type="submit" class="pill-btn btn-primary-red" style="padding: 0.625rem 1.5rem; font-size: 0.8125rem;" :disabled="joining">
                                    <span x-show="!joining">Gabung Langsung ke Komunitas &rarr;</span>
                                    <span x-cloak x-show="joining">Bergabung…</span>
                                </button>
                            </form>
                        @else
                            <p style="font-size: 0.8125rem; color: #dc2626; margin: 0;">Akses bergabung tidak tersedia.</p>
                        @endif
                    @else
                        <a class="pill-btn btn-primary-red" style="padding: 0.625rem 1.5rem; font-size: 0.8125rem;" href="{{ route('login') }}">
                            Masuk untuk Bergabung &rarr;
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-layout>
