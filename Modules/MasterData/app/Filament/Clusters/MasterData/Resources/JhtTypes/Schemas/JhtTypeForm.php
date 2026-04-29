<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\JhtType;

class JhtTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(JhtType::class)
            ->components([
                Section::make('JHT Configuration')
                    ->description('Define the types of Jaminan Hari Tua (JHT) coverage.')
                    ->schema([
                        TextInput::make('name')
                            ->label('JHT Type Name')
                            ->placeholder('e.g. Standard JHT')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the JHT category.'),
                        TextInput::make('code')
                            ->label('JHT Code')
                            ->placeholder('e.g. JHT-STD')
                            ->required()
                            ->unique(JhtType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique identifier for this JHT type.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this JHT type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Inactive types will not be available for selection.'),
                        Toggle::make('is_default')
                            ->label('Default JHT')
                            ->default(false)
                            ->helperText('Set as the default JHT type for new employees.'),
                    ])->columns(2),
            ]);
    }
}
