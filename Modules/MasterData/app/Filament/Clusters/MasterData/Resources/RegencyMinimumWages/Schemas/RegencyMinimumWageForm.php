<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ProjectArea;

class RegencyMinimumWageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_area_id')
                    ->label('Project Area')
                    ->options(ProjectArea::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('year')
                    ->numeric()
                    ->default(date('Y'))
                    ->required(),
                TextInput::make('amount')
                    ->label('Monthly Wage (UMK)')
                    ->numeric()
                    ->required(),
            ]);
    }
}
