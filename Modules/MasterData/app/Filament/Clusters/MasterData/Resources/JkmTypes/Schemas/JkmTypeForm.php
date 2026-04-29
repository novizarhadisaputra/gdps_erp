<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\JkmType;

class JkmTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(JkmType::class)
            ->components([
                Section::make('JKM Configuration')
                    ->description('Define the types of Jaminan Kematian (JKM) coverage.')
                    ->schema([
                        TextInput::make('name')
                            ->label('JKM Type Name')
                            ->placeholder('e.g. Standard JKM')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the JKM category.'),
                        TextInput::make('code')
                            ->label('JKM Code')
                            ->placeholder('e.g. JKM-STD')
                            ->required()
                            ->unique(JkmType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique identifier for this JKM type.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this JKM type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Inactive types will not be available for selection.'),
                        Toggle::make('is_default')
                            ->label('Default JKM')
                            ->default(false)
                            ->helperText('Set as the default JKM type for new employees.'),
                    ])->columns(2),
            ]);
    }
}
