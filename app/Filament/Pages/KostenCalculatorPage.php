<?php

namespace App\Filament\Pages;

use App\Models\Country;
use Filament\Pages\Page;

class KostenCalculatorPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calculator';
    protected static string | \UnitEnum | null $navigationGroup = 'Gezin';
    protected static ?string $navigationLabel = 'Kosten Calculator';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Kosten Calculator';
    protected static ?string $slug = 'kosten-calculator';

    protected string $view = 'filament.pages.kosten-calculator-page';

    public int $purchasePrice = 45000;
    public int $renovationBudget = 10000;
    public ?int $selectedCountryId = null;

    public function mount(): void
    {
        $this->selectedCountryId = Country::first()?->id;
    }

    public function updatedPurchasePrice(): void {}
    public function updatedRenovationBudget(): void {}
    public function updatedSelectedCountryId(): void {}

    protected function getViewData(): array
    {
        $countries = Country::orderBy('name')->get();
        $selectedCountry = $this->selectedCountryId ? Country::find($this->selectedCountryId) : null;

        $calculation = null;
        if ($selectedCountry) {
            $purchaseCostsPct = (float) ($selectedCountry->purchase_costs_pct ?? 5);
            $purchaseCosts = (int) round($this->purchasePrice * ($purchaseCostsPct / 100));
            $inspection = 800;
            $totalPurchase = $this->purchasePrice + $purchaseCosts + $inspection + $this->renovationBudget;

            $annualTaxPct = (float) ($selectedCountry->annual_property_tax_pct ?? 1);
            $annualTax = (int) round($this->purchasePrice * ($annualTaxPct / 100));
            $annualInsurance = 300;
            $annualMaintenance = 500;
            $annualTotal = $annualTax + $annualInsurance + $annualMaintenance;

            $calculation = [
                'purchase_price' => $this->purchasePrice,
                'purchase_costs_pct' => $purchaseCostsPct,
                'purchase_costs' => $purchaseCosts,
                'inspection' => $inspection,
                'renovation' => $this->renovationBudget,
                'total_purchase' => $totalPurchase,
                'annual_tax_pct' => $annualTaxPct,
                'annual_tax' => $annualTax,
                'annual_insurance' => $annualInsurance,
                'annual_maintenance' => $annualMaintenance,
                'annual_total' => $annualTotal,
                'notes' => $selectedCountry->annual_costs_notes,
            ];
        }

        // Vergelijkingstabel: zelfde bedrag in alle landen
        $comparison = $countries->map(function ($country) {
            $pct = (float) ($country->purchase_costs_pct ?? 5);
            $costs = (int) round($this->purchasePrice * ($pct / 100));
            $total = $this->purchasePrice + $costs + 800 + $this->renovationBudget;
            $taxPct = (float) ($country->annual_property_tax_pct ?? 1);
            $annual = (int) round($this->purchasePrice * ($taxPct / 100)) + 800;
            return [
                'country' => $country,
                'purchase_costs' => $costs,
                'total' => $total,
                'annual' => $annual,
            ];
        })->sortBy('total');

        return compact('countries', 'selectedCountry', 'calculation', 'comparison');
    }
}
