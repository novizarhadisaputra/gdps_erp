<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

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
                    ->maxLength(255)
                    ->helperText('The name of the banking institution (e.g., BCA, Mandiri).'),
                TextInput::make('account_number')
                    ->label('Account Number')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The official account number assigned by the bank.'),
                TextInput::make('account_name')
                    ->label('Account Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The registered name associated with the bank account.'),
                TextInput::make('swift_code')
                    ->label('SWIFT Code')
                    ->maxLength(255)
                    ->helperText('The unique identification code for the bank (required for international transactions).'),
                TextInput::make('currency')
                    ->default('IDR')
                    ->required()
                    ->maxLength(3)
                    ->helperText('The 3-letter currency code (e.g., IDR, USD, EUR).'),
                Toggle::make('is_active')
                    ->default(true)
                    ->helperText('Enable or disable this bank account for use in Transactions.'),
            ]);
    }
}
