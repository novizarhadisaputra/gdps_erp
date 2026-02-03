<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('bank_name')
                    ->label('Bank Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_number')
                    ->label('Account Number')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_name')
                    ->label('Account Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('swift_code')
                    ->label('SWIFT Code')
                    ->maxLength(255),
                TextInput::make('currency')
                    ->default('IDR')
                    ->required()
                    ->maxLength(3),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
