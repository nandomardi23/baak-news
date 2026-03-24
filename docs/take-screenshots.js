import puppeteer from 'puppeteer-core';

(async () => {
    console.log('Starting screenshot capture using local Edge...');
    const browser = await puppeteer.launch({ 
        headless: 'new',
        executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe'
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900 });

    try {
        console.log('1. Capturing Landing Page...');
        await page.goto('http://127.0.0.1:8000', { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'docs/screenshots/01-landing-page.png', fullPage: true });

        console.log('2. Capturing Profil Page...');
        await page.goto('http://127.0.0.1:8000/profil', { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'docs/screenshots/02-profil-page.png', fullPage: true });

        console.log('3. Capturing Kalender Akademik...');
        await page.goto('http://127.0.0.1:8000/kalender-akademik', { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'docs/screenshots/03-kalender-akademik.png', fullPage: true });

        console.log('4. Capturing Login Page...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'docs/screenshots/04-login-page.png', fullPage: true });

        console.log('Successfully captured all screenshots to docs/screenshots/');
    } catch (e) {
        console.error('Error:', e);
    } finally {
        await browser.close();
    }
})();
