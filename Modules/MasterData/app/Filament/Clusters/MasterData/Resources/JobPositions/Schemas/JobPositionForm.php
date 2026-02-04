<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\RemunerationComponent;

class JobPositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('basic_salary')
                    ->label('Basic Salary (Gapok)')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Select::make('risk_level')
                    ->options([
                        'very_low' => 'Very Low (0.24%)',
                        'low' => 'Low (0.54%)',
                        'medium' => 'Medium (0.89%)',
                        'high' => 'High (1.27%)',
                        'very_high' => 'Very High (1.74%)',
                    ])
                    ->default('very_low')
                    ->required(),
                Toggle::make('is_labor_intensive')
                    ->label('Labor Intensive (Padat Karya)')
                    ->helperText('Enable for 50% JKK reduction if applicable.')
                    ->default(false),
                Repeater::make('remunerationComponents')
                    ->relationship('remunerationComponents')
                    ->schema([
                        Select::make('remuneration_component_id')
                            ->label('Component')
                            ->options(RemunerationComponent::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('amount')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
