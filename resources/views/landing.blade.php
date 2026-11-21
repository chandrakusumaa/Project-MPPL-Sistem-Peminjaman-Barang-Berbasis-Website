<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Organization Inventory — Barang Tertata, Peminjaman Tercatat</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
:root {
  --oi-red: #dc2626;
  --oi-red-dark: #b91c1c;
  --oi-black: #0f172a;
  --oi-ink: #172033;
  --oi-muted: #64748b;
  --oi-line: #e2e8f0;
  --oi-bg: #f8fafc;
  --oi-card-bg: rgba(255, 255, 255, 0.96);
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html {
  scroll-behavior: smooth;
}

body.guest-body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Plus Jakarta Sans", sans-serif;
  background-color: var(--oi-bg);
  color: var(--oi-ink);
  letter-spacing: -0.02em;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
  padding: 1rem 1.5rem;
  -webkit-font-smoothing: antialiased;
}

@media (min-width: 640px) {
  body.guest-body {
    padding: 1.5rem 2.5rem;
  }
}

@media (min-width: 1024px) {
  body.guest-body {
    padding: 1.75rem 3.5rem;
  }
}

/* Background Silhouettes & Ambient Watermark */
.bg-siluet-layer {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  overflow: hidden;
}

.ambient-glow {
  position: absolute;
  top: 5rem;
  left: 50%;
  transform: translateX(-50%);
  width: 650px;
  height: 320px;
  background: radial-gradient(circle, rgba(220, 38, 38, 0.06) 0%, transparent 70%);
  filter: blur(50px);
  border-radius: 9999px;
}

/* Content Container */
.content-wrapper {
  position: relative;
  z-index: 10;
  max-width: 64rem;
  margin-left: auto;
  margin-right: auto;
  width: 100%;
}

/* Sticky Floating Navbar */
.sticky-navbar {
  position: -webkit-sticky;
  position: sticky;
  top: 14px;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1.75rem;
  margin-bottom: 2.25rem;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border: 1px solid rgba(226, 232, 240, 0.9);
  border-radius: 9999px;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
  transition: all 0.2s ease;
}

.sticky-navbar:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.07);
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  text-decoration: none;
  color: var(--oi-black);
}

.nav-links {
  display: none;
  align-items: center;
  gap: 1.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--oi-muted);
}

@media (min-width: 768px) {
  .nav-links {
    display: flex;
  }
}

.nav-link {
  text-decoration: none;
  color: var(--oi-muted);
  padding: 0.375rem 0.625rem;
  border-radius: 9999px;
  transition: all 0.15s ease;
}

.nav-link:hover {
  color: var(--oi-black);
  background-color: rgba(241, 245, 249, 0.8);
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

/* Pill Buttons */
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

.pill-btn:active {
  transform: scale(0.97);
}

.btn-primary-red {
  background-color: var(--oi-red);
  color: #ffffff !important;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.22);
}

.btn-primary-red:hover {
  background-color: var(--oi-red-dark);
  box-shadow: 0 6px 16px rgba(220, 38, 38, 0.3);
}

.btn-solid-black {
  background-color: var(--oi-black);
  color: #ffffff !important;
}

.btn-solid-black:hover {
  background-color: #000000;
}

/* Hero Section */
.hero-section {
  text-align: center;
  padding-top: 1.5rem;
  padding-bottom: 4rem;
  max-width: 48rem;
  margin-left: auto;
  margin-right: auto;
  position: relative;
}

@media (min-width: 640px) {
  .hero-section {
    padding-top: 2rem;
    padding-bottom: 5rem;
  }
}

.hero-headline {
  font-size: 2.75rem;
  line-height: 1.08;
  font-weight: 900;
  letter-spacing: -0.04em;
  color: var(--oi-black);
  max-width: 44rem;
  margin-left: auto;
  margin-right: auto;
}

.text-accent-orange {
  color: #dc2626;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #0f172a 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline-block;
}

@media (min-width: 640px) {
  .hero-headline {
    font-size: 3.75rem;
  }
}

@media (min-width: 768px) {
  .hero-headline {
    font-size: 4.5rem;
  }
}

.hero-subtitle {
  color: var(--oi-muted);
  font-size: 0.9375rem;
  line-height: 1.65;
  margin-top: 1.5rem;
  max-width: 38rem;
  margin-left: auto;
  margin-right: auto;
}

@media (min-width: 640px) {
  .hero-subtitle {
    font-size: 1.0625rem;
  }
}

.hero-cta {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.875rem;
  margin-top: 2.25rem;
  flex-wrap: wrap;
}

.hero-indicators {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.75rem;
  margin-top: 2.75rem;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
  flex-wrap: wrap;
}

.indicator-dot-red {
  color: var(--oi-red);
  font-weight: 800;
}

.indicator-dot-black {
  color: var(--oi-black);
  font-weight: 800;
}

/* Feature Cards Grid */
.features-section {
  padding-bottom: 4.5rem;
  max-width: 64rem;
  margin-left: auto;
  margin-right: auto;
}

.features-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .features-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.feature-card {
  background: var(--oi-card-bg);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(226, 232, 240, 0.95);
  border-radius: 1.25rem;
  padding: 1.75rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 4px 12px -2px rgba(0, 0, 0, 0.03);
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.feature-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.08);
  transform: translateY(-2px);
}

.feature-card.card-accent-red:hover {
  border-color: rgba(220, 38, 38, 0.4);
}

.card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.card-icon-box {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease;
}

.feature-card:hover .card-icon-box {
  transform: scale(1.05);
}

.icon-black {
  background-color: var(--oi-black);
  color: #ffffff;
}

.icon-red {
  background-color: var(--oi-red);
  color: #ffffff;
}

.micro-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 0.25rem 0.625rem;
  font-size: 0.6875rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.micro-badge-red {
  background-color: #fff1f2;
  border-color: #fecdd6;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 800;
  color: var(--oi-black);
  letter-spacing: -0.02em;
  margin-bottom: 0.5rem;
}

.card-description {
  color: var(--oi-muted);
  font-size: 0.8125rem;
  line-height: 1.6;
}

.card-footer {
  margin-top: 1.5rem;
  padding-top: 0.875rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.75rem;
}

.status-valid {
  color: #047857;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.6875rem;
}

.status-dot {
  width: 0.375rem;
  height: 0.375rem;
  border-radius: 9999px;
  background-color: #10b981;
}

.action-link {
  font-weight: 700;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  transition: transform 0.15s ease;
}

.action-link-red {
  color: var(--oi-red);
}

.action-link-black {
  color: var(--oi-black);
}

.feature-card:hover .action-link {
  transform: translateX(2px);
}

/* Call To Action Banner */
.cta-banner {
  padding-top: 3.5rem;
  padding-bottom: 4.5rem;
  max-width: 64rem;
  margin-left: auto;
  margin-right: auto;
  border-top: 1px solid rgba(226, 232, 240, 0.7);
  text-align: center;
}

.cta-banner-content {
  max-width: 38rem;
  margin-left: auto;
  margin-right: auto;
}

.cta-title {
  font-size: 1.625rem;
  font-weight: 900;
  color: var(--oi-black);
  letter-spacing: -0.03em;
}

.cta-subtitle {
  color: var(--oi-muted);
  font-size: 0.875rem;
  margin-top: 0.5rem;
  margin-bottom: 1.5rem;
}

/* Footer */
.site-footer {
  padding-top: 1.75rem;
  padding-bottom: 2.25rem;
  border-top: 1px solid rgba(226, 232, 240, 0.7);
  text-align: center;
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Floating Back to Top Button */
.back-to-top-btn {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  z-index: 50;
  background-color: rgba(15, 23, 42, 0.92);
  color: #ffffff;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.625rem 1.125rem;
  border-radius: 9999px;
  box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.18);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0;
  pointer-events: none;
  transform: translateY(1rem);
}

.back-to-top-btn.is-visible {
  opacity: 1;
  pointer-events: auto;
  transform: translateY(0);
}

.back-to-top-btn:hover {
  background-color: #000000;
  transform: scale(1.05);
}

.back-to-top-btn:active {
  transform: scale(0.95);
}
  </style>
</head>
<body class="guest-body">

  <!-- BACKGROUND SILUET & VECTOR WATERMARK (Halus, Elegan, 4-5% Opacity) -->
  <div class="bg-siluet-layer" aria-hidden="true">
    <svg style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.035;" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="grid-dots" width="32" height="32" patternUnits="userSpaceOnUse">
          <circle cx="2" cy="2" r="1.5" fill="#172033" />
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#grid-dots)" />
    </svg>

    <!-- Siluet 1: Kamera & Tripod (Kiri Atas) -->
    <svg style="position: absolute; left: -3rem; top: 5rem; width: 22rem; height: 22rem; color: #0f172a; opacity: 0.045; transform: rotate(-6deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
      <circle cx="12" cy="13" r="4" />
      <path d="M12 17v4m-3 0h6m-3-4l-3 4m3-4l3 4" stroke-dasharray="2 2" />
    </svg>

    <!-- Siluet 2: Proyektor & Lensa (Kanan Atas) -->
    <svg style="position: absolute; right: -4rem; top: 4rem; width: 24rem; height: 24rem; color: #05214c; opacity: 0.04; transform: rotate(12deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
      <rect x="2" y="7" width="20" height="10" rx="2" />
      <circle cx="8" cy="12" r="3" />
      <path d="M16 10h2m-2 4h2" />
      <path d="M6 17l-2 3m14-3l2 3" />
      <path d="M8 9l12-6m-12 12l12 6" stroke-dasharray="1.5 2" opacity="0.6" />
    </svg>

    <!-- Siluet 3: Flight Case / Rak Penyimpanan Aset (Tengah Bawah) -->
    <svg style="position: absolute; left: 50%; transform: translateX(-50%); bottom: 2rem; width: 40rem; height: 16rem; color: #1e293b; opacity: 0.035;" viewBox="0 0 100 40" fill="none" stroke="currentColor" stroke-width="0.8">
      <rect x="10" y="8" width="80" height="26" rx="3" />
      <line x1="10" y1="18" x2="90" y2="18" stroke-dasharray="2 2" />
      <rect x="44" y="14" width="12" height="8" rx="1.5" />
      <circle cx="20" cy="18" r="2" />
      <circle cx="80" cy="18" r="2" />
      <line x1="30" y1="8" x2="30" y2="34" />
      <line x1="70" y1="8" x2="70" y2="34" />
    </svg>

    <div class="ambient-glow"></div>
  </div>

  <!-- MAIN WRAPPER -->
  <div class="content-wrapper" id="top">

    <!-- STICKY FLOATING NAVBAR -->
    <header class="sticky-navbar">
      <a href="{{ route('landing') }}" class="brand-logo" aria-label="Organization Inventory">
        <img src="{{ asset('images/organizing-inventory-logo.png') }}" alt="Organizing Inventory" class="app-logo" style="height: 38px; width: auto; object-fit: contain;">
      </a>

      <nav class="nav-links" aria-label="Navigasi Utama">
        <a href="#fitur" class="nav-link">Fitur Utama</a>
        <a href="{{ route('organizations') }}" class="nav-link">Jelajahi Komunitas</a>
        <a href="#tentang" class="nav-link">Tentang</a>
      </nav>

      <div class="nav-actions">
        @auth
          <a href="{{ route('dashboard') }}" class="pill-btn btn-solid-black" style="font-size: 0.8125rem; padding: 0.45rem 1.125rem;">
            Dashboard &rarr;
          </a>
        @else
          <a href="{{ route('login') }}" class="nav-link" style="color: #0f172a; font-weight: 700; text-decoration: none; padding: 0.4rem 0.75rem; font-size: 0.875rem;">Masuk</a>
          <a href="{{ route('register') }}" class="pill-btn btn-solid-black" style="font-size: 0.8125rem; padding: 0.45rem 1rem;">Buat Akun</a>
        @endauth
      </div>
    </header>

    <!-- HERO SECTION (Swiss Editorial Style, Minimalist, High Contrast) -->
    <main class="hero-section">
      <h1 class="hero-headline">
        Barang tertata.
        <br>
        <span class="text-accent-orange">Peminjaman tercatat.</span>
      </h1>

      <p class="hero-subtitle">
        Kelola perlengkapan pribadi dan kelompok melalui Komunitas. Temukan barang fisik dengan QR code, ajukan peminjaman instan, dan telusuri pengembaliannya.
      </p>

      <!-- Dual Pill Action Buttons -->
      <div class="hero-cta">
        <a href="{{ route('organizations') }}" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.75rem 1.625rem; gap: 0.5rem;">
          Jelajahi Komunitas &rarr;
        </a>
        @auth
          <a href="{{ route('dashboard') }}" class="pill-btn btn-solid-black" style="font-size: 0.8125rem; padding: 0.75rem 1.625rem;">
            Buka Dashboard Pribadi
          </a>
        @else
          <a href="{{ route('register') }}" class="pill-btn btn-solid-black" style="font-size: 0.8125rem; padding: 0.75rem 1.625rem;">
            Buat akun gratis
          </a>
        @endauth
      </div>

      <!-- Micro Indicators with Checkmarks -->
      <div class="hero-indicators">
        <span><strong class="indicator-dot-red">&#10003;</strong> Satu unit satu kode QR fisik</span>
        <span><strong class="indicator-dot-black">&#10003;</strong> Peran independen per komunitas</span>
        <span><strong class="indicator-dot-red">&#10003;</strong> Riwayat audit terverifikasi</span>
      </div>
    </main>

    <!-- 3 FEATURE CARDS -->
    <section id="fitur" class="features-section">
      <div class="features-grid">

        <!-- Card 1: Satu Barang, Satu Identitas -->
        <article class="feature-card">
          <div>
            <div class="card-top">
              <div class="card-icon-box icon-black">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>

              <!-- Micro-Badge: QR Fisik -->
              <div class="micro-badge" style="font-family: monospace;">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v2h-4v-2zm-4 0h2v4h-2v-4zm4 4h4v4h-4v-4zm-4 2h2v2h-2v-2zm-2-2h2v2h-2v-2z" />
                </svg>
                <strong style="color: var(--oi-black);">AST-0024</strong>
              </div>
            </div>

            <h2 class="card-title">Satu barang, satu identitas</h2>
            <p class="card-description">
              Satu unit fisik memiliki kode QR dan nomor seri unik yang stabil. Bukan sistem stok abstrak, melainkan pelacakan unit nyata di rak atau lemari.
            </p>
          </div>

          <div class="card-footer">
            <span class="status-valid">
              <span class="status-dot"></span> Stiker Fisik Valid
            </span>
            <a href="{{ route('organizations') }}" class="action-link action-link-red">Label QR &rarr;</a>
          </div>
        </article>

        <!-- Card 2: Peran Sesuai Komunitas -->
        <article class="feature-card card-accent-red">
          <div>
            <div class="card-top">
              <div class="card-icon-box icon-red">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>

              <!-- Micro-Badge: Role Isolation -->
              <div class="micro-badge micro-badge-red">
                <strong style="color: var(--oi-red); font-weight: 900;">ADMIN</strong>
                <span style="color: #cbd5e1;">|</span>
                <strong style="color: #475467;">MEMBER</strong>
              </div>
            </div>

            <h2 class="card-title">Peran sesuai Komunitas</h2>
            <p class="card-description">
              Satu akun dapat menjadi Admin di Komunitas miliknya sendiri, dan menjadi Member di komunitas lain tanpa mencampur otorisasi atau hak akses data.
            </p>
          </div>

          <div class="card-footer">
            <span class="status-valid">
              <span class="status-dot status-dot-red"></span> Role Independen
            </span>
            <a href="{{ route('organizations') }}" class="action-link action-link-black">Lihat Komunitas &rarr;</a>
          </div>
        </article>

        <!-- Card 3: Riwayat yang Terjaga -->
        <article class="feature-card">
          <div>
            <div class="card-top">
              <div class="card-icon-box icon-black">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>

              <!-- Micro-Badge: 100% Audit Trail -->
              <div class="micro-badge">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <strong style="color: #047857;">100% TERCATAT</strong>
              </div>
            </div>

            <h2 class="card-title">Riwayat yang terjaga</h2>
            <p class="card-description">
              Setiap serah terima, inspeksi kembali, dan denda terlambat tercatat di log audit. Pembayaran diverifikasi langsung oleh Admin secara transparan.
            </p>
          </div>

          <div class="card-footer">
            <span class="status-valid">
              <span class="status-dot status-dot-blue"></span> Audit Trail Finansial
            </span>
            <a href="{{ route('register') }}" class="action-link action-link-red">Pelajari &rarr;</a>
          </div>
        </article>

      </div>
    </section>

    <!-- CALL TO ACTION BANNER -->
    <section class="cta-banner" id="tentang">
      <div class="cta-banner-content">
        <h2 class="cta-title">Siap merapikan inventaris komunitas Anda?</h2>
        <p class="cta-subtitle">
          Buat ruang inventaris pertama Anda dalam hitungan menit. Gratis, tanpa biaya tersembunyi.
        </p>
        <div style="display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap;">
          <a href="{{ route('register') }}" class="pill-btn btn-primary-red" style="font-size: 0.8125rem; padding: 0.75rem 1.625rem;">
            Mulai Sekarang — Gratis
          </a>
          <a href="{{ route('organizations') }}" class="pill-btn btn-secondary-light" style="font-size: 0.8125rem; padding: 0.75rem 1.625rem;">
            Jelajahi Komunitas
          </a>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="site-footer">
      <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem;">
        <span style="font-weight: 800; color: var(--oi-black);">Organization Inventory</span>
        <span>•</span>
        <span>Sistem Peminjaman Barang Berbasis Website</span>
      </div>
      <div>&copy; {{ date('Y') }} Organization Inventory. Seluruh hak cipta dilindungi.</div>
    </footer>

  </div>

  <!-- FLOATING BACK TO TOP BUTTON -->
  <button id="backToTopBtn" class="back-to-top-btn" type="button" aria-label="Kembali ke atas">
    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
    <span>Kembali ke atas</span>
  </button>

</body>
</html>
