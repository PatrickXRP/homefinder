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

    protected function fetchPage(string $url): ?string
    {
        $lastException = null;

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

                Log::warning("Scraper: HTTP {$response->status()} for {$url} (attempt {$attempt})");
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("Scraper: Exception on {$url} (attempt {$attempt}): {$e->getMessage()}");
            }

            if ($attempt < $this->maxRetries) {
                sleep(rand(3, 8));
            }
        }

        Log::error("Scraper: Failed after {$this->maxRetries} attempts for {$url}", [
            'exception' => $lastException?->getMessage(),
        ]);

        return null;
    }

    protected function rateLimitPause(): void
    {
        usleep(rand(2000000, 5000000)); // 2-5 seconds
    }

    abstract public function scrapeListingPage(string $url): Collection;
    abstract public function scrapePropertyDetail(string $url): array;
}
