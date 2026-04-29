<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\JpType;

class JpTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(JpType::class)
            ->components([
                Section::make('JP Configuration')
                    ->description('Define the types of Jaminan Pensiun (JP) coverage.')
                    ->schema([
                        TextInput::make('name')
                            ->label('JP Type Name')
                            ->placeholder('e.g. Standard JP')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the JP category.'),
                        TextInput::make('code')
                            ->label('JP Code')
                            ->placeholder('e.g. JP-STD')
                            ->required()
                            ->unique(JpType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique identifier for this JP type.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this JP type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Inactive types will not be available for selection.'),
                        Toggle::make('is_default')
                            ->label('Default JP')
                            ->default(false)
                            ->helperText('Set as the default JP type for new employees.'),
                    ])->columns(2),
            ]);
    }
}
