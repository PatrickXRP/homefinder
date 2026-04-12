<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Helpers\CurrencyHelper;
use App\Models\Property;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home-modern';
    protected static string | \UnitEnum | null $navigationGroup = 'Woningen';
    protected static ?string $navigationLabel = 'Woningen';
    protected static ?string $modelLabel = 'Woning';
    protected static ?string $pluralModelLabel = 'Woningen';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('Woning')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Basis')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Naam')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Select::make('country_id')
                                ->label('Land')
                                ->relationship('country', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('source_id')
                                ->label('Bron')
                                ->relationship('source', 'name')
                                ->searchable()
                                ->preload(),
                            Forms\Components\TextInput::make('url')
                                ->label('Listing URL')
                                ->url()
                                ->columnSpanFull(),
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'gezien_online' => '👀 Gezien online',
                                    'bezichtigen' => '📅 Bezichtigen',
                                    'bezichtigd' => '✅ Bezichtigd',
                                    'interesse' => '💛 Interesse',
                                    'bod_gedaan' => '💰 Bod gedaan',
                                    'afgewezen' => '❌ Afgewezen',
                                    'gekocht' => '🎉 Gekocht',
                                ])
                                ->required()
                                ->default('gezien_online'),
                            Forms\Components\TextInput::make('external_id')
                                ->label('Extern ID'),
                            Forms\Components\DateTimePicker::make('added_at')
                                ->label('Toegevoegd op')
                                ->default(now()),
                        ])->columns(2),

                    Tab::make('Locatie')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Forms\Components\TextInput::make('address')
                                ->label('Adres')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('city')
                                ->label('Stad'),
                            Forms\Components\TextInput::make('region')
                                ->label('Regio'),
                            Forms\Components\TextInput::make('latitude')
                                ->label('Breedtegraad')
                                ->numeric(),
                            Forms\Components\TextInput::make('longitude')
                                ->label('Lengtegraad')
                                ->numeric(),
                        ])->columns(2),

                    Tab::make('Details')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\TextInput::make('asking_price')
                                ->label('Vraagprijs')
                                ->numeric(),
                            Forms\Components\Select::make('currency')
                                ->label('Valuta')
                                ->options(collect(CurrencyHelper::RATES)->keys()->mapWithKeys(fn ($k) => [$k => $k])->toArray())
                                ->default('EUR'),
                            Forms\Components\TextInput::make('asking_price_eur')
                                ->label('Vraagprijs in EUR')
                                ->numeric()
                                ->prefix('€'),
                            Forms\Components\TextInput::make('price_per_m2')
                                ->label('Prijs per m²')
                                ->numeric()
                                ->prefix('€'),
                            Forms\Components\TextInput::make('year_built')
                                ->label('Bouwjaar')
                                ->numeric(),
                            Forms\Components\TextInput::make('living_area_m2')
                                ->label('Woonoppervlak m²')
                                ->numeric(),
                            Forms\Components\TextInput::make('plot_area_m2')
                                ->label('Perceeloppervlak m²')
                                ->numeric(),
                            Forms\Components\TextInput::make('bedrooms')
                                ->label('Slaapkamers')
                                ->numeric(),
                            Forms\Components\TextInput::make('bathrooms')
                                ->label('Badkamers')
                                ->numeric(),
                            Forms\Components\TextInput::make('energy_class')
                                ->label('Energieklasse'),
                            Forms\Components\Select::make('condition')
                                ->label('Staat')
                                ->options([
                                    'turnkey' => 'Instapklaar',
                                    'goed' => 'Goed',
                                    'matig' => 'Matig',
                                    'opknapper' => 'Opknapper',
                                    'slooprijp' => 'Slooprijp',
                                ]),
                        ])->columns(2),

                    Tab::make('Kenmerken')
                        ->icon('heroicon-o-sparkles')
                        ->schema([
                            Forms\Components\Select::make('water_type')
                                ->label('Water')
                                ->options([
                                    'meer' => '🏞️ Meer',
                                    'zee' => '🌊 Zee',
                                    'rivier' => '🏞️ Rivier',
                                    'geen' => 'Geen',
                                ]),
                            Forms\Components\TextInput::make('water_name')
                                ->label('Naam water'),
                            Forms\Components\Toggle::make('has_sauna')
                                ->label('Sauna'),
                            Forms\Components\Toggle::make('has_jetty')
                                ->label('Steiger'),
                            Forms\Components\Toggle::make('has_guest_house')
                                ->label('Gastenverblijf'),
                            Forms\Components\Toggle::make('year_round_accessible')
                                ->label('Jaarrond bereikbaar')
                                ->default(true),
                            Forms\Components\Toggle::make('own_road')
                                ->label('Eigen weg'),
                        ])->columns(2),

                    Tab::make('Score & Analyse')
                        ->icon('heroicon-o-star')
                        ->schema([
                            Forms\Components\Select::make('my_score')
                                ->label('Mijn score')
                                ->options([
                                    1 => '⭐',
                                    2 => '⭐⭐',
                                    3 => '⭐⭐⭐',
                                    4 => '⭐⭐⭐⭐',
                                    5 => '⭐⭐⭐⭐⭐',
                                ]),
                            Forms\Components\Placeholder::make('ai_score_display')
                                ->label('AI Score')
                                ->content(fn (?Property $record) => $record?->ai_score ? "{$record->ai_score} / 100" : 'Nog niet geanalyseerd'),
                            Forms\Components\Placeholder::make('ai_analysis_display')
                                ->label('AI Analyse')
                                ->content(fn (?Property $record) => $record?->ai_analysis ?? 'Klik "AI Analyse" om te genereren.')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Tab::make('Prijsgeschiedenis')
                        ->icon('heroicon-o-arrow-trending-down')
                        ->schema([
                            Forms\Components\Repeater::make('priceHistory')
                                ->label('')
                                ->relationship()
                                ->schema([
                                    Forms\Components\DatePicker::make('recorded_date')
                                        ->label('Datum')
                                        ->required(),
                                    Forms\Components\TextInput::make('price')
                                        ->label('Prijs')
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\TextInput::make('price_eur')
                                        ->label('Prijs EUR')
                                        ->numeric()
                                        ->prefix('€'),
                                    Forms\Components\TextInput::make('note')
                                        ->label('Notitie'),
                                    Forms\Components\Select::make('source')
                                        ->label('Bron')
                                        ->options([
                                            'scrape' => 'Scrape',
                                            'handmatig' => 'Handmatig',
                                        ])
                                        ->default('handmatig'),
                                ])
                                ->columns(5)
                                ->defaultItems(0)
                                ->addActionLabel('Prijswijziging toevoegen')
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Bezichtiging')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Forms\Components\DatePicker::make('viewing_date')
                                ->label('Bezichtigingsdatum'),
                            Forms\Components\Textarea::make('notes')
                                ->label('Notities')
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('water_type')
                    ->label('')
                    ->width('30px')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'meer' => '🏞️',
                        'zee' => '🌊',
                        'rivier' => '🏞️',
                        default => '🏠',
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.flag_emoji')
                    ->label('Land')
                    ->formatStateUsing(fn ($state, Property $record) => ($state ?? '') . ' ' . ($record->country?->name ?? '')),
                Tables\Columns\TextColumn::make('city')
                    ->label('Stad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('asking_price')
                    ->label('Prijs')
                    ->formatStateUsing(fn ($state, Property $record) => $state ? CurrencyHelper::format((int) $state, $record->currency) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('asking_price_eur')
                    ->label('EUR')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_m2')
                    ->label('€/m²')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('my_score')
                    ->label('Score')
                    ->formatStateUsing(fn (?int $state) => $state ? str_repeat('⭐', $state) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'gezien_online' => 'gray',
                        'bezichtigen' => 'warning',
                        'bezichtigd' => 'info',
                        'interesse' => 'success',
                        'bod_gedaan' => 'primary',
                        'afgewezen' => 'danger',
                        'gekocht' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'gezien_online' => '👀 Online',
                        'bezichtigen' => '📅 Bezichtigen',
                        'bezichtigd' => '✅ Bezichtigd',
                        'interesse' => '💛 Interesse',
                        'bod_gedaan' => '💰 Bod',
                        'afgewezen' => '❌ Afgewezen',
                        'gekocht' => '🎉 Gekocht',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('added_at')
                    ->label('Toegevoegd')
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->defaultSort('added_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('country_id')
                    ->label('Land')
                    ->relationship('country', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'gezien_online' => 'Gezien online',
                        'bezichtigen' => 'Bezichtigen',
                        'bezichtigd' => 'Bezichtigd',
                        'interesse' => 'Interesse',
                        'bod_gedaan' => 'Bod gedaan',
                        'afgewezen' => 'Afgewezen',
                        'gekocht' => 'Gekocht',
                    ]),
                Tables\Filters\SelectFilter::make('water_type')
                    ->options([
                        'meer' => 'Meer',
                        'zee' => 'Zee',
                        'rivier' => 'Rivier',
                        'geen' => 'Geen',
                    ]),
                Tables\Filters\TernaryFilter::make('has_sauna')
                    ->label('Sauna'),
                Tables\Filters\TernaryFilter::make('has_jetty')
                    ->label('Steiger'),
            ])
            ->actions([
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
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
