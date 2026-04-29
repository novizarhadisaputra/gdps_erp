<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerFeeTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fee Type Definition')
                    ->description('Define the naming and calculation logic for partner or management fees.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Fee Name')
                            ->placeholder('e.g. Management Fee, Service Fee')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the fee type.'),
                        TextInput::make('code')
                            ->label('Fee Code')
                            ->placeholder('e.g. FEE-MGMT, FEE-SRV')
                            ->required()
                            ->unique(\Modules\MasterData\Models\PartnerFeeType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code for this fee type.'),
                        Select::make('calculation_basis')
                            ->label('Calculation Basis')
                            ->options([
                                'flat' => 'Flat / Fixed Amount',
                                'per_day' => 'Per Day',
                                'per_hour' => 'Per Hour',
                                'per_output' => 'Per Output / Unit',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->required()
                            ->native(false)
                            ->placeholder('Select basis')
                            ->helperText('The mathematical basis for calculating the fee amount.'),
                    ])->columns(2),

                Section::make('Tax & Status')
                    ->description('Manage tax implications and record availability.')
                    ->schema([
                        Toggle::make('is_taxable')
                            ->label('Is Taxable')
                            ->helperText('Enable if this fee type should be included in tax calculations.')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this fee type can be selected in new transactions.'),
                        Toggle::make('is_default')
                            ->label('Default Record')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new fee entries.'),
                    ])->columns(3),
            ]);
    }
}
