<?php

namespace App\Services\HomeFinder;

use Anthropic\Laravel\Facades\Anthropic;
use App\Models\Property;

class EmailComposerService
{
    public function composeEmail(Property $property, string $type, string $tone = 'neutraal'): array
    {
        $property->load('country', 'priceHistory');

        $languageMap = [
            'SE' => 'Zweeds',
            'RO' => 'Roemeens',
            'BG' => 'Bulgaars',
            'PT' => 'Portugees',
            'GR' => 'Grieks',
            'HR' => 'Kroatisch',
            'SK' => 'Slowaaks',
            'GE' => 'Georgisch',
            'MK' => 'Macedonisch',
            'AL' => 'Albanees',
        ];

        $countryCode = $property->country?->code ?? 'EN';
        $language = $languageMap[$countryCode] ?? 'Engels';

        $typeLabels = [
            'interesse' => 'Eerste contact / interesse tonen',
            'bezichtiging_aanvragen' => 'Bezichtiging aanvragen',
            'opvolging' => 'Opvolging na bezichtiging',
            'eerste_bod' => 'Eerste bod uitbrengen',
            'tegenbod' => 'Tegenbod',
            'vragen' => 'Aanvullende vragen stellen',
            'bod_intrekken' => 'Bod intrekken',
            'bevestiging' => 'Bevestiging / akkoord',
        ];

        $typeLabel = $typeLabels[$type] ?? $type;

        $priceHistory = $property->priceHistory->map(fn ($ph) => "{$ph->recorded_date->format('d-m-Y')}: €{$ph->price_eur}")->join(', ');

        $context = collect([
            'Woning' => $property->name,
            'Locatie' => ($property->city ?? '') . ', ' . ($property->country?->name ?? ''),
            'Vraagprijs' => $property->asking_price_eur ? "€{$property->asking_price_eur}" : 'Onbekend',
            'Dagen op markt' => $property->days_on_market ?? 'Onbekend',
            'Prijsgeschiedenis' => $priceHistory ?: 'Geen verlagingen bekend',
            'URL' => $property->url ?? 'N/A',
        ])->map(fn ($v, $k) => "{$k}: {$v}")->join("\n");

        $toneInstruction = match ($tone) {
            'zacht' => 'Gebruik een zeer beleefde, warme toon. Toon veel waardering voor de woning.',
            'hard' => 'Wees zakelijk en direct. Benoem concrete punten die een lagere prijs rechtvaardigen.',
            default => 'Gebruik een professionele maar vriendelijke toon.',
        };

        $system = "Je bent een ervaren vastgoedmakelaar die emails schrijft namens Patrick Leegte.
Patrick is een Nederlandse remote AI consultant die met zijn gezin (partner Nathalie, 3 kinderen) een vakantiehuis zoekt.

Schrijf de email in het {$language}.
Type email: {$typeLabel}
Toon: {$toneInstruction}

Context:
{$context}

Geef je antwoord als JSON:
```json
{
    \"subject\": \"<onderwerp in {$language}>\",
    \"body\": \"<volledige email body in {$language}>\",
    \"language\": \"{$language}\",
    \"suggested_send_time\": \"<bijv. 'ochtend werkdag' of 'direct'>\"
}
```";

        $response = Anthropic::messages()->create([
            'model' => config('homefinder.anthropic_model', 'claude-sonnet-4-20250514'),
            'max_tokens' => 1500,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => "Schrijf een '{$typeLabel}' email voor deze woning met toon '{$tone}'."],
            ],
        ]);

        $text = $response->content[0]->text;

        if (preg_match('/```json\s*(\{[\s\S]*?\})\s*```/', $text, $match)) {
            $data = json_decode($match[1], true);
            if ($data) {
                return [
                    'subject' => $data['subject'] ?? '',
                    'body' => $data['body'] ?? '',
                    'language' => $data['language'] ?? $language,
                    'suggested_send_time' => $data['suggested_send_time'] ?? null,
                ];
            }
        }

        // Fallback: return raw text
        return [
            'subject' => "Re: {$property->name}",
            'body' => $text,
            'language' => $language,
            'suggested_send_time' => null,
        ];
    }
}
