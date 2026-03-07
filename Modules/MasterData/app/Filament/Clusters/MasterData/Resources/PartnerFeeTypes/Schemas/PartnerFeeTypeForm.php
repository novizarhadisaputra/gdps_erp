<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PartnerFeeTypes\Schemas;

use Filament\Schemas\Schema;

class PartnerFeeTypeForm
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
                        \Filament\Forms\Components\TextInput::make('calculation_basis')
                            ->label('Calculation Basis')
                            ->placeholder('Enter Calculation Basis...')
                            ->helperText('Brief and clear Calculation Basis for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('is_taxable')
                            ->label('Is Taxable')
                            ->placeholder('Enter Is Taxable...')
                            ->helperText('Brief and clear Is Taxable for this record.')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
