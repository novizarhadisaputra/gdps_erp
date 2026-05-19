<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('General Information'))
                    ->description(__('Primary details for the Customer Purchase Order (PO), including document date and customer reference.'))
                    ->schema([
                        TextInput::make('number')
                            ->label(__('PO Number'))
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder(__('PO-YYYYMM-XXXX'))
                            ->helperText(__('The unique identifier for this Purchase Order, automatically generated.')),
                        DatePicker::make('document_date')
                            ->label(__('Document Date'))
                            ->required()
                            ->default(now())
                            ->placeholder(__('Select date'))
                            ->helperText(__('The official date this Purchase Order was issued.')),
                        Select::make('customer_id')
                            ->label(__('Customer'))
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->placeholder(__('Select a customer...'))
                            ->helperText(__('The client who issued this Purchase Order.')),
                        TextInput::make('amount')
                            ->label(__('Total PO Value'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->readonly()
                            ->placeholder(__('0'))
                            ->helperText(__('The total value of all line items combined.'))
                            ->afterStateHydrated(fn ($set, $get) => $set('amount', collect($get('items'))->sum('total_price'))),
                    ])->columns(2),

                Section::make(__('Line Items'))
                    ->description(__('Detailed breakdown of items or services requested in this Purchase Order.'))
                    ->schema([
                        Repeater::make('items')
                            ->label(__('Ordered Items'))
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('item_name')
                                        ->label(__('Item/Service Name'))
                                        ->required()
                                        ->placeholder(__('e.g. Server Maintenance'))
                                        ->helperText(__('Provide the specific name of the item or service.'))
                                        ->columnSpanFull(),
                                    TextInput::make('quantity')
                                        ->label(__('Quantity'))
                                        ->numeric()
                                        ->required()
                                        ->live()
                                        ->placeholder(__('1'))
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', round(floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0)))),
                                    TextInput::make('uom')
                                        ->label(__('Unit of Measure'))
                                        ->required()
                                        ->placeholder(__('e.g. Unit, Lot, Service')),
                                    TextInput::make('unit_price')
                                        ->label(__('Unit Price'))
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR')
                                        ->required()
                                        ->live()
                                        ->placeholder(__('0'))
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', round(floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0)))),
                                    TextInput::make('total_price')
                                        ->label(__('Line Total'))
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR')
                                        ->readonly()
                                        ->placeholder(__('0')),
                                ]),
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('amount', collect($state)->sum('total_price'));
                            })
                            ->columnSpanFull()
                            ->addActionLabel('Add New Item'),
                    ]),
            ]);
    }
}
