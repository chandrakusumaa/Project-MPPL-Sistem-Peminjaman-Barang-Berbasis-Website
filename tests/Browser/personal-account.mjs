import { chromium } from 'playwright-core';
import { spawnSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';

const base = process.env.APP_URL || 'http://127.0.0.1:8000';
const output = process.env.VISUAL_OUTPUT || 'docs/visual/account-dashboard';
const token = randomBytes(8).toString('hex');
function fixture(action) {
    const args = ['tests/Browser/fixtures/personal-account.php', action, token];
    const docker = process.env.BROWSER_DOCKER === '1';
    const result = spawnSync(docker ? 'docker' : (process.env.PHP_BINARY || 'php'), docker ? ['compose', 'exec', '-T', 'app', 'php', ...args] : args, { encoding: 'utf8', env: process.env });
    assert.equal(result.status, 0, result.stderr || result.stdout);
    return result.stdout.trim();
}
fs.mkdirSync(output, { recursive: true });
const data = JSON.parse(fixture('setup'));
const browser = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium', args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 });
page.setDefaultTimeout(15000);
const errors = [];
page.on('pageerror', error => errors.push(error.message));
page.on('response', response => { if (response.url().includes('/livewire/update') && response.status() >= 400) errors.push('Livewire HTTP ' + response.status()); });
const screenshot = name => page.screenshot({ path: path.join(output, name), animations: 'disabled' });
const noOverflow = async () => assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false, 'Page must not overflow horizontally');
const metrics = {};
async function measure(selectors) {
    return page.evaluate(selectors => Object.fromEntries(selectors.map(selector => {
        const element = document.querySelector(selector), box = element.getBoundingClientRect(), css = getComputedStyle(element);
        return [selector, { x: box.x, y: box.y, width: box.width, height: box.height, padding: css.padding, radius: css.borderRadius }];
    })), selectors);
}

try {
    if (process.env.REFERENCE_URL) {
        await page.goto(process.env.REFERENCE_URL);
        await page.locator('.oi-review-pages').selectOption('user-dashboard.html');
        await page.locator('.oi-site .stats').waitFor();
        await page.addStyleTag({ content: 'body{margin:0}.oi-review-bar{display:none!important}' });
        metrics.reference = await measure(['.oi-site .topbar', '.oi-site .sidebar', '.oi-site .page-head', '.oi-site .stats', '.oi-site .stat-card']);
        await screenshot('reference-desktop.png');
        await page.setViewportSize({ width: 390, height: 844 });
        await screenshot('reference-mobile.png');
        await page.setViewportSize({ width: 1440, height: 1000 });
    }
    await page.goto(base + '/login');
    await page.getByLabel('Email', { exact: true }).fill(data.email);
    await page.getByLabel('Kata sandi', { exact: true }).fill('DemoPassword123!');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await page.waitForURL('**/dashboard');
    assert.deepEqual(await page.locator('.oi-stat-number').allTextContents(), ['2', '4', '1', '1']);
    metrics.actual = await measure(['.oi-topbar', '.oi-sidebar', '.oi-page-head', '.oi-stats', '.oi-stat-card']);
    if (metrics.reference) {
        for (const [source, actual] of [['.oi-site .topbar', '.oi-topbar'], ['.oi-site .sidebar', '.oi-sidebar'], ['.oi-site .page-head', '.oi-page-head'], ['.oi-site .stats', '.oi-stats'], ['.oi-site .stat-card', '.oi-stat-card']]) {
            for (const axis of ['x', 'y', 'width']) assert.ok(Math.abs(metrics.reference[source][axis] - metrics.actual[actual][axis]) <= 1, source + ' ' + axis);
        }
    }
    await page.getByRole('heading', { name: 'Organization Saya', exact: true }).waitFor();
    assert.equal(await page.locator('#account-sidebar').getByRole('link', { name: 'Notifikasi', exact: true }).count(), 0);
    assert.equal(await page.locator('.oi-org-menu').getAttribute('open'), null);
    await page.locator('.oi-org-menu > summary').click();
    await page.locator('.oi-org-links').getByRole('link', { name: /Studio Kreatif Nadia/ }).waitFor({ state: 'visible' });
    await screenshot('after-desktop.png');
    await page.screenshot({ path: path.join(output, 'after-desktop-full.png'), fullPage: true });
    await page.getByLabel('Status peminjaman', { exact: true }).selectOption('pending');
    await page.waitForFunction(() => document.querySelectorAll('.oi-history-table tbody tr').length === 1 && document.querySelector('.oi-history-table tbody').textContent.includes('Kamera Canon EOS'));
    await page.getByLabel('Status peminjaman', { exact: true }).selectOption('');
    await page.waitForFunction(() => document.querySelectorAll('.oi-history-table tbody tr').length === 4);

    const topnav = page.getByRole('navigation', { name: 'Navigasi utama', exact: true });
    await topnav.getByRole('link', { name: 'Beranda', exact: true }).click();
    await page.waitForURL(base + '/');
    await page.getByRole('heading', { name: /Barang tertata/ }).waitFor();
    await topnav.getByRole('link', { name: 'Jelajahi Organization', exact: true }).click();
    await page.getByLabel('Cari Organization', { exact: true }).fill('Studio Kreatif Nadia');
    await page.waitForFunction(() => document.querySelectorAll('.oi-org-card').length === 1);
    await page.getByRole('heading', { name: 'Studio Kreatif Nadia', exact: true }).waitFor();
    assert.equal(await page.locator('.oi-personal-dashboard').count(), 0);
    await screenshot('explore-desktop.png');
    await topnav.getByRole('link', { name: 'Dashboard', exact: true }).click();
    await page.waitForURL('**/dashboard');

    await page.getByLabel('Notifikasi', { exact: true }).click();
    await page.getByRole('link', { name: 'Semua notifikasi →' }).click();
    await page.getByRole('heading', { name: 'Notifikasi', exact: true }).waitFor();
    await page.getByLabel('Profil Nadia Putri', { exact: true }).click();
    await page.getByRole('heading', { name: 'Profil Saya', exact: true }).waitFor();

    await page.getByRole('link', { name: 'Tagihan Saya', exact: true }).click();
    assert.deepEqual(await page.locator('.oi-stat-number').allTextContents(), ['Rp300.000', 'Rp200.000', 'Rp100.000', 'Rp0']);
    await page.getByText('Sebagian dibayar', { exact: true }).waitFor();
    await page.getByText('Lunas', { exact: true }).last().waitFor();
    await page.getByText('Koreksi nominal pencatatan', { exact: true }).waitFor();
    await screenshot('charges-desktop.png');
    await page.screenshot({ path: path.join(output, 'charges-desktop-full.png'), fullPage: true });
    await page.getByLabel('Status tagihan', { exact: true }).selectOption('paid');
    await page.waitForFunction(() => document.querySelectorAll('.oi-charges-table tbody tr').length === 1);
    await page.getByLabel('Status pembayaran', { exact: true }).selectOption('pending');
    await page.waitForFunction(() => document.querySelectorAll('.oi-payments-table tbody tr').length === 1);
    assert.equal(await page.locator('.oi-charges-table tbody').getByText('Lunas', { exact: true }).count(), 1);
    await page.getByLabel('Status tagihan', { exact: true }).selectOption('');
    await page.getByLabel('Status pembayaran', { exact: true }).selectOption('');
    await page.waitForFunction(() => document.querySelectorAll('.oi-payments-table tbody tr').length === 4);

    await page.setViewportSize({ width: 390, height: 844 });
    await noOverflow();
    assert.ok(await page.locator('.oi-charges-table').evaluate(el => el.parentElement.scrollWidth > el.parentElement.clientWidth));
    await screenshot('charges-mobile.png');
    await page.getByRole('button', { name: 'Buka menu akun' }).click();
    await page.locator('#account-sidebar.is-open').waitFor({ state: 'visible' });
    await page.locator('#account-sidebar').getByRole('link', { name: 'Dashboard Pribadi', exact: true }).click();
    await page.waitForURL('**/dashboard');
    await noOverflow();
    for (const name of ['Beranda', 'Jelajahi Organization', 'Dashboard']) await topnav.getByRole('link', { name, exact: true }).waitFor({ state: 'visible' });
    await screenshot('after-mobile.png');
    await page.getByRole('button', { name: 'Buka menu akun' }).click();
    await page.locator('.oi-org-menu > summary').click();
    await page.locator('.oi-org-links').getByRole('link', { name: /Perlengkapan Komunitas/ }).waitFor({ state: 'visible' });
    await page.screenshot({ path: path.join(output, 'after-mobile-menu.png'), animations: 'disabled' });
    await page.keyboard.press('Escape');
    await page.locator('#account-sidebar').waitFor({ state: 'hidden' });
    await page.getByRole('button', { name: 'Buka menu akun' }).click();
    await page.getByRole('button', { name: 'Tutup menu akun', exact: true }).click({ position: { x: 380, y: 16 } });
    await page.locator('#account-sidebar').waitFor({ state: 'hidden' });
    await page.getByLabel('Notifikasi', { exact: true }).click();
    await page.getByRole('link', { name: 'Semua notifikasi →' }).waitFor({ state: 'visible' });
    await noOverflow();
    await page.keyboard.press('Escape');
    await page.getByLabel('Profil Nadia Putri', { exact: true }).click();
    await page.getByLabel('Nama', { exact: true }).waitFor({ state: 'visible' });
    await noOverflow();
    await topnav.getByRole('link', { name: 'Dashboard', exact: true }).click();
    await page.locator('.oi-head-actions').getByRole('link', { name: 'Buat Organization', exact: true }).click();
    await page.getByLabel('Nama ruang', { exact: true }).waitFor({ state: 'visible' });
    assert.ok(await page.getByLabel('Nama ruang', { exact: true }).evaluate(element => element.clientWidth > 280));
    await noOverflow();
    await screenshot('create-mobile.png');

    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto(base + '/o/' + data.followedSlug);
    await page.getByRole('heading', { name: 'Katalog aset', exact: true }).waitFor();
    assert.match(await page.locator('.oi-context').innerText(), /MEMBER/);
    await page.locator('#room-sidebar .oi-org-links').getByRole('link', { name: /Studio Kreatif Nadia/ }).click();
    await page.getByRole('heading', { name: 'Studio Kreatif Nadia', exact: true }).waitFor();
    assert.match(await page.locator('.oi-context').innerText(), /ADMIN/);
    assert.equal((await page.goto(base + '/o/' + data.hiddenSlug)).status(), 403);
    assert.deepEqual(errors, []);
    fs.writeFileSync(path.join(output, 'metrics.json'), JSON.stringify(metrics, null, 2) + '\n');
    console.log('PASS: personal metrics/history, separate landing/discovery/dashboard, Organization dropdown and local roles, notification/profile navigation, confirmed/pending/voided billing, live filters, 1440x1000 and 390x844 screenshots, and mobile navigation/overflow.');
} finally {
    await browser.close();
    fixture('cleanup');
}
