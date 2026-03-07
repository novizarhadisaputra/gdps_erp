<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BankAccounts\Schemas;

use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                \Filament\Schemas\Components\Section::make('General Details')
                    ->description('Fill in the necessary configuration properties below.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->placeholder('Enter Bank Name...')
                            ->helperText('Brief and clear Bank Name for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('account_number')
                            ->label('Account Number')
                            ->placeholder('Enter Account Number...')
                            ->helperText('Brief and clear Account Number for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('account_name')
                            ->label('Account Name')
                            ->placeholder('Enter Account Name...')
                            ->helperText('Brief and clear Account Name for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('swift_code')
                            ->label('Swift Code')
                            ->placeholder('Enter Swift Code...')
                            ->helperText('Brief and clear Swift Code for this record.')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('currency')
                            ->label('Currency')
                            ->placeholder('Enter Currency...')
                            ->helperText('Brief and clear Currency for this record.')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status (Active / Inactive)')
                            ->helperText('Toggle on to make this record available in standard lists within the system.'),
                    ])->columns(2),
            ]);
    }
}
