<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RemunerationComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->helperText('The unique name of the remuneration component (e.g., Transport Allowance, THR).'),
            Select::make('type')
                ->options([
                    'fixed_allowance' => 'Fixed Allowance',
                    'non_fixed_allowance' => 'Non-Fixed Allowance',
                    'benefit' => 'Benefit',
                ])
                ->required()
                ->helperText('The category of the remuneration (e.g., Fixed vs Variable).'),
            TextInput::make('default_amount')
                ->numeric()
                ->default(0)
                ->required()
                ->helperText('The standard amount for this component before adjustments.'),
            Toggle::make('is_bpjs_base')
                ->label('BPJS Base?')
                ->default(true)
                ->helperText('Enable if this component is part of the BPJS salary calculation base.'),
            Toggle::make('is_taxable')
                ->label('Taxable?')
                ->default(true)
                ->helperText('Enable if this component is subject to income tax (PPh 21).'),
            Toggle::make('is_active')
                ->default(true)
                ->helperText('Toggle to enable or disable this component for payroll processing.'),
        ];
    }
}
