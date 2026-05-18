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
                Section::make(__('Fee Type Definition'))
                    ->description(__('Define the naming and calculation logic for partner or management fees.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Fee Name'))
                            ->placeholder(__('e.g. Management Fee, Service Fee'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('The descriptive name of the fee type.')),
                        TextInput::make('code')
                            ->label(__('Fee Code'))
                            ->placeholder(__('e.g. FEE-MGMT, FEE-SRV'))
                            ->required()
                            ->unique(\Modules\MasterData\Models\PartnerFeeType::class, 'code', ignoreRecord: true)
                            ->helperText(__('Unique short code for this fee type.')),
                        Select::make('calculation_basis')
                            ->label(__('Calculation Basis'))
                            ->options([
                                'flat' => __('Flat / Fixed Amount'),
                                'per_day' => __('Per Day'),
                                'per_hour' => __('Per Hour'),
                                'per_output' => __('Per Output / Unit'),
                                'percentage' => __('Percentage (%)'),
                            ])
                            ->required()
                            ->native(false)
                            ->placeholder(__('Select basis'))
                            ->helperText(__('The mathematical basis for calculating the fee amount.')),
                    ])->columns(2),

                Section::make(__('Tax & Status'))
                    ->description(__('Manage tax implications and record availability.'))
                    ->schema([
                        Toggle::make('is_taxable')
                            ->label(__('Is Taxable'))
                            ->helperText(__('Enable if this fee type should be included in tax calculations.'))
                            ->required(),
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this fee type can be selected in new transactions.')),
                        Toggle::make('is_default')
                            ->label(__('Default Record'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new fee entries.')),
                    ])->columns(3),
            ]);
    }
}
