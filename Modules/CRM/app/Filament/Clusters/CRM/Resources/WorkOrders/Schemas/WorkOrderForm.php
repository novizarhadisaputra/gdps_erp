<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('General Information'))
                    ->description(__('Primary details for the Work Order (SPK), including document date and customer reference.'))
                    ->schema([
                        TextInput::make('number')
                            ->label(__('SPK Number'))
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder(__('SPK-YYYYMM-XXXX'))
                            ->helperText(__('The unique identifier for this Work Order, automatically generated.')),
                        DatePicker::make('document_date')
                            ->label(__('Document Date'))
                            ->required()
                            ->default(now())
                            ->placeholder(__('Select date'))
                            ->helperText(__('The official date this Work Order was issued.')),
                        Select::make('customer_id')
                            ->label(__('Customer'))
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->placeholder(__('Select a customer...'))
                            ->helperText(__('The client for whom this work is being performed.')),
                        TextInput::make('amount')
                            ->label(__('Total Estimated Amount'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->readonly()
                            ->placeholder(__('0'))
                            ->helperText(__('The total value of all line items combined.'))
                            ->afterStateHydrated(fn ($set, $get) => $set('amount', collect($get('items'))->sum('total_price'))),
                    ])->columns(2),

                Section::make(__('Line Items'))
                    ->description(__('Detailed breakdown of the tasks, services, or goods provided under this SPK.'))
                    ->schema([
                        Repeater::make('items')
                            ->label(__('Tasks & Services'))
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('item_name')
                                        ->label(__('Item/Task Description'))
                                        ->required()
                                        ->placeholder(__('e.g. Maintenance of HVAC System'))
                                        ->helperText(__('Provide a clear description of the work or item.'))
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
                                        ->placeholder(__('e.g. Hour, Visit, Lot')),
                                    TextInput::make('unit_price')
                                        ->label(__('Unit Rate'))
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
                            ->addActionLabel('Add New Line Item'),
                    ]),
            ]);
    }
}
