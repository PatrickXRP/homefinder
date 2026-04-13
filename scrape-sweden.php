<?php
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scraper = new \App\Services\HomeFinder\Scrapers\HomestraScraper();
$se = \App\Models\Country::where('code', 'SE')->first();
$source = \App\Models\PropertySource::where('country_id', $se->id)->where('scraper_class', 'HomestraScraper')->first();
$maxBudget = 80000;

$regions = [
    'dalarna', 'varmland', 'jamtland', 'gavleborg', 'vasternorrland',
    'norrbotten', 'vasterbotten', 'smaland', 'orebro',
    'sodermanland', 'ostergotland', 'vastmanland', 'kronoberg',
    'kalmar', 'blekinge', 'jonkoping', 'halland', 'vastra-gotaland',
    'skane', 'stockholm', 'uppsala', 'gotland', 'hagfors', 'torsby',
    'arvika', 'sunne', 'filipstad', 'mora', 'rattvik', 'leksand',
    'malung-salen', 'vansbro', 'alvdalen', 'orsa', 'are',
    'ostersund', 'krokom', 'berg', 'harjedalen',
];

$types = ['cabin', 'country_home', 'chalet', 'house', 'villa'];
$totalCreated = 0;

function createProperty($m, $se, $source, $maxBudget) {
    $price = $m['asking_price'] ?? null;
    if (!$price || $price > $maxBudget) return false;

    $la = $m['living_area_m2'] ?? null;
    \App\Models\Property::create([
        'country_id' => $se->id, 'source_id' => $source->id,
        'name' => $m['name'] ?? 'Onbekend', 'external_id' => $m['external_id'] ?? null,
        'url' => $m['url'] ?? null, 'status' => 'gezien_online',
        'address' => $m['address'] ?? null, 'city' => $m['city'] ?? null,
        'region' => $m['region'] ?? null,
        'asking_price' => $price, 'currency' => 'EUR', 'asking_price_eur' => $price,
        'price_per_m2' => ($price && $la && $la > 0) ? (int)round($price/$la) : null,
        'living_area_m2' => $la, 'plot_area_m2' => $m['plot_area_m2'] ?? null,
        'bedrooms' => $m['bedrooms'] ?? null, 'bathrooms' => $m['bathrooms'] ?? null,
        'condition' => $m['condition'] ?? null, 'images' => $m['images'] ?? null,
        'listed_date' => $m['listed_date'] ?? now()->toDateString(),
        'added_at' => now(), 'scraped_at' => now(),
    ]);
    return true;
}

// Broad search: all of Sweden per type
foreach ($types as $type) {
    $url = "https://homestra.com/list/houses-for-sale/sweden/?maximum-price={$maxBudget}&property-type={$type}";
    echo "Sweden / {$type}... ";
    try { $listings = $scraper->scrapeListingPage($url); } catch (\Throwable $e) { echo "err\n"; continue; }
    echo $listings->count() . " found, ";
    $created = 0;
    foreach ($listings as $listing) {
        if (empty($listing['external_id'])) continue;
        if (\App\Models\Property::where('external_id', $listing['external_id'])->exists()) continue;
        $detail = [];
        if (!empty($listing['url'])) { try { $detail = $scraper->scrapePropertyDetail($listing['url']); } catch (\Throwable $e) {} }
        $m = $listing;
        foreach (array_filter($detail) as $k => $v) { if (!isset($m[$k]) || $m[$k] === null || $m[$k] === 'Onbekend' || $m[$k] === '' || $m[$k] === 0) $m[$k] = $v; }
        if (createProperty($m, $se, $source, $maxBudget)) { $created++; $totalCreated++; }
    }
    echo "{$created} new\n";
}

// Per region search
foreach ($regions as $region) {
    foreach (['cabin', 'country_home', 'house'] as $type) {
        $url = "https://homestra.com/list/houses-for-sale/sweden/{$region}/?maximum-price={$maxBudget}&property-type={$type}";
        echo "{$region}/{$type}... ";
        try { $listings = $scraper->scrapeListingPage($url); } catch (\Throwable $e) { echo "err\n"; continue; }
        $created = 0;
        foreach ($listings as $listing) {
            if (empty($listing['external_id'])) continue;
            if (\App\Models\Property::where('external_id', $listing['external_id'])->exists()) continue;
            $detail = [];
            if (!empty($listing['url'])) { try { $detail = $scraper->scrapePropertyDetail($listing['url']); } catch (\Throwable $e) {} }
            $m = $listing;
            foreach (array_filter($detail) as $k => $v) { if (!isset($m[$k]) || $m[$k] === null || $m[$k] === 'Onbekend' || $m[$k] === '' || $m[$k] === 0) $m[$k] = $v; }
            if (!isset($m['region']) || !$m['region']) $m['region'] = $region;
            if (createProperty($m, $se, $source, $maxBudget)) { $created++; $totalCreated++; }
        }
        echo $listings->count() . " / {$created} new\n";
    }
}

// Deduplicate
$dupeRows = \Illuminate\Support\Facades\DB::select("SELECT external_id, count(*) as cnt FROM properties WHERE external_id IS NOT NULL GROUP BY external_id HAVING count(*) > 1");
$deleted = 0;
foreach ($dupeRows as $d) {
    $ids = \App\Models\Property::where('external_id', $d->external_id)->orderBy('id')->pluck('id');
    $toDelete = $ids->slice(1);
    \App\Models\Property::whereIn('id', $toDelete)->delete();
    $deleted += $toDelete->count();
}

echo "\n=== RESULT ===\n";
echo "New: {$totalCreated}, Deduped: {$deleted}\n";
echo "Sweden: " . \App\Models\Property::whereHas('country', fn($q) => $q->where('code', 'SE'))->count() . "\n";
echo "Total: " . \App\Models\Property::count() . "\n";
