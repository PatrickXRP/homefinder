<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Models\PropertyEmail;
use App\Services\HomeFinder\EmailComposerService;
use App\Services\HomeFinder\PropertyAnalysisService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected \Filament\Support\Enums\Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ai_analyze')
                ->label('AI Analyse')
                ->icon('heroicon-o-cpu-chip')
                ->color('info')
                ->requiresConfirmation()
                ->modalDescription('Analyseer deze woning met Claude AI. Dit kan 10-20 seconden duren.')
                ->action(function () {
                    try {
                        app(PropertyAnalysisService::class)->analyzeProperty($this->record);
                        $this->fillForm();
                        Notification::make()
                            ->title('AI analyse voltooid')
                            ->body("Score: {$this->record->fresh()->ai_score}/100")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fout bij analyse')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('compose_email')
                ->label('Mail genereren')
                ->icon('heroicon-o-envelope')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('type')
                        ->label('Type email')
                        ->options([
                            'interesse' => 'Interesse tonen',
                            'bezichtiging_aanvragen' => 'Bezichtiging aanvragen',
                            'opvolging' => 'Opvolging',
                            'eerste_bod' => 'Eerste bod',
                            'tegenbod' => 'Tegenbod',
                            'vragen' => 'Vragen stellen',
                            'bod_intrekken' => 'Bod intrekken',
                            'bevestiging' => 'Bevestiging',
                        ])
                        ->required(),
                    Forms\Components\Select::make('tone')
                        ->label('Toon')
                        ->options([
                            'zacht' => 'Zacht',
                            'neutraal' => 'Neutraal',
                            'hard' => 'Hard / zakelijk',
                        ])
                        ->default('neutraal')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $result = app(EmailComposerService::class)
                            ->composeEmail($this->record, $data['type'], $data['tone']);

                        PropertyEmail::create([
                            'property_id' => $this->record->id,
                            'type' => $data['type'],
                            'subject' => $result['subject'],
                            'body' => $result['body'],
                            'language' => $result['language'],
                            'tone' => $data['tone'],
                            'status' => 'concept',
                        ]);

                        Notification::make()
                            ->title('Email gegenereerd')
                            ->body("'{$result['subject']}' opgeslagen als concept.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Fout bij email generatie')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
