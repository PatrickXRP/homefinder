<?php

namespace App\Services\HomeFinder\Scrapers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BasePropertyScraper implements ScraperInterface
{
    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:121.0) Gecko/20100101 Firefox/121.0',
    ];

    protected int $maxRetries = 3;
    public bool $usePuppeteer = false;

    protected function fetchPage(string $url): ?string
    {
        // Try Puppeteer first if enabled (Cloudflare bypass)
        if ($this->usePuppeteer) {
            return $this->fetchWithPuppeteer($url);
        }

        $lastException = null;
        $got503 = false;

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgents[array_rand($this->userAgents)],
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5,sv;q=0.3',
                ])
                ->timeout(30)
                ->get($url);

                if ($response->successful()) {
                    $this->rateLimitPause();
                    return $response->body();
                }

                if ($response->status() === 503) {
                    $got503 = true;
                }

                Log::warning("Scraper: HTTP {$response->status()} for {$url} (attempt {$attempt})");
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("Scraper: Exception on {$url} (attempt {$attempt}): {$e->getMessage()}");
            }

            if ($attempt < $this->maxRetries) {
                sleep(rand(3, 8));
            }
        }

        // Cloudflare 503 — fallback to Puppeteer
        if ($got503) {
            Log::info("Scraper: 503 detected, falling back to Puppeteer for {$url}");
            $html = $this->fetchWithPuppeteer($url);
            if ($html) {
                // Switch to Puppeteer for remaining requests this session
                $this->usePuppeteer = true;
                return $html;
            }
        }

        Log::error("Scraper: Failed after {$this->maxRetries} attempts for {$url}", [
            'exception' => $lastException?->getMessage(),
        ]);

        return null;
    }

    protected function fetchWithPuppeteer(string $url): ?string
    {
        $scriptPath = base_path('scripts/fetch-page.mjs');

        if (!file_exists($scriptPath)) {
            Log::warning("Scraper: Puppeteer script not found at {$scriptPath}");
            return null;
        }

        $escapedUrl = escapeshellarg($url);
        $cmd = "node {$scriptPath} {$escapedUrl} 45000 2>/dev/null";

        $output = null;
        $exitCode = null;
        exec($cmd, $outputLines, $exitCode);

        if ($exitCode !== 0 || empty($outputLines)) {
            Log::warning("Scraper: Puppeteer failed for {$url} (exit {$exitCode})");
            return null;
        }

        $html = implode("\n", $outputLines);

        // Verify we got real content, not a challenge page
        if (str_contains($html, 'Just a moment') && strlen($html) < 5000) {
            Log::warning("Scraper: Puppeteer got Cloudflare challenge page for {$url}");
            return null;
        }

        $this->rateLimitPause();
        Log::info("Scraper: Puppeteer success for {$url} (" . strlen($html) . " bytes)");

        return $html;
    }

    protected function rateLimitPause(): void
    {
        usleep(rand(2000000, 5000000)); // 2-5 seconds
    }

    abstract public function scrapeListingPage(string $url): Collection;
    abstract public function scrapePropertyDetail(string $url): array;
}
