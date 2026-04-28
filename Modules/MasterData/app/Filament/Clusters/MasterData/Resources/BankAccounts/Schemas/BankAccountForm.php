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
                Section::make('General Details')
                    ->description('Provide the banking information required for financial transactions.')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('e.g. Bank Mandiri')
                            ->helperText('The full official name of the banking institution.')
                            ->required(),
                        TextInput::make('account_number')
                            ->label('Account Number')
                            ->placeholder('e.g. 1234567890')
                            ->helperText('The unique number assigned to the bank account.')
                            ->required(),
                        TextInput::make('account_name')
                            ->label('Account Name')
                            ->placeholder('e.g. PT. Global Daya Profesional Solusi')
                            ->helperText('The name of the entity or individual who owns the account.')
                            ->required(),
                        TextInput::make('swift_code')
                            ->label('Swift Code / BIC')
                            ->placeholder('e.g. BMRIIDJA')
                            ->helperText('The international standard code for identifying banks globally.')
                            ->required(),
                        TextInput::make('currency')
                            ->label('Currency Code')
                            ->placeholder('e.g. IDR, USD')
                            ->helperText('The ISO currency code used for this account (e.g., IDR).')
                            ->required(),
                        TextInput::make('account_code')
                            ->label('General Ledger Code')
                            ->placeholder('e.g. 110101')
                            ->helperText('The COA/GL account code associated with this bank account for accounting purposes.')
                            ->required(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Active Status')
                            ->helperText('Enable this to allow this account to be selected in payments and invoices.'),
                    ])->columns(2),
            ]);
    }
}
