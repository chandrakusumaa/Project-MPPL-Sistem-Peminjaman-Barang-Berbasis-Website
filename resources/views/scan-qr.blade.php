<x-layout context="public">
    <div style="max-width: 680px; margin: 0 auto; text-align: center; padding: 1.5rem 1rem 4rem 1rem;">
        
        <!-- TITLE & SUBTITLE (SESUAI FOTO 5) -->
        <h1 style="font-size: 1.875rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 0.5rem 0;">
            Pemindai QR Cepat
        </h1>
        <p style="font-size: 0.84rem; color: #64748b; max-width: 500px; margin: 0 auto 2rem auto; line-height: 1.5;">
            Arahkan kamera ke stiker QR fisik barang untuk menelusuri riwayat, status ketersediaan, atau verifikasi serah terima.
        </p>

        <!-- DARK CAMERA VIEWFINDER BOX -->
        <div style="background: #0f172a; border-radius: 1.5rem; padding: 2.5rem 1.5rem; position: relative; overflow: hidden; margin-bottom: 1.5rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);">
            <!-- Status Badge -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <span style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 9999px; padding: 0.25rem 0.75rem; font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.04em;">
                    ● KAMERA AKTIF - 1080p
                </span>
                <div style="display: flex; gap: 0.5rem; color: #94a3b8;">
                    <span style="cursor: pointer;">⚡</span>
                    <span style="cursor: pointer;">🔄</span>
                </div>
            </div>

            <!-- Red Target Frame -->
            <div style="width: 220px; height: 160px; margin: 0 auto 2rem auto; position: relative; display: flex; align-items: center; justify-content: center;">
                <!-- Corners -->
                <div style="position: absolute; top: 0; left: 0; width: 24px; height: 24px; border-top: 3px solid #80142B; border-left: 3px solid #80142B; border-top-left-radius: 8px;"></div>
                <div style="position: absolute; top: 0; right: 0; width: 24px; height: 24px; border-top: 3px solid #80142B; border-right: 3px solid #80142B; border-top-right-radius: 8px;"></div>
                <div style="position: absolute; bottom: 0; left: 0; width: 24px; height: 24px; border-bottom: 3px solid #80142B; border-left: 3px solid #80142B; border-bottom-left-radius: 8px;"></div>
                <div style="position: absolute; bottom: 0; right: 0; width: 24px; height: 24px; border-bottom: 3px solid #80142B; border-right: 3px solid #80142B; border-bottom-right-radius: 8px;"></div>

                <!-- Red Scan Line -->
                <div style="position: absolute; left: 10px; right: 10px; height: 2px; background: #80142B; box-shadow: 0 0 12px #80142B;"></div>

                <!-- Center QR Silhouette -->
                <svg width="48" height="48" fill="none" stroke="rgba(255,255,255,0.2)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>

            <p style="font-size: 0.75rem; color: #94a3b8; margin: 0;">
                Posisikan kode QR 26-karakter di dalam kotak pemindai
            </p>
        </div>

        <!-- SIMULASI SCAN BUTTON (MAROON) -->
        <div style="margin-bottom: 2rem;">
            <button type="button" class="btn-maroon" style="width: 100%; padding: 0.85rem 1.25rem; font-size: 0.875rem; gap: 0.5rem;" onclick="document.getElementById('manual-asset-code').value='AST-0024'; alert('Kode AST-0024 terdeteksi dari QR code!');">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                Simulasi Scan QR Fisik (AST-0024)
            </button>
        </div>

        <div style="font-size: 0.6875rem; font-weight: 800; color: #94a3b8; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 1.5rem;">
            ATAU INPUT MANUAL
        </div>

        <!-- MANUAL INPUT CARD -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 1.75rem; text-align: left; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <label style="display: block; font-size: 0.8125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">
                Kode Fisik Barang (26 Karakter / Short Code)
            </label>
            <input type="text" id="manual-asset-code" class="form-input" placeholder="Contoh: AST-0024" value="AST-0024" style="width: 100%; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.75rem 1rem; margin-bottom: 1.25rem;">

            <button type="button" class="pill-btn btn-solid-black" style="width: 100%; padding: 0.75rem 1.25rem; font-size: 0.84rem; justify-content: center;" onclick="window.location.href='{{ route('organizations') }}';">
                Telusuri Aset Fisik &rarr;
            </button>
        </div>

    </div>
</x-layout>
