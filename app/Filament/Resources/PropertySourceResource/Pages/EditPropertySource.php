<?php

namespace App\Filament\Resources\PropertySourceResource\Pages;

use App\Filament\Resources\PropertySourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPropertySource extends EditRecord
{
    protected static string $resource = PropertySourceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
