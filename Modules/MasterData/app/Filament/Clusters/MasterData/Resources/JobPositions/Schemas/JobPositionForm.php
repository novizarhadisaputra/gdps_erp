<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Schemas\RemunerationComponentForm;

class JobPositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('basic_salary')
                ->label('Basic Salary (Gapok)')
                ->numeric()
                ->default(0)
                ->required(),
            Select::make('risk_level')
                ->options(RiskLevel::class)
                ->default(RiskLevel::VeryLow)
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
                        ->relationship('remunerationComponents', 'name')
                        ->createOptionForm(RemunerationComponentForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
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
        ];
    }
}
