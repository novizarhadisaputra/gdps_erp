<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\PurchaseOrder;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\Invoice;
use Modules\Project\Models\WorkCompletionReport;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Header Information')
                    ->description('Define the core identity and global properties for this journal entry.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('number')
                                    ->label('Voucher Number')
                                    ->placeholder('JV-YYYY-MM-XXXX')
                                    ->helperText('Unique reference number. Automatically generated upon submission.')
                                    ->disabled()
                                    ->dehydrated(false),

                                DatePicker::make('date')
                                    ->label('Transaction Date')
                                    ->required()
                                    ->default(now())
                                    ->placeholder('Select transaction date')
                                    ->helperText('The date this entry will be recorded in the general ledger.'),

                                MorphToSelect::make('reference')
                                    ->label('Reference Document')
                                    ->types([
                                        MorphToSelect\Type::make(Invoice::class)
                                            ->label('Invoice')
                                            ->titleAttribute('number'),
                                        MorphToSelect\Type::make(WorkCompletionReport::class)
                                            ->label('BAPP')
                                            ->titleAttribute('number'),
                                        MorphToSelect\Type::make(PurchaseOrder::class)
                                            ->label('Purchase Order')
                                            ->titleAttribute('number'),
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Link to a source document (optional)')
                                    ->helperText('Associate this entry with an existing business document.'),

                                Textarea::make('description')
                                    ->label('General Description')
                                    ->placeholder('e.g. Monthly payroll accrual for Jan 2024...')
                                    ->helperText('Provide a clear explanation for this bookkeeping entry.')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Journal Items')
                    ->description('Specify the debit and credit lines for this transaction. Total debits must equal total credits.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('chart_of_account_id')
                                            ->label('Account')
                                            ->options(ChartOfAccount::query()->where('is_active', true)->get()->mapWithKeys(fn ($item) => [$item->id => "{$item->code} - {$item->name}"]))
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Search and select an account...')
                                            ->helperText('Select the General Ledger account.')
                                            ->columnSpan(2),

                                        TextInput::make('debit')
                                            ->label('Debit Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->required()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->placeholder('0')
                                            ->helperText('Enter amount to increase asset/expense or decrease liability.'),

                                        TextInput::make('credit')
                                            ->label('Credit Amount')
                                            ->numeric()
                                            ->default(0)
                                            ->required()
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->placeholder('0')
                                            ->helperText('Enter amount to increase liability/equity or decrease asset.'),

                                        TextInput::make('note')
                                            ->label('Line Item Memo')
                                            ->placeholder('Specific detail for this line...')
                                            ->helperText('Additional context for this specific ledger entry.')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->minItems(2)
                            ->itemLabel(fn (array $state): ?string => ($state['debit'] > 0 ? 'Debit: IDR '.number_format($state['debit'], 0, ',', '.') : ($state['credit'] > 0 ? 'Credit: IDR '.number_format($state['credit'], 0, ',', '.') : 'New Item')))
                            ->addActionLabel('Add Journal Item'),
                    ]),
            ]);
    }
}
