<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\SalesOrder;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Project\Models\WorkCompletionReport;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Identification')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. INV/2024/001'),
                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->default(now()),
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now()->addDays(30)),
                        Select::make('status')
                            ->options(InvoiceStatus::class)
                            ->required()
                            ->default(InvoiceStatus::Draft),
                    ])->columns(2),

                Section::make('Related Documents')
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        Select::make('sales_order_id')
                            ->label('Sales Order')
                            ->options(SalesOrder::all()->pluck('so_number', 'id'))
                            ->searchable()
                            ->live(),
                        Select::make('work_completion_report_id')
                            ->label('Work Completion Report (BAPP)')
                            ->options(WorkCompletionReport::all()->pluck('report_number', 'id'))
                            ->searchable()
                            ->live(),
                    ])->columns(3),

                Section::make('Financial Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('amount')
                                    ->label('Base Amount')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set) {
                                        $amount = (float) str_replace('.', '', $state);
                                        $tax = $amount * 0.11;
                                        $set('tax_amount', $tax);
                                        $set('total_amount', $amount + $tax);
                                    }),
                                TextInput::make('tax_amount')
                                    ->label('Tax (11%)')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($get, $set) {
                                        $amount = (float) str_replace('.', '', $get('amount') ?? 0);
                                        $tax = (float) str_replace('.', '', $get('tax_amount') ?? 0);
                                        $set('total_amount', $amount + $tax);
                                    }),
                                TextInput::make('total_amount')
                                    ->label('Total Amount')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->readonly()
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
