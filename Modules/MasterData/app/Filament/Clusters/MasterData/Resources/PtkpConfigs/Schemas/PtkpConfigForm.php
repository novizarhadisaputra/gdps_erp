<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Schemas;

use Filament\Schemas\Schema;

class PtkpConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('General Details')
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('code')
                            ->label('Code Identifier')
                            ->placeholder('e.g., KODE-01 (Auto-generated if empty)')
                            ->helperText('Unique 3-10 character code. Leave empty to auto-generate from Name.'),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter Name...')
                            ->helperText('Brief and clear Name for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('tax_category')
                            ->label('Tax Category')
                            ->placeholder('Enter Tax Category...')
                            ->helperText('Brief and clear Tax Category for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('annual_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Annual Amount')
                            ->placeholder('0.00')
                            ->helperText('Enter the numerical Annual Amount amount.')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
