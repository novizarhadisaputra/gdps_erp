<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Schemas;

use Filament\Schemas\Schema;

class BpjsBasisTypeForm
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
                        \Filament\Forms\Components\TextInput::make('formula_code')
                            ->label('Formula Code')
                            ->placeholder('Enter Formula Code...')
                            ->helperText('Brief and clear Formula Code for this record.')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
