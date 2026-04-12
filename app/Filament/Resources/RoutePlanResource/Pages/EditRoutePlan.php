<?php

namespace App\Filament\Resources\RoutePlanResource\Pages;

use App\Filament\Resources\RoutePlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoutePlan extends EditRecord
{
    protected static string $resource = RoutePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
