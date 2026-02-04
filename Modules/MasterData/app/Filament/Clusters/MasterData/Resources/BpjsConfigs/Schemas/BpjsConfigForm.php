<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class BpjsConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->options([
                        'Health' => 'BPJS Kesehatan',
                        'JKK' => 'BPJS JKK (Kecelakaan Kerja)',
                        'JKM' => 'BPJS JKM (Kematian)',
                        'JHT' => 'BPJS JHT (Hari Tua)',
                        'JP' => 'BPJS JP (Pensiun)',
                    ])
                    ->required(),
                TextInput::make('type')
                    ->default('employment')
                    ->required(),
                Grid::make(2)
                    ->schema([
                        TextInput::make('employer_rate')
                            ->label('Employer Rate (Decimal, e.g. 0.04)')
                            ->numeric()
                            ->step(0.0001)
                            ->required(),
                        TextInput::make('employee_rate')
                            ->label('Employee Rate (Decimal, e.g. 0.01)')
                            ->numeric()
                            ->step(0.0001)
                            ->required(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('cap_type')
                            ->options([
                                'none' => 'None',
                                'nominal' => 'Nominal',
                                'percentage' => 'Percentage',
                            ])
                            ->default('none'),
                        TextInput::make('cap_nominal')
                            ->numeric()
                            ->nullable(),
                    ]),
                Grid::make(3)
                    ->schema([
                        Select::make('floor_type')
                            ->options([
                                'none' => 'None',
                                'nominal' => 'Nominal',
                                'umk' => 'UMK/UMP',
                            ])
                            ->default('none'),
                        TextInput::make('floor_nominal')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('risk_level')
                            ->label('Risk Level (for JKK)')
                            ->helperText('very_low, low, medium, high, very_high')
                            ->nullable(),
                    ]),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
