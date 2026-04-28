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
                Section::make('General Information')
                    ->description('Primary details for the Customer Purchase Order (PO), including document date and customer reference.')
                    ->schema([
                        TextInput::make('number')
                            ->label('PO Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('PO-YYYYMM-XXXX')
                            ->helperText('The unique identifier for this Purchase Order, automatically generated.'),
                        DatePicker::make('document_date')
                            ->label('Document Date')
                            ->required()
                            ->default(now())
                            ->placeholder('Select date')
                            ->helperText('The official date this Purchase Order was issued.'),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->placeholder('Select a customer...')
                            ->helperText('The client who issued this Purchase Order.'),
                        TextInput::make('amount')
                            ->label('Total PO Value')
                            ->numeric()
                            ->prefix('IDR')
                            ->readonly()
                            ->placeholder('0')
                            ->helperText('The total value of all line items combined.')
                            ->afterStateHydrated(fn ($set, $get) => $set('amount', collect($get('items'))->sum('total_price'))),
                    ])->columns(2),

                Section::make('Line Items')
                    ->description('Detailed breakdown of items or services requested in this Purchase Order.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Ordered Items')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('item_name')
                                        ->label('Item/Service Name')
                                        ->required()
                                        ->placeholder('e.g. Server Maintenance')
                                        ->helperText('Provide the specific name of the item or service.')
                                        ->columnSpanFull(),
                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->required()
                                        ->live()
                                        ->placeholder('1')
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', round(floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0)))),
                                    TextInput::make('uom')
                                        ->label('Unit of Measure')
                                        ->required()
                                        ->placeholder('e.g. Unit, Lot, Service'),
                                    TextInput::make('unit_price')
                                        ->label('Unit Price')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required()
                                        ->live()
                                        ->placeholder('0')
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', round(floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0)))),
                                    TextInput::make('total_price')
                                        ->label('Line Total')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->readonly()
                                        ->placeholder('0'),
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
