<?php

namespace App\Services\HomeFinder;

use Anthropic\Laravel\Facades\Anthropic;
use App\Models\BudgetScenario;
use App\Models\Country;
use App\Models\CountryWishScore;
use App\Models\Wish;

class CountryResearchService
{
    public function generateReport(Country $country): void
    {
        $wishes = Wish::orderBy('category')->orderBy('sort_order')->get();
        $budget = BudgetScenario::where('is_active', true)->first();

        $wishList = $wishes->map(function ($w) {
            $weight = match ($w->weight) {
                'must_have' => 'MUST HAVE',
                'nice_to_have' => 'Nice to have',
                'bonus' => 'Bonus',
            };
            return "- [{$weight}] {$w->category}: {$w->label}" . ($w->value ? " (waarde: {$w->value})" : '');
        })->join("\n");

        $budgetInfo = $budget
            ? "Totaal: €{$budget->total_budget_eur}, Aankoop: €{$budget->purchase_budget_eur}, Verbouwing: €{$budget->renovation_budget_eur}, Max jaarlast: €{$budget->annual_costs_max_eur}"
            : 'Budget: €60.000 totaal';

        $system = "Je bent een expert adviseur voor internationale vastgoedaankoop en emigratie.
Analyseer het opgegeven land voor het gezin Leegte:
- Patrick (remote AI consultant, engineering achtergrond) + partner Nathalie
- Drie kinderen: zoon ~11 jaar, dochters ~10 en ~8 jaar
- Leefstijl: nomadisch, remote werk via laptop, technisch hands-on
- Woonbasis: Dubai
- Budget: {$budgetInfo}
- Wensen (gewogen):
{$wishList}

Genereer een gedetailleerd rapport met:
1. Samenvatting geschiktheid voor dit gezin
2. Vastgoedmarkt voor buitenlanders (rechten, proces, kosten)
3. Wat het budget realistisch kan kopen in de natuur
4. Praktisch leven (internet, zorg, taal, kinderen)
5. Bereikbaarheid vanuit Dubai
6. Top 5 voordelen voor dit gezin
7. Top 5 risico's of nadelen

Na het rapport, geef een JSON blok met scores per wens:
```json
{
    \"scores\": [
        {\"wish_id\": <id>, \"score\": <0-10>, \"explanation\": \"<korte uitleg>\"}
    ],
    \"pros\": [\"voordeel 1\", \"voordeel 2\", ...],
    \"cons\": [\"nadeel 1\", \"nadeel 2\", ...],
    \"summary\": \"<1 zin samenvatting>\"
}
```

Gebruik exact de wish_id's die ik meegeef. Antwoord in het Nederlands.";

        $wishIds = $wishes->map(fn ($w) => "wish_id={$w->id}: [{$w->weight}] {$w->category} - {$w->label}")->join("\n");

        $userMessage = "Analyseer {$country->name} ({$country->code}) voor ons gezin.\n\nWens ID's:\n{$wishIds}";

        $response = Anthropic::messages()->create([
            'model' => config('homefinder.anthropic_model', 'claude-sonnet-4-20250514'),
            'max_tokens' => 4000,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        $text = $response->content[0]->text;

        // Extract JSON block
        $jsonMatch = [];
        if (preg_match('/```json\s*(\{[\s\S]*?\})\s*```/', $text, $jsonMatch)) {
            $data = json_decode($jsonMatch[1], true);

            if ($data) {
                // Save wish scores
                if (isset($data['scores'])) {
                    foreach ($data['scores'] as $scoreData) {
                        CountryWishScore::updateOrCreate(
                            ['country_id' => $country->id, 'wish_id' => $scoreData['wish_id']],
                            ['score' => $scoreData['score'], 'explanation' => $scoreData['explanation'] ?? null]
                        );
                    }
                }

                // Save pros/cons
                $country->pros = $data['pros'] ?? [];
                $country->cons = $data['cons'] ?? [];
                $country->match_summary = $data['summary'] ?? null;
            }

            // Remove JSON block from report text
            $reportText = trim(preg_replace('/```json[\s\S]*?```/', '', $text));
        } else {
            $reportText = $text;
        }

        $country->ai_report = $reportText;
        $country->ai_report_generated_at = now();
        $country->save();

        // Recalculate match score
        app(WishMatchingService::class)->scoreCountry($country);
    }
}
