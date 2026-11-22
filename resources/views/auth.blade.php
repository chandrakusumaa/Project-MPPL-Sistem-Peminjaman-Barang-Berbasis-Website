<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ match($mode){'register'=>'Buat Akun Baru','forgot-password'=>'Pemulihan Sandi','reset-password'=>'Kata Sandi Baru',default=>'Selamat Datang'} }} — Organizing Inventory</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1.5">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    body.guest-body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
      letter-spacing: -0.015em;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
      padding: 2.5rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      -webkit-font-smoothing: antialiased;
    }
    .auth-card-wrapper {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 28rem;
      margin: auto;
    }
    .auth-card-box {
      background: #ffffff;
      border: 1px solid #eef2f6;
      border-radius: 1.75rem;
      padding: 2.5rem 2.25rem;
      box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.02);
    }
    .form-group {
      margin-bottom: 1.25rem;
    }
    .form-label {
      display: block;
      font-size: 0.8125rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 0.45rem;
    }
    .form-input {
      width: 100%;
      border: 1px solid #e2e8f0;
      background-color: #ffffff;
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
      color: #0f172a;
      transition: all 0.15s ease;
      box-sizing: border-box;
    }
    .form-input:focus {
      outline: none;
      border-color: #80142B;
      box-shadow: 0 0 0 3px rgba(128, 20, 43, 0.12);
    }
    .btn-maroon {
      background-color: #80142B;
      color: #ffffff !important;
      border-radius: 9999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid transparent;
      white-space: nowrap;
      box-shadow: 0 4px 14px rgba(128, 20, 43, 0.25);
    }
    .btn-maroon:hover {
      background-color: #671023;
      box-shadow: 0 6px 18px rgba(128, 20, 43, 0.35);
    }
    .alert-box {
      padding: 0.75rem 1rem;
      border-radius: 0.75rem;
      font-size: 0.8125rem;
      font-weight: 600;
      margin-bottom: 1.25rem;
      line-height: 1.5;
    }
    .alert-danger {
      background: #fff1f2;
      border: 1px solid #fecdd6;
      color: #991b1b;
    }
    .alert-success {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #065f46;
    }
  </style>
</head>
<body class="guest-body">

  <!-- Ambient Dotted Grid & Watermark Silhouette Background -->
  <div style="position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden;" aria-hidden="true">
    <svg style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.04;" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="grid-dots-auth" width="28" height="28" patternUnits="userSpaceOnUse">
          <circle cx="2" cy="2" r="1.2" fill="#0f172a" />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#grid-dots-auth)" />
    </svg>

    <!-- Faint Silhouette: Kamera (Kiri) -->
    <svg style="position: absolute; left: 8%; top: 22%; width: 24rem; height: 24rem; color: #0f172a; opacity: 0.085; transform: rotate(-5deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
      <circle cx="12" cy="13" r="4" stroke-width="1.4" />
      <circle cx="12" cy="13" r="2.2" stroke-width="1.2" opacity="0.6" />
    </svg>

    <!-- Faint Silhouette: Proyektor / Gadget (Kanan) -->
    <svg style="position: absolute; right: 8%; top: 25%; width: 26rem; height: 26rem; color: #0f172a; opacity: 0.085; transform: rotate(8deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
      <rect x="2" y="7" width="20" height="10" rx="2" />
      <circle cx="8" cy="12" r="3" stroke-width="1.4" />
      <path d="M16 11h2m-2 2h2" stroke-linecap="round" />
      <path d="M6 17l-2 3M18 17l2 3" stroke-linecap="round" stroke-width="1.4" />
    </svg>
  </div>

  <div class="auth-card-wrapper">
    <!-- Brand Logo Top Center -->
    <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
      <a href="{{ route('landing') }}" style="display: inline-flex; justify-content: center; text-decoration: none;">
        <img src="{{ asset('images/logo.png') }}" alt="Organizing Inventory" style="height: 48px; width: auto; object-fit: contain;">
      </a>
    </div>

    <!-- Main Card Box -->
    <div class="auth-card-box">
      <h1 style="font-size: 1.625rem; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.35rem 0;">
        {{ match($mode){
          'register' => 'Buat Akun Baru',
          'forgot-password' => 'Pemulihan Sandi',
          'reset-password' => 'Kata Sandi Baru',
          default => 'Selamat Datang'
        } }}
      </h1>
      <p style="font-size: 0.8125rem; font-weight: 400; color: #64748b; margin: 0 0 1.75rem 0; line-height: 1.5;">
        {{ match($mode){
          'register' => 'Mulai pinjam barang fisik dan kelola komunitas organisasi Anda.',
          'forgot-password' => 'Masukkan email terdaftar untuk menerima tautan atur ulang sandi.',
          'reset-password' => 'Tentukan kata sandi baru yang aman untuk akun Anda.',
          default => 'Masuk ke akun Anda untuk mengelola komunitas & peminjaman.'
        } }}
      </p>

      <!-- Status Notification -->
      @if(session('status'))
        <div class="alert-box alert-success">
          {{ session('status') }}
        </div>
      @endif

      <!-- Error Validation Box -->
      @if($errors->any())
        <div class="alert-box alert-danger">
          <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- FORM -->
      <form method="POST" action="{{ match($mode){
        'register' => route('register.store'),
        'forgot-password' => route('password.email'),
        'reset-password' => route('password.update'),
        default => url('/login')
      } }}">
        @csrf

        @if($mode === 'reset-password')
          <input type="hidden" name="token" value="{{ $token }}">
        @endif

        @if($mode === 'login' && !app()->isProduction())
          <!-- Quick Demo 1-Click Fill -->
          <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 0.75rem; padding: 0.65rem 0.75rem; margin-bottom: 1.25rem;">
            <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.4rem; display: flex; align-items: center; justify-content: space-between;">
              <span>Demo Cepat (Localhost):</span>
              <span style="font-size: 0.65rem; color: #94a3b8; font-weight: 400;">Otomatis isi form</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button type="button" onclick="document.getElementById('auth-email').value='admin@example.test'; document.getElementById('auth-password').value='DemoPassword123!';" style="flex: 1; padding: 0.35rem 0.5rem; font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; color: #0f172a; cursor: pointer; transition: all 0.15s ease;">
                Admin (Raka)
              </button>
              <button type="button" onclick="document.getElementById('auth-email').value='member@example.test'; document.getElementById('auth-password').value='DemoPassword123!';" style="flex: 1; padding: 0.35rem 0.5rem; font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; color: #0f172a; cursor: pointer; transition: all 0.15s ease;">
                Member (Sinta)
              </button>
            </div>
          </div>
        @endif

        @if($mode === 'register')
          <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-input" value="{{ old('name') }}" required maxlength="150" autocomplete="name" placeholder="Contoh: Wildan Amal Abdillah" autofocus>
          </div>

          <div class="form-group">
            <label class="form-label">Alamat Email (Kampus / Pribadi)</label>
            <input type="email" id="auth-email" name="email" class="form-input" value="{{ old('email', request('email')) }}" required autocomplete="email" placeholder="nama@kampus.ac.id">
          </div>

          <div class="form-group">
            <label class="form-label">Nomor WhatsApp Aktif</label>
            <input type="tel" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="081234567890">
            <small style="display: block; font-size: 0.72rem; color: #94a3b8; margin-top: 0.35rem;">Diperlukan untuk konfirmasi serah terima fisik & pemberitahuan H-1.</small>
          </div>

          <div class="form-group">
            <label class="form-label">Kata Sandi</label>
            <input type="password" id="auth-password" name="password" class="form-input" required autocomplete="new-password" placeholder="Minimal 8 karakter">
          </div>
        @elseif($mode === 'login')
          <div class="form-group">
            <label class="form-label">Alamat Email</label>
            <input type="email" id="auth-email" name="email" class="form-input" value="{{ old('email', request('email', 'wildan@orginventory.id')) }}" required autocomplete="email" placeholder="wildan@orginventory.id" autofocus>
          </div>

          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem;">
              <label class="form-label" style="margin-bottom: 0;">Kata Sandi</label>
              <a href="{{ route('password.request') }}" style="font-size: 0.75rem; color: #80142B; text-decoration: none; font-weight: 600;">Lupa Sandi?</a>
            </div>
            <input type="password" id="auth-password" name="password" class="form-input" required autocomplete="current-password" placeholder="••••••••">
          </div>
        @else
          <div class="form-group">
            <label class="form-label">Alamat Email</label>
            <input type="email" id="auth-email" name="email" class="form-input" value="{{ old('email', request('email')) }}" required autocomplete="email" placeholder="nama@kampus.ac.id" autofocus>
          </div>

          @if($mode === 'reset-password')
            <div class="form-group">
              <label class="form-label">Kata Sandi Baru</label>
              <input type="password" id="auth-password" name="password" class="form-input" required autocomplete="new-password" placeholder="Minimal 8 karakter">
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Kata Sandi</label>
              <input type="password" name="password_confirmation" class="form-input" required autocomplete="new-password" placeholder="••••••••">
            </div>
          @endif
        @endif

        <button type="submit" class="btn-maroon" style="width: 100%; padding: 0.8rem 1rem; font-size: 0.875rem; margin-top: 0.75rem; gap: 0.5rem; cursor: pointer;">
          {{ match($mode){
            'register' => 'Daftar Akun Sekarang →',
            'forgot-password' => 'Kirim Tautan Pemulihan →',
            'reset-password' => 'Simpan Kata Sandi Baru →',
            default => 'Masuk ke Akun →'
          } }}
        </button>
      </form>

      <!-- Bottom Navigation Links -->
      <div style="text-align: center; margin-top: 1.75rem; font-size: 0.84rem; color: #64748b;">
        @if($mode === 'login')
          Belum memiliki akun? <a href="{{ route('register') }}" style="color: #0f172a; font-weight: 700; text-decoration: none; margin-left: 0.2rem;">Daftar gratis</a>
        @elseif($mode === 'register')
          Sudah memiliki akun? <a href="{{ route('login') }}" style="color: #0f172a; font-weight: 700; text-decoration: none; margin-left: 0.2rem;">Masuk di sini</a>
        @else
          <a href="{{ route('login') }}" style="color: #80142B; font-weight: 700; text-decoration: none;">&larr; Kembali ke halaman Masuk</a>
        @endif
      </div>
    </div>
  </div>

</body>
</html>
