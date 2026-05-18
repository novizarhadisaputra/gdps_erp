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
                Section::make(__('General Details'))
                    ->description(__('Fill in the necessary configuration properties below.'))
                    ->schema([
                        Select::make('external_id')
                            ->relationship('external', 'name')
                            ->searchable()
                            ->preload()
                            ->label(__('External'))
                            ->placeholder(__('Select specific External'))
                            ->helperText(__('Choose the relevant External for this record.'))
                            ->required(),
                        TextInput::make('code')
                            ->label(__('Code Identifier'))
                            ->placeholder(__('e.g., KODE-01 (Auto-generated if empty)'))
                            ->helperText(__('Unique 3-10 character code. Leave empty to auto-generate from Name.')),
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->placeholder(__('Enter Name...'))
                            ->helperText(__('Brief and clear Name for this record.'))
                            ->required(),
                        TextInput::make('superior_unit')
                            ->label(__('Superior Unit'))
                            ->placeholder(__('Enter Superior Unit...'))
                            ->helperText(__('Brief and clear Superior Unit for this record.'))
                            ->required(),
                    ])->columns(2),

                Section::make(__('Status'))
                    ->description(__('Manage active status and default settings.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this unit is currently active.')),
                        Toggle::make('is_default')
                            ->label(__('Set as Default'))
                            ->default(false)
                            ->helperText(__('If enabled, this will be the default unit for its category.')),
                    ]),
            ]);
    }
}
