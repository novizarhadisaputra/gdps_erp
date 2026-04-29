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
                Section::make('Contract Classification')
                    ->description('Define the various types of employment contracts supported by the organization.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Contract Name')
                            ->placeholder('e.g. PKWT, PKWTT, Freelance')
                            ->helperText('The descriptive name of the contract type.')
                            ->required(),
                        TextInput::make('code')
                            ->label('Contract Code')
                            ->placeholder('e.g. C01, PKWT-01')
                            ->helperText('A unique short identifier for this contract type.'),
                    ])->columns(2),

                Section::make('Status & Defaults')
                    ->description('Manage the availability and default status of this contract type.')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Determines if this contract type can be used for new employee contracts.'),
                        Toggle::make('is_default')
                            ->label('Default Contract')
                            ->default(false)
                            ->helperText('Sets this as the pre-selected option for new contract entries.'),
                    ])->columns(2),
            ]);
    }
}
