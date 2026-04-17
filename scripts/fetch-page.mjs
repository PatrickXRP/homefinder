#!/usr/bin/env node
/**
 * Puppeteer page fetcher — bypasses Cloudflare JS challenges.
 * Usage: node scripts/fetch-page.mjs <url> [timeout_ms]
 * Outputs rendered HTML to stdout, errors to stderr.
 * Exit code 0 = success, 1 = error.
 */
import puppeteer from 'puppeteer-core';

const url = process.argv[2];
const timeout = parseInt(process.argv[3] || '60000', 10);

if (!url) {
    process.stderr.write('Usage: node fetch-page.mjs <url> [timeout_ms]\n');
    process.exit(1);
}

const chromiumPath = process.env.PUPPETEER_EXECUTABLE_PATH
    || '/nix/var/nix/profiles/default/bin/chromium';

function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
}

let browser;
try {
    browser = await puppeteer.launch({
        executablePath: chromiumPath,
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-blink-features=AutomationControlled',
            '--window-size=1920,1080',
        ],
    });

    const page = await browser.newPage();

    // Evade bot detection
    await page.evaluateOnNewDocument(() => {
        Object.defineProperty(navigator, 'webdriver', { get: () => false });
        Object.defineProperty(navigator, 'plugins', { get: () => [1, 2, 3, 4, 5] });
        Object.defineProperty(navigator, 'languages', { get: () => ['en-US', 'en', 'sv'] });
        window.chrome = { runtime: {} };
    });

    await page.setUserAgent(
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'
    );
    await page.setViewport({ width: 1920, height: 1080 });
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'en-US,en;q=0.9,sv;q=0.8' });

    // Navigate and wait for initial load
    await page.goto(url, { waitUntil: 'domcontentloaded', timeout });

    // Wait for Cloudflare challenge to resolve — poll up to 30s
    const maxWait = 30000;
    const start = Date.now();
    let html = await page.content();

    while (Date.now() - start < maxWait) {
        const title = await page.title();
        // Cloudflare challenge pages have specific titles
        if (title.includes('Just a moment') || title.includes('503') || title.includes('Server Error')) {
            await sleep(2000);
            html = await page.content();
            continue;
        }
        // If we got redirected and page has real content, break
        if (html.length > 10000 || (!title.includes('503') && !title.includes('Error'))) {
            break;
        }
        await sleep(2000);
        html = await page.content();
    }

    // Final wait for dynamic content
    await sleep(2000);
    html = await page.content();

    process.stdout.write(html);
    process.exit(0);
} catch (err) {
    process.stderr.write(`Error: ${err.message}\n`);
    process.exit(1);
} finally {
    if (browser) await browser.close().catch(() => {});
}
