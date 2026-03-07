<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Schemas;

use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('General Details')
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        \Filament\Forms\Components\Select::make('external_id')
                            ->relationship('external', 'name')
                            ->searchable()
                            ->preload()
                            ->label('External')
                            ->placeholder('Select specific External')
                            ->helperText('Choose the relevant External for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('code')
                            ->label('Code Identifier')
                            ->placeholder('e.g., KODE-01 (Auto-generated if empty)')
                            ->helperText('Unique 3-10 character code. Leave empty to auto-generate from Name.'),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter Name...')
                            ->helperText('Brief and clear Name for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('superior_unit')
                            ->label('Superior Unit')
                            ->placeholder('Enter Superior Unit...')
                            ->helperText('Brief and clear Superior Unit for this record.')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
