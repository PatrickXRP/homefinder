<?php

namespace App\Filament\Resources\WishResource\Pages;

use App\Filament\Resources\WishResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWishes extends ListRecords
{
    protected static string $resource = WishResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
