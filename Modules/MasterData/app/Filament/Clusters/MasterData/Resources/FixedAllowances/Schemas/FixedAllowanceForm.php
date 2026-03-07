<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Schemas;

use Filament\Schemas\Schema;

class FixedAllowanceForm
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
                        \Filament\Forms\Components\TextInput::make('is_bpjs_base')
                            ->label('Is Bpjs Base')
                            ->placeholder('Enter Is Bpjs Base...')
                            ->helperText('Brief and clear Is Bpjs Base for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('is_taxable')
                            ->label('Is Taxable')
                            ->placeholder('Enter Is Taxable...')
                            ->helperText('Brief and clear Is Taxable for this record.')
                            ->required(),
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
