<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\JkkType;

class JkkTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(JkkType::class)
            ->components([
                Section::make('JKK Configuration')
                    ->description('Define the types of Jaminan Kecelakaan Kerja (JKK) coverage.')
                    ->schema([
                        TextInput::make('name')
                            ->label('JKK Type Name')
                            ->placeholder('e.g. Very Low Risk, High Risk')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the JKK risk category.'),
                        TextInput::make('code')
                            ->label('JKK Code')
                            ->placeholder('e.g. JKK-VLR, JKK-HR')
                            ->required()
                            ->unique(JkkType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique identifier for this JKK type.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this JKK type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Inactive types will not be available for selection.'),
                        Toggle::make('is_default')
                            ->label('Default JKK')
                            ->default(false)
                            ->helperText('Set as the default JKK type for new employees.'),
                    ])->columns(2),
            ]);
    }
}
