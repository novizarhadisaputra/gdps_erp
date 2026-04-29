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
                Section::make('BPJS Basis Definition')
                    ->description('Define the calculation basis and formulas used for BPJS contributions.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Basis Name')
                            ->placeholder('e.g. UMK + Allowances, Fixed Salary')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the BPJS calculation basis.'),
                        TextInput::make('code')
                            ->label('Basis Code')
                            ->placeholder('e.g. BPJS-UMK, BPJS-FIXED')
                            ->required()
                            ->unique(\Modules\MasterData\Models\BpjsBasisType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code for this basis type.'),
                        TextInput::make('formula_code')
                            ->label('Formula Identifier')
                            ->placeholder('e.g. gaji_pokok, basic_plus_fixed')
                            ->required()
                            ->helperText('A unique identifier used in the internal calculation logic.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this BPJS basis type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this basis type can be used for BPJS configurations.'),
                        Toggle::make('is_default')
                            ->label('Default Basis')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new BPJS setups.'),
                    ])->columns(2),
            ]);
    }
}
