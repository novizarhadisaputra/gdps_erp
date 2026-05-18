<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class BpjsJpConfigForm
{
    public static function schema(): array
    {
        return [
            Section::make(__('General Information'))
                ->description(__('Pension Security (JP) configuration. Manages contribution percentages and maximum wage limits (cap).'))
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('Clear configuration name, e.g., JP PPU Standard')),
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
                    Select::make('floor_type')
                        ->label(__('Calculation Basis (Lower Limit)'))
                        ->options([
                            'none' => __('No Lower Limit'),
                            'umk' => __('Regional Minimum Wage (UMK/UMR)'),
                        ])
                        ->default('none')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText(__('Select the basis for the lower wage limit calculation, if applicable.')),
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
                        ->helperText(__('Usually 2.0% for PPU')),
                    TextInput::make('employee_rate')
                        ->label(__('Employee Rate (%)'))
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText(__('Usually 1.0% for PPU')),
                    TextInput::make('cap_nominal')
                        ->label(__('Maximum Wage Limit (Cap)'))
                        ->numeric()
                        ->prefix('Rp')
                        ->helperText(__('Example: Rp 10,547,400. Leave empty if no limit.')),
                ])->columns(2),

            Section::make(__('Configuration Status'))
                ->description(__('Enable or disable this configuration in the system.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Determines if this configuration is currently available for use.')),
                    Toggle::make('is_default')
                        ->label(__('Set as Default'))
                        ->default(false)
                        ->helperText(__('If enabled, this will be the default configuration for its category. Only one can be default.')),
                ]),
        ];
    }
}
