<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-globe-europe-africa';
    protected static string | \UnitEnum | null $navigationGroup = 'Onderzoek';
    protected static ?string $navigationLabel = 'Landen';
    protected static ?string $modelLabel = 'Land';
    protected static ?string $pluralModelLabel = 'Landen';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('Land')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Algemeen')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Naam')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('name_local')
                                ->label('Lokale naam')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('code')
                                ->label('ISO code')
                                ->required()
                                ->maxLength(2),
                            Forms\Components\TextInput::make('flag_emoji')
                                ->label('Vlag emoji'),
                            Forms\Components\Select::make('continent')
                                ->options([
                                    'Europa' => 'Europa',
                                    'Azië' => 'Azië',
                                    'Afrika' => 'Afrika',
                                ]),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'onderzoek' => '🔍 Onderzoek',
                                    'actief' => '✅ Actief',
                                    'afgewezen' => '❌ Afgewezen',
                                    'favoriet' => '⭐ Favoriet',
                                ])
                                ->required()
                                ->default('onderzoek'),
                            Forms\Components\Toggle::make('eu_member')
                                ->label('EU-lid'),
                            Forms\Components\Textarea::make('notes')
                                ->label('Notities')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Tab::make('Vastgoed')
                        ->icon('heroicon-o-home')
                        ->schema([
                            Forms\Components\Toggle::make('foreigners_can_buy')
                                ->label('Buitenlanders mogen kopen')
                                ->default(true),
                            Forms\Components\Textarea::make('foreigners_notes')
                                ->label('Toelichting buitenlands eigendom')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('avg_price_per_m2_eur')
                                ->label('Gem. prijs per m²')
                                ->numeric()
                                ->prefix('€'),
                            Forms\Components\TextInput::make('purchase_costs_pct')
                                ->label('Aankoopkosten %')
                                ->numeric()
                                ->suffix('%'),
                            Forms\Components\TextInput::make('annual_property_tax_pct')
                                ->label('Jaarlijkse belasting %')
                                ->numeric()
                                ->suffix('%'),
                            Forms\Components\Textarea::make('annual_costs_notes')
                                ->label('Toelichting jaarlijkse kosten')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('realistic_budget_min_eur')
                                ->label('Realistisch min. budget')
                                ->numeric()
                                ->prefix('€'),
                            Forms\Components\Textarea::make('realistic_budget_notes')
                                ->label('Toelichting budget')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Tab::make('Leven')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\Select::make('internet_quality')
                                ->label('Internet kwaliteit')
                                ->options([
                                    'uitstekend' => '🟢 Uitstekend',
                                    'goed' => '🟡 Goed',
                                    'matig' => '🟠 Matig',
                                    'slecht' => '🔴 Slecht',
                                ]),
                            Forms\Components\Select::make('healthcare_quality')
                                ->label('Gezondheidszorg')
                                ->options([
                                    'uitstekend' => '🟢 Uitstekend',
                                    'goed' => '🟡 Goed',
                                    'matig' => '🟠 Matig',
                                    'slecht' => '🔴 Slecht',
                                ]),
                            Forms\Components\Select::make('language_barrier')
                                ->label('Taalbarrière')
                                ->options([
                                    'laag' => '🟢 Laag',
                                    'middel' => '🟡 Middel',
                                    'hoog' => '🔴 Hoog',
                                ]),
                            Forms\Components\Select::make('expat_community')
                                ->label('Expat community')
                                ->options([
                                    'groot' => 'Groot',
                                    'middel' => 'Middel',
                                    'klein' => 'Klein',
                                    'geen' => 'Geen',
                                ]),
                            Forms\Components\Toggle::make('international_schools')
                                ->label('Internationale scholen'),
                            Forms\Components\TextInput::make('flight_hours_from_dubai')
                                ->label('Vlieguren vanuit Dubai')
                                ->numeric()
                                ->step(0.5),
                            Forms\Components\TextInput::make('nearest_airport')
                                ->label('Dichtstbijzijnde vliegveld'),
                            Forms\Components\Textarea::make('flight_connections_notes')
                                ->label('Vliegverbindingen notities')
                                ->columnSpanFull(),
                        ])->columns(2),

                    Tab::make('Match Score')
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            Forms\Components\Placeholder::make('match_score_display')
                                ->label('')
                                ->content(function (?Country $record) {
                                    if (!$record) return 'Sla het land eerst op.';
                                    $score = $record->match_score;
                                    $color = $score >= 70 ? '#22c55e' : ($score >= 40 ? '#f59e0b' : '#ef4444');
                                    $summary = e($record->match_summary ?? 'Nog niet berekend');
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='text-center mb-4'>
                                            <div class='text-6xl font-bold' style='color: {$color}'>{$score}</div>
                                            <div class='text-sm text-gray-500 mt-1'>/ 100</div>
                                            <div class='w-full bg-gray-200 rounded-full h-3 mt-3 dark:bg-gray-700'>
                                                <div class='h-3 rounded-full' style='width: {$score}%; background-color: {$color}'></div>
                                            </div>
                                            <div class='mt-3 text-sm'>{$summary}</div>
                                        </div>
                                    ");
                                }),
                            Forms\Components\Placeholder::make('wish_scores_table')
                                ->label('Scores per wens')
                                ->content(function (?Country $record) {
                                    if (!$record) return '';
                                    $scores = $record->wishScores()->with('wish')->get();
                                    if ($scores->isEmpty()) return 'Nog geen scores berekend. Klik "Herbereken match score".';
                                    $rows = $scores->map(function ($ws) {
                                        $cat = e($ws->wish->category ?? '');
                                        $label = e($ws->wish->label ?? '');
                                        $score = $ws->score;
                                        $expl = e($ws->explanation ?? '-');
                                        $color = $score >= 7 ? '#22c55e' : ($score >= 4 ? '#f59e0b' : '#ef4444');
                                        return "<tr><td class='px-2 py-1'>{$cat}</td><td class='px-2 py-1'>{$label}</td><td class='px-2 py-1 text-center' style='color:{$color}'><strong>{$score}/10</strong></td><td class='px-2 py-1 text-sm'>{$expl}</td></tr>";
                                    })->join('');
                                    return new \Illuminate\Support\HtmlString("<table class='w-full text-sm'><thead><tr class='border-b'><th class='px-2 py-1 text-left'>Cat.</th><th class='px-2 py-1 text-left'>Wens</th><th class='px-2 py-1 text-center'>Score</th><th class='px-2 py-1 text-left'>Toelichting</th></tr></thead><tbody>{$rows}</tbody></table>");
                                }),
                        ]),

                    Tab::make('AI Rapport')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Forms\Components\Placeholder::make('ai_report_display')
                                ->label('')
                                ->content(function (?Country $record) {
                                    if (!$record || !$record->ai_report) {
                                        return 'Nog geen AI rapport gegenereerd. Klik "Genereer AI rapport" in de header.';
                                    }
                                    $date = $record->ai_report_generated_at?->format('d-m-Y H:i') ?? 'Onbekend';
                                    $report = nl2br(e($record->ai_report));
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='text-xs text-gray-500 mb-3'>Gegenereerd op: {$date}</div>
                                        <div class='prose prose-sm max-w-none dark:prose-invert'>{$report}</div>
                                    ");
                                }),
                            Forms\Components\Placeholder::make('pros_cons')
                                ->label('')
                                ->content(function (?Country $record) {
                                    if (!$record) return '';
                                    $pros = $record->pros ?? [];
                                    $cons = $record->cons ?? [];
                                    if (empty($pros) && empty($cons)) return '';
                                    $prosHtml = collect($pros)->map(fn ($p) => "<li class='text-green-700'>✅ " . e($p) . "</li>")->join('');
                                    $consHtml = collect($cons)->map(fn ($c) => "<li class='text-red-700'>❌ " . e($c) . "</li>")->join('');
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='grid grid-cols-2 gap-4 mt-4'>
                                            <div><h4 class='font-bold text-green-700 mb-2'>Voordelen</h4><ul class='space-y-1'>{$prosHtml}</ul></div>
                                            <div><h4 class='font-bold text-red-700 mb-2'>Nadelen</h4><ul class='space-y-1'>{$consHtml}</ul></div>
                                        </div>
                                    ");
                                }),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('flag_emoji')
                    ->label('')
                    ->width('40px'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Land')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('continent')
                    ->label('Continent')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'onderzoek' => 'warning',
                        'actief' => 'success',
                        'afgewezen' => 'danger',
                        'favoriet' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'onderzoek' => '🔍 Onderzoek',
                        'actief' => '✅ Actief',
                        'afgewezen' => '❌ Afgewezen',
                        'favoriet' => '⭐ Favoriet',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('match_score')
                    ->label('Match')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                Tables\Columns\IconColumn::make('eu_member')
                    ->label('EU')
                    ->boolean(),
                Tables\Columns\TextColumn::make('properties_count')
                    ->counts('properties')
                    ->label('Woningen')
                    ->sortable(),
            ])
            ->defaultSort('match_score', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'onderzoek' => 'Onderzoek',
                        'actief' => 'Actief',
                        'afgewezen' => 'Afgewezen',
                        'favoriet' => 'Favoriet',
                    ]),
                Tables\Filters\TernaryFilter::make('eu_member')
                    ->label('EU-lid'),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
