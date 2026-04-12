<?php

namespace App\Services\HomeFinder;

use App\Models\Country;
use App\Models\CountryWishScore;
use App\Models\Wish;

class WishMatchingService
{
    public function scoreCountry(Country $country): void
    {
        $wishes = Wish::all();
        $scores = CountryWishScore::where('country_id', $country->id)->get()->keyBy('wish_id');

        if ($scores->isEmpty()) {
            $country->update(['match_score' => 0]);
            return;
        }

        $weightMultipliers = [
            'must_have' => 3,
            'nice_to_have' => 2,
            'bonus' => 1,
        ];

        $totalWeightedScore = 0;
        $totalMaxScore = 0;

        foreach ($wishes as $wish) {
            $multiplier = $weightMultipliers[$wish->weight] ?? 1;
            $maxForWish = 10 * $multiplier;
            $totalMaxScore += $maxForWish;

            if ($scores->has($wish->id)) {
                $totalWeightedScore += $scores->get($wish->id)->score * $multiplier;
            }
        }

        $normalizedScore = $totalMaxScore > 0
            ? (int) round(($totalWeightedScore / $totalMaxScore) * 100)
            : 0;

        $country->update(['match_score' => $normalizedScore]);
    }

    public function scoreAllCountries(): void
    {
        Country::all()->each(fn (Country $country) => $this->scoreCountry($country));
    }
}
