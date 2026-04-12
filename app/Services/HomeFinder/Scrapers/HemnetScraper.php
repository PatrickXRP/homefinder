<?php

namespace App\Services\HomeFinder\Scrapers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HemnetScraper extends BasePropertyScraper
{
    public function scrapeListingPage(string $url): Collection
    {
        $html = $this->fetchPage($url);
        if (!$html) {
            return collect();
        }

        $properties = collect();

        // Hemnet uses structured data and specific CSS classes
        // Parse listing cards from search results
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        // Try JSON-LD structured data first
        $scripts = $xpath->query('//script[@type="application/ld+json"]');
        foreach ($scripts as $script) {
            $json = json_decode($script->textContent, true);
            if ($json && isset($json['@type']) && $json['@type'] === 'ItemList') {
                foreach ($json['itemListElement'] ?? [] as $item) {
                    if (isset($item['item'])) {
                        $listing = $item['item'];
                        $properties->push($this->parseJsonLdListing($listing));
                    }
                }
            }
        }

        // Fallback: parse HTML listing cards
        if ($properties->isEmpty()) {
            $cards = $xpath->query('//*[contains(@class, "listing-card")]|//*[contains(@class, "normal-results")]//li[contains(@class, "normal-results__hit")]');
            foreach ($cards as $card) {
                $parsed = $this->parseHtmlCard($card, $xpath);
                if ($parsed) {
                    $properties->push($parsed);
                }
            }
        }

        // Additional fallback: look for hemnet's react data
        if ($properties->isEmpty()) {
            if (preg_match_all('/"listing":\s*(\{[^}]+(?:\{[^}]*\}[^}]*)*\})/', $html, $matches)) {
                foreach ($matches[1] as $jsonStr) {
                    $data = json_decode($jsonStr, true);
                    if ($data && isset($data['id'])) {
                        $properties->push([
                            'external_id' => 'hemnet-' . $data['id'],
                            'name' => $data['street_address'] ?? $data['location_name'] ?? 'Onbekend',
                            'url' => isset($data['id']) ? "https://www.hemnet.se/bostad/{$data['id']}" : null,
                            'asking_price' => $data['asking_price'] ?? $data['list_price'] ?? null,
                            'currency' => 'SEK',
                            'living_area_m2' => isset($data['living_area']) ? (int) $data['living_area'] : null,
                            'plot_area_m2' => isset($data['land_area']) ? (int) $data['land_area'] : null,
                            'bedrooms' => $data['number_of_rooms'] ?? null,
                            'city' => $data['location_name'] ?? null,
                            'address' => $data['street_address'] ?? null,
                        ]);
                    }
                }
            }
        }

        Log::info("HemnetScraper: Found {$properties->count()} listings from {$url}");

        return $properties;
    }

    public function scrapePropertyDetail(string $url): array
    {
        $html = $this->fetchPage($url);
        if (!$html) {
            return [];
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        $data = [
            'url' => $url,
            'raw_data' => [],
        ];

        // Try JSON-LD
        $scripts = $xpath->query('//script[@type="application/ld+json"]');
        foreach ($scripts as $script) {
            $json = json_decode($script->textContent, true);
            if ($json && isset($json['@type']) && in_array($json['@type'], ['Residence', 'House', 'SingleFamilyResidence'])) {
                $data['name'] = $json['name'] ?? null;
                $data['address'] = $json['address']['streetAddress'] ?? null;
                $data['city'] = $json['address']['addressLocality'] ?? null;
                $data['region'] = $json['address']['addressRegion'] ?? null;
                $data['asking_price'] = isset($json['offers']['price']) ? (int) $json['offers']['price'] : null;
                $data['currency'] = $json['offers']['priceCurrency'] ?? 'SEK';
                $data['living_area_m2'] = isset($json['floorSize']['value']) ? (int) $json['floorSize']['value'] : null;
                $data['plot_area_m2'] = isset($json['lotSize']['value']) ? (int) $json['lotSize']['value'] : null;
                $data['bedrooms'] = isset($json['numberOfRooms']) ? (int) $json['numberOfRooms'] : null;
                $data['year_built'] = isset($json['yearBuilt']) ? (int) $json['yearBuilt'] : null;
                $data['latitude'] = $json['geo']['latitude'] ?? null;
                $data['longitude'] = $json['geo']['longitude'] ?? null;

                // Images
                if (isset($json['photo'])) {
                    $photos = is_array($json['photo']) ? $json['photo'] : [$json['photo']];
                    $data['images'] = collect($photos)->pluck('contentUrl')->filter()->values()->toArray();
                }

                $data['raw_data'] = $json;
                break;
            }
        }

        // Parse additional details from HTML
        $this->parseHtmlDetails($xpath, $data);

        // Extract external ID from URL
        if (preg_match('/bostad\/(\d+)/', $url, $m)) {
            $data['external_id'] = 'hemnet-' . $m[1];
        }

        return $data;
    }

    private function parseJsonLdListing(array $listing): array
    {
        return [
            'external_id' => isset($listing['url']) && preg_match('/(\d+)$/', $listing['url'], $m) ? 'hemnet-' . $m[1] : null,
            'name' => $listing['name'] ?? 'Onbekend',
            'url' => $listing['url'] ?? null,
            'asking_price' => isset($listing['offers']['price']) ? (int) $listing['offers']['price'] : null,
            'currency' => $listing['offers']['priceCurrency'] ?? 'SEK',
            'living_area_m2' => isset($listing['floorSize']['value']) ? (int) $listing['floorSize']['value'] : null,
            'plot_area_m2' => isset($listing['lotSize']['value']) ? (int) $listing['lotSize']['value'] : null,
            'bedrooms' => isset($listing['numberOfRooms']) ? (int) $listing['numberOfRooms'] : null,
            'city' => $listing['address']['addressLocality'] ?? null,
            'address' => $listing['address']['streetAddress'] ?? null,
            'images' => isset($listing['photo']) ? collect(is_array($listing['photo']) ? $listing['photo'] : [$listing['photo']])->pluck('contentUrl')->filter()->values()->toArray() : [],
        ];
    }

    private function parseHtmlCard(\DOMElement $card, \DOMXPath $xpath): ?array
    {
        $link = $xpath->query('.//a[contains(@href, "/bostad/")]', $card)->item(0);
        if (!$link) return null;

        $url = $link->getAttribute('href');
        if (!str_starts_with($url, 'http')) {
            $url = 'https://www.hemnet.se' . $url;
        }

        $externalId = null;
        if (preg_match('/bostad\/(\d+)/', $url, $m)) {
            $externalId = 'hemnet-' . $m[1];
        }

        $name = trim($link->textContent) ?: 'Onbekend';

        // Try to find price
        $priceNode = $xpath->query('.//*[contains(@class, "listing-card__attribute--primary")]', $card)->item(0);
        $price = null;
        if ($priceNode) {
            $priceText = preg_replace('/[^\d]/', '', $priceNode->textContent);
            $price = $priceText ? (int) $priceText : null;
        }

        // Try to find area
        $areaNode = $xpath->query('.//*[contains(@class, "listing-card__attribute--secondary")]', $card)->item(0);
        $area = null;
        if ($areaNode) {
            if (preg_match('/([\d,]+)\s*m²/', $areaNode->textContent, $am)) {
                $area = (int) str_replace(',', '', $am[1]);
            }
        }

        return [
            'external_id' => $externalId,
            'name' => $name,
            'url' => $url,
            'asking_price' => $price,
            'currency' => 'SEK',
            'living_area_m2' => $area,
        ];
    }

    private function parseHtmlDetails(\DOMXPath $xpath, array &$data): void
    {
        // Look for property attributes in definition lists or tables
        $dts = $xpath->query('//dt');
        foreach ($dts as $dt) {
            $label = mb_strtolower(trim($dt->textContent));
            $dd = $dt->nextSibling;
            while ($dd && $dd->nodeName !== 'dd') {
                $dd = $dd->nextSibling;
            }
            if (!$dd) continue;

            $value = trim($dd->textContent);

            match (true) {
                str_contains($label, 'bostadstyp') => $data['raw_data']['property_type'] = $value,
                str_contains($label, 'upplåtelseform') => $data['raw_data']['tenure'] = $value,
                str_contains($label, 'antal rum') => $data['bedrooms'] = $data['bedrooms'] ?? (int) $value,
                str_contains($label, 'boarea') => $data['living_area_m2'] = $data['living_area_m2'] ?? (int) preg_replace('/[^\d]/', '', $value),
                str_contains($label, 'tomtarea') => $data['plot_area_m2'] = $data['plot_area_m2'] ?? (int) preg_replace('/[^\d]/', '', $value),
                str_contains($label, 'byggår') => $data['year_built'] = $data['year_built'] ?? (int) $value,
                str_contains($label, 'energiklass') => $data['energy_class'] = $value,
                str_contains($label, 'driftskostnad') => $data['raw_data']['running_cost'] = $value,
                default => null,
            };
        }
    }
}
