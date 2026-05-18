<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('General Details'))
                    ->description(__('Provide the banking information required for financial transactions.'))
                    ->schema([
                        TextInput::make('bank_name')
                            ->label(__('Bank Name'))
                            ->placeholder(__('e.g. Bank Mandiri'))
                            ->helperText(__('The full official name of the banking institution.'))
                            ->required(),
                        TextInput::make('account_number')
                            ->label(__('Account Number'))
                            ->placeholder(__('e.g. 1234567890'))
                            ->helperText(__('The unique number assigned to the bank account.'))
                            ->required(),
                        TextInput::make('account_name')
                            ->label(__('Account Name'))
                            ->placeholder(__('e.g. PT. Global Daya Profesional Solusi'))
                            ->helperText(__('The name of the entity or individual who owns the account.'))
                            ->required(),
                        TextInput::make('swift_code')
                            ->label(__('Swift Code / BIC'))
                            ->placeholder(__('e.g. BMRIIDJA'))
                            ->helperText(__('The international standard code for identifying banks globally.'))
                            ->required(),
                        TextInput::make('currency')
                            ->label(__('Currency Code'))
                            ->placeholder(__('e.g. IDR, USD'))
                            ->helperText(__('The ISO currency code used for this account (e.g., IDR).'))
                            ->required(),
                        TextInput::make('account_code')
                            ->label(__('General Ledger Code'))
                            ->placeholder(__('e.g. 110101'))
                            ->helperText(__('The COA/GL account code associated with this bank account for accounting purposes.'))
                            ->required(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->label(__('Active Status'))
                            ->helperText(__('Enable this to allow this account to be selected in payments and invoices.')),
                    ])->columns(2),
            ]);
    }
}
