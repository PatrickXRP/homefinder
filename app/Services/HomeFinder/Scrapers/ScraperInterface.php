<?php

namespace App\Services\HomeFinder\Scrapers;

use Illuminate\Support\Collection;

interface ScraperInterface
{
    public function scrapeListingPage(string $url): Collection;
    public function scrapePropertyDetail(string $url): array;
}
