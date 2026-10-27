import { useCallback, useEffect, useRef, useState } from 'react';
import { mountInventory } from './InventoryEngine';
import './OrgInventory.css';
const ROUTES = [["Publik", "index.html", "Beranda publik"], ["Publik", "organizations.html", "Jelajahi Ruang"], ["Publik", "organization-detail.html?id=org_komunitas", "Detail organisasi"], ["Publik", "asset-public.html?id=asset_projector", "Detail aset & ajukan pinjam"], ["Autentikasi", "login.html", "Masuk"], ["Autentikasi", "register.html", "Daftar"], ["Autentikasi", "forgot-password.html", "Lupa kata sandi"], ["Akun pengguna", "user-dashboard.html", "Beranda pengguna"], ["Akun pengguna", "dashboard.html", "Dashboard pengguna"], ["Akun pengguna", "my-organizations.html", "Ruang Saya"], ["Akun pengguna", "create-organization.html", "Buat Ruang Inventaris"], ["Akun pengguna", "my-borrowings.html", "Peminjamanku"], ["Akun pengguna", "notifications.html", "Notifikasi"], ["Akun pengguna", "profile.html", "Profil saya"], ["Workspace organisasi", "organization-dashboard.html", "Dashboard organisasi"], ["Workspace organisasi", "inventory.html", "Inventory"], ["Workspace organisasi", "asset-form.html", "Tambah / edit aset"], ["Workspace organisasi", "borrowings.html", "Peminjaman"], ["Workspace organisasi", "borrowing-detail.html?id=brw_02", "Detail & persetujuan pinjam"], ["Workspace organisasi", "damage-reports.html", "Laporan kerusakan"], ["Workspace organisasi", "maintenance.html", "Maintenance"], ["Workspace organisasi", "categories.html", "Kategori"], ["Workspace organisasi", "locations.html", "Lokasi"], ["Workspace organisasi", "members.html", "Anggota"], ["Workspace organisasi", "reports.html", "Laporan"], ["Workspace organisasi", "settings.html", "Pengaturan ruang"], ["Workspace organisasi", "charges.html", "Tagihan & Pembayaran"], ["Akun pengguna", "my-charges.html", "Tagihan Saya"], ["Workspace organisasi", "charge-detail.html?id=chg_demo", "Detail tagihan & verifikasi"], ["Workspace organisasi", "inspection.html?id=brw_01", "Inspeksi pengembalian"], ["Workspace organisasi", "audit.html", "Audit aktivitas"], ["Akun pengguna", "scan-qr.html", "Scan QR"]] as const;
const GROUPS = ["Publik", "Autentikasi", "Akun pengguna", "Workspace organisasi"] as const;
type Props = {
  initialRoute: string;
  storageKey: string;
  title: string;
  autoDemo?: boolean;
};
export function InventoryPrototype({
  initialRoute,
  storageKey,
  title,
  autoDemo = false
}: Props) {
  const root = useRef<HTMLDivElement>(null);
  const first = useRef(true);
  const [route, setRoute] = useState(initialRoute);
  const [visit, setVisit] = useState(0);
  const [wireframe, setWireframe] = useState(false);
  const navigate = useCallback((target: string) => {
    setRoute(target);
    setVisit(v => v + 1);
    window.scrollTo({
      top: 0,
      behavior: 'instant'
    });
  }, []);
  useEffect(() => {
    if (!root.current) return;
    const boot = first.current && autoDemo;
    first.current = false;
    try {
      return mountInventory(root.current, route, navigate, storageKey, boot);
    } catch (error) {
      root.current.innerHTML = '<div class="empty"><h3>Halaman belum termuat</h3><p>Coba pilih halaman lain dari menu pratinjau.</p></div>';
      console.error('OrgInventory preview:', error);
    }
  }, [route, visit, storageKey, autoDemo, navigate]);
  const current = ROUTES.find(r => r[1] === route) || ROUTES.find(r => r[1].split('?')[0] === route.split('?')[0]);
  return <div className={'oi-prototype' + (wireframe ? ' oi-wireframe' : '')}>
    <div className="oi-review-bar" aria-label="Kontrol pratinjau desain">
      <div className="oi-review-title"><span className="oi-review-dot" />{title}<span className="oi-review-count">32 halaman · PRD 0.4 / SRS 1.2</span></div>
      <div className="oi-review-actions">
        <select className="oi-review-pages" aria-label="Pilih halaman prototipe" value={current?.[1] || initialRoute} onChange={e => navigate(e.target.value)}>
          {GROUPS.map(group => <optgroup key={group} label={group}>{ROUTES.filter(r => r[0] === group).map(r => <option key={r[1]} value={r[1]}>{r[2]}</option>)}</optgroup>)}
        </select>
        <div className="oi-review-modes" aria-label="Mode pratinjau">
          <button type="button" aria-pressed={!wireframe} onClick={() => setWireframe(false)}>Desain</button>
          <button type="button" aria-pressed={wireframe} onClick={() => setWireframe(true)}>Wireframe</button>
        </div>
      </div>
    </div>
    <div className="oi-site" lang="id" ref={root}><div className="empty" role="status">Memuat OrgInventory…</div></div>
  </div>;
}