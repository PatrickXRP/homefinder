<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertySourceResource\Pages;
use App\Jobs\ScrapePropertiesJob;
use App\Models\PropertySource;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PropertySourceResource extends Resource
{
    protected static ?string $model = PropertySource::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string | \UnitEnum | null $navigationGroup = 'Woningen';
    protected static ?string $navigationLabel = 'Bronnen';
    protected static ?string $modelLabel = 'Bron';
    protected static ?string $pluralModelLabel = 'Bronnen';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('country_id')
                ->label('Land')
                ->relationship('country', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')
                ->label('Naam')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('base_url')
                ->label('Website URL')
                ->url(),
            Forms\Components\Textarea::make('search_url_template')
                ->label('Zoek URL template')
                ->columnSpanFull(),
            Forms\Components\Select::make('scraper_class')
                ->label('Scraper')
                ->options([
                    'HemnetScraper' => 'Hemnet (Zweden)',
                ]),
            Forms\Components\Toggle::make('is_active')
                ->label('Actief')
                ->default(true),
            Forms\Components\TextInput::make('scrape_interval_hours')
                ->label('Scrape interval (uren)')
                ->numeric()
                ->default(12),
            Forms\Components\Textarea::make('notes')
                ->label('Notities')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Land')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_scraped_at')
                    ->label('Laatst gescraped')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('properties_count')
                    ->counts('properties')
                    ->label('Woningen'),
            ])
            ->actions([
                Tables\Actions\Action::make('scrape_now')
                    ->label('Nu scrapen')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PropertySource $record) {
                        ScrapePropertiesJob::dispatch($record->id);
                        Notification::make()
                            ->title("Scrape gestart voor {$record->name}")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPropertySources::route('/'),
            'create' => Pages\CreatePropertySource::route('/create'),
            'edit' => Pages\EditPropertySource::route('/{record}/edit'),
        ];
    }
}
