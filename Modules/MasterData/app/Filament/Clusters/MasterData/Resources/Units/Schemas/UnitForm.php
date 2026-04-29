<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Details')
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        Select::make('external_id')
                            ->relationship('external', 'name')
                            ->searchable()
                            ->preload()
                            ->label('External')
                            ->placeholder('Select specific External')
                            ->helperText('Choose the relevant External for this record.')
                            ->required(),
                        TextInput::make('code')
                            ->label('Code Identifier')
                            ->placeholder('e.g., KODE-01 (Auto-generated if empty)')
                            ->helperText('Unique 3-10 character code. Leave empty to auto-generate from Name.'),
                        TextInput::make('name')
                            ->label('Name')
                            ->placeholder('Enter Name...')
                            ->helperText('Brief and clear Name for this record.')
                            ->required(),
                        TextInput::make('superior_unit')
                            ->label('Superior Unit')
                            ->placeholder('Enter Superior Unit...')
                            ->helperText('Brief and clear Superior Unit for this record.')
                            ->required(),
                    ])->columns(2),

                Section::make('Status')
                    ->description('Manage active status and default settings.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this unit is currently active.'),
                        Toggle::make('is_default')
                            ->label('Set as Default')
                            ->default(false)
                            ->helperText('If enabled, this will be the default unit for its category.'),
                    ]),
            ]);
    }
}
