<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsBasisTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BpjsBasisTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('BPJS Basis Definition'))
                    ->description(__('Define the calculation basis and formulas used for BPJS contributions.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Basis Name'))
                            ->placeholder(__('e.g. UMK + Allowances, Fixed Salary'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('The descriptive name of the BPJS calculation basis.')),
                        TextInput::make('code')
                            ->label(__('Basis Code'))
                            ->placeholder(__('e.g. BPJS-UMK, BPJS-FIXED'))
                            ->required()
                            ->unique(\Modules\MasterData\Models\BpjsBasisType::class, 'code', ignoreRecord: true)
                            ->helperText(__('Unique short code for this basis type.')),
                        TextInput::make('formula_code')
                            ->label(__('Formula Identifier'))
                            ->placeholder(__('e.g. gaji_pokok, basic_plus_fixed'))
                            ->required()
                            ->helperText(__('A unique identifier used in the internal calculation logic.')),
                    ])->columns(2),

                Section::make(__('Status & Defaults'))
                    ->description(__('Manage the availability and default status of this BPJS basis type.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this basis type can be used for BPJS configurations.')),
                        Toggle::make('is_default')
                            ->label(__('Default Basis'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new BPJS setups.')),
                    ])->columns(2),
            ]);
    }
}
