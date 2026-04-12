<?php

namespace App\Filament\Resources\CountryResource\Pages;

use App\Filament\Resources\CountryResource;
use App\Services\HomeFinder\CountryResearchService;
use App\Services\HomeFinder\WishMatchingService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCountry extends EditRecord
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_ai_report')
                ->label('Genereer AI rapport')
                ->icon('heroicon-o-cpu-chip')
                ->color('info')
                ->requiresConfirmation()
                ->modalDescription('Dit genereert een nieuw AI rapport via Claude. Dit kan 10-30 seconden duren.')
                ->action(function () {
                    try {
                        app(CountryResearchService::class)->generateReport($this->record);
                        $this->fillForm();
                        Notification::make()
                            ->title('AI rapport gegenereerd')
                            ->body("Rapport en scores voor {$this->record->name} zijn bijgewerkt.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fout bij genereren rapport')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('recalculate_score')
                ->label('Herbereken match score')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    app(WishMatchingService::class)->scoreCountry($this->record);
                    $this->fillForm();
                    Notification::make()
                        ->title('Match score herberekend')
                        ->body("Score: {$this->record->fresh()->match_score}%")
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
