<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListProperties extends ListRecords
{
    protected static string $resource = PropertyResource::class;

    protected \Filament\Support\Enums\Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
