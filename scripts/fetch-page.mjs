#!/usr/bin/env node
/**
 * Puppeteer page fetcher with stealth — bypasses Cloudflare JS challenges.
 * Usage: node scripts/fetch-page.mjs <url> [timeout_ms]
 * Outputs rendered HTML to stdout, errors to stderr.
 * Exit code 0 = success, 1 = error.
 */
import puppeteer from 'puppeteer-extra';
import StealthPlugin from 'puppeteer-extra-plugin-stealth';

puppeteer.use(StealthPlugin());

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
            '--window-size=1920,1080',
        ],
    });

    const page = await browser.newPage();
    await page.setUserAgent(
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'
    );
    await page.setViewport({ width: 1920, height: 1080 });

    await page.goto(url, { waitUntil: 'domcontentloaded', timeout });

    // Wait for Cloudflare challenge to resolve — poll up to 35s
    const maxWait = 35000;
    const start = Date.now();
    let html = '';

    while (Date.now() - start < maxWait) {
        await sleep(3000);
        html = await page.content();
        const title = await page.title();

        // Challenge resolved when title no longer indicates error/challenge
        if (!title.includes('Just a moment') && !title.includes('503') && !title.includes('Server Error')) {
            break;
        }
    }

    // Final content grab after small delay
    await sleep(1500);
    html = await page.content();

    process.stdout.write(html);
    process.exit(0);
} catch (err) {
    process.stderr.write(`Error: ${err.message}\n`);
    process.exit(1);
} finally {
    if (browser) await browser.close().catch(() => {});
}
