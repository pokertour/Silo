// Génère les captures d'écran du site GitHub Pages à partir d'une instance
// de Silo peuplée par DemoSeeder. Lancé par .github/workflows/pages.yml.
//
// Variables : BASE_URL, ADMIN_EMAIL, ADMIN_PASSWORD, SHARE_TOKEN, OUT_DIR

import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';

const BASE_URL = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const OUT_DIR = process.env.OUT_DIR ?? 'site/screenshots';
const { ADMIN_EMAIL, ADMIN_PASSWORD, SHARE_TOKEN } = process.env;

const DESKTOP = { width: 1440, height: 900 };
const MOBILE = { width: 390, height: 844 };

await mkdir(OUT_DIR, { recursive: true });

const browser = await chromium.launch();
let failures = 0;

async function newPage(viewport) {
    const context = await browser.newContext({
        viewport,
        deviceScaleFactor: 2,
        locale: 'en-US',
        extraHTTPHeaders: { 'Accept-Language': 'en-US,en;q=0.9' },
        reducedMotion: 'reduce',
        colorScheme: 'dark',
    });
    return context.newPage();
}

async function settle(page) {
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(800);
}

async function shoot(page, name, { fullPage = false } = {}) {
    const path = `${OUT_DIR}/${name}.jpg`;
    await page.screenshot({ path, type: 'jpeg', quality: 85, fullPage });
    console.log(`✓ ${path}`);
}

async function capture(name, fn) {
    try {
        await fn();
    } catch (error) {
        failures++;
        console.error(`✗ ${name}: ${error.message}`);
    }
}

async function login(page) {
    await page.goto(`${BASE_URL}/login`);
    await settle(page);
    await page.fill('#email', ADMIN_EMAIL);
    await page.fill('#password', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 15000 });
    await settle(page);
}

// --- Pages publiques -------------------------------------------------------

const guest = await newPage(DESKTOP);

await capture('login', async () => {
    await guest.goto(`${BASE_URL}/login`);
    await settle(guest);
    await shoot(guest, 'login');
});

await capture('share-public', async () => {
    await guest.goto(`${BASE_URL}/s/${SHARE_TOKEN}`);
    await settle(guest);
    await shoot(guest, 'share-public');
});

// --- Espace connecté (administrateur) --------------------------------------

const page = await newPage(DESKTOP);
await login(page);

const pages = [
    ['dashboard', '/dashboard'],
    ['library', '/isos'],
    ['upload', '/upload'],
    ['share-links', '/share-links'],
    ['admin-stats', '/admin/stats'],
    ['admin-users', '/admin/users'],
    ['admin-groups', '/admin/groups'],
    ['admin-invitations', '/admin/invitations'],
    ['admin-audit', '/admin/audit'],
    ['profile', '/profile'],
];

for (const [name, path] of pages) {
    await capture(name, async () => {
        await page.goto(`${BASE_URL}${path}`);
        await settle(page);
        await shoot(page, name);
    });
}

await capture('admin-storage', async () => {
    await page.goto(`${BASE_URL}/admin/settings`);
    await settle(page);
    // Onglet « Storage » s'il existe, sinon la page par défaut
    const tab = page.getByRole('button', { name: /storage/i }).first();
    if (await tab.count()) {
        await tab.click();
        await settle(page);
    }
    await shoot(page, 'admin-storage');
});

await capture('detail', async () => {
    await page.goto(`${BASE_URL}/isos`);
    await settle(page);
    // Seul l'élément visible (le texte existe aussi dans le menu des notifications, masqué)
    await page.locator('text="Ubuntu Server 24.04.1 LTS" >> visible=true').first().click();
    await page.waitForURL('**/isos/*');
    await settle(page);
    await shoot(page, 'detail');

    // Modale de partage ouverte depuis la page de détail
    await page.getByRole('button', { name: /share/i }).first().click();
    await page.waitForTimeout(1200);
    await shoot(page, 'share-modal');
});

// --- Mobile -----------------------------------------------------------------

const mobile = await newPage(MOBILE);
await capture('mobile', async () => {
    await login(mobile);
    await shoot(mobile, 'mobile-dashboard');
});

await browser.close();

if (failures > 0) {
    console.error(`${failures} capture(s) en échec`);
    process.exit(1);
}
