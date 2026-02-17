<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Modules\MasterData\Enums\BpjsCategory;
use Modules\MasterData\Enums\BpjsType;
use Modules\MasterData\Enums\CalculationCapType;
use Modules\MasterData\Enums\CalculationFloorType;
use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Models\RemunerationComponent;

class BpjsConfigForm
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
                ->label('NAME')
                ->required()
                ->maxLength(255),
            Select::make('category')
                ->label('CATEGORY')
                ->options(BpjsCategory::class)
                ->live()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state === BpjsCategory::Health->value) {
                        $set('employer_rate', 0.04);
                        $set('employee_rate', 0.01);
                        $set('floor_type', 'umk');
                        $set('cap_type', 'nominal');
                        $set('cap_nominal', 12000000);
                        $set('type', BpjsType::Health->value);
                    } elseif ($state === BpjsCategory::JHT->value) {
                        $set('employer_rate', 0.037);
                        $set('employee_rate', 0.02);
                        $set('type', BpjsType::Employment->value);
                    } elseif ($state === BpjsCategory::JP->value) {
                        $set('employer_rate', 0.02);
                        $set('employee_rate', 0.01);
                        $set('cap_type', CalculationCapType::Nominal->value);
                        $set('cap_nominal', 10540000);
                        $set('type', BpjsType::Employment->value);
                    } elseif ($state === BpjsCategory::JKK->value) {
                        $set('risk_level', RiskLevel::VeryLow->value);
                        $set('type', BpjsType::Employment->value);
                    }
                })
                ->required(),
            Select::make('type')
                ->options(BpjsType::class)
                ->label('BPJS TYPE')
                ->helperText('Select the type of BPJS configuration.')
                ->default(BpjsType::Employment)
                ->required(),
            Select::make('calculation_basis')
                ->label('CALCULATION BASIS')
                ->multiple()
                ->options(RemunerationComponent::where('is_active', true)->pluck('name', 'id'))
                ->helperText('Select components used for calculation (e.g., Gaji Pokok + Tunjangan Tetap)')
                ->placeholder('Search components...')
                ->preload()
                ->searchable()
                ->required(),
            Grid::make(2)
                ->schema([
                    TextInput::make('employer_rate')
                        ->label('EMPLOYER RATE')
                        ->helperText('Example: 0.04 for 4%')
                        ->numeric()
                        ->step(0.0001)
                        ->required(),
                    TextInput::make('employee_rate')
                        ->label('EMPLOYEE RATE')
                        ->helperText('Example: 0.01 for 1%')
                        ->numeric()
                        ->step(0.0001)
                        ->required(),
                ]),
            Grid::make(2)
                ->schema([
                    Select::make('cap_type')
                        ->label('CAP TYPE')
                        ->options(CalculationCapType::class)
                        ->default(CalculationCapType::None),
                    TextInput::make('cap_nominal')
                        ->label('CAP NOMINAL')
                        ->numeric()
                        ->nullable(),
                ]),
            Grid::make(3)
                ->schema([
                    Select::make('floor_type')
                        ->label('FLOOR TYPE')
                        ->options(CalculationFloorType::class)
                        ->default(CalculationFloorType::None),
                    TextInput::make('floor_nominal')
                        ->label('FLOOR NOMINAL')
                        ->numeric()
                        ->nullable(),
                    Select::make('risk_level')
                        ->label('RISK LEVEL')
                        ->options(RiskLevel::class)
                        ->helperText('Specific risk level for JKK (Kecelakaan Kerja).')
                        ->nullable(),
                ]),
            Toggle::make('is_active')
                ->label('IS ACTIVE')
                ->default(true),
        ];
    }
}
