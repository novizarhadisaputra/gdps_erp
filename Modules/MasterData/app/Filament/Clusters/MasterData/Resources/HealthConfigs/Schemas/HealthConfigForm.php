<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class HealthConfigForm
{
    public static function schema(): array
    {
        return [
            Section::make('General Information')
                ->description('Health Insurance (BPJS Kesehatan) configuration. Manages contribution calculation for PPU, PBPU/Independent, and PBI.')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Clear configuration name, e.g., BPJS Kes PPU Standard'),
                    Select::make('employee_type')
                        ->label('Membership Type')
                        ->options([
                            'ppu' => 'Wage Earner (PPU)',
                            'pbpu' => 'Non-Wage Earner (BPU/Independent)',
                            'pbi' => 'Contribution Assistance Recipient (PBI)',
                        ])
                        ->required()
                        ->live()
                        ->helperText('Select the membership category that determines how contributions are calculated.'),
                ])->columns(2),

            Section::make('Rate & Tier Configuration')
                ->description('Determine the deduction percentage for PPU and nominal range for PBPU/Independent.')
                ->schema([
                    TextInput::make('employer_rate')
                        ->label('Employer Rate (%)')
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText('Usually 4.0% for PPU'),
                    TextInput::make('employee_rate')
                        ->label('Employee Rate (%)')
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText('Usually 1.0% for PPU'),
                    Select::make('floor_type')
                        ->label('Calculation Basis (Lower Limit)')
                        ->options([
                            'none' => 'No Lower Limit',
                            'umk' => 'Regional Minimum Wage (UMK/UMR)',
                        ])
                        ->default('none')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText('If UMK is selected, UMP value is used when wage < UMP.'),
                    TextInput::make('cap_nominal')
                        ->label('Maximum Wage Limit (Cap)')
                        ->numeric()
                        ->prefix('Rp')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText('Example: Rp 12,000,000. Leave empty if no limit.'),
                    TextInput::make('employer_nominal')
                        ->label('Fixed Employer Nominal')
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp')
                        ->visible(fn (Get $get) => in_array($get('employee_type'), ['pbpu', 'pbi']))
                        ->helperText('Used for specific classes that are fully covered.'),
                    TextInput::make('employee_nominal')
                        ->label('Fixed Employee Nominal')
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp')
                        ->visible(fn (Get $get) => in_array($get('employee_type'), ['pbpu', 'pbi']))
                        ->helperText('Used for Independent Class 1, 2, or 3. E.g., Class 1: 150,000'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Only active configurations will be used in Costing calculations.'),
                ])->columns(2),
        ];
    }
}
