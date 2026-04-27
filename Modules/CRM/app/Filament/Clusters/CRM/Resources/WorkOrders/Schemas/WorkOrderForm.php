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
                    ->schema([
                        TextInput::make('number')
                            ->label('SPK Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        DatePicker::make('document_date')
                            ->label('Document Date')
                            ->required()
                            ->default(now()),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        TextInput::make('amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->readonly()
                            ->afterStateHydrated(fn ($set, $get) => $set('amount', collect($get('items'))->sum('total_price'))),
                    ])->columns(2),

                Section::make('Line Items')
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('item_name')
                                        ->label('Item Name')
                                        ->required()
                                        ->columnSpanFull(),
                                    TextInput::make('quantity')
                                        ->numeric()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
                                    TextInput::make('uom')
                                        ->label('Unit')
                                        ->required(),
                                    TextInput::make('unit_price')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
                                    TextInput::make('total_price')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->readonly(),
                                ]),
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $set('amount', collect($state)->sum('total_price'));
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
