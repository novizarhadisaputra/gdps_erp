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
                        ->placeholder('e.g. 1101, 4000')
                        ->label('Account Code')
                        ->helperText('The unique numerical code for this account.'),

                    TextInput::make('name')
                        ->required()
                        ->placeholder('e.g. Cash in Bank, Sales Revenue')
                        ->label('Account Name')
                        ->helperText('The descriptive name of the account.'),

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
                        ->label('Account Type')
                        ->placeholder('Select type')
                        ->helperText('The financial category this account belongs to.'),

                    Select::make('parent_id')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Parent Account')
                        ->placeholder('Select parent (optional)')
                        ->helperText('Choose a parent account for hierarchical grouping.'),
                ])->columns(2),

            Section::make('Additional Details')
                ->schema([
                    Textarea::make('description')
                        ->placeholder('Provide additional context for this account...')
                        ->helperText('Internal notes regarding the usage of this account.')
                        ->rows(3),

                    Toggle::make('is_active')
                        ->default(true)
                        ->label('Is Active')
                        ->helperText('Whether this account is available for use in journal entries.'),
                ]),
        ];
    }
}
