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
                Section::make(__('Tax Object Information'))
                    ->description(__('Define the classification of items or services that are subject to taxation.'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Object Code'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder(__('e.g. PPH-21, PPN'))
                            ->helperText(__('The unique identifier for this tax object.')),
                        TextInput::make('name')
                            ->label(__('Object Name'))
                            ->required()
                            ->placeholder(__('e.g. Income Tax Article 21'))
                            ->helperText(__('The descriptive name of the tax object.')),
                        Toggle::make('is_taxable')
                            ->label(__('Subject to Tax'))
                            ->default(true)
                            ->helperText(__('Enable if this object is liable for taxation.')),
                        Textarea::make('description')
                            ->label(__('Detailed Description'))
                            ->placeholder(__('Provide additional context for this tax object...'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('Status & Defaults'))
                    ->description(__('Manage the availability and default status of this tax object.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this tax object can be used in the system.')),
                        Toggle::make('is_default')
                            ->label(__('Default Record'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new tax entries.')),
                    ])->columns(2),
            ]);
    }
}
