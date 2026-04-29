<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TaxPasal17RateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)
                ->schema([
                    TextInput::make('min_amount')
                        ->label('Min Taxable Income')
                        ->numeric()
                        ->prefix('IDR')
                        ->required(),
                    TextInput::make('max_amount')
                        ->label('Max Taxable Income')
                        ->numeric()
                        ->prefix('IDR')
                        ->placeholder('Leave empty for no upper limit'),
                ]),
            Grid::make(2)
                ->schema([
                    TextInput::make('rate')
                        ->label('Tax Rate (%)')
                        ->numeric()
                        ->step(0.01)
                        ->required()
                        ->prefix('%'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ]),
        ]);
    }
}
