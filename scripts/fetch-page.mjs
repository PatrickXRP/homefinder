#!/usr/bin/env node
/**
 * Puppeteer page fetcher — bypasses Cloudflare JS challenges.
 * Usage: node scripts/fetch-page.mjs <url> [timeout_ms]
 * Outputs rendered HTML to stdout, errors to stderr.
 * Exit code 0 = success, 1 = error.
 */
import puppeteer from 'puppeteer-core';

const url = process.argv[2];
const timeout = parseInt(process.argv[3] || '30000', 10);

if (!url) {
    process.stderr.write('Usage: node fetch-page.mjs <url> [timeout_ms]\n');
    process.exit(1);
}

const chromiumPath = process.env.PUPPETEER_EXECUTABLE_PATH
    || '/nix/var/nix/profiles/default/bin/chromium';

let browser;
try {
    browser = await puppeteer.launch({
        executablePath: chromiumPath,
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--single-process',
        ],
    });

    const page = await browser.newPage();
    await page.setUserAgent(
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'
    );
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'en-US,en;q=0.9,sv;q=0.8' });

    await page.goto(url, { waitUntil: 'networkidle2', timeout });

    // Wait for Cloudflare challenge to resolve (if any)
    try {
        await page.waitForFunction(
            () => !document.title.includes('Just a moment'),
            { timeout: 15000 }
        );
    } catch {
        // Might not have a challenge — continue anyway
    }

    const html = await page.content();
    process.stdout.write(html);
    process.exit(0);
} catch (err) {
    process.stderr.write(`Error: ${err.message}\n`);
    process.exit(1);
} finally {
    if (browser) await browser.close().catch(() => {});
}
