<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxObjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tax Object Information')
                    ->description('Define the classification of items or services that are subject to taxation.')
                    ->schema([
                        TextInput::make('code')
                            ->label('Object Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. PPH-21, PPN')
                            ->helperText('The unique identifier for this tax object.'),
                        TextInput::make('name')
                            ->label('Object Name')
                            ->required()
                            ->placeholder('e.g. Income Tax Article 21')
                            ->helperText('The descriptive name of the tax object.'),
                        Toggle::make('is_taxable')
                            ->label('Subject to Tax')
                            ->default(true)
                            ->helperText('Enable if this object is liable for taxation.'),
                        Textarea::make('description')
                            ->label('Detailed Description')
                            ->placeholder('Provide additional context for this tax object...')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this tax object.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this tax object can be used in the system.'),
                        Toggle::make('is_default')
                            ->label('Default Record')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new tax entries.'),
                    ])->columns(2),
            ]);
    }
}
