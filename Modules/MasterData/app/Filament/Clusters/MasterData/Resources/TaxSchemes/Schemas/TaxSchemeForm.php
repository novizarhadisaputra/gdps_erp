<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxSchemeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Tax Scheme Definition'))
                    ->description(__('Define standard tax schemes and rates used for financial accounting.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Scheme Name'))
                            ->placeholder(__('e.g. PPh 21 Ter A, PPN Standar'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('The descriptive name of the tax scheme.')),
                        TextInput::make('code')
                            ->label(__('Scheme Code'))
                            ->placeholder(__('e.g. TAX-PPH21A, TAX-PPN'))
                            ->required()
                            ->unique(\Modules\MasterData\Models\TaxScheme::class, 'code', ignoreRecord: true)
                            ->helperText(__('Unique short code for this tax scheme.')),
                        TextInput::make('scheme_code')
                            ->label(__('Scheme Identifier'))
                            ->placeholder(__('e.g. skema_1, skema_2a'))
                            ->required()
                            ->helperText(__('Internal identifier used for calculation logic mapping.')),
                        TextInput::make('rate_percentage')
                            ->label(__('Tax Rate (%)'))
                            ->numeric()
                            ->placeholder(__('0.00'))
                            ->default(0)
                            ->required()
                            ->helperText(__('The percentage rate to be applied for this scheme.')),
                        Textarea::make('notes')
                            ->label(__('Scheme Details'))
                            ->placeholder(__('Enter specific conditions or legal references...'))
                            ->helperText(__('Optional contextual information regarding this tax scheme.'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('Status & Defaults'))
                    ->description(__('Manage the availability and default status of this tax scheme.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this tax scheme can be assigned to tax objects.')),
                        Toggle::make('is_default')
                            ->label(__('Default Scheme'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new tax configurations.')),
                    ])->columns(2),
            ]);
    }
}
