<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetScenarioResource\Pages;
use App\Models\BudgetScenario;
use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class BudgetScenarioResource extends Resource
{
    protected static ?string $model = BudgetScenario::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static string | \UnitEnum | null $navigationGroup = 'Onderzoek';
    protected static ?string $navigationLabel = 'Budget';
    protected static ?string $modelLabel = 'Budget Scenario';
    protected static ?string $pluralModelLabel = 'Budget Scenarios';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label('Naam')
                ->required()
                ->maxLength(255),
            Forms\Components\Toggle::make('is_active')
                ->label('Actief scenario')
                ->helperText('Er kan maar één scenario tegelijk actief zijn'),
            Forms\Components\TextInput::make('total_budget_eur')
                ->label('Totaal budget')
                ->numeric()
                ->prefix('€')
                ->required(),
            Forms\Components\TextInput::make('purchase_budget_eur')
                ->label('Aankoopbudget')
                ->numeric()
                ->prefix('€')
                ->required(),
            Forms\Components\TextInput::make('renovation_budget_eur')
                ->label('Verbouwingsbudget')
                ->numeric()
                ->prefix('€')
                ->default(0),
            Forms\Components\TextInput::make('annual_costs_max_eur')
                ->label('Max jaarlijkse kosten')
                ->numeric()
                ->prefix('€'),
            Forms\Components\Textarea::make('notes')
                ->label('Notities')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actief')
                    ->boolean(),
                Tables\Columns\TextColumn::make('total_budget_eur')
                    ->label('Totaal')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_budget_eur')
                    ->label('Aankoop')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('renovation_budget_eur')
                    ->label('Verbouwing')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('annual_costs_max_eur')
                    ->label('Max jaarlast')
                    ->money('EUR'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('activate')
                    ->label('Maak actief')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BudgetScenario $record) => !$record->is_active)
                    ->requiresConfirmation()
                    ->action(function (BudgetScenario $record) {
                        BudgetScenario::where('is_active', true)->update(['is_active' => false]);
                        $record->update(['is_active' => true]);
                    }),
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
            'index' => Pages\ListBudgetScenarios::route('/'),
            'create' => Pages\CreateBudgetScenario::route('/create'),
            'edit' => Pages\EditBudgetScenario::route('/{record}/edit'),
        ];
    }
}
