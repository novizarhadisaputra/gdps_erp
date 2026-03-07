<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class JpConfigForm
{
    public static function schema(): array
    {
        return [
            Section::make('General Information')
                ->description('Pension Security (JP) configuration. Manages contribution percentages and maximum wage limits (cap).')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Clear configuration name, e.g., JP PPU Standard'),
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
                    Select::make('floor_type')
                        ->label('Calculation Basis (Lower Limit)')
                        ->options([
                            'none' => 'No Lower Limit',
                            'umk' => 'Regional Minimum Wage (UMK/UMR)',
                        ])
                        ->default('none')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText('Select the basis for the lower wage limit calculation, if applicable.'),
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
                        ->helperText('Usually 2.0% for PPU'),
                    TextInput::make('employee_rate')
                        ->label('Employee Rate (%)')
                        ->numeric()
                        ->default(0)
                        ->suffix('%')
                        ->visible(fn (Get $get) => $get('employee_type') === 'ppu')
                        ->helperText('Usually 1.0% for PPU'),
                    TextInput::make('cap_nominal')
                        ->label('Maximum Wage Limit (Cap)')
                        ->numeric()
                        ->prefix('Rp')
                        ->helperText('Example: Rp 10,547,400. Leave empty if no limit.'),
                    Toggle::make('has_tier')
                        ->label('Use Nominal Tier Table')
                        ->visible(fn (Get $get) => $get('employee_type') === 'pbpu')
                        ->live()
                        ->helperText('Enable this if contributions follow a specific income tier table for BPU.'),
                ])->columns(2),

            Section::make('Income Tier Table')
                ->description('Manage the list of income ranges and nominal contributions (usually for PBPU/Independent).')
                ->visible(fn (Get $get) => $get('employee_type') === 'pbpu' && $get('has_tier'))
                ->schema([
                    Repeater::make('tiers')
                        ->relationship()
                        ->schema([
                            TextInput::make('min_income')
                                ->label('Minimum Income')
                                ->numeric()
                                ->required()
                                ->prefix('Rp'),
                            TextInput::make('max_income')
                                ->label('Maximum Income')
                                ->numeric()
                                ->prefix('Rp')
                                ->helperText('Leave empty for infinite limit (> nominal).'),
                            TextInput::make('employer_nominal')
                                ->label('Employer Nominal')
                                ->numeric()
                                ->default(0)
                                ->prefix('Rp'),
                            TextInput::make('employee_nominal')
                                ->label('Employee Nominal (Participant)')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->prefix('Rp'),
                        ])->columns(4)
                        ->defaultItems(1)
                        ->addActionLabel('Add Income Tier'),
                ]),

            Section::make('Configuration Status')
                ->description('Enable or disable this configuration in the system.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Only active configurations will be used in Costing calculations.'),
                ]),
        ];
    }
}
