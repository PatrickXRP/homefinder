<?php

namespace App\Jobs;

use App\Helpers\CurrencyHelper;
use App\Models\PriceHistory;
use App\Models\Property;
use App\Models\PropertySource;
use App\Services\HomeFinder\Scrapers\HemnetScraper;
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
                    // Create new property
                    $askingPrice = $listing['asking_price'] ?? null;
                    $currency = $listing['currency'] ?? 'EUR';
                    $askingPriceEur = $askingPrice ? CurrencyHelper::toEur($askingPrice, $currency) : null;
                    $livingArea = $listing['living_area_m2'] ?? null;
                    $pricePerM2 = ($askingPriceEur && $livingArea && $livingArea > 0)
                        ? (int) round($askingPriceEur / $livingArea)
                        : null;

                    Property::create([
                        'country_id' => $source->country_id,
                        'source_id' => $source->id,
                        'name' => $listing['name'] ?? 'Onbekend',
                        'external_id' => $listing['external_id'],
                        'url' => $listing['url'] ?? null,
                        'status' => 'gezien_online',
                        'address' => $listing['address'] ?? null,
                        'city' => $listing['city'] ?? null,
                        'asking_price' => $askingPrice,
                        'currency' => $currency,
                        'asking_price_eur' => $askingPriceEur,
                        'price_per_m2' => $pricePerM2,
                        'living_area_m2' => $livingArea,
                        'plot_area_m2' => $listing['plot_area_m2'] ?? null,
                        'bedrooms' => $listing['bedrooms'] ?? null,
                        'images' => $listing['images'] ?? null,
                        'added_at' => now(),
                        'listed_date' => now()->toDateString(),
                        'scraped_at' => now(),
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
            default => null,
        };
    }
}
