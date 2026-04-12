<?php

namespace App\Filament\Pages;

use App\Models\BudgetScenario;
use App\Models\Country;
use App\Models\Wish;
use Filament\Pages\Page;

class WensenPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string | \UnitEnum | null $navigationGroup = 'Onderzoek';
    protected static ?string $navigationLabel = 'Wensen Overzicht';
    protected static ?int $navigationSort = 0;
    protected static ?string $title = 'Wensen & Profiel';
    protected static ?string $slug = 'wensen-overzicht';

    protected string $view = 'filament.pages.wensen-page';

    protected function getViewData(): array
    {
        $wishes = Wish::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');
        $budget = BudgetScenario::where('is_active', true)->first();
        $countries = Country::orderByDesc('match_score')->get();

        $categories = [
            'natuur' => ['icon' => '🌿', 'label' => 'Natuur'],
            'woning' => ['icon' => '🏠', 'label' => 'Woning'],
            'bereikbaarheid' => ['icon' => '✈️', 'label' => 'Bereikbaarheid'],
            'remote_werk' => ['icon' => '💻', 'label' => 'Remote werk'],
            'financieel' => ['icon' => '💰', 'label' => 'Financieel'],
            'kinderen' => ['icon' => '👶', 'label' => 'Kinderen'],
        ];

        $totalMustHaves = Wish::where('weight', 'must_have')->count();
        $filledMustHaves = Wish::where('weight', 'must_have')->whereNotNull('value')->where('value', '!=', '')->count();
        $completeness = $totalMustHaves > 0 ? round(($filledMustHaves / $totalMustHaves) * 100) : 0;

        $family = config('homefinder.family');

        return compact('wishes', 'budget', 'countries', 'categories', 'completeness', 'family');
    }
}
