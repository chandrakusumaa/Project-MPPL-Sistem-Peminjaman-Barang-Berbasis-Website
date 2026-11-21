<div style="max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.75rem;">
    <div class="oi-page-head" style="margin-bottom: 0;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin: 0;">Profil Saya</h1>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0.25rem 0 0 0;">Kelola data pribadi, keamanan kata sandi, dan preferensi notifikasi akun.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        <!-- Informasi Pribadi -->
        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <h2 class="panel-title">Informasi Pribadi</h2>
            </div>
            <div class="panel-body oi-panel-body">
                <form wire:submit="save" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                        <x-avatar :name="auth()->user()->name" :image="auth()->user()->avatar" size="lg" />
                        <div>
                            <div style="font-weight: 800; color: #0f172a;">{{ auth()->user()->name }}</div>
                            <div style="font-size: 0.8125rem; color: #64748b;">{{ auth()->user()->email }} · <span class="oi-tag oi-tag-green">Terverifikasi</span></div>
                        </div>
                    </div>

                    <label>
                        <span style="font-weight: 700; font-size: 0.875rem;">Nama Lengkap</span>
                        <input class="oi-input" wire:model="name" style="width: 100%; margin-top: 0.375rem;" required>
                    </label>

                    <label>
                        <span style="font-weight: 700; font-size: 0.875rem;">Foto Avatar (Opsional, JPG/PNG maks 2 MB)</span>
                        <input type="file" wire:model="avatarUpload" accept="image/jpeg,image/png" style="width: 100%; margin-top: 0.375rem;">
                    </label>

                    <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                        <h3 style="font-size: 0.875rem; font-weight: 800; color: #0f172a; margin: 0 0 0.75rem 0;">Preferensi Notifikasi Email</h3>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            @foreach(['membership' => 'Keanggotaan Komunitas', 'borrowing' => 'Aktivitas Peminjaman', 'damage' => 'Laporan Kerusakan & Tagihan'] as $key => $label)
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #334155; cursor: pointer;">
                                    <input type="checkbox" wire:model="{{ $key }}">
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="oi-btn oi-btn-primary" style="border-radius: 9999px; margin-top: 0.5rem;" wire:loading.attr="disabled">
                        Simpan Profil
                    </button>
                </form>
            </div>
        </section>

        <!-- Ubah Kata Sandi -->
        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <h2 class="panel-title">Ubah Kata Sandi</h2>
            </div>
            <div class="panel-body oi-panel-body">
                <form wire:submit="changePassword" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <label>
                        <span style="font-weight: 700; font-size: 0.875rem;">Kata Sandi Saat Ini</span>
                        <input type="password" class="oi-input" wire:model="currentPassword" style="width: 100%; margin-top: 0.375rem;" required>
                    </label>
                    <label>
                        <span style="font-weight: 700; font-size: 0.875rem;">Kata Sandi Baru</span>
                        <input type="password" class="oi-input" wire:model="password" style="width: 100%; margin-top: 0.375rem;" required minlength="8">
                    </label>
                    <label>
                        <span style="font-weight: 700; font-size: 0.875rem;">Konfirmasi Kata Sandi Baru</span>
                        <input type="password" class="oi-input" wire:model="password_confirmation" style="width: 100%; margin-top: 0.375rem;" required minlength="8">
                    </label>
                    <button type="submit" class="oi-btn oi-btn-primary" style="border-radius: 9999px; margin-top: 0.5rem;" wire:loading.attr="disabled">
                        Perbarui Kata Sandi
                    </button>
                </form>
            </div>
        </section>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        <!-- Ubah Email -->
        <section class="panel-card oi-panel">
            <div class="panel-header oi-panel-head">
                <h2 class="panel-title">Ubah Alamat Email</h2>
            </div>
            <div class="panel-body oi-panel-body">
                <form wire:submit="emailChange" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <label>
                        <span style="font-weight: 700; font-size: 0.875rem;">Alamat Email Baru</span>
                        <input type="email" class="oi-input" wire:model="email" style="width: 100%; margin-top: 0.375rem;" placeholder="emailbaru@domain.com" required>
                    </label>
                    <p style="font-size: 0.8125rem; color: #64748b; margin: 0;">Tautan konfirmasi akan dikirimkan ke email baru Anda sebelum perubahan diterapkan.</p>
                    <button type="submit" class="oi-btn oi-btn-secondary" style="border-radius: 9999px;" wire:loading.attr="disabled">
                        Kirim Verifikasi Email Baru
                    </button>
                </form>
            </div>
        </section>

        <!-- Hapus Akun -->
        <section class="panel-card oi-panel" style="border-left: 4px solid #ef4444;">
            <div class="panel-header oi-panel-head">
                <h2 class="panel-title" style="color: #ef4444;">Hapus Akun</h2>
            </div>
            <div class="panel-body oi-panel-body">
                <p style="font-size: 0.8125rem; color: #64748b; margin: 0 0 1rem 0; line-height: 1.5;">
                    Histori audit dan peminjaman Anda akan tetap disimpan demi integritas catatan. Permintaan ditolak jika Anda adalah <strong>Admin terakhir</strong> pada sebuah Komunitas atau masih memiliki tanggungan tagihan terbuka.
                </p>
                <form wire:submit="deleteAccount" wire:confirm="Hapus akun Anda secara permanen?" style="display: flex; flex-direction: column; gap: 1rem;">
                    <label>
                        <span style="font-weight: 700; font-size: 0.875rem;">Masukkan Kata Sandi untuk Konfirmasi</span>
                        <input type="password" class="oi-input" wire:model="deletePassword" style="width: 100%; margin-top: 0.375rem;" required>
                    </label>
                    <button type="submit" class="oi-btn" style="background-color: #ef4444; color: #fff; border-radius: 9999px;" wire:loading.attr="disabled">
                        Hapus Akun Saya
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
