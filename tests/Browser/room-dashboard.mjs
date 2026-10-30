import { chromium } from 'playwright-core';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

const base = process.env.APP_URL || 'http://127.0.0.1:8000';
const output = process.env.VISUAL_OUTPUT || 'docs/visual/room-dashboard';
fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium',
    headless: true, args: ['--no-sandbox'],
});
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 });
const errors = [];
page.on('pageerror', error => errors.push(error.message));
const screenshot = name => page.screenshot({ path: path.join(output, name) });
const room = base + '/o/peralatan-raka';
let temporaryRoom;
let metrics = {};
async function login(account) {
    await page.goto(base + '/login');
    await page.getByLabel('Email', { exact: true }).fill(account + '@example.test');
    await page.getByLabel('Kata sandi', { exact: true }).fill('DemoPassword123!');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await page.waitForURL('**/dashboard');
}
async function logout() {
    await page.getByRole('button', { name: 'Keluar', exact: true }).click();
    await page.waitForURL(base + '/');
}

try {
    // Optional source-reference capture. Only the preview host margin/toolbar offset
    // is normalized; no dashboard content, token, or fixture is modified.
    if (process.env.REFERENCE_URL) {
        await page.goto(process.env.REFERENCE_URL);
        await page.locator('.oi-site .main h1').waitFor();
        await page.addStyleTag({ content: 'body{margin:0}.oi-review-bar{display:none!important}.oi-site .app-layout{min-height:calc(100vh - 74px)}@media(max-width:700px){.oi-site .sidebar{top:74px}}' });
        await screenshot('reference-desktop.png');
        metrics.reference = await page.evaluate(() => Object.fromEntries(
            ['.topbar', '.sidebar', '.main', '.page-head', '.stats', '.stat-card', '.panel']
                .map(selector => { const rect = document.querySelector('.oi-site ' + selector).getBoundingClientRect();
                    return [selector, { x: rect.x, y: rect.y, width: rect.width, height: rect.height }]; })));
        await page.setViewportSize({ width: 390, height: 844 });
        await screenshot('reference-mobile.png');
        await page.setViewportSize({ width: 1440, height: 1000 });
    }

    await login('admin');
    await page.goto(room);
    await page.locator('.oi-stats').waitFor();
    assert.equal(await page.locator('[aria-current="page"]').innerText(), 'Dashboard');
    await page.getByLabel('Notifikasi', { exact: true }).click();
    await page.getByRole('link', { name: 'Semua notifikasi →' }).click();
    await page.getByRole('heading', { name: 'Notifikasi', exact: true }).waitFor();
    await page.goto(room);
    await page.getByRole('button', { name: '+ Tambah aset', exact: true }).click();
    await page.locator('#oi-asset-form[open]').waitFor();
    await page.waitForFunction(() => document.activeElement?.id === 'oi-assetName');
    await page.getByRole('button', { name: 'Dashboard', exact: true }).click();

    // Create and remove only this test's own empty development room via existing UI.
    await page.goto(base + '/dashboard');
    await page.locator('.oi-head-actions').getByRole('link', { name: 'Buat Organization', exact: true }).click();
    const name = 'Uji visual ' + Date.now();
    await page.getByLabel('Nama Organization', { exact: true }).fill(name);
    await page.getByRole('button', { name: 'Buat Organization', exact: true }).click();
    const card = page.locator('article').filter({ has: page.getByRole('heading', { name, exact: true }) });
    const link = card.getByRole('link', { name: 'Buka Organization' });
    await link.waitFor();
    temporaryRoom = { name, url: await link.getAttribute('href') };
    const slug = new URL(temporaryRoom.url, base).pathname.split('/').pop();
    await page.goto(room);
    await page.getByLabel('Organization aktif', { exact: true }).selectOption(slug);
    await page.waitForURL('**/o/' + slug);
    await page.getByRole('heading', { name, exact: true }).waitFor();
    await page.getByLabel('Organization aktif', { exact: true }).selectOption('peralatan-raka');
    await page.waitForURL('**/o/peralatan-raka');

    await page.goto(temporaryRoom.url + '?tab=settings');
    await page.getByLabel('Konfirmasi nama Organization untuk hapus').fill(name);
    page.once('dialog', dialog => dialog.accept());
    await page.getByRole('button', { name: 'Hapus Organization', exact: true }).click();
    await page.waitForURL('**/dashboard');
    temporaryRoom = null;

    await page.goto(room);
    await page.getByRole('button', { name: 'Peminjaman', exact: true }).click();
    await page.getByRole('heading', { name: 'Pengelolaan peminjaman' }).waitFor();
    await page.getByRole('button', { name: 'Dashboard', exact: true }).click();
    await page.locator('.oi-stats').waitFor();
    await page.getByRole('link', { name: 'Laporan', exact: true }).click();
    await page.getByRole('heading', { name: 'Laporan Peralatan Raka' }).waitFor();
    assert.equal(await page.locator('.oi-side-link.is-active').innerText(), 'Laporan');
    await page.locator('#room-sidebar').getByRole('link', { name: 'Dashboard', exact: true }).click();
    await page.locator('.oi-stats').waitFor();

    await page.locator('.oi-analytics > summary').click();
    await page.waitForFunction(() => [...document.querySelectorAll('.oi-chart canvas')].every(el => el.clientWidth > 0 && el.clientHeight > 0));
    await page.locator('.oi-analytics > summary').click();
    await page.evaluate(() => window.scrollTo(0, 0));

    metrics.actual = await page.evaluate(() => Object.fromEntries(
        ['.oi-topbar', '.oi-sidebar', '.oi-main', '.oi-page-head', '.oi-stats', '.oi-stat-card', '.oi-panel']
            .map(selector => { const el = document.querySelector(selector), rect = el.getBoundingClientRect(), css = getComputedStyle(el);
                return [selector, { x: rect.x, y: rect.y, width: rect.width, height: rect.height, padding: css.padding, radius: css.borderRadius }]; })));
    if (metrics.reference) {
        for (const [reference, actual] of [
            ['.topbar', '.oi-topbar'], ['.sidebar', '.oi-sidebar'], ['.page-head', '.oi-page-head'],
            ['.stats', '.oi-stats'], ['.stat-card', '.oi-stat-card'], ['.panel', '.oi-panel'],
        ]) {
            // Table height depends on real rows, so compare origins and widths;
            // compare fixed heights for topbar/statistics as well.
            for (const key of ['x', 'y', 'width']) {
                assert.ok(Math.abs(metrics.reference[reference][key] - metrics.actual[actual][key]) <= 1,
                    reference + ' ' + key + ' differs from the reference');
            }
            if (['.topbar', '.stats', '.stat-card'].includes(reference)) {
                assert.ok(Math.abs(metrics.reference[reference].height - metrics.actual[actual].height) <= 1);
            }
        }
    }
    await screenshot('after-desktop.png');
    await page.screenshot({ path: path.join(output, 'after-desktop-full.png'), fullPage: true });
    await page.setViewportSize({ width: 390, height: 844 });
    await screenshot('after-mobile.png');
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false);
    assert.ok(await page.locator('.oi-table-wrap').first().evaluate(el => el.scrollWidth > el.clientWidth));
    await page.getByRole('button', { name: 'Buka menu Organization' }).click();
    await page.locator('.oi-sidebar.is-open').waitFor({ state: 'visible' });
    await screenshot('after-mobile-menu.png');
    await page.getByRole('button', { name: 'Tutup menu Organization', exact: true }).click({ position: { x: 380, y: 16 } });
    await page.locator('.oi-sidebar').waitFor({ state: 'hidden' });
    await page.getByRole('button', { name: 'Buka menu Organization' }).click();
    await page.locator('.oi-sidebar.is-open').waitFor({ state: 'visible' });
    await page.keyboard.press('Escape');
    await page.locator('.oi-sidebar').waitFor({ state: 'hidden' });
    await page.getByRole('button', { name: 'Buka menu Organization' }).click();
    await page.locator('#room-sidebar').getByRole('button', { name: 'Peminjaman', exact: true }).click();
    await page.getByRole('heading', { name: 'Pengelolaan peminjaman' }).waitFor();
    await page.locator('.oi-sidebar').waitFor({ state: 'hidden' });
    await page.getByRole('button', { name: 'Buka menu Organization' }).click();
    await page.locator('#room-sidebar').getByRole('link', { name: 'Beranda pengguna' }).click();
    await page.waitForURL('**/dashboard');
    await page.goto(room);
    await page.getByRole('button', { name: 'Buka menu Organization' }).click();
    await logout();

    await page.setViewportSize({ width: 1440, height: 1000 });
    await login('staff');
    await page.goto(room);
    await page.locator('.oi-stats').waitFor();
    for (const label of ['Anggota', 'Laporan', 'Pengaturan', 'Audit Aktivitas']) {
        assert.equal(await page.locator('#room-sidebar').getByRole('button', { name: label, exact: true }).isDisabled(), true);
    }
    assert.equal((await page.goto(room + '?tab=members')).status(), 403);
    await page.goto(room);
    await logout();

    await login('member');
    await page.goto(room);
    await page.getByRole('heading', { name: 'Katalog aset' }).waitFor();
    assert.equal(await page.locator('.oi-stats').count(), 0);
    assert.equal((await page.goto(room + '?tab=dashboard')).status(), 403);
    await page.goto(room);
    await logout();
    assert.deepEqual(errors, []);
    fs.writeFileSync(path.join(output, 'metrics.json'), JSON.stringify(metrics, null, 2) + '\n');
    console.log('PASS: ' + (metrics.reference ? 'reference geometry, ' : '') + 'desktop/mobile screenshots, mobile navigation, asset CTA, room switch, personal context, reports, and Admin/Staff/Member permissions');
} finally {
    if (temporaryRoom) {
        // Best effort: only remove the uniquely named empty room created above.
        try {
            await page.goto(temporaryRoom.url + '?tab=settings');
            await page.getByLabel('Konfirmasi nama Organization untuk hapus').fill(temporaryRoom.name);
            page.once('dialog', dialog => dialog.accept());
            await page.getByRole('button', { name: 'Hapus Organization', exact: true }).click();
            await page.waitForURL('**/dashboard');
        } catch { console.error('Temporary test room cleanup needs review:', temporaryRoom.name); }
    }
    await browser.close();
}
