<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Account Information')
                ->description('General details of the account.')
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->unique(ChartOfAccount::class, 'code', ignoreRecord: true)
                        ->placeholder('e.g., 1101')
                        ->label('Account Code'),

                    TextInput::make('name')
                        ->required()
                        ->placeholder('e.g., Cash in Bank')
                        ->label('Account Name'),

                    Select::make('account_type')
                        ->required()
                        ->options([
                            'Asset' => 'Asset',
                            'Liability' => 'Liability',
                            'Equity' => 'Equity',
                            'Revenue' => 'Revenue',
                            'Expense' => 'Expense',
                            'Other' => 'Other',
                        ])
                        ->label('Account Type'),

                    Select::make('parent_id')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Parent Account')
                        ->placeholder('Root'),
                ])->columns(2),

            Section::make('Additional Details')
                ->schema([
                    Textarea::make('description')
                        ->placeholder('Enter account description...')
                        ->rows(3),

                    Toggle::make('is_active')
                        ->default(true)
                        ->label('Is Active'),
                ]),
        ];
    }
}
