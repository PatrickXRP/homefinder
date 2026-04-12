<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WishResource\Pages;
use App\Models\Wish;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WishResource extends Resource
{
    protected static ?string $model = Wish::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-heart';
    protected static string | \UnitEnum | null $navigationGroup = 'Onderzoek';
    protected static ?string $navigationLabel = 'Wensen';
    protected static ?string $modelLabel = 'Wens';
    protected static ?string $pluralModelLabel = 'Wensen';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('category')
                ->label('Categorie')
                ->options([
                    'natuur' => '🌿 Natuur',
                    'woning' => '🏠 Woning',
                    'bereikbaarheid' => '✈️ Bereikbaarheid',
                    'remote_werk' => '💻 Remote werk',
                    'financieel' => '💰 Financieel',
                    'kinderen' => '👶 Kinderen',
                ])
                ->required(),
            Forms\Components\TextInput::make('label')
                ->label('Wens')
                ->required()
                ->maxLength(255),
            Forms\Components\Radio::make('weight')
                ->label('Gewicht')
                ->options([
                    'must_have' => 'Must have',
                    'nice_to_have' => 'Nice to have',
                    'bonus' => 'Bonus',
                ])
                ->descriptions([
                    'must_have' => 'Zonder dit geen deal',
                    'nice_to_have' => 'Sterk gewenst',
                    'bonus' => 'Mooi meegenomen',
                ])
                ->required()
                ->default('nice_to_have'),
            Forms\Components\TextInput::make('value')
                ->label('Waarde / drempelwaarde')
                ->helperText('Bijv. "25 Mbps", "3", "€60.000"'),
            Forms\Components\Textarea::make('notes')
                ->label('Notities')
                ->rows(2)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')
                ->label('Volgorde')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->label('Categorie')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'natuur' => '🌿 Natuur',
                        'woning' => '🏠 Woning',
                        'bereikbaarheid' => '✈️ Bereikbaarheid',
                        'remote_werk' => '💻 Remote werk',
                        'financieel' => '💰 Financieel',
                        'kinderen' => '👶 Kinderen',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Wens')
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label('Gewicht')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'must_have' => 'danger',
                        'nice_to_have' => 'warning',
                        'bonus' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'must_have' => 'Must have',
                        'nice_to_have' => 'Nice to have',
                        'bonus' => 'Bonus',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Waarde'),
            ])
            ->defaultSort('category')
            ->defaultGroup('category')
            ->reorderable('sort_order')
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
            'index' => Pages\ListWishes::route('/'),
            'create' => Pages\CreateWish::route('/create'),
            'edit' => Pages\EditWish::route('/{record}/edit'),
        ];
    }
}
