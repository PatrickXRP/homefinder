<?php
require '/app/vendor/autoload.php';
$app = require_once '/app/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$scraper = new \App\Services\HomeFinder\Scrapers\HomestraScraper();

// 1. Re-enrich Homestra with 0-1 photo
$homestra = \App\Models\Property::where('status', '!=', 'archief')
    ->where('url', 'like', '%homestra.com%')
    ->get()
    ->filter(fn($p) => is_array($p->images) ? count($p->images) <= 1 : true);

echo "Homestra 0-1 foto: " . $homestra->count() . "\n";
foreach ($homestra as $prop) {
    try {
        $detail = $scraper->scrapePropertyDetail($prop->url);
        if (!empty($detail['images']) && count($detail['images']) > 1) {
            $prop->update(['images' => $detail['images']]);
            echo "  + " . $prop->city . ": " . count($detail['images']) . "\n";
        }
    } catch (\Throwable $e) {}
}

// 2. SwedenEstates properties — fetch photos
$noPhoto = \App\Models\Property::where('status', '!=', 'archief')
    ->where('url', 'like', '%swedenestates%')
    ->get()
    ->filter(fn($p) => !is_array($p->images) || count($p->images) === 0);

echo "\nSwedenEstates 0 foto: " . $noPhoto->count() . "\n";
foreach ($noPhoto as $prop) {
    try {
        $html = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'])->timeout(15)->get($prop->url)->body();
        $pattern = '/https?:\/\/[^\s"\'<>]+?\.(?:jpg|jpeg|png|webp)(?:\?[^\s"\'<>]*)?/i';
        preg_match_all($pattern, $html, $matches);
        $images = collect($matches[0] ?? [])
            ->filter(fn($u) => !str_contains($u, 'logo') && !str_contains($u, 'icon') && !str_contains($u, 'favicon') && !str_contains($u, 'sprite') && (str_contains($u, 'FSDIMAGE') || str_contains($u, 'MED') || str_contains($u, 'CBILD') || str_contains($u, 'property')))
            ->unique()->values()->take(20)->toArray();
        if (count($images) > 0) {
            $prop->update(['images' => $images]);
            echo "  + " . $prop->city . ": " . count($images) . "\n";
        } else {
            echo "  - " . $prop->city . ": 0\n";
        }
        usleep(rand(2000000, 4000000));
    } catch (\Throwable $e) {
        echo "  x " . $prop->city . "\n";
    }
}

// 3. Booli properties — fetch photos
$booli = \App\Models\Property::where('status', '!=', 'archief')
    ->where('url', 'like', '%booli%')
    ->get()
    ->filter(fn($p) => !is_array($p->images) || count($p->images) === 0);

echo "\nBooli 0 foto: " . $booli->count() . "\n";
foreach ($booli as $prop) {
    try {
        $html = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0'])->timeout(15)->get($prop->url)->body();
        $pattern = '/https?:\/\/[^\s"\'<>]+?\.(?:jpg|jpeg|png|webp)(?:\?[^\s"\'<>]*)?/i';
        preg_match_all($pattern, $html, $matches);
        $images = collect($matches[0] ?? [])
            ->filter(fn($u) => !str_contains($u, 'logo') && !str_contains($u, 'icon') && !str_contains($u, 'favicon') && !str_contains($u, 'sprite') && !str_contains($u, 'analytics') && (str_contains($u, 'cdn') || str_contains($u, 'bilder') || str_contains($u, 'images') || str_contains($u, 'hemnet') || str_contains($u, 'booli')))
            ->unique()->values()->take(20)->toArray();
        if (count($images) > 0) {
            $prop->update(['images' => $images]);
            echo "  + " . $prop->city . ": " . count($images) . "\n";
        } else {
            echo "  - " . $prop->city . ": 0\n";
        }
        usleep(rand(2000000, 4000000));
    } catch (\Throwable $e) {
        echo "  x " . $prop->city . "\n";
    }
}

echo "\n=== RESULT ===\n";
$all = \App\Models\Property::where('status', '!=', 'archief')->get();
$counts = $all->map(fn($p) => is_array($p->images) ? count($p->images) : 0);
echo "0 foto: " . $counts->filter(fn($c) => $c === 0)->count() . "\n";
echo "1 foto: " . $counts->filter(fn($c) => $c === 1)->count() . "\n";
echo "2-4: " . $counts->filter(fn($c) => $c >= 2 && $c <= 4)->count() . "\n";
echo "5+: " . $counts->filter(fn($c) => $c >= 5)->count() . "\n";
echo "Gem: " . round($counts->avg(), 1) . "\n";
