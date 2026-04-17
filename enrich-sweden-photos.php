<?php
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scraper = new \App\Services\HomeFinder\Scrapers\HomestraScraper();
$scraper->usePuppeteer = true; // Cloudflare bypass

// Alle Zweedse properties met 0-1 foto's, Homestra URL
$total = \App\Models\Property::where('status', '!=', 'archief')
    ->where('url', 'like', '%homestra.com%')
    ->whereHas('country', fn($q) => $q->where('code', 'SE'))
    ->get()
    ->filter(fn($p) => !is_array($p->images) || count($p->images) <= 1)
    ->count();

echo "Te verrijken: {$total}\n";

$processed = 0;
$enriched = 0;
$failed = 0;

// Chunk-based processing om memory te sparen
\App\Models\Property::where('status', '!=', 'archief')
    ->where('url', 'like', '%homestra.com%')
    ->whereHas('country', fn($q) => $q->where('code', 'SE'))
    ->chunkById(50, function ($chunk) use ($scraper, &$processed, &$enriched, &$failed, $total) {
        foreach ($chunk as $prop) {
            if (is_array($prop->images) && count($prop->images) > 1) continue;
            $processed++;
            try {
                $detail = $scraper->scrapePropertyDetail($prop->url);
                if (!empty($detail['images']) && count($detail['images']) > 1) {
                    $prop->update(['images' => $detail['images']]);
                    $enriched++;
                    if ($enriched % 25 === 0) {
                        echo "  [{$processed}/{$total}] verrijkt: {$enriched}, faal: {$failed}\n";
                    }
                }
            } catch (\Throwable $e) {
                $failed++;
            }
            // Forceer GC om memory te sparen
            if ($processed % 100 === 0) {
                gc_collect_cycles();
            }
        }
    });

echo "\n=== RESULT ===\n";
echo "Processed: {$processed} | Enriched: {$enriched} | Failed: {$failed}\n";

$all = \App\Models\Property::where('status', '!=', 'archief')
    ->whereHas('country', fn($q) => $q->where('code', 'SE'))
    ->get();
$counts = $all->map(fn($p) => is_array($p->images) ? count($p->images) : 0);
echo "0 foto: " . $counts->filter(fn($c) => $c === 0)->count() . "\n";
echo "1 foto: " . $counts->filter(fn($c) => $c === 1)->count() . "\n";
echo "2-4: " . $counts->filter(fn($c) => $c >= 2 && $c <= 4)->count() . "\n";
echo "5+: " . $counts->filter(fn($c) => $c >= 5)->count() . "\n";
echo "Gem: " . round($counts->avg(), 1) . "\n";
