import puppeteer from 'puppeteer-core';

const routes = [
    { url: 'http://127.0.0.1:8000/admin', name: '05-admin-dashboard.png' },
    { url: 'http://127.0.0.1:8000/admin/mahasiswa', name: '06-admin-mahasiswa.png' },
    { url: 'http://127.0.0.1:8000/admin/pejabat', name: '08-admin-pejabat.png' },
    { url: 'http://127.0.0.1:8000/admin/dosen', name: '09-admin-dosen.png' },
    { url: 'http://127.0.0.1:8000/admin/kelas-kuliah', name: '10-admin-kelas-kuliah.png' },
    { url: 'http://127.0.0.1:8000/admin/surat', name: '11-admin-surat-pengajuan.png' },
    { url: 'http://127.0.0.1:8000/admin/user', name: '12-admin-users.png' },
    { url: 'http://127.0.0.1:8000/admin/akademik/matakuliah', name: '13-akademik-matakuliah.png' },
    { url: 'http://127.0.0.1:8000/admin/akademik/kurikulum', name: '14-akademik-kurikulum.png' },
    { url: 'http://127.0.0.1:8000/admin/akademik/semester', name: '15-akademik-semester.png' },
    { url: 'http://127.0.0.1:8000/admin/akademik/prodi', name: '16-akademik-prodi.png' },
    { url: 'http://127.0.0.1:8000/admin/kalender', name: '17-admin-kalender-akademik.png' },
    { url: 'http://127.0.0.1:8000/admin/settings/neofeeder', name: '18-settings-neofeeder.png' },
    { url: 'http://127.0.0.1:8000/admin/settings/general', name: '19-settings-general.png' },
    { url: 'http://127.0.0.1:8000/admin/templates', name: '20-settings-templates.png' },
];

(async () => {
    console.log('Starting complete screenshot capture using local Edge...');
    const browser = await puppeteer.launch({ 
        headless: 'new',
        executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        defaultViewport: { width: 1440, height: 900 }
    });
    const page = await browser.newPage();

    try {
        console.log('Logging in as Admin...');
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle2' });
        
        await page.waitForSelector('input[type="email"]');
        await page.type('input[type="email"]', 'admin@example.com');
        await page.type('input[type="password"]', 'password');
        
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle0' }),
            page.evaluate(() => document.querySelector('button[type="submit"]').click())
        ]);

        console.log('Login successful. Capturing pages sequentially...');

        for (const route of routes) {
            console.log(`Navigating to ${route.url}...`);
            await page.goto(route.url, { waitUntil: 'networkidle0' });
            // Wait for animations
            await new Promise(r => setTimeout(r, 1200));
            await page.screenshot({ path: `docs/screenshots/${route.name}`, fullPage: true });
            console.log(`--> Saved ${route.name}`);
        }

        console.log('All admin screenshots captured successfully!');
    } catch (e) {
        console.error('Error during capture:', e);
    } finally {
        await browser.close();
    }
})();
