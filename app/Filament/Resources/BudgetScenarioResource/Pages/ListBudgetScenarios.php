<?php

namespace App\Filament\Resources\BudgetScenarioResource\Pages;

use App\Filament\Resources\BudgetScenarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBudgetScenarios extends ListRecords
{
    protected static string $resource = BudgetScenarioResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
