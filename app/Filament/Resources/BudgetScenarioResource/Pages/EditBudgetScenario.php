<?php

namespace App\Filament\Resources\BudgetScenarioResource\Pages;

use App\Filament\Resources\BudgetScenarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBudgetScenario extends EditRecord
{
    protected static string $resource = BudgetScenarioResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
