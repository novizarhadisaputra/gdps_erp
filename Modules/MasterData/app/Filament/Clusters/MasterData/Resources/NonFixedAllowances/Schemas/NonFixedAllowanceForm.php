<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\NonFixedAllowances\Schemas;

use Filament\Schemas\Schema;

class NonFixedAllowanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('General Details')
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter Name...')
                            ->helperText('Brief and clear Name for this record.')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_taxable')
                            ->label('Is Taxable')
                            ->helperText('Enable if this allowance is subject to income tax.')
                            ->required(),
                        \Filament\Forms\Components\Select::make('calculation_basis')
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
                            ->helperText('Basis for calculating this allowance.'),
                        \Filament\Forms\Components\TextInput::make('default_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Default Amount')
                            ->placeholder('0.00')
                            ->helperText('Enter the numerical Default Amount amount.')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
