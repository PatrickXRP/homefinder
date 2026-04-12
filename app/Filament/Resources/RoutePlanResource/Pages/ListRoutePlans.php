<?php

namespace App\Filament\Resources\RoutePlanResource\Pages;

use App\Filament\Resources\RoutePlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoutePlans extends ListRecords
{
    protected static string $resource = RoutePlanResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
