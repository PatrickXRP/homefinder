<?php

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use App\Services\HomeFinder\WishMatchingService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('score_all')
                ->label('Herbereken alle scores')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    app(WishMatchingService::class)->scoreAllCountries();
                    Notification::make()
                        ->title('Alle match scores herberekend')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
