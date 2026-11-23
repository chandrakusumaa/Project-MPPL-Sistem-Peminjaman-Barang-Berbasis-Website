<x-layout>
    <div style="max-width: 600px; margin: 4rem auto; padding: 0 1.5rem;">
        <div class="panel-card" style="text-align: center; padding: 3rem 2rem;">
            <div style="width: 56px; height: 56px; border-radius: 1rem; background-color: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
                <x-icon name="warning" :size="32" />
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0 0 0.75rem 0;">Keanggotaan Diperlukan</h1>
            <p style="font-size: 0.9375rem; color: #64748b; line-height: 1.6; margin: 0 0 2rem 0;">
                Anda harus menjadi anggota aktif Komunitas <strong>{{ $org->name }}</strong> untuk membuka katalog dan detail inventaris.
            </p>
            <a class="oi-btn oi-btn-primary" style="border-radius: 9999px; padding: 0.75rem 2rem; display: inline-flex;" href="{{ route('organization.public', $org) }}">
                Lihat Profil & Gabung Komunitas →
            </a>
        </div>
    </div>
</x-layout>
