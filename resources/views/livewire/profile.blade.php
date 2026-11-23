<div style="max-width: 680px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.75rem; padding-bottom: 3rem;">
    <!-- PAGE HEADER (SESUAI FOTO 3) -->
    <div>
        <h1 style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0 0 0.35rem 0;">
            Profil & Keamanan Akun
        </h1>
        <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0;">
            Perbarui data kontak Anda untuk kelancaran verifikasi peminjaman barang.
        </p>
    </div>

    <x-feedback />

    <!-- CARD 1: INFORMASI KONTAK -->
    <section style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 1.25rem 0;">
            Informasi Kontak
        </h2>

        <form wire:submit="save" style="display: flex; flex-direction: column; gap: 1.15rem;">
            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem;">
                    Nama Lengkap
                </label>
                <input type="text" class="form-input" wire:model="name" value="{{ auth()->user()->name }}" style="max-width: 440px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem;" required>
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem;">
                    Alamat Email
                </label>
                <input type="email" class="form-input" value="{{ auth()->user()->email }}" style="max-width: 440px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem; background: #f8fafc;" readonly>
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem;">
                    Nomor WhatsApp Aktif
                </label>
                <input type="tel" class="form-input" placeholder="081234567890" style="max-width: 320px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem;">
            </div>

            <div style="margin-top: 0.5rem;">
                <button type="submit" class="pill-btn btn-solid-black" style="padding: 0.65rem 1.6rem; font-size: 0.8125rem; font-weight: 700; border-radius: 9999px; cursor: pointer; border: none;" wire:loading.attr="disabled">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </section>

    <!-- CARD 2: UBAH KATA SANDI -->
    <section style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <h2 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 1.25rem 0;">
            Ubah Kata Sandi
        </h2>

        <form wire:submit="changePassword" style="display: flex; flex-direction: column; gap: 1.15rem;">
            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem;">
                    Kata Sandi Saat Ini
                </label>
                <input type="password" class="form-input" wire:model="current_password" placeholder="••••••••" style="max-width: 360px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem;" required>
            </div>

            <div>
                <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.4rem;">
                    Kata Sandi Baru
                </label>
                <input type="password" class="form-input" wire:model="password" placeholder="Minimal 8 karakter" style="max-width: 360px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 0.875rem;" required>
            </div>

            <div style="margin-top: 0.5rem;">
                <button type="submit" class="btn-maroon" style="padding: 0.65rem 1.6rem; font-size: 0.8125rem; font-weight: 700; border-radius: 9999px; cursor: pointer; border: none;" wire:loading.attr="disabled">
                    Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </section>

    <!-- CARD 3: KELUAR DARI AKUN (RED ACCENT) -->
    <section style="background: #ffffff; border: 1px solid #e2e8f0; border-left: 4px solid #80142B; border-radius: 1.25rem; padding: 1.5rem 1.75rem; display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
        <div>
            <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">
                Keluar dari Akun
            </h3>
            <p style="font-size: 0.78rem; font-weight: 400; color: #64748b; margin: 0;">
                Akhiri sesi Anda pada perangkat ini. Anda dapat masuk kembali kapan saja.
            </p>
        </div>

        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="btn-maroon" style="padding: 0.6rem 1.25rem; font-size: 0.8125rem; font-weight: 700; border-radius: 9999px; gap: 0.4rem; cursor: pointer; border: none; display: inline-flex; align-items: center;">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar Sekarang
            </button>
        </form>
    </section>
</div>
