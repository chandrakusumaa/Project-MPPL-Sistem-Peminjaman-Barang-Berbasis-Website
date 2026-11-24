<div id="modalNotifications" class="modal-backdrop" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;" onclick="if(event.target === this) closeModal('modalNotifications')">
  <div class="modal-dialog" style="max-width: 520px; width: 100%; background: #ffffff; border-radius: 1.25rem; box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
    <div class="modal-header" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9;">
      <div style="display: flex; align-items: center; gap: 0.625rem;">
        <svg width="20" height="20" fill="none" stroke="#7f112e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <h3 class="modal-title" style="font-size: 1.0625rem; font-weight: 800; color: #0f172a; margin: 0;">Notifikasi Sistem</h3>
      </div>
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <button type="button" onclick="const dot = document.getElementById('unreadNotifDot'); if(dot) dot.style.display='none';" class="pill-btn btn-ghost btn-sm" style="font-size: 0.75rem; color: #64748b; font-weight: 600; background: none; border: none; cursor: pointer; padding: 0.25rem 0.5rem;" onmouseover="this.style.color='#7f112e'" onmouseout="this.style.color='#64748b'">
          Tandai Semua Dibaca
        </button>
        <button type="button" onclick="closeModal('modalNotifications')" class="modal-close-btn" style="background: none; border: none; font-size: 1.25rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0.25rem;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
      </div>
    </div>
    <div class="modal-body" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 0.875rem; max-height: 65vh; overflow-y: auto;">
      
      <!-- Notif 1: Denda Terbit (Sesuai Foto 2) -->
      <div class="notif-card-item" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.875rem; padding: 1.15rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
          <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
            <span style="font-size: 1.2rem; line-height: 1.2;">⚠️</span>
            <div>
              <div style="font-size: 0.875rem; color: #92400e; font-weight: 800;">Tagihan Denda Terbit: INV-2026-881</div>
              <p style="font-size: 0.8125rem; color: #78350f; margin-top: 0.35rem; margin-bottom: 0; line-height: 1.5;">
                Admin Media Kreatif Lab menerbitkan tagihan keterlambatan 2 hari untuk Tripod Manfrotto 055 sebesar Rp 25.000 (Lunas).
              </p>
              <div style="margin-top: 0.75rem;">
                <a href="{{ route('personal.charges') }}" style="font-size: 0.75rem; padding: 0.45rem 1rem; background: #7f112e; color: #ffffff; text-decoration: none; border-radius: 0.5rem; display: inline-block; font-weight: 700; transition: background 0.15s ease;" onmouseover="this.style.background='#680e2c'" onmouseout="this.style.background='#7f112e'">
                  Lihat Tagihan & Bayar &rarr;
                </a>
              </div>
            </div>
          </div>
          <span style="font-size: 0.6875rem; color: #b45309; white-space: nowrap; font-weight: 600;">10 mnt lalu</span>
        </div>
      </div>

      <!-- Notif 2: Peringatan Jatuh Tempo (Sesuai Foto 2) -->
      <div class="notif-card-item" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.875rem; padding: 1.15rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
          <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
            <span style="font-size: 1.2rem; line-height: 1.2;">🕒</span>
            <div>
              <div style="font-size: 0.875rem; color: #0f172a; font-weight: 800;">Peringatan Batas Kembali Barang (H-2)</div>
              <p style="font-size: 0.8125rem; color: #475467; margin-top: 0.35rem; margin-bottom: 0; line-height: 1.5;">
                Peminjaman Tripod Manfrotto 055 jatuh tempo pada 22 Nov 2026. Harap bawa unit ke meja inspeksi tepat waktu.
              </p>
            </div>
          </div>
          <span style="font-size: 0.6875rem; color: #94a3b8; white-space: nowrap; font-weight: 600;">3 jam lalu</span>
        </div>
      </div>

      <!-- Notif 3: Pengajuan Disetujui (Sesuai Foto 2) -->
      <div class="notif-card-item" style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.875rem; padding: 1.15rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
          <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
            <span style="font-size: 1.2rem; line-height: 1.2; color: #059669; font-weight: 800;">✓</span>
            <div>
              <div style="font-size: 0.875rem; color: #065f46; font-weight: 800;">Pengajuan Pinjaman Disetujui</div>
              <p style="font-size: 0.8125rem; color: #047857; margin-top: 0.35rem; margin-bottom: 0; line-height: 1.5;">
                Staff Media Kreatif Lab telah menyetujui pengajuan pinjaman Audio Recorder Zoom H6. Silakan ambil unit fisik di lab komunitas.
              </p>
            </div>
          </div>
          <span style="font-size: 0.6875rem; color: #059669; white-space: nowrap; font-weight: 600;">1 hari lalu</span>
        </div>
      </div>

    </div>
    <div class="modal-footer" style="padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
      <span style="font-size: 0.75rem; color: #64748b;">Semua transaksi fisik terdata di audit log.</span>
      <button type="button" onclick="closeModal('modalNotifications')" style="padding: 0.5rem 1.35rem; font-size: 0.8125rem; font-weight: 700; background: #05214c; color: #ffffff; border-radius: 0.5rem; border: none; cursor: pointer; transition: background 0.15s ease;" onmouseover="this.style.background='#021430'" onmouseout="this.style.background='#05214c'">
        Tutup
      </button>
    </div>
  </div>
</div>
