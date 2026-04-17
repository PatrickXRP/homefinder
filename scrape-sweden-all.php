<?php
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scraper = new \App\Services\HomeFinder\Scrapers\HomestraScraper();
$se = \App\Models\Country::where('code', 'SE')->first();
$source = \App\Models\PropertySource::where('country_id', $se->id)->where('scraper_class', 'HomestraScraper')->first();

if (!$source) {
    $source = \App\Models\PropertySource::create([
        'country_id' => $se->id,
        'name' => 'Homestra Sweden',
        'scraper_class' => 'HomestraScraper',
        'base_url' => 'https://homestra.com',
        'is_active' => true,
    ]);
}

// Alle Zweedse regio's (län) + bekende gemeentes
$regions = [
    // Län
    'dalarna', 'varmland', 'jamtland', 'gavleborg', 'vasternorrland',
    'norrbotten', 'vasterbotten', 'smaland', 'orebro',
    'sodermanland', 'ostergotland', 'vastmanland', 'kronoberg',
    'kalmar', 'blekinge', 'jonkoping', 'halland', 'vastra-gotaland',
    'skane', 'stockholm', 'uppsala', 'gotland',
    // Värmland gemeentes
    'hagfors', 'torsby', 'arvika', 'sunne', 'filipstad', 'karlstad',
    'kristinehamn', 'saffle', 'kil', 'forshaga', 'hammaro', 'munkfors',
    'storfors', 'eda', 'grums', 'arjang',
    // Dalarna gemeentes
    'mora', 'rattvik', 'leksand', 'malung-salen', 'vansbro', 'alvdalen',
    'orsa', 'falun', 'borlange', 'avesta', 'hedemora', 'ludvika',
    'gagnef', 'smedjebacken', 'sater',
    // Jämtland gemeentes
    'are', 'ostersund', 'krokom', 'berg', 'harjedalen', 'stromsund',
    'ragunda', 'bracke',
    // Småland
    'vaxjo', 'kalmar-stad', 'jonkoping-stad', 'vimmerby', 'vetlanda',
    'eksjo', 'nassjo', 'tranas', 'savsjo',
    // Norrland
    'lulea', 'umea', 'sundsvall', 'pitea', 'kiruna', 'gallivare',
    'arvidsjaur', 'arjeplog', 'jokkmokk', 'sorsele', 'lycksele',
];

// Alle property types die Homestra ondersteunt
$types = ['cabin', 'country_home', 'chalet', 'house', 'villa', 'townhouse', 'apartment'];

$totalCreated = 0;
$totalSeen = 0;

function createProperty($m, $se, $source, $region = null) {
    $price = $m['asking_price'] ?? null;
    if (!$price) return false; // alleen filteren op aanwezigheid prijs

    $la = $m['living_area_m2'] ?? null;
    \App\Models\Property::create([
        'country_id' => $se->id, 'source_id' => $source->id,
        'name' => $m['name'] ?? 'Onbekend', 'external_id' => $m['external_id'] ?? null,
        'url' => $m['url'] ?? null, 'status' => 'gezien_online',
        'address' => $m['address'] ?? null, 'city' => $m['city'] ?? null,
        'region' => $m['region'] ?? $region,
        'asking_price' => $price, 'currency' => 'EUR', 'asking_price_eur' => $price,
        'price_per_m2' => ($price && $la && $la > 0) ? (int)round($price/$la) : null,
        'living_area_m2' => $la, 'plot_area_m2' => $m['plot_area_m2'] ?? null,
        'bedrooms' => $m['bedrooms'] ?? null, 'bathrooms' => $m['bathrooms'] ?? null,
        'condition' => $m['condition'] ?? null, 'images' => $m['images'] ?? null,
        'latitude' => $m['latitude'] ?? null, 'longitude' => $m['longitude'] ?? null,
        'listed_date' => $m['listed_date'] ?? now()->toDateString(),
        'added_at' => now(), 'scraped_at' => now(),
    ]);
    return true;
}

function processListing($scraper, $listing, $se, $source, &$totalSeen, &$totalCreated, $region = null) {
    $totalSeen++;
    if (empty($listing['external_id'])) return 0;
    if (\App\Models\Property::where('external_id', $listing['external_id'])->exists()) return 0;
    $detail = [];
    if (!empty($listing['url'])) {
        try { $detail = $scraper->scrapePropertyDetail($listing['url']); } catch (\Throwable $e) {}
    }
    $m = $listing;
    foreach (array_filter($detail) as $k => $v) {
        if (!isset($m[$k]) || $m[$k] === null || $m[$k] === 'Onbekend' || $m[$k] === '' || $m[$k] === 0) {
            $m[$k] = $v;
        }
    }
    if (createProperty($m, $se, $source, $region)) {
        $totalCreated++;
        return 1;
    }
    return 0;
}

// 1. Brede zoekopdracht: heel Zweden per type, GEEN max budget
foreach ($types as $type) {
    $url = "https://homestra.com/list/houses-for-sale/sweden/?property-type={$type}";
    echo "Sweden / {$type}... ";
    try { $listings = $scraper->scrapeListingPage($url); } catch (\Throwable $e) { echo "err: {$e->getMessage()}\n"; continue; }
    $created = 0;
    foreach ($listings as $listing) {
        $created += processListing($scraper, $listing, $se, $source, $totalSeen, $totalCreated);
    }
    echo $listings->count() . " seen / {$created} new\n";
}

// 2. Per regio/gemeente
foreach ($regions as $region) {
    foreach (['cabin', 'country_home', 'house', 'villa'] as $type) {
        $url = "https://homestra.com/list/houses-for-sale/sweden/{$region}/?property-type={$type}";
        echo "{$region}/{$type}... ";
        try { $listings = $scraper->scrapeListingPage($url); } catch (\Throwable $e) { echo "err\n"; continue; }
        $created = 0;
        foreach ($listings as $listing) {
            $created += processListing($scraper, $listing, $se, $source, $totalSeen, $totalCreated, $region);
        }
        echo $listings->count() . " / {$created} new\n";
    }
}

// 3. Brede prijsklasses (zonder type filter)
foreach ([
    ['min' => 0, 'max' => 50000],
    ['min' => 50000, 'max' => 100000],
    ['min' => 100000, 'max' => 200000],
    ['min' => 200000, 'max' => 500000],
    ['min' => 500000, 'max' => 9999999],
] as $range) {
    $url = "https://homestra.com/list/houses-for-sale/sweden/?minimum-price={$range['min']}&maximum-price={$range['max']}";
    echo "Sweden price €{$range['min']}-{$range['max']}... ";
    try { $listings = $scraper->scrapeListingPage($url); } catch (\Throwable $e) { echo "err\n"; continue; }
    $created = 0;
    foreach ($listings as $listing) {
        $created += processListing($scraper, $listing, $se, $source, $totalSeen, $totalCreated);
    }
    echo $listings->count() . " / {$created} new\n";
}

// Deduplicatie
$dupeRows = \Illuminate\Support\Facades\DB::select("SELECT external_id, count(*) as cnt FROM properties WHERE external_id IS NOT NULL GROUP BY external_id HAVING count(*) > 1");
$deleted = 0;
foreach ($dupeRows as $d) {
    $ids = \App\Models\Property::where('external_id', $d->external_id)->orderBy('id')->pluck('id');
    $toDelete = $ids->slice(1);
    \App\Models\Property::whereIn('id', $toDelete)->delete();
    $deleted += $toDelete->count();
}

echo "\n=== RESULT ===\n";
echo "Seen: {$totalSeen} | New: {$totalCreated} | Deduped: {$deleted}\n";
echo "SE actief: " . \App\Models\Property::where('status', '!=', 'archief')->whereHas('country', fn($q) => $q->where('code', 'SE'))->count() . "\n";
echo "SE totaal: " . \App\Models\Property::whereHas('country', fn($q) => $q->where('code', 'SE'))->count() . "\n";
echo "DB totaal: " . \App\Models\Property::count() . "\n";
$prices = \App\Models\Property::where('status', '!=', 'archief')->whereHas('country', fn($q) => $q->where('code', 'SE'))->pluck('asking_price_eur');
echo "Prijsrange: €" . $prices->min() . " - €" . $prices->max() . " (gem: €" . round($prices->avg()) . ")\n";
