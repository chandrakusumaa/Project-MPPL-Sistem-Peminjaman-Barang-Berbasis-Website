// Authentication handlers
window.loginUser = function(event) {
  if (event) event.preventDefault();
  localStorage.setItem('user_token', 'demo-session-token-valid');
  localStorage.setItem('user_name', 'Wildan Amal');
  localStorage.setItem('user_email', 'wildan@orginventory.id');
  showToast('Masuk berhasil! Mengalihkan ke Dashboard...', 'success');
  setTimeout(() => {
    window.location.href = 'user-dashboard.html';
  }, 400);
};

window.registerUser = function(event) {
  if (event) event.preventDefault();
  localStorage.setItem('user_token', 'demo-session-token-valid');
  localStorage.setItem('user_name', 'Wildan Amal');
  showToast('Akun berhasil dibuat! Mengalihkan ke Dashboard...', 'success');
  setTimeout(() => {
    window.location.href = 'user-dashboard.html';
  }, 400);
};

window.quickLoginDemo = function(role) {
  localStorage.setItem('user_token', 'demo-session-token-valid');
  if (role === 'admin') {
    localStorage.setItem('user_role', 'ADMIN');
    showToast('Masuk sebagai Admin Komunitas (Media Kreatif Lab)...', 'success');
    setTimeout(() => {
      window.location.href = 'workspace.html?org=1&role=admin';
    }, 400);
  } else {
    localStorage.setItem('user_role', 'MEMBER');
    showToast('Masuk sebagai Member Aktif...', 'success');
    setTimeout(() => {
      window.location.href = 'user-dashboard.html';
    }, 400);
  }
};

// Global utility functions for Organization Inventory
window.showToast = function(message, type = 'info') {
  let toastContainer = document.getElementById('toastContainer');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.style.cssText = 'position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 999999; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none;';
    document.body.appendChild(toastContainer);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  let bg = '#0f172a';
  let border = '#334155';
  let icon = 'ℹ️';
  if (type === 'success') { bg = '#15803d'; border = '#16a34a'; icon = '✓'; }
  if (type === 'error' || type === 'danger') { bg = '#b91c1c'; border = '#dc2626'; icon = '✕'; }
  if (type === 'warning') { bg = '#b45309'; border = '#d97706'; icon = '⚠️'; }

  toast.style.cssText = `background: ${bg}; color: white; padding: 0.75rem 1.25rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); pointer-events: auto; display: flex; align-items: center; gap: 0.5rem; border: 1px solid ${border}; transform: translateY(1rem); opacity: 0; transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);`;
  toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
  toastContainer.appendChild(toast);

  requestAnimationFrame(() => {
    toast.style.transform = 'translateY(0)';
    toast.style.opacity = '1';
  });

  setTimeout(() => {
    toast.style.transform = 'translateY(1rem)';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 250);
  }, 3500);
};

// Modal handlers
window.openModal = function(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) {
    console.warn('Modal not found:', modalId);
    return;
  }

  // Close and isolate any other active modals to prevent stacking/overlaps
  document.querySelectorAll('.modal-backdrop, .modal-overlay').forEach(m => {
    if (m !== modal) {
      m.classList.remove('is-open', 'is-active');
      m.style.setProperty('display', 'none', 'important');
      m.style.setProperty('opacity', '0', 'important');
      m.style.setProperty('visibility', 'hidden', 'important');
      m.style.setProperty('pointer-events', 'none', 'important');
    }
  });

  // Activate and guarantee visibility regardless of cache or CSS timing
  modal.classList.add('is-open', 'is-active');
  modal.style.setProperty('display', 'flex', 'important');
  modal.style.setProperty('opacity', '1', 'important');
  modal.style.setProperty('visibility', 'visible', 'important');
  modal.style.setProperty('pointer-events', 'auto', 'important');
  document.body.style.overflow = 'hidden';
};

window.closeModal = function(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('is-open', 'is-active');
    modal.style.setProperty('display', 'none', 'important');
    modal.style.setProperty('opacity', '0', 'important');
    modal.style.setProperty('visibility', 'hidden', 'important');
    modal.style.setProperty('pointer-events', 'none', 'important');
  }
  const remaining = document.querySelectorAll('.modal-backdrop.is-open, .modal-backdrop.is-active, .modal-overlay.is-open, .modal-overlay.is-active');
  if (remaining.length === 0) {
    document.body.style.overflow = '';
  }
};

// Universal borrow modal opener
window.openApplyBorrowModal = function(assetCode, assetName) {
  const modal = document.getElementById('modalApplyBorrow');
  if (modal) {
    if (assetCode) {
      const select = document.getElementById('borrowAssetSelect');
      if (select) {
        let found = false;
        for (let i = 0; i < select.options.length; i++) {
          if (select.options[i].value === assetCode) {
            select.selectedIndex = i;
            found = true;
            break;
          }
        }
        if (!found && assetName) {
          const opt = new Option(`${assetName} · ${assetCode} (Tersedia)`, assetCode, true, true);
          select.add(opt);
        }
      }
    }
    openModal('modalApplyBorrow');
  } else {
    window.location.href = 'my-borrowings.html?action=apply';
  }
};

// Universal alias for openApplyForAsset
window.openApplyForAsset = function(code, name) {
  window.openApplyBorrowModal(code, name);
};

// Universal approve borrow alias
window.approveBorrow = function(borrowerName) {
  if (typeof approveBorrowing === 'function') {
    approveBorrowing('rowApprovalWildan', 'badgeStatusWildan', 'btnGroupWildan', 'Sony Alpha 7 IV');
  }
  showToast(`Permohonan pinjaman ${borrowerName ? 'oleh ' + borrowerName : ''} berhasil disetujui! Unit resmi diserahkan.`, 'success');
};

// Universal handler for borrow submission in my-borrowings.html
window.handleApplyBorrowInPage = function(event) {
  if (event) event.preventDefault();
  closeModal('modalApplyBorrow');

  const select = document.getElementById('borrowAssetSelect');
  let assetName = 'Unit Aset Fisik';
  let assetCode = 'AST-7829-0024';
  if (select && select.selectedIndex >= 0 && select.options[select.selectedIndex]) {
    assetCode = select.value;
    assetName = select.options[select.selectedIndex].text.split('·')[0].trim();
  }

  showToast(`Pengajuan peminjaman untuk ${assetName} (${assetCode}) berhasil dikirim! Menunggu approval Staff lab.`, 'success');

  const tbody = document.querySelector('.data-table tbody');
  if (tbody) {
    const tr = document.createElement('tr');
    tr.style.background = '#fef2f2';
    tr.innerHTML = `
      <td><span class="micro-badge" style="font-family: monospace;">BRW-2026-004</span></td>
      <td><span class="micro-badge" style="font-family: monospace;">${assetCode}</span></td>
      <td><strong>${assetName}</strong></td>
      <td>Media Kreatif Lab</td>
      <td>21 Nov 2026 — <strong>25 Nov 2026</strong></td>
      <td><span class="micro-badge micro-badge-blue"><span class="status-dot status-dot-blue"></span> Menunggu Approval</span></td>
      <td>
        <button onclick="if(confirm('Batalkan pengajuan pinjaman ini?')) { this.closest('tr').remove(); showToast('Pengajuan pinjaman berhasil dibatalkan', 'info'); }" class="pill-btn btn-ghost btn-sm" style="color: #e11d48; font-weight: 700;">Batalkan</button>
      </td>
    `;
    tbody.prepend(tr);
  }
};

// Sidebar Toggle Function (Desktop & Mobile)
window.toggleSidebar = function() {
  const sidebar = document.querySelector('.app-sidebar');
  if (!sidebar) return;

  const isMobile = window.innerWidth <= 1024;
  if (isMobile) {
    sidebar.classList.toggle('is-open');
    document.body.classList.toggle('sidebar-open');
  } else {
    sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
  }
};

// Notification Read All
window.markAllNotificationsRead = function() {
  document.querySelectorAll('.notification-item.unread').forEach(el => {
    el.classList.remove('unread');
    el.style.backgroundColor = '#ffffff';
  });
  const badges = document.querySelectorAll('.notification-badge, .badge-counter');
  badges.forEach(b => b.remove());
  showToast('Semua notifikasi telah ditandai sebagai dibaca', 'success');
};

// Logout User
window.logoutUser = function(event) {
  if (event) event.preventDefault();
  if (confirm('Apakah Anda yakin ingin keluar dari akun?')) {
    localStorage.removeItem('user_token');
    showToast('Anda telah keluar dari sesi.', 'info');
    setTimeout(() => {
      window.location.href = 'login.html';
    }, 600);
  }
};

// Initialize listeners on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  // Close modals when clicking backdrop
  document.querySelectorAll('.modal-backdrop, .modal-overlay').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) {
        closeModal(backdrop.id);
      }
    });
  });

  // ESC key closes active modal
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const activeModal = document.querySelector('.modal-backdrop.is-open, .modal-backdrop.is-active, .modal-overlay.is-open, .modal-overlay.is-active, .modal-backdrop[style*="display: flex"], .modal-overlay[style*="display: flex"]');
      if (activeModal) {
        closeModal(activeModal.id);
      }
    }
  });

  // Hotkey Ctrl + B to toggle sidebar
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
      e.preventDefault();
      toggleSidebar();
    }
  });

  // Restore sidebar preference (desktop only, default expanded as requested)
  const savedState = localStorage.getItem('sidebar_collapsed');
  const sidebar = document.querySelector('.app-sidebar');
  if (sidebar && savedState === 'true' && window.innerWidth > 1024) {
    sidebar.classList.add('collapsed');
    document.body.classList.add('sidebar-collapsed');
  }

  // Click outside to close mobile sidebar
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024) {
      const sb = document.querySelector('.app-sidebar');
      const toggleBtn = document.querySelector('.btn-toggle-sidebar');
      if (sb && sb.classList.contains('is-open')) {
        if (!sb.contains(e.target) && !toggleBtn?.contains(e.target)) {
          sb.classList.remove('is-open');
          document.body.classList.remove('sidebar-open');
        }
      }
    }
  });
});
