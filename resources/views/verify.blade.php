<x-layout>
    <div style="max-width: 540px; margin: 4rem auto; padding: 0 1.5rem;">
        <div class="panel-card" style="text-align: center; padding: 3rem 2rem;">
            <div style="width: 56px; height: 56px; border-radius: 1rem; background-color: #f1f5f9; color: #0f172a; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
                <x-icon name="check" :size="32" />
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0 0 0.75rem 0;">Verifikasi Email Anda</h1>
            <p style="font-size: 0.9375rem; color: #64748b; line-height: 1.6; margin: 0 0 2rem 0;">
                Periksa kotak masuk email Anda untuk mengaktifkan akun. Pada tahap pengembangan, tautan verifikasi tersedia di log Laravel.
            </p>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="oi-btn oi-btn-primary" style="border-radius: 9999px; padding: 0.75rem 2rem;">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>
        </div>
    </div>
</x-layout>
