<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class BpjsJkkConfigForm
{
    public static function schema(): array
    {
        return [
            Section::make(__('General Information'))
                ->description(__('Work Accident Insurance (JKK) configuration. Manages contribution percentages based on occupational risk levels.'))
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('Clear configuration name, e.g., JKK PPU Medium Risk Level')),
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
                    Select::make('risk_level')
                        ->label(__('Occupational Risk Level'))
                        ->options([
                            'sangat_rendah' => __('Very Low (0.24%)'),
                            'rendah' => __('Low (0.54%)'),
                            'sedang' => __('Medium (0.89%)'),
                            'tinggi' => __('High (1.27%)'),
                            'sangat_tinggi' => __('Very High (1.74%)'),
                        ])
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText(__('Select the occupational risk level that fits your company profile.')),
                    TextInput::make('employer_rate')
                        ->label(__('Employer Rate (%)'))
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu'),
                    TextInput::make('employee_rate')
                        ->label(__('Employee Rate (%)'))
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu'),
                    Toggle::make('has_tier')
                        ->label(__('Use Nominal Tier Table'))
                        ->live()
                        ->helperText(__('Enable this if contributions follow a specific income tier table (e.g. BPU or Jakon).')),
                    Select::make('tier_category')
                        ->label(__('Tier Category'))
                        ->options([
                            'jkk_bpu' => __('JKK PBPU/BPU'),
                            'jkk_jakon' => __('JKK Jakon (Construction)'),
                        ])
                        ->visible(fn (Get $get) => $get('has_tier'))
                        ->required(fn (Get $get) => $get('has_tier'))
                        ->live()
                        ->helperText(__('Select the lookup category for this nominal tier table.')),
                ])->columns(2),

            Section::make(__('Income/Contract Tier Table'))
                ->description(__('Manage the list of income/contract ranges and contributions.'))
                ->visible(fn (Get $get) => $get('has_tier'))
                ->schema([
                    Repeater::make('tiers')
                        ->relationship()
                        ->schema([
                            TextInput::make('min_value')
                                ->label(__('Min Value'))
                                ->numeric()
                                ->required()
                                ->prefix('Rp'),
                            TextInput::make('max_value')
                                ->label(__('Max Value'))
                                ->numeric()
                                ->prefix('Rp')
                                ->helperText(__('Leave empty for infinite limit.')),
                            TextInput::make('employer_nominal')
                                ->label(__('Employer Nominal'))
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                            TextInput::make('employee_nominal')
                                ->label(__('Employee Nominal'))
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                            TextInput::make('employer_rate')
                                ->label(__('Employer Rate (%)'))
                                ->numeric()
                                ->default(0)
                                ->suffix('%'),
                            TextInput::make('employee_rate')
                                ->label(__('Employee Rate (%)'))
                                ->numeric()
                                ->default(0)
                                ->suffix('%'),
                        ])->columns(3)
                        ->defaultItems(1)
                        ->addActionLabel('Add Income Tier'),
                ]),

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
