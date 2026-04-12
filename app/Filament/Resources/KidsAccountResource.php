<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KidsAccountResource\Pages;
use App\Models\Country;
use App\Models\KidsAccount;
use App\Models\Property;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class KidsAccountResource extends Resource
{
    protected static ?string $model = KidsAccount::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static string | \UnitEnum | null $navigationGroup = 'Gezin';
    protected static ?string $navigationLabel = 'Kids Accounts';
    protected static ?string $modelLabel = 'Kids Account';
    protected static ?string $pluralModelLabel = 'Kids Accounts';
    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Profiel')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Naam')
                        ->required()
                        ->maxLength(50),
                    Forms\Components\TextInput::make('pin')
                        ->label('PIN code')
                        ->required()
                        ->maxLength(4)
                        ->minLength(4)
                        ->numeric()
                        ->password()
                        ->revealable(),
                    Forms\Components\TextInput::make('emoji')
                        ->label('Emoji')
                        ->default('👤')
                        ->maxLength(10),
                    Forms\Components\ColorPicker::make('color')
                        ->label('Kleur')
                        ->default('#3b82f6'),
                    Forms\Components\TextInput::make('age')
                        ->label('Leeftijd')
                        ->numeric(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Actief')
                        ->default(true),
                ])->columns(3),

            Section::make('Modules')
                ->description('Welke onderdelen mag dit account gebruiken?')
                ->schema([
                    Forms\Components\Toggle::make('module_photo_swiper')
                        ->label('📸 Foto Swiper')
                        ->helperText('Eén foto per keer beoordelen, zonder locatie-info')
                        ->default(true),
                    Forms\Components\Toggle::make('module_property_swiper')
                        ->label('🏠 Woning Swiper')
                        ->helperText('Meerdere foto\'s per woning + specs (prijs, m², kamers)')
                        ->default(false),
                    Forms\Components\Toggle::make('module_property_overview')
                        ->label('📋 Woningen Overzicht')
                        ->helperText('Volledig overzicht met alle details, locatie en filters')
                        ->default(false),
                ])->columns(1),

            Section::make('Filters')
                ->description('Beperk welke woningen dit account te zien krijgt. Laat leeg voor alles.')
                ->schema([
                    Forms\Components\Select::make('allowed_country_ids')
                        ->label('Landen')
                        ->multiple()
                        ->options(fn () => Country::orderBy('name')->pluck('name', 'id')->mapWithKeys(fn ($name, $id) => [$id => Country::find($id)->flag_emoji . ' ' . $name])->toArray())
                        ->helperText('Laat leeg = alle landen')
                        ->searchable(),
                    Forms\Components\Select::make('allowed_regions')
                        ->label('Provincies')
                        ->multiple()
                        ->options(fn () => Property::whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region', 'region')->toArray())
                        ->helperText('Laat leeg = alle provincies')
                        ->searchable(),
                    Forms\Components\TextInput::make('filter_price_min')
                        ->label('Min prijs €')
                        ->numeric(),
                    Forms\Components\TextInput::make('filter_price_max')
                        ->label('Max prijs €')
                        ->numeric(),
                    Forms\Components\TextInput::make('filter_bedrooms_min')
                        ->label('Min kamers')
                        ->numeric(),
                ])->columns(2),

            Section::make('Statistieken')
                ->schema([
                    Forms\Components\Placeholder::make('stats')
                        ->label('')
                        ->content(function (?KidsAccount $record) {
                            if (!$record) return 'Sla eerst op.';
                            $swipes = $record->photoSwipes()->count();
                            $properties = $record->filteredProperties()->count();
                            return new \Illuminate\Support\HtmlString("
                                <div class='flex gap-6'>
                                    <div><span class='text-2xl font-bold'>{$swipes}</span> <span class='text-sm text-gray-500'>foto's geswiped</span></div>
                                    <div><span class='text-2xl font-bold'>{$properties}</span> <span class='text-sm text-gray-500'>woningen beschikbaar</span></div>
                                </div>
                            ");
                        }),
                ])->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('emoji')
                    ->label('')
                    ->width('40px'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('age')
                    ->label('Leeftijd')
                    ->formatStateUsing(fn ($state) => $state ? $state . 'j' : '-'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean(),
                Tables\Columns\IconColumn::make('module_photo_swiper')
                    ->label('📸')
                    ->boolean(),
                Tables\Columns\IconColumn::make('module_property_swiper')
                    ->label('🏠')
                    ->boolean(),
                Tables\Columns\IconColumn::make('module_property_overview')
                    ->label('📋')
                    ->boolean(),
                Tables\Columns\TextColumn::make('swipe_count')
                    ->label('Swipes')
                    ->getStateUsing(fn (KidsAccount $record) => $record->photoSwipes()->count()),
            ])
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
            'index' => Pages\ListKidsAccounts::route('/'),
            'create' => Pages\CreateKidsAccount::route('/create'),
            'edit' => Pages\EditKidsAccount::route('/{record}/edit'),
        ];
    }
}
