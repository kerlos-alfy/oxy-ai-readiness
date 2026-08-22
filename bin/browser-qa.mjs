import { chromium } from 'playwright';
import { createServer } from 'node:http';
import { readFile, mkdir } from 'node:fs/promises';
import { extname, join, normalize } from 'node:path';

const root = process.cwd();
const manifest = JSON.parse(await readFile(join(root, 'dist/.vite/manifest.json'), 'utf8'));
const entry = manifest['assets/react/main.tsx'];
if (!entry?.file) throw new Error('Vite entry missing.');

const css = (entry.css ?? []).map((file) => `<link rel="stylesheet" href="/dist/${file}">`).join('');
const html = `<!doctype html><html><head><meta charset="utf-8">${css}</head><body><div id="oxy-ai-readiness-root"></div><script>window.oxyAiReadiness={restUrl:'http://127.0.0.1:4173/wp-json/oxy-ai/v1',nonce:'qa',version:'1.0.0-alpha.5'}</script><script type="module" src="/dist/${entry.file}"></script></body></html>`;

const mime = { '.js': 'text/javascript', '.css': 'text/css', '.html': 'text/html', '.svg': 'image/svg+xml' };
const server = createServer(async (req, res) => {
    try {
        if (req.url === '/' || req.url === '/index.html') {
            res.writeHead(200, { 'content-type': 'text/html' }); res.end(html); return;
        }
        const path = normalize(join(root, req.url ?? '/'));
        if (!path.startsWith(root)) throw new Error('bad path');
        const data = await readFile(path);
        res.writeHead(200, { 'content-type': mime[extname(path)] ?? 'application/octet-stream' }); res.end(data);
    } catch {
        res.writeHead(404); res.end('not found');
    }
});
await new Promise((resolve) => server.listen(4173, '127.0.0.1', resolve));
await mkdir(join(root, 'build/browser-qa'), { recursive: true });

const base = {
    current_version: '1.0.0-alpha.5', latest_version: null, update_available: false,
    released_at: null, changelog_url: null, changelog: '', last_checked: null, auto_update: false,
    license_activated: false, license_key_masked: null,
    license: { valid: false, tier: null, sites_used: 0, sites_max: 0, expires_at: null, trial: false, checked_at: null, network_error: false },
    security_events: [], manage_license_url: 'https://oxyadvertising.com/account', support_url: 'https://oxyadvertising.com/contact', update_url: null,
};

const scenarios = {
    'license-not-activated.png': base,
    'license-dev-valid.png': { ...base, license_activated: true, license_key_masked: '••••••••••••DEV5', license: { ...base.license, valid: true, tier: 'agency', sites_used: 2, sites_max: 10, checked_at: '2026-08-23T00:00:00Z' } },
    'license-invalid.png': { ...base, license_activated: true, license_key_masked: '••••••••••••BAD5', license: { ...base.license, checked_at: '2026-08-23T00:00:00Z' } },
    'updates-no-update.png': { ...base, latest_version: '1.0.0-alpha.5', released_at: '2026-08-23T00:00:00Z', last_checked: '2026-08-23T00:05:00Z', changelog: '- Signed updater foundation\n- Encrypted license storage\n- Fail-closed package verification' },
    'updates-update-available.png': { ...base, latest_version: '1.0.0', update_available: true, released_at: '2026-09-01T00:00:00Z', last_checked: '2026-08-23T00:05:00Z', update_url: '#qa-update-flow', changelog: '- Stable signed updater release\n- License validation hardening\n- WordPress compatibility updates\n- Additional security logging' },
    'signature-verification-failure-log.png': { ...base, security_events: [{ code: 'signature_invalid', message: 'Update signature verification failed. Installation was refused.', at: '2026-08-23T00:10:00Z' }] },
};

const browser = await chromium.launch({ headless: true });
try {
    for (const [filename, state] of Object.entries(scenarios)) {
        const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
        await page.route('**/wp-json/oxy-ai/v1/**', async (route) => {
            const path = new URL(route.request().url()).pathname;
            if (path.endsWith('/score')) return route.fulfill({ json: { success: true, data: { score: 0, grade: 'F', label: 'Not audited', trend: 'stable', confidence: 'low' } } });
            if (path.endsWith('/audit')) return route.fulfill({ json: { success: true, data: null } });
            if (path.endsWith('/recommendations')) return route.fulfill({ json: { success: true, data: [] } });
            if (path.endsWith('/updater/status')) return route.fulfill({ json: { success: true, data: state } });
            return route.fulfill({ status: 404, json: { success: false, message: 'QA route not configured' } });
        });
        await page.goto('http://127.0.0.1:4173/', { waitUntil: 'networkidle' });
        await page.getByRole('button', { name: 'License & Updates' }).click();
        await page.getByRole('heading', { name: 'License & Updates' }).waitFor();
        await page.screenshot({ path: join(root, 'build/browser-qa', filename), fullPage: true });
        await page.close();
    }
} finally {
    await browser.close();
    await new Promise((resolve) => server.close(resolve));
}

console.log(`Chromium QA PASS: ${Object.keys(scenarios).length} required screenshots captured from real built DOM/CSS.`);
