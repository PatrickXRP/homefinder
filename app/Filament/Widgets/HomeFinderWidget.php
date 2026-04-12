<?php

namespace App\Filament\Widgets;

use App\Models\BudgetScenario;
use App\Models\Country;
use App\Models\Property;
use Filament\Widgets\Widget;

class HomeFinderWidget extends Widget
{
    protected static ?int $sort = -1;
    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.home-finder-widget';

    protected function getViewData(): array
    {
        $topCountries = Country::orderByDesc('match_score')->take(3)->get();
        $totalProperties = Property::count();
        $budget = BudgetScenario::where('is_active', true)->first();

        $statusCounts = Property::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'gezien_online' => '👀 Online',
            'bezichtigen' => '📅 Bezichtigen',
            'bezichtigd' => '✅ Bezichtigd',
            'interesse' => '💛 Interesse',
            'bod_gedaan' => '💰 Bod',
            'afgewezen' => '❌ Afgewezen',
            'gekocht' => '🎉 Gekocht',
        ];

        $countriesWithProperties = Country::withCount('properties')
            ->having('properties_count', '>', 0)
            ->orderByDesc('properties_count')
            ->get();

        return compact('topCountries', 'totalProperties', 'budget', 'statusCounts', 'statusLabels', 'countriesWithProperties');
    }
}
