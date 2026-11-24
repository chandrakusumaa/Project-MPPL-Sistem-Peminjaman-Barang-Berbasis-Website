<div class="page-container" style="max-width: 48rem; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem; padding-bottom: 3.5rem;">
    <!-- PAGE HEADER -->
    <div class="page-header" style="margin-bottom: 0;">
        <h1 class="page-title" style="font-size: 1.75rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.35rem 0;">
            Profil & Keamanan Akun
        </h1>
        <p class="page-subtitle" style="font-size: 0.875rem; font-weight: 400; color: #64748b; margin: 0;">
            Perbarui data kontak Anda untuk kelancaran verifikasi peminjaman barang fisik.
        </p>
    </div>

    <x-feedback />

    <!-- CARD 1: INFORMASI KONTAK -->
    <section class="panel-card" style="padding: 2rem; border-radius: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
            <h2 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0;">Informasi Kontak Pengguna</h2>
            <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem; margin-bottom: 0;">
                Data ini digunakan oleh staf organisasi untuk konfirmasi serah terima fisik barang.
            </p>
        </div>

        <form wire:submit="save" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div class="form-group">
                <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Nama Lengkap
                </label>
                <input type="text" class="form-input" wire:model="name" required style="max-width: 440px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem;">
                @error('name') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Alamat Email Utama
                </label>
                <input type="email" class="form-input" value="{{ auth()->user()->email }}" style="max-width: 440px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem; background: #f8fafc;" readonly>
                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Email terverifikasi ✓</small>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Nomor WhatsApp / Telepon Aktif
                </label>
                <input type="tel" class="form-input" placeholder="081234567890" value="081234567890" style="max-width: 360px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem;">
            </div>

            <div style="display: flex; justify-content: flex-start; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid #f8fafc;">
                <button type="submit" class="pill-btn btn-solid-black btn-md" style="box-shadow: 0 4px 14px rgba(5, 33, 76, 0.22); font-weight: 800; padding: 0.75rem 1.625rem; display: inline-flex; align-items: center; gap: 0.5rem;" wire:loading.attr="disabled">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </section>

    <!-- CARD 2: UBAH KATA SANDI -->
    <section class="panel-card" style="padding: 2rem; border-radius: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="margin-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
            <h2 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0;">Ubah Kata Sandi</h2>
            <p style="font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem; margin-bottom: 0;">
                Pastikan kata sandi baru Anda minimal 8 karakter dengan kombinasi yang aman.
            </p>
        </div>

        <form wire:submit="changePassword" style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div class="form-group">
                <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Kata Sandi Saat Ini
                </label>
                <input type="password" class="form-input" wire:model="currentPassword" placeholder="Masukkan kata sandi lama Anda" required style="max-width: 380px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem;">
                @error('currentPassword') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Kata Sandi Baru
                </label>
                <input type="password" class="form-input" wire:model="password" placeholder="Minimal 8 karakter kombinasi huruf & angka" required style="max-width: 380px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem;">
                @error('password') <span style="font-size: 0.75rem; color: #dc2626; font-weight: 600; display: block; margin-top: 0.25rem;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" style="font-size: 0.8125rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem;">
                    Ulangi Kata Sandi Baru
                </label>
                <input type="password" class="form-input" wire:model="password_confirmation" placeholder="Ketik ulang kata sandi baru" required style="max-width: 380px; width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; font-size: 0.875rem;">
            </div>

            <div style="display: flex; justify-content: flex-start; margin-top: 0.5rem; padding-top: 1rem; border-top: 1px solid #f8fafc;">
                <button type="submit" class="pill-btn btn-primary-red btn-md" style="box-shadow: 0 4px 14px rgba(127, 17, 46, 0.25); font-weight: 800; padding: 0.75rem 1.625rem; display: inline-flex; align-items: center; gap: 0.5rem;" wire:loading.attr="disabled">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </section>

    <!-- CARD 3: KELUAR DARI AKUN (RED ACCENT) -->
    <section class="panel-card" style="padding: 1.75rem 2rem; border-left: 4px solid #7f112e; border-radius: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-left-width: 4px; border-left-color: #7f112e;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">
                    Keluar dari Akun
                </h3>
                <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">
                    Akhiri sesi Anda pada perangkat ini. Anda dapat masuk kembali kapan saja.
                </p>
            </div>

            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="pill-btn btn-primary-red btn-md" style="font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; box-shadow: 0 4px 14px rgba(127, 17, 46, 0.25);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar Sekarang
                </button>
            </form>
        </div>
    </section>
</div>
