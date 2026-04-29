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
            Section::make('General Information')
                ->description('Work Accident Insurance (JKK) configuration. Manages contribution percentages based on occupational risk levels.')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Clear configuration name, e.g., JKK PPU Medium Risk Level'),
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
                    Select::make('risk_level')
                        ->label('Occupational Risk Level')
                        ->options([
                            'sangat_rendah' => 'Very Low (0.24%)',
                            'rendah' => 'Low (0.54%)',
                            'sedang' => 'Medium (0.89%)',
                            'tinggi' => 'High (1.27%)',
                            'sangat_tinggi' => 'Very High (1.74%)',
                        ])
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText('Select the occupational risk level that fits your company profile.'),
                    TextInput::make('employer_rate')
                        ->label('Employer Rate (%)')
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu'),
                    TextInput::make('employee_rate')
                        ->label('Employee Rate (%)')
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu'),
                    Toggle::make('has_tier')
                        ->label('Use Nominal Tier Table')
                        ->live()
                        ->helperText('Enable this if contributions follow a specific income tier table (e.g. BPU or Jakon).'),
                ])->columns(2),

            Section::make('Income/Contract Tier Table')
                ->description('Manage the list of income/contract ranges and contributions.')
                ->visible(fn (Get $get) => $get('has_tier'))
                ->schema([
                    Repeater::make('tiers')
                        ->relationship()
                        ->schema([
                            TextInput::make('min_value')
                                ->label('Min Value')
                                ->numeric()
                                ->required()
                                ->prefix('Rp'),
                            TextInput::make('max_value')
                                ->label('Max Value')
                                ->numeric()
                                ->prefix('Rp')
                                ->helperText('Leave empty for infinite limit.'),
                            TextInput::make('employer_nominal')
                                ->label('Employer Nominal')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                            TextInput::make('employee_nominal')
                                ->label('Employee Nominal')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                            TextInput::make('employer_rate')
                                ->label('Employer Rate (%)')
                                ->numeric()
                                ->default(0)
                                ->suffix('%'),
                            TextInput::make('employee_rate')
                                ->label('Employee Rate (%)')
                                ->numeric()
                                ->default(0)
                                ->suffix('%'),
                        ])->columns(3)
                        ->defaultItems(1)
                        ->addActionLabel('Add Income Tier'),
                ]),

            Section::make('Configuration Status')
                ->description('Enable or disable this configuration in the system.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Determines if this configuration is currently available for use.'),
                    Toggle::make('is_default')
                        ->label('Set as Default')
                        ->default(false)
                        ->helperText('If enabled, this will be the default configuration for its category. Only one can be default.'),
                ]),
        ];
    }
}
