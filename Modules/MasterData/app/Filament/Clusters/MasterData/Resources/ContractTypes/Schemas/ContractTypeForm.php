<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContractTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Contract Classification'))
                    ->description(__('Define the various types of employment contracts supported by the organization.'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Contract Name'))
                            ->placeholder(__('e.g. PKWT, PKWTT, Freelance'))
                            ->helperText(__('The descriptive name of the contract type.'))
                            ->required(),
                        TextInput::make('code')
                            ->label(__('Contract Code'))
                            ->placeholder(__('e.g. C01, PKWT-01'))
                            ->helperText(__('A unique short identifier for this contract type.')),
                    ])->columns(2),

                Section::make(__('Status & Defaults'))
                    ->description(__('Manage the availability and default status of this contract type.'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active Status'))
                            ->default(true)
                            ->helperText(__('Determines if this contract type can be used for new employee contracts.')),
                        Toggle::make('is_default')
                            ->label(__('Default Contract'))
                            ->default(false)
                            ->helperText(__('Sets this as the pre-selected option for new contract entries.')),
                    ])->columns(2),
            ]);
    }
}
