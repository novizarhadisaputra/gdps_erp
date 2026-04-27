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
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('Enter Bank Name...')
                            ->helperText('Brief and clear Bank Name for this record.')
                            ->required(),
                        TextInput::make('account_number')
                            ->label('Account Number')
                            ->placeholder('Enter Account Number...')
                            ->helperText('Brief and clear Account Number for this record.')
                            ->required(),
                        TextInput::make('account_name')
                            ->label('Account Name')
                            ->placeholder('Enter Account Name...')
                            ->helperText('Brief and clear Account Name for this record.')
                            ->required(),
                        TextInput::make('swift_code')
                            ->label('Swift Code')
                            ->placeholder('Enter Swift Code...')
                            ->helperText('Brief and clear Swift Code for this record.')
                            ->required(),
                        TextInput::make('currency')
                            ->label('Currency')
                            ->placeholder('Enter Currency...')
                            ->helperText('Brief and clear Currency for this record.')
                            ->required(),
                        TextInput::make('account_code')
                            ->label('Account Code (GL)')
                            ->placeholder('e.g., 1101')
                            ->helperText('The General Ledger account code associated with this bank account.')
                            ->required(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
