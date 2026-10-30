import { chromium } from 'playwright-core';
import { spawnSync } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';

const base = process.env.APP_URL || 'http://127.0.0.1:8000';
const output = process.env.VISUAL_OUTPUT || 'docs/visual/direct-membership';
const run = randomBytes(8).toString('hex');
function fixture(action) {
    const args = ['tests/Browser/fixtures/personal-account.php', action, run];
    const docker = process.env.BROWSER_DOCKER === '1';
    const result = spawnSync(docker ? 'docker' : (process.env.PHP_BINARY || 'php'), docker ? ['compose', 'exec', '-T', 'app', 'php', ...args] : args, { encoding: 'utf8', env: process.env });
    assert.equal(result.status, 0, result.stderr || result.stdout);
    return result.stdout.trim();
}
const data = JSON.parse(fixture('setup'));
fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium', args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 });
page.setDefaultTimeout(15000);
const errors = [];
page.on('pageerror', error => errors.push(error.message));
page.on('response', response => { if (response.url().includes('/livewire/update') && response.status() >= 400) errors.push('Livewire HTTP ' + response.status()); });
let releaseJoin;
async function login(target, email) {
    await target.goto(base + '/login');
    await target.getByLabel('Email', { exact: true }).fill(email);
    await target.getByLabel('Kata sandi', { exact: true }).fill('DemoPassword123!');
    await target.getByRole('button', { name: 'Masuk', exact: true }).click();
    await target.waitForURL('**/dashboard');
}
const noOverflow = async target => assert.equal(await target.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1), false);
try {
    await login(page, data.email);
    await page.goto(base + '/my-organizations?listing=all');
    const card = page.locator('.oi-org-card').filter({ has: page.getByRole('heading', { name: 'Organization Privat Pengelola', exact: true }) });
    const join = card.getByRole('button', { name: 'Gabung', exact: true });
    await join.waitFor({ state: 'visible' });
    const requestReached = page.waitForRequest(request => request.url().includes('/livewire/update')
        && request.postDataJSON()?.components?.some(component => component.calls?.some(call => call.method === 'join')));
    const gate = new Promise(resolve => { releaseJoin = resolve; });
    const intercept = async route => {
        const body = route.request().postDataJSON();
        if (body?.components?.some(component => component.calls?.some(call => call.method === 'join'))) {
            await gate;
        }
        await route.continue();
    };
    await page.route('**/livewire/update', intercept);
    const button = await join.elementHandle();
    await join.click();
    await requestReached;
    assert.equal(await button.isDisabled(), true, 'Join must be disabled while in flight');
    releaseJoin();
    await card.getByRole('link', { name: 'Buka Organization →', exact: true }).waitFor();
    await page.unroute('**/livewire/update', intercept);
    await page.getByText('Berhasil bergabung. Kamu sekarang menjadi Member dan dapat membuka Organization.', { exact: true }).waitFor();
    await page.waitForFunction(() => document.querySelector('.oi-menu-count')?.textContent.trim() === '3');
    assert.match(await card.innerText(), /MEMBER/);
    assert.equal(await card.getByText('Menunggu persetujuan', { exact: true }).count(), 0);
    await page.screenshot({ path: path.join(output, 'joined-desktop.png'), animations: 'disabled' });
    await page.setViewportSize({ width: 390, height: 844 });
    await noOverflow(page);
    await card.scrollIntoViewIfNeeded();
    await page.screenshot({ path: path.join(output, 'joined-mobile.png'), animations: 'disabled' });
    await card.getByRole('link', { name: 'Buka Organization →', exact: true }).click();
    await page.getByRole('heading', { name: 'Katalog aset', exact: true }).waitFor();
    assert.match(await page.locator('.oi-context').innerText(), /MEMBER/);
    await noOverflow(page);
    await page.screenshot({ path: path.join(output, 'member-mobile.png'), animations: 'disabled' });
    await page.setViewportSize({ width: 1440, height: 1000 });
    const csrf = await page.locator('input[name="_token"]').first().getAttribute('value');
    for (const role of ['admin', 'staff']) {
        const response = await page.request.post(base + '/ruang/' + data.hiddenSlug + '/join', { form: { _token: csrf, role }, maxRedirects: 0 });
        assert.equal(response.status(), 302);
    }
    await page.reload();
    assert.match(await page.locator('.oi-context').innerText(), /MEMBER/);
    assert.equal((await page.goto(base + '/o/' + data.hiddenSlug + '?tab=members')).status(), 403);

    const manager = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
    manager.setDefaultTimeout(15000);
    manager.on('pageerror', error => errors.push(error.message));
    await login(manager, data.managerEmail);
    await manager.goto(base + '/o/' + data.hiddenSlug + '?tab=members');
    await manager.getByRole('heading', { name: 'Anggota Organization', exact: true }).waitFor();
    assert.equal(await manager.getByText('Undang anggota', { exact: true }).count(), 0);
    assert.equal(await manager.getByText('Pengajuan bergabung', { exact: true }).count(), 0);
    const member = manager.locator('.list-row').filter({ hasText: 'Nadia Putri' });
    manager.once('dialog', dialog => dialog.accept());
    await member.getByRole('button', { name: 'staff', exact: true }).click();
    await manager.waitForFunction(() => [...document.querySelectorAll('.list-row')].some(row => row.textContent.includes('Nadia Putri') && row.querySelector('.badge')?.textContent.includes('staff / active')));
    await manager.screenshot({ path: path.join(output, 'admin-members-desktop.png'), animations: 'disabled' });
    await manager.setViewportSize({ width: 390, height: 844 });
    await noOverflow(manager);
    await manager.screenshot({ path: path.join(output, 'admin-members-mobile.png'), animations: 'disabled' });
    await page.goto(base + '/o/' + data.hiddenSlug);
    assert.match(await page.locator('.oi-context').innerText(), /STAFF/);
    assert.equal(await page.locator('#room-sidebar').getByRole('button', { name: 'Anggota', exact: true }).isDisabled(), true);
    await page.screenshot({ path: path.join(output, 'staff-context-desktop.png'), animations: 'disabled' });
    assert.deepEqual(errors, []);
    console.log('PASS: disabled/loading Gabung -> immediate Member -> Buka Organization, forged role replay cannot escalate, local Admin promotion to Staff, no invitation/review UI, and desktop/mobile navigation.');
} finally {
    releaseJoin?.();
    await browser.close();
    fixture('cleanup');
}
