<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\Finance\Enums\RevenueType;
use Modules\Finance\Models\Invoice;
use Modules\Project\Models\Project;

class AccrueRevenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Information')
                    ->description('Provide the project and time period for this revenue accrual.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('project_id')
                                    ->label('Project')
                                    ->options(Project::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder('Select a project')
                                    ->helperText('Select the project this accrual belongs to.')
                                    ->required()
                                    ->live(),
                                Select::make('month')
                                    ->label('Month')
                                    ->options([
                                        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                                    ])
                                    ->placeholder('Select Month')
                                    ->helperText('The month this revenue is being accrued for.')
                                    ->required()
                                    ->default(now()->month)
                                    ->native(false),
                                TextInput::make('year')
                                    ->label('Year')
                                    ->numeric()
                                    ->placeholder(now()->year)
                                    ->helperText('The fiscal year for this accrual.')
                                    ->default(now()->year)
                                    ->required(),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Revenue Items')
                    ->description('Record multiple revenue types (Main, Overtime, SPPD) and associate them with costs or invoices.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('revenue_type')
                                            ->label('Revenue Type')
                                            ->options(RevenueType::class)
                                            ->required()
                                            ->native(false),
                                        Select::make('invoice_id')
                                            ->label('Related Invoice (Optional)')
                                            ->options(fn (Get $get) => Invoice::where('customer_id', Project::find($get('../../project_id'))?->customer_id)->pluck('number', 'id'))
                                            ->searchable()
                                            ->placeholder('Select invoice')
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if ($state) {
                                                    $invoice = Invoice::find($state);
                                                    if ($invoice) {
                                                        $set('amount_actual', $invoice->total_amount);
                                                    }
                                                }
                                            }),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('amount_estimated')
                                            ->label('Estimated Revenue')
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->required()
                                            ->live(onBlur: true),
                                        TextInput::make('amount_actual')
                                            ->label('Actual Amount / Cost')
                                            ->prefix('IDR')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->required(),
                                    ]),
                                Textarea::make('description')
                                    ->label('Item Description')
                                    ->placeholder('Notes for this specific revenue type...')
                                    ->columnSpanFull(),
                            ])
                            ->itemLabel(fn (array $state): ?string => RevenueType::tryFrom($state['revenue_type'] ?? '')?->getLabel())
                            ->addActionLabel('Add Revenue Item')
                            ->collapsible()
                            ->defaultItems(1)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_amount_estimated')
                                    ->label('Total Estimated')
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'bg-gray-50']),
                                TextInput::make('total_amount_actual')
                                    ->label('Total Actual')
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'bg-gray-50']),
                            ]),

                        Textarea::make('description')
                            ->label('General Notes')
                            ->placeholder('Enter additional notes for the entire submission...')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }
}
