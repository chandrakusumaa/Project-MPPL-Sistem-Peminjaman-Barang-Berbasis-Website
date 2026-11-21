<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ match($mode){'register'=>'Daftar Akun Baru','forgot-password'=>'Lupa Kata Sandi','reset-password'=>'Atur Ulang Sandi',default=>'Masuk'} }} — Organization Inventory</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1.0">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    body.guest-body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Plus Jakarta Sans", sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
      letter-spacing: -0.02em;
      min-height: 100vh;
      position: relative;
      overflow-x: hidden;
      padding: 1.5rem 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      -webkit-font-smoothing: antialiased;
    }
    .auth-card-wrapper {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 27rem;
      margin: auto;
    }
    .auth-card-box {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 1.5rem;
      padding: 2.25rem 2rem;
      box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    }
    .form-group {
      margin-bottom: 1.125rem;
    }
    .form-label {
      display: block;
      font-size: 0.8125rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.375rem;
    }
    .form-input {
      width: 100%;
      border: 1px solid #cbd5e1;
      background-color: #ffffff;
      border-radius: 0.75rem;
      padding: 0.65rem 0.875rem;
      font-size: 0.875rem;
      color: #0f172a;
      transition: all 0.15s ease;
      box-sizing: border-box;
    }
    .form-input:focus {
      outline: none;
      border-color: #dc2626;
      box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }
    .pill-btn {
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
    }
    .btn-primary-red {
      background-color: #dc2626;
      color: #ffffff !important;
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
    }
    .btn-primary-red:hover {
      background-color: #b91c1c;
      box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
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

  <!-- Ambient Watermark & Siluet Background -->
  <div style="position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden;" aria-hidden="true">
    <svg style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.035;" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="grid-dots-auth" width="32" height="32" patternUnits="userSpaceOnUse">
          <circle cx="2" cy="2" r="1.5" fill="#172033" />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#grid-dots-auth)" />
    </svg>

    <!-- Siluet 1: Kamera (Kiri) -->
    <svg style="position: absolute; left: 4%; top: 6rem; width: 20rem; height: 20rem; color: #0f172a; opacity: 0.035; transform: rotate(-6deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
      <circle cx="12" cy="13" r="4" />
    </svg>

    <!-- Siluet 2: Proyektor (Kanan) -->
    <svg style="position: absolute; right: 4%; top: 8rem; width: 22rem; height: 22rem; color: #05214c; opacity: 0.03; transform: rotate(10deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
      <rect x="2" y="7" width="20" height="10" rx="2" />
      <circle cx="8" cy="12" r="3" />
    </svg>
  </div>

  <div class="auth-card-wrapper">
    <!-- Brand Logo -->
    <div style="display: flex; justify-content: center; margin-bottom: 1.75rem;">
      <a href="{{ route('landing') }}" style="display: inline-flex; justify-content: center;">
        <img src="{{ asset('images/logo.png') }}" alt="Organization Inventory" style="height: 44px; width: auto; object-fit: contain;">
      </a>
    </div>

    <!-- Main Card Box -->
    <div class="auth-card-box">
      @if(in_array($mode, ['login', 'register']))
        <div style="display: flex; background: #f1f5f9; border-radius: 9999px; padding: 0.25rem; margin-bottom: 1.5rem; gap: 0.25rem;">
          <a href="{{ route('login') }}" style="flex: 1; text-align: center; padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 800; text-decoration: none; transition: all 0.2s ease; {{ $mode === 'login' ? 'background: #ffffff; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.08);' : 'color: #64748b;' }}">
            Masuk
          </a>
          <a href="{{ route('register') }}" style="flex: 1; text-align: center; padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 800; text-decoration: none; transition: all 0.2s ease; {{ $mode === 'register' ? 'background: #ffffff; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.08);' : 'color: #64748b;' }}">
            Daftar
          </a>
        </div>
      @endif

      <h1 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.25rem 0;">
        {{ match($mode){
          'register' => 'Buat Akun Baru',
          'forgot-password' => 'Pemulihan Sandi',
          'reset-password' => 'Kata Sandi Baru',
          default => 'Selamat Datang'
        } }}
      </h1>
      <p style="font-size: 0.8125rem; color: #64748b; margin: 0 0 1.5rem 0;">
        {{ match($mode){
          'register' => 'Daftar untuk mengelola komunitas dan meminjam peralatan fisik.',
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

        @if($mode === 'register')
          <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-input" value="{{ old('name') }}" required maxlength="150" autocomplete="name" placeholder="Nama Anda" autofocus>
          </div>
        @endif

        @if($mode === 'login' && !app()->isProduction())
          <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 0.75rem; padding: 0.75rem; margin-bottom: 1.125rem;">
            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
              <span>Akun Demo Cepat (Localhost):</span>
              <span style="font-size: 0.6875rem; color: #94a3b8; font-weight: 400;">Klik untuk isi otomatis</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button type="button" onclick="document.getElementById('auth-email').value='admin@example.test'; document.getElementById('auth-password').value='DemoPassword123!';" style="flex: 1; padding: 0.375rem 0.625rem; font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; color: #0f172a; cursor: pointer; transition: all 0.15s ease;">
                Admin (Raka)
              </button>
              <button type="button" onclick="document.getElementById('auth-email').value='member@example.test'; document.getElementById('auth-password').value='DemoPassword123!';" style="flex: 1; padding: 0.375rem 0.625rem; font-size: 0.75rem; font-weight: 700; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; color: #0f172a; cursor: pointer; transition: all 0.15s ease;">
                Member (Sinta)
              </button>
            </div>
          </div>
        @endif

        <div class="form-group">
          <label class="form-label">Alamat Email</label>
          <input type="email" id="auth-email" name="email" class="form-input" value="{{ old('email', request('email')) }}" required autocomplete="email" placeholder="nama@email.com" @if($mode!=='register') autofocus @endif>
        </div>

        @if($mode !== 'forgot-password')
          <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">
              <label class="form-label" style="margin-bottom: 0;">Kata Sandi</label>
              @if($mode === 'login')
                <a href="{{ route('password.request') }}" style="font-size: 0.75rem; color: #dc2626; text-decoration: none; font-weight: 700;">Lupa Sandi?</a>
              @endif
            </div>
            <input type="password" id="auth-password" name="password" class="form-input" required autocomplete="{{ $mode === 'login' ? 'current-password' : 'new-password' }}" placeholder="••••••••">
          </div>
        @endif

        @if(in_array($mode, ['register', 'reset-password']))
          <div class="form-group">
            <label class="form-label">Konfirmasi Kata Sandi</label>
            <input type="password" name="password_confirmation" class="form-input" required autocomplete="new-password" placeholder="••••••••">
          </div>
        @endif

        <button type="submit" class="pill-btn btn-primary-red" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.875rem; margin-top: 0.5rem; gap: 0.5rem;">
          {{ match($mode){
            'register' => 'Daftar Sekarang',
            'forgot-password' => 'Kirim Tautan Pemulihan',
            'reset-password' => 'Simpan Kata Sandi Baru',
            default => 'Masuk ke Akun'
          } }}
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
      </form>

      <!-- Bottom Navigation Links -->
      <div style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: #64748b;">
        @if($mode === 'login')
          Belum memiliki akun? <a href="{{ route('register') }}" style="color: #dc2626; font-weight: 800; text-decoration: none; margin-left: 0.25rem;">Daftar gratis &rarr;</a>
        @elseif($mode === 'register')
          Sudah memiliki akun? <a href="{{ route('login') }}" style="color: #dc2626; font-weight: 800; text-decoration: none; margin-left: 0.25rem;">Masuk ke akun &rarr;</a>
        @else
          <a href="{{ route('login') }}" style="color: #dc2626; font-weight: 800; text-decoration: none;">&larr; Kembali ke halaman Masuk</a>
        @endif
      </div>
    </div>
  </div>

</body>
</html>
