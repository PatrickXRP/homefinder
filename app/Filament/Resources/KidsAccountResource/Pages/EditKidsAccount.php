<?php
namespace App\Filament\Resources\KidsAccountResource\Pages;
use App\Filament\Resources\KidsAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditKidsAccount extends EditRecord
{
    protected static string $resource = KidsAccountResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
