import {chromium} from 'playwright-core';
import assert from 'node:assert/strict';
import fs from 'node:fs';
const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',headless:true,args:['--no-sandbox']});
const context=await browser.newContext({viewport:{width:1440,height:1000}});const page=await context.newPage();
const errors=[];page.on('pageerror',e=>errors.push(e.message));
const base=process.env.APP_URL||'http://127.0.0.1:8000';
try{
 await page.goto(base+'/login');await page.getByLabel('Email',{exact:true}).fill('admin@example.test');await page.getByLabel('Kata sandi',{exact:true}).fill('DemoPassword123!');await page.getByRole('button',{name:'Masuk',exact:true}).click();await page.waitForURL('**/dashboard');
 await page.getByRole('link',{name:'Buka ruang'}).first().click();await page.getByRole('heading',{name:'Peralatan Raka',exact:true}).waitFor();
 await page.getByRole('button',{name:'Peminjaman',exact:true}).click();await page.getByRole('heading',{name:'Pengelolaan peminjaman'}).waitFor();
 await page.getByRole('button',{name:'Inventaris',exact:true}).click();await page.getByRole('heading',{name:'Katalog aset'}).waitFor();
 await page.getByText('Tambah kategori dan aset',{exact:true}).click();const name='Browser asset '+Date.now();
 await page.getByLabel('Nama aset',{exact:true}).fill(name);await page.locator('select').filter({has:page.locator('option').filter({hasText:'Pilih kategori'})}).selectOption({label:'Dokumentasi'});await page.getByLabel('Lokasi',{exact:true}).fill('Lemari browser');await page.getByRole('button',{name:'Simpan aset',exact:true}).click();await page.getByRole('cell',{name:name,exact:false}).waitFor();
 await page.screenshot({path:'/tmp/orginventory-desktop.png',fullPage:true});
 await page.setViewportSize({width:390,height:844});await page.screenshot({path:'/tmp/orginventory-mobile.png',fullPage:true});
 assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+2),false,'mobile page must not overflow horizontally');
 await page.getByRole('button',{name:'Buka menu ruang'}).click();await page.locator('.oi-sidebar.is-open').waitFor({state:'visible'});await page.getByRole('button',{name:'Keluar',exact:true}).click();await page.waitForURL(base+'/');
 assert.deepEqual(errors,[]);console.log('PASS: real Chromium login, database-backed Livewire navigation, asset creation, logout, desktop/mobile layouts');
}finally{await browser.close();}
