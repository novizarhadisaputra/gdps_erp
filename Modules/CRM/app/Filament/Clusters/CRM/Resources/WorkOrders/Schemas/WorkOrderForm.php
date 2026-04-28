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
                Section::make('General Information')
                    ->description('Primary details for the Work Order (SPK), including document date and customer reference.')
                    ->schema([
                        TextInput::make('number')
                            ->label('SPK Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('SPK-YYYYMM-XXXX')
                            ->helperText('The unique identifier for this Work Order, automatically generated.'),
                        DatePicker::make('document_date')
                            ->label('Document Date')
                            ->required()
                            ->default(now())
                            ->placeholder('Select date')
                            ->helperText('The official date this Work Order was issued.'),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->placeholder('Select a customer...')
                            ->helperText('The client for whom this work is being performed.'),
                        TextInput::make('amount')
                            ->label('Total Estimated Amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->readonly()
                            ->placeholder('0')
                            ->helperText('The total value of all line items combined.')
                            ->afterStateHydrated(fn ($set, $get) => $set('amount', collect($get('items'))->sum('total_price'))),
                    ])->columns(2),

                Section::make('Line Items')
                    ->description('Detailed breakdown of the tasks, services, or goods provided under this SPK.')
                    ->schema([
                        Repeater::make('items')
                            ->label('Tasks & Services')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('item_name')
                                        ->label('Item/Task Description')
                                        ->required()
                                        ->placeholder('e.g. Maintenance of HVAC System')
                                        ->helperText('Provide a clear description of the work or item.')
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
                                        ->placeholder('e.g. Hour, Visit, Lot'),
                                    TextInput::make('unit_price')
                                        ->label('Unit Rate')
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
                            ->addActionLabel('Add New Line Item'),
                    ]),
            ]);
    }
}
