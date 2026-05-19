<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\CooperationAgreements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Models\Customer;

class CooperationAgreementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('General Information'))
                    ->schema([
                        TextInput::make('number')
                            ->label(__('PKS Number'))
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder(__('Auto-generated')),
                        DatePicker::make('document_date')
                            ->label(__('Document Date'))
                            ->required()
                            ->default(now()),
                        Select::make('customer_id')
                            ->label(__('Customer'))
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live(),
                        TextInput::make('amount')
                            ->label(__('Total Amount'))
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR')
                            ->readonly()
                            ->afterStateHydrated(fn ($set, $get) => $set('amount', collect($get('items'))->sum('total_price'))),
                    ])->columns(2),

                Section::make(__('Line Items'))
                    ->schema([
                        Repeater::make('items')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('item_name')
                                        ->label(__('Item Name'))
                                        ->required()
                                        ->columnSpanFull(),
                                    TextInput::make('quantity')
                                        ->numeric()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
                                    TextInput::make('uom')
                                        ->label(__('Unit'))
                                        ->required(),
                                    TextInput::make('unit_price')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($get, $set) => $set('total_price', floatval($get('quantity') ?? 0) * floatval($get('unit_price') ?? 0))),
                                    TextInput::make('total_price')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
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
