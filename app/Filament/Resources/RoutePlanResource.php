<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoutePlanResource\Pages;
use App\Models\RoutePlan;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RoutePlanResource extends Resource
{
    protected static ?string $model = RoutePlan::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';
    protected static string | \UnitEnum | null $navigationGroup = 'Woningen';
    protected static ?string $navigationLabel = 'Route Plannen';
    protected static ?string $modelLabel = 'Route Plan';
    protected static ?string $pluralModelLabel = 'Route Plannen';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Route details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Naam')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Bijv. "Zweden rondreis juni 2026"'),
                    Forms\Components\TextInput::make('start_point')
                        ->label('Vertrekpunt')
                        ->placeholder('Bijv. "Stockholm Arlanda Airport"'),
                    Forms\Components\DatePicker::make('travel_date')
                        ->label('Reisdatum'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notities')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Woningen in route')
                ->schema([
                    Forms\Components\Repeater::make('propertyEntries')
                        ->label('')
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->schema([
                            Forms\Components\Select::make('property_id')
                                ->label('Woning')
                                ->relationship('property', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(2),
                            Forms\Components\TimePicker::make('visit_time')
                                ->label('Bezoektijd')
                                ->seconds(false),
                            Forms\Components\TextInput::make('duration_minutes')
                                ->label('Duur (min)')
                                ->numeric()
                                ->default(60),
                            Forms\Components\TextInput::make('notes')
                                ->label('Notitie')
                                ->columnSpan(2),
                        ])
                        ->columns(6)
                        ->defaultItems(0)
                        ->addActionLabel('Woning toevoegen')
                        ->itemLabel(function (array $state): ?string {
                            if (!empty($state['property_id'])) {
                                $property = \App\Models\Property::find($state['property_id']);
                                if ($property) {
                                    $time = $state['visit_time'] ?? '';
                                    return ($time ? "{$time} — " : '') . $property->name . ' (' . ($property->city ?? $property->country?->name ?? '') . ')';
                                }
                            }
                            return null;
                        })
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            Section::make('Google Maps Route')
                ->schema([
                    Forms\Components\Placeholder::make('google_maps_link')
                        ->label('')
                        ->content(function (?RoutePlan $record) {
                            if (!$record) {
                                return 'Sla het route plan eerst op om een Google Maps link te genereren.';
                            }

                            $entries = $record->propertyEntries()
                                ->with('property')
                                ->orderBy('sort_order')
                                ->get();

                            if ($entries->isEmpty()) {
                                return 'Voeg woningen toe om een route te genereren.';
                            }

                            $addresses = collect();

                            if ($record->start_point) {
                                $addresses->push($record->start_point);
                            }

                            foreach ($entries as $entry) {
                                $prop = $entry->property;
                                if ($prop) {
                                    $addr = $prop->address ?? $prop->city ?? $prop->name;
                                    if ($prop->country) {
                                        $addr .= ', ' . $prop->country->name;
                                    }
                                    $addresses->push($addr);
                                }
                            }

                            if ($addresses->count() < 2) {
                                return 'Voeg minimaal 2 locaties toe.';
                            }

                            $origin = urlencode($addresses->first());
                            $destination = urlencode($addresses->last());
                            $waypoints = $addresses->slice(1, -1)->map(fn ($a) => urlencode($a))->implode('|');

                            $url = "https://www.google.com/maps/dir/?api=1&origin={$origin}&destination={$destination}&travelmode=driving";
                            if ($waypoints) {
                                $url .= "&waypoints={$waypoints}";
                            }

                            return new \Illuminate\Support\HtmlString("
                                <div class='flex gap-3'>
                                    <a href='{$url}' target='_blank' class='inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium'>
                                        🗺️ Open in Google Maps
                                    </a>
                                    <button onclick=\"navigator.clipboard.writeText('{$url}'); alert('Link gekopieerd!')\" class='inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm font-medium'>
                                        📋 Kopieer link
                                    </button>
                                </div>
                                <div class='mt-2 text-xs text-gray-500'>Route: {$addresses->implode(' → ')}</div>
                            ");
                        }),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_point')
                    ->label('Vertrekpunt')
                    ->limit(30),
                Tables\Columns\TextColumn::make('travel_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('property_entries_count')
                    ->counts('propertyEntries')
                    ->label('Stops')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('travel_date', 'desc')
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoutePlans::route('/'),
            'create' => Pages\CreateRoutePlan::route('/create'),
            'edit' => Pages\EditRoutePlan::route('/{record}/edit'),
        ];
    }
}
