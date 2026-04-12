<?php

namespace App\Jobs;

use App\Helpers\CurrencyHelper;
use App\Models\PriceHistory;
use App\Models\Property;
use App\Models\PropertySource;
use App\Services\HomeFinder\Scrapers\HemnetScraper;
use App\Services\HomeFinder\Scrapers\HomestraScraper;
use App\Services\HomeFinder\Scrapers\ScraperInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapePropertiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public ?int $sourceId = null
    ) {}

    public function handle(): void
    {
        $sources = $this->sourceId
            ? PropertySource::where('id', $this->sourceId)->where('is_active', true)->get()
            : PropertySource::where('is_active', true)->get();

        foreach ($sources as $source) {
            $this->scrapeSource($source);
        }
    }

    private function scrapeSource(PropertySource $source): void
    {
        $scraper = $this->resolveScraper($source->scraper_class);
        if (!$scraper) {
            Log::warning("ScrapePropertiesJob: No scraper found for class '{$source->scraper_class}'");
            return;
        }

        $searchUrl = $source->search_url_template ?? $source->base_url;
        if (!$searchUrl) {
            Log::warning("ScrapePropertiesJob: No search URL for source '{$source->name}'");
            return;
        }

        Log::info("ScrapePropertiesJob: Scraping {$source->name} ({$searchUrl})");

        try {
            $listings = $scraper->scrapeListingPage($searchUrl);

            $created = 0;
            $updated = 0;

            // Get max budget from active scenario
            $maxBudget = \App\Models\BudgetScenario::where('is_active', true)->value('purchase_budget_eur') ?? 60000;

            foreach ($listings as $listing) {
                if (empty($listing['external_id'])) continue;

                $existing = Property::where('external_id', $listing['external_id'])
                    ->where('source_id', $source->id)
                    ->first();

                if ($existing) {
                    // Check for price change
                    $newPrice = $listing['asking_price'] ?? null;
                    if ($newPrice && $existing->asking_price && $newPrice != $existing->asking_price) {
                        PriceHistory::create([
                            'property_id' => $existing->id,
                            'price' => $newPrice,
                            'price_eur' => CurrencyHelper::toEur($newPrice, $listing['currency'] ?? 'EUR'),
                            'recorded_date' => now()->toDateString(),
                            'note' => "Prijswijziging: {$existing->asking_price} → {$newPrice}",
                            'source' => 'scrape',
                        ]);

                        Log::info("Price change for {$existing->name}: {$existing->asking_price} → {$newPrice}");
                    }

                    // Update existing
                    $existing->update(array_filter([
                        'asking_price' => $newPrice,
                        'asking_price_eur' => $newPrice ? CurrencyHelper::toEur($newPrice, $listing['currency'] ?? 'EUR') : null,
                        'living_area_m2' => $listing['living_area_m2'] ?? null,
                        'plot_area_m2' => $listing['plot_area_m2'] ?? null,
                        'scraped_at' => now(),
                        'days_on_market' => $existing->listed_date ? now()->diffInDays($existing->listed_date) : null,
                    ]));

                    $updated++;
                } else {
                    // Enrich from detail page first
                    $detail = [];
                    if (!empty($listing['url'])) {
                        try {
                            $detail = $scraper->scrapePropertyDetail($listing['url']);
                        } catch (\Exception $e) {
                            Log::warning("ScrapePropertiesJob: Detail fetch failed for {$listing['url']}: {$e->getMessage()}");
                        }
                    }

                    // Merge: detail data fills in missing/default listing fields
                    $filteredDetail = array_filter($detail);
                    $merged = $listing;
                    foreach ($filteredDetail as $key => $value) {
                        if (!isset($merged[$key]) || $merged[$key] === null || $merged[$key] === 'Onbekend' || $merged[$key] === '' || $merged[$key] === 0) {
                            $merged[$key] = $value;
                        }
                    }

                    // Calculate price from merged data
                    $askingPrice = $merged['asking_price'] ?? null;
                    $currency = $merged['currency'] ?? 'EUR';
                    $askingPriceEur = $merged['asking_price_eur']
                        ?? ($askingPrice ? CurrencyHelper::toEur($askingPrice, $currency) : null);
                    $livingArea = $merged['living_area_m2'] ?? null;
                    $plotArea = $merged['plot_area_m2'] ?? null;
                    $pricePerM2Merged = ($askingPriceEur && $livingArea && $livingArea > 0)
                        ? (int) round($askingPriceEur / $livingArea)
                        : ($merged['price_per_m2'] ?? null);

                    // Skip properties above budget (with 20% margin for negotiation)
                    if ($askingPriceEur && $askingPriceEur > $maxBudget * 1.2) {
                        Log::debug("ScrapePropertiesJob: Skipping {$merged['name']} — €{$askingPriceEur} above budget");
                        continue;
                    }

                    Property::create([
                        'country_id' => $source->country_id,
                        'source_id' => $source->id,
                        'name' => $merged['name'] ?? 'Onbekend',
                        'external_id' => $listing['external_id'],
                        'url' => $merged['url'] ?? null,
                        'status' => 'gezien_online',
                        'address' => $merged['address'] ?? null,
                        'city' => $merged['city'] ?? null,
                        'region' => $merged['region'] ?? null,
                        'latitude' => $merged['latitude'] ?? null,
                        'longitude' => $merged['longitude'] ?? null,
                        'asking_price' => $askingPrice,
                        'currency' => $currency,
                        'asking_price_eur' => $askingPriceEur,
                        'price_per_m2' => $pricePerM2Merged,
                        'living_area_m2' => $livingArea,
                        'plot_area_m2' => $plotArea,
                        'bedrooms' => $merged['bedrooms'] ?? null,
                        'bathrooms' => $merged['bathrooms'] ?? null,
                        'year_built' => $merged['year_built'] ?? null,
                        'energy_class' => $merged['energy_class'] ?? null,
                        'condition' => $merged['condition'] ?? null,
                        'images' => $merged['images'] ?? null,
                        'listed_date' => $merged['listed_date'] ?? now()->toDateString(),
                        'added_at' => now(),
                        'scraped_at' => now(),
                        'raw_data' => $merged['raw_data'] ?? null,
                    ]);

                    $created++;
                }
            }

            $source->update(['last_scraped_at' => now()]);

            Log::info("ScrapePropertiesJob: {$source->name} done — {$created} new, {$updated} updated");
        } catch (\Exception $e) {
            Log::error("ScrapePropertiesJob: Error scraping {$source->name}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function resolveScraper(?string $class): ?ScraperInterface
    {
        return match ($class) {
            'HemnetScraper' => new HemnetScraper(),
            'HomestraScraper' => new HomestraScraper(),
            default => null,
        };
    }
}
