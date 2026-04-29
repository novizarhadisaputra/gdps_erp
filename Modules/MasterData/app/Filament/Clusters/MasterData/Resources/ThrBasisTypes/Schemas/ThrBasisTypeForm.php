<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ThrBasisTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ThrBasisTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('THR Basis Definition')
                    ->description('Define the calculation basis and formulas used for THR (Religious Holiday Allowance).')
                    ->schema([
                        TextInput::make('name')
                            ->label('Basis Name')
                            ->placeholder('e.g. 1x Gaji Pokok, Pro-rata')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The descriptive name of the THR calculation basis.'),
                        TextInput::make('code')
                            ->label('Basis Code')
                            ->placeholder('e.g. THR-FIXED, THR-PRORATA')
                            ->required()
                            ->unique(\Modules\MasterData\Models\ThrBasisType::class, 'code', ignoreRecord: true)
                            ->helperText('Unique short code for this basis type.'),
                        TextInput::make('formula_code')
                            ->label('Formula Identifier')
                            ->placeholder('e.g. gaji_pokok, total_thp')
                            ->required()
                            ->helperText('A unique identifier used in the internal calculation logic.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this THR basis type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this basis type can be used for THR configurations.'),
                        Toggle::make('is_default')
                            ->label('Default Basis')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new THR setups.'),
                    ])->columns(2),
            ]);
    }
}
