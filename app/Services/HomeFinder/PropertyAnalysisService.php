<?php

namespace App\Services\HomeFinder;

use Anthropic\Laravel\Facades\Anthropic;
use App\Models\BudgetScenario;
use App\Models\Property;
use App\Models\Wish;

class PropertyAnalysisService
{
    public function analyzeProperty(Property $property): void
    {
        $property->load('country', 'priceHistory');
        $wishes = Wish::orderBy('category')->orderBy('sort_order')->get();
        $budget = BudgetScenario::where('is_active', true)->first();

        $wishList = $wishes->map(fn ($w) => "- [{$w->weight}] {$w->category}: {$w->label}")->join("\n");

        $priceHistory = $property->priceHistory->map(fn ($ph) => "{$ph->recorded_date->format('d-m-Y')}: €{$ph->price_eur}")->join(', ');

        $details = collect([
            'Naam' => $property->name,
            'Land' => $property->country?->name,
            'Stad' => $property->city,
            'Vraagprijs' => $property->asking_price_eur ? "€{$property->asking_price_eur}" : 'Onbekend',
            'Prijs/m²' => $property->price_per_m2 ? "€{$property->price_per_m2}" : 'Onbekend',
            'Woonoppervlak' => $property->living_area_m2 ? "{$property->living_area_m2}m²" : 'Onbekend',
            'Perceel' => $property->plot_area_m2 ? "{$property->plot_area_m2}m²" : 'Onbekend',
            'Slaapkamers' => $property->bedrooms ?? 'Onbekend',
            'Bouwjaar' => $property->year_built ?? 'Onbekend',
            'Staat' => $property->condition ?? 'Onbekend',
            'Water' => $property->water_type ? "{$property->water_type} ({$property->water_name})" : 'Geen',
            'Sauna' => $property->has_sauna ? 'Ja' : 'Nee',
            'Steiger' => $property->has_jetty ? 'Ja' : 'Nee',
            'Gastenverblijf' => $property->has_guest_house ? 'Ja' : 'Nee',
            'Jaarrond bereikbaar' => $property->year_round_accessible ? 'Ja' : 'Nee',
            'Dagen op markt' => $property->days_on_market ?? 'Onbekend',
            'Prijsgeschiedenis' => $priceHistory ?: 'Geen',
        ])->map(fn ($v, $k) => "{$k}: {$v}")->join("\n");

        $system = "Je bent een vastgoedadviseur die woningen analyseert voor het gezin Leegte (Patrick + Nathalie + 3 kinderen, budget €" . ($budget?->total_budget_eur ?? 60000) . ").

Analyseer deze woning en geef:
1. Match score (0-100) op basis van de gezinswensen
2. Top 3 positieve punten
3. Rode vlaggen / risico's
4. Aanbevolen biedstrategie (op basis van prijs, dagen op markt, prijsverlagingen)
5. Aanbevolen openingsbod en walk-away prijs

Wensen:
{$wishList}

Sluit af met een JSON blok:
```json
{\"ai_score\": <0-100>, \"summary\": \"<2 zinnen>\"}
```

Antwoord in het Nederlands.";

        $response = Anthropic::messages()->create([
            'model' => config('homefinder.anthropic_model', 'claude-sonnet-4-20250514'),
            'max_tokens' => 2000,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => "Analyseer deze woning:\n\n{$details}"],
            ],
        ]);

        $text = $response->content[0]->text;

        // Extract score from JSON
        if (preg_match('/```json\s*(\{[\s\S]*?\})\s*```/', $text, $match)) {
            $data = json_decode($match[1], true);
            if ($data && isset($data['ai_score'])) {
                $property->ai_score = (int) $data['ai_score'];
            }
            $analysisText = trim(preg_replace('/```json[\s\S]*?```/', '', $text));
        } else {
            $analysisText = $text;
        }

        $property->ai_analysis = $analysisText;
        $property->save();
    }
}
