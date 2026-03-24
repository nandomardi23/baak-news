import puppeteer from 'puppeteer-core';

(async () => {
    console.log('Starting admin screenshot capture using local Edge...');
    const browser = await puppeteer.launch({ 
        headless: 'new',
        executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        defaultViewport: { width: 1440, height: 900 }
    });
    const page = await browser.newPage();

    try {
        console.log('Login as Admin (admin@example.com)...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
        
        await page.waitForSelector('input[type="email"]');
        await page.type('input[type="email"]', 'admin@example.com');
        await page.type('input[type="password"]', 'password');
        
        // Let's use evaluate to click the submit button just in case PrimeVue or something masks it
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle0' }),
            page.evaluate(() => document.querySelector('button[type="submit"]').click())
        ]);

        console.log('5. Capturing Admin Dashboard...');
        // Wait an extra second for Vue/Inertia to load animations
        await new Promise(r => setTimeout(r, 1000));
        await page.screenshot({ path: 'docs/screenshots/05-admin-dashboard.png', fullPage: true });

        console.log('6. Capturing Data Mahasiswa...');
        await page.goto('http://127.0.0.1:8000/admin/mahasiswa', { waitUntil: 'networkidle0' });
        await new Promise(r => setTimeout(r, 1000));
        await page.screenshot({ path: 'docs/screenshots/06-admin-mahasiswa.png', fullPage: true });

        console.log('7. Capturing Pengaturan Neo Feeder...');
        await page.goto('http://127.0.0.1:8000/admin/settings/neofeeder', { waitUntil: 'networkidle0' });
        await new Promise(r => setTimeout(r, 1000));
        await page.screenshot({ path: 'docs/screenshots/07-admin-neofeeder.png', fullPage: true });

        console.log('Admin Screenshots captured successfully!');
    } catch (e) {
        console.error('Error during capture:', e);
    } finally {
        await browser.close();
    }
})();
