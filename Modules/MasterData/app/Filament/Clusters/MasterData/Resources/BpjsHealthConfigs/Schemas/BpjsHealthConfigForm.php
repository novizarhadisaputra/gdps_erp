<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class BpjsHealthConfigForm
{
    public static function schema(): array
    {
        return [
            Section::make(__('General Information'))
                ->description(__('Health Insurance (BPJS Kesehatan) configuration. Manages contribution calculation for PPU, PBPU/Independent, and PBI.'))
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('Clear configuration name, e.g., BPJS Kes PPU Standard')),
                    Select::make('employee_type')
                        ->label(__('Membership Type'))
                        ->options([
                            'ppu' => __('Wage Earner (PPU)'),
                            'pbpu' => __('Non-Wage Earner (BPU/Independent)'),
                            'pbi' => __('Contribution Assistance Recipient (PBI)'),
                        ])
                        ->required()
                        ->live()
                        ->helperText(__('Select the membership category that determines how contributions are calculated.')),
                ])->columns(2),

            Section::make(__('Rate & Tier Configuration'))
                ->description(__('Determine the deduction percentage for PPU and nominal range for PBPU/Independent.'))
                ->schema([
                    TextInput::make('employer_rate')
                        ->label(__('Employer Rate (%)'))
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText(__('Usually 4.0% for PPU')),
                    TextInput::make('employee_rate')
                        ->label(__('Employee Rate (%)'))
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText(__('Usually 1.0% for PPU')),
                    Select::make('floor_type')
                        ->label(__('Calculation Basis (Lower Limit)'))
                        ->options([
                            'none' => __('No Lower Limit'),
                            'umk' => __('Regional Minimum Wage (UMK/UMR)'),
                        ])
                        ->default('none')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText(__('If UMK is selected, UMP value is used when wage < UMP.')),
                    TextInput::make('cap_nominal')
                        ->label(__('Maximum Wage Limit (Cap)'))
                        ->numeric()
                        ->prefix('Rp')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText(__('Example: Rp 12,000,000. Leave empty if no limit.')),
                    TextInput::make('employer_nominal')
                        ->label(__('Fixed Employer Nominal'))
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp')
                        ->visible(fn (Get $get) => in_array($get('employee_type'), ['pbpu', 'pbi']))
                        ->helperText(__('Used for specific classes that are fully covered.')),
                    TextInput::make('employee_nominal')
                        ->label(__('Fixed Employee Nominal'))
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp')
                        ->visible(fn (Get $get) => in_array($get('employee_type'), ['pbpu', 'pbi']))
                        ->helperText(__('Used for Independent Class 1, 2, or 3. E.g., Class 1: 150,000')),
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Determines if this configuration is currently available for use.')),
                    Toggle::make('is_default')
                        ->label(__('Set as Default'))
                        ->default(false)
                        ->helperText(__('If enabled, this will be the default configuration for its category. Only one can be default.')),
                ])->columns(2),
        ];
    }
}
