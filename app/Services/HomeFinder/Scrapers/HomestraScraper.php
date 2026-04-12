<?php

namespace App\Services\HomeFinder\Scrapers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HomestraScraper extends BasePropertyScraper
{
    private const BASE_URL = 'https://homestra.com';

    private const COUNTRY_SLUGS = [
        'SE' => 'sweden', 'FI' => 'finland', 'NO' => 'norway',
        'EE' => 'estonia', 'LV' => 'latvia', 'LT' => 'lithuania',
        'PL' => 'poland', 'HU' => 'hungary', 'SK' => 'slovakia',
        'PT' => 'portugal', 'ES' => 'spain', 'IT' => 'italy',
        'GR' => 'greece', 'HR' => 'croatia', 'RS' => 'serbia',
        'MK' => 'north-macedonia', 'GE' => 'georgia',
        'JP' => 'japan', 'AR' => 'argentina', 'PY' => 'paraguay',
    ];

    public function scrapeListingPage(string $url): Collection
    {
        $allListings = collect();
        $page = 1;
        $maxPages = 10;

        while ($page <= $maxPages) {
            $pageUrl = $url . (str_contains($url, '?') ? '&' : '?') . "page={$page}";
            $html = $this->fetchPage($pageUrl);

            if (!$html) break;

            $listings = $this->parseListingPage($html);

            if ($listings->isEmpty()) break;

            $allListings = $allListings->concat($listings);
            Log::info("HomestraScraper: Page {$page} — {$listings->count()} listings (total: {$allListings->count()})");

            $page++;
        }

        return $allListings;
    }

    public function scrapePropertyDetail(string $url): array
    {
        $html = $this->fetchPage($url);
        if (!$html) return [];

        $data = ['url' => $url];

        // Parse JSON-LD first (most reliable)
        $this->parseJsonLd($html, $data);

        // Parse dt/dd detail pairs
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        $this->parseDetailPairs($xpath, $data);
        $this->parseImages($html, $data);
        $this->parseListedDate($html, $data);

        // Generate external_id from URL slug
        if (preg_match('/\/property\/([^\/]+)\/?$/', $url, $m)) {
            $data['external_id'] = 'homestra-' . substr(md5($m[1]), 0, 12);
        }

        return $data;
    }

    /**
     * Build a search URL for a given country and filters.
     */
    public static function buildSearchUrl(string $countryCode, array $filters = []): string
    {
        $slug = self::COUNTRY_SLUGS[strtoupper($countryCode)] ?? strtolower($countryCode);

        $params = [];
        if (!empty($filters['max_price'])) $params['maximum-price'] = $filters['max_price'];
        if (!empty($filters['min_price'])) $params['minimum-price'] = $filters['min_price'];
        if (!empty($filters['property_type'])) $params['property-type'] = $filters['property_type'];
        if (!empty($filters['min_area'])) $params['minimum-size'] = $filters['min_area'];

        $query = $params ? '?' . http_build_query($params) : '';

        return self::BASE_URL . "/list/houses-for-sale/{$slug}/{$query}";
    }

    private function parseListingPage(string $html): Collection
    {
        $listings = collect();

        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        // Find all links to /property/ pages
        $links = $xpath->query('//a[contains(@href, "/property/")]');
        $seen = [];

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Skip non-property links
            if (!preg_match('/\/property\/[a-z0-9-]+\/?$/', $href)) continue;

            // Full URL
            $propertyUrl = str_starts_with($href, 'http') ? $href : self::BASE_URL . $href;

            // Deduplicate
            if (isset($seen[$propertyUrl])) continue;
            $seen[$propertyUrl] = true;

            // Extract data from listing card
            $listing = $this->parseListingCard($link, $xpath);
            $listing['url'] = $propertyUrl;

            // Generate external_id
            if (preg_match('/\/property\/([^\/]+)\/?$/', $href, $m)) {
                $listing['external_id'] = 'homestra-' . substr(md5($m[1]), 0, 12);
            }

            $listings->push($listing);
        }

        return $listings;
    }

    private function parseListingCard(\DOMElement $link, \DOMXPath $xpath): array
    {
        $data = [
            'name' => 'Onbekend',
            'asking_price' => null,
            'currency' => 'EUR',
            'images' => [],
        ];

        // Title from h3 — truncate long marketing titles
        $h3 = $xpath->query('.//h3', $link)->item(0);
        if ($h3) {
            $title = trim($h3->textContent);
            // Keep max 80 chars, cut at last word
            if (mb_strlen($title) > 80) {
                $title = mb_substr($title, 0, 77);
                $lastSpace = mb_strrpos($title, ' ');
                if ($lastSpace) $title = mb_substr($title, 0, $lastSpace);
                $title .= '...';
            }
            $data['name'] = $title;
        }

        // Image
        $img = $xpath->query('.//img', $link)->item(0);
        if ($img) {
            $src = $img->getAttribute('src');
            // Extract original URL from CDN wrapper
            $data['images'] = [$this->extractOriginalImageUrl($src)];
        }

        // Price - look for text containing €
        $text = $link->textContent;
        if (preg_match('/€\s*([\d,]+)/', $text, $m)) {
            $data['asking_price'] = (int) str_replace([',', '.'], '', $m[1]);
            $data['asking_price_eur'] = $data['asking_price'];
        }

        return $data;
    }

    private function parseJsonLd(string $html, array &$data): void
    {
        if (!preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $m)) {
            return;
        }

        $json = json_decode($m[1], true);
        if (!$json) return;

        // Address
        if (isset($json['address'])) {
            $addr = $json['address'];
            $data['address'] = $addr['streetAddress'] ?? null;
            $data['city'] = $addr['addressLocality'] ?? null;
            $data['region'] = $addr['addressRegion'] ?? null;
        }

        // Name — JSON-LD 'name' is usually the address, not a good display name
        // Only use it if we don't have a name yet, and prefer generating from city
        if (isset($json['name']) && empty($data['name'])) {
            // Use city + region as name instead of full address
            $city = $data['city'] ?? $json['address']['addressLocality'] ?? null;
            $region = $data['region'] ?? $json['address']['addressRegion'] ?? null;
            if ($city) {
                $data['name'] = 'Stuga in ' . $city . ($region ? ', ' . $region : '');
            } else {
                $data['name'] = $json['name'];
            }
        }

        // Price
        if (isset($json['offers']['price'])) {
            $data['asking_price'] = (int) $json['offers']['price'];
            $data['currency'] = $json['offers']['priceCurrency'] ?? 'EUR';
            $data['asking_price_eur'] = $data['asking_price']; // Homestra lists in EUR
        }

        // Bedrooms/bathrooms
        if (isset($json['numberOfBedrooms'])) {
            $data['bedrooms'] = (int) $json['numberOfBedrooms'];
        }
        if (isset($json['numberOfFullBathrooms'])) {
            $data['bathrooms'] = (int) $json['numberOfFullBathrooms'];
        }

        // Geo
        if (isset($json['geo'])) {
            $data['latitude'] = $json['geo']['latitude'] ?? null;
            $data['longitude'] = $json['geo']['longitude'] ?? null;
        }
    }

    private function parseDetailPairs(\DOMXPath $xpath, array &$data): void
    {
        $dts = $xpath->query('//dt');
        foreach ($dts as $dt) {
            $label = strtolower(trim($dt->textContent));
            $dd = $dt->nextSibling;
            while ($dd && $dd->nodeName !== 'dd') {
                $dd = $dd->nextSibling;
            }
            if (!$dd) continue;
            $value = trim($dd->textContent);

            match (true) {
                str_contains($label, 'size') && !str_contains($label, 'garden') && !str_contains($label, 'plot')
                    => $data['living_area_m2'] = $data['living_area_m2'] ?? (int) preg_replace('/[^\d]/', '', $value),
                str_contains($label, 'garden') || str_contains($label, 'plot') || str_contains($label, 'land')
                    => $data['plot_area_m2'] = $data['plot_area_m2'] ?? (int) preg_replace('/[^\d]/', '', $value),
                str_contains($label, 'bedroom')
                    => $data['bedrooms'] = $data['bedrooms'] ?? (int) $value,
                str_contains($label, 'bathroom')
                    => $data['bathrooms'] = $data['bathrooms'] ?? (int) $value,
                str_contains($label, 'year') && str_contains($label, 'built')
                    => $data['year_built'] = (int) $value,
                str_contains($label, 'energy')
                    => $data['energy_class'] = $value !== 'Unknown' ? $value : null,
                str_contains($label, 'condition')
                    => $data['condition'] = $this->mapCondition($value),
                str_contains($label, 'price per')
                    => $data['price_per_m2'] = (int) preg_replace('/[^\d]/', '', $value),
                str_contains($label, 'furnished')
                    => $data['raw_data']['furnished'] = $value,
                str_contains($label, 'parking')
                    => $data['raw_data']['parking'] = $value,
                str_contains($label, 'pool')
                    => $data['raw_data']['pool'] = $value,
                str_contains($label, 'garden') && str_contains($label, 'has')
                    => $data['raw_data']['has_garden'] = $value,
                default => null,
            };
        }
    }

    private function parseImages(string $html, array &$data): void
    {
        $images = [];

        // Match GCS URLs — both direct and CDN-wrapped
        // CDN format: homestra.com/cdn-cgi/image/width=256,quality=65/https://storage.googleapis.com/...
        // Direct format: https://storage.googleapis.com/homestra-images/property-image-UUID-TIMESTAMP.jpg
        preg_match_all(
            '/(https:\/\/storage\.googleapis\.com\/homestra-images\/property-image-[a-f0-9-]+-\d+\.jpg)/',
            $html,
            $matches
        );

        if (!empty($matches[1])) {
            $images = array_values(array_unique($matches[1]));
        }

        // Also check img src attributes for CDN-wrapped URLs
        if (empty($images)) {
            preg_match_all(
                '/src=["\'](?:https?:\/\/[^"\']*?\/cdn-cgi\/image\/[^"\']*?\/)?(https:\/\/storage\.googleapis\.com\/homestra-images\/[^"\']+)["\']/',
                $html,
                $matches2
            );
            if (!empty($matches2[1])) {
                $images = array_values(array_unique($matches2[1]));
            }
        }

        if (!empty($images)) {
            $data['images'] = $images;
        }
    }

    private function parseListedDate(string $html, array &$data): void
    {
        // "Listed on 30/12/24" or "Listed on 30/12/2024"
        if (preg_match('/[Ll]isted\s+on\s+(\d{1,2})\/(\d{1,2})\/(\d{2,4})/', $html, $m)) {
            $year = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            $data['listed_date'] = "{$year}-{$m[2]}-{$m[1]}";
        }
    }

    private function extractOriginalImageUrl(string $cdnUrl): string
    {
        // CDN format: https://homestra.com/cdn-cgi/image/width=256,quality=65/https://storage.googleapis.com/...
        if (preg_match('/(https:\/\/storage\.googleapis\.com\/homestra-images\/[^\s"\']+)/', $cdnUrl, $m)) {
            return $m[1];
        }
        return $cdnUrl;
    }

    private function mapCondition(string $value): ?string
    {
        return match (strtolower(trim($value))) {
            'new', 'excellent' => 'turnkey',
            'good' => 'goed',
            'fair', 'reasonable' => 'matig',
            'renovation needed', 'poor' => 'opknapper',
            'ruin' => 'slooprijp',
            default => null,
        };
    }
}
