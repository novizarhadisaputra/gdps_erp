<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Resources\Taxes\Schemas\TaxForm;
use Modules\MasterData\Filament\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Item;

class ProfitabilityAnalysisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('general_information_id')
                    ->relationship('generalInformation', 'id')
                    ->label('GI Form (RR Submission)')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->helperText('Customer associated with the project.')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm(CustomerForm::schema()),
                TextInput::make('document_number')
                    ->label('Document Number'),

                Section::make('Project Documents')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('tor')
                                    ->collection('tor')
                                    ->disk('s3')
                                    ->label('ToR Document')
                                    ->hint('Terms of Reference'),
                                SpatieMediaLibraryFileUpload::make('rfp')
                                    ->collection('rfp')
                                    ->disk('s3')
                                    ->label('RFP Document')
                                    ->hint('Request for Proposal'),
                                SpatieMediaLibraryFileUpload::make('rfi')
                                    ->collection('rfi')
                                    ->disk('s3')
                                    ->label('RFI Document')
                                    ->hint('Request for Information'),
                            ]),
                    ])->collapsible(),

                Section::make('Project Code Parameters')
                    ->columns(columns: 1)
                    ->schema([
                        Select::make('work_scheme_id')
                            ->relationship('workScheme', 'name')
                            ->helperText('The work scheme for this project.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Defines the operational scheme.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(WorkSchemeForm::schema()),
                        Select::make('product_cluster_id')
                            ->relationship('productCluster', 'name')
                            ->helperText('Cluster of the product/service.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Group of related products.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(ProductClusterForm::schema()),
                        Select::make('tax_id')
                            ->relationship('tax', 'name')
                            ->helperText('Applicable tax regulation.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Tax rules for this project.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(TaxForm::schema()),
                        Select::make('project_area_id')
                            ->relationship('projectArea', 'name')
                            ->helperText('Geographical area of the project.')
                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Location where project is executed.')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->createOptionForm(ProjectAreaForm::schema()),
                    ]),

                Section::make('Financial Analysis')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('asset_ownership')
                                    ->options([
                                        'gdps-owned' => 'GDPS-Owned',
                                        'customer-owned' => 'Customer-Owned',
                                    ])
                                    ->default('gdps-owned')
                                    ->required()
                                    ->native(false),
                                TextInput::make('management_fee')
                                    ->label('Management Fee (Flat)')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->helperText('Additional flat fee.'),
                                TextInput::make('margin_percentage')
                                    ->label('Gross Margin')
                                    ->numeric()
                                    ->suffix('%')
                                    ->readOnly()
                                    ->placeholder('Auto'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('management_expense_rate')
                                    ->label('Mgmt Expense (%)')
                                    ->numeric()
                                    ->default(2.50)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('interest_rate')
                                    ->label('Interest Rate (%)')
                                    ->numeric()
                                    ->default(1.50)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('tax_rate')
                                    ->label('Corp Tax Rate (%)')
                                    ->numeric()
                                    ->default(22.00)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('revenue_per_month')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->label('Total Revenue/Mo')
                                    ->readOnly()
                                    ->live(onBlur: true),
                                TextInput::make('direct_cost')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->label('Total Direct Cost/Mo')
                                    ->readOnly()
                                    ->live(),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('ebitda')
                                    ->label('EBITDA')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->readOnly(),
                                TextInput::make('ebit')
                                    ->label('EBIT')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->readOnly(),
                                TextInput::make('ebt')
                                    ->label('EBT')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->readOnly(),
                                TextInput::make('net_profit')
                                    ->label('Net Profit')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->readOnly()
                                    ->hintIcon('heroicon-m-check-circle', tooltip: 'Final monthly profitability.'),
                            ]),
                    ]),

                Section::make('Costing Details')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item')
                                    ->relationship('item', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            return;
                                        }
                                        $item = Item::find($state);
                                        if ($item) {
                                            $set('unit_cost_price', $item->price);
                                            $set('unit_of_measure', $item->unitOfMeasure?->name ?? 'Unit');

                                            // Depreciation Logic: Item Specific > Asset Group > Default
                                            $depreciation = $item->depreciation_months;
                                            if (empty($depreciation) || $depreciation <= 0) {
                                                // Check Asset Group via Category
                                                $usefulLifeYears = $item->category?->assetGroup?->useful_life_years;
                                                if ($usefulLifeYears && $usefulLifeYears > 0) {
                                                    $depreciation = $usefulLifeYears * 12;
                                                }
                                            }

                                            $set('depreciation_months', $depreciation ?? 1);
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('unit_of_measure')
                                    ->label('UoM')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(1),
                                TextInput::make('unit_cost_price')
                                    ->label('Modal Price')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('depreciation_months')
                                    ->label('Depreciation (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('markup_percentage')
                                    ->label('Markup (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('total_monthly_cost')
                                    ->label('Modal/Mo')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->placeholder(fn (Get $get) => self::calculateItemMonthlyCost($get))
                                    ->columnSpan(1),
                                TextInput::make('total_monthly_sale')
                                    ->label('Selling/Mo')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->placeholder(fn (Get $get) => self::calculateItemMonthlySale($get))
                                    ->columnSpan(1),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => Item::find($state['item_id'] ?? null)?->name ?? 'New Item')
                            ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                    ]),
            ]);
    }

    protected static function calculateDirectCost($get, $set): void
    {
        $items = $get('items') ?? [];
        $totalDirectCost = 0;
        $totalRevenue = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $costPrice = (float) ($item['unit_cost_price'] ?? 0);
            $deprMonths = (float) ($item['depreciation_months'] ?? 1);
            $markup = (float) ($item['markup_percentage'] ?? 0);

            if ($deprMonths <= 0) {
                $deprMonths = 1;
            }

            $monthlyCost = ($costPrice / $deprMonths) * $qty;
            $monthlySale = $monthlyCost * (1 + ($markup / 100));

            $totalDirectCost += $monthlyCost;
            $totalRevenue += $monthlySale;
        }

        $set('direct_cost', $totalDirectCost);
        $set('revenue_per_month', $totalRevenue);

        // Advanced Financial Tiers
        $mgmtExpenseRate = (float) ($get('management_expense_rate') ?? 3.0);
        $interestRate = (float) ($get('interest_rate') ?? 1.5);
        $taxRate = (float) ($get('tax_rate') ?? 22.0);

        $mgmtExpense = $totalRevenue * ($mgmtExpenseRate / 100);
        $ebitda = ($totalRevenue - $totalDirectCost) - $mgmtExpense;

        // In this project model, direct_cost is already the monthly depreciation of used assets
        $ebit = $ebitda;

        $interest = $totalDirectCost * ($interestRate / 100);
        $ebt = $ebit - $interest;

        $tax = $ebt > 0 ? ($ebt * ($taxRate / 100)) : 0;
        $netProfit = $ebt - $tax;

        $set('ebitda', $ebitda);
        $set('ebit', $ebit);
        $set('ebt', $ebt);
        $set('net_profit', $netProfit);

        // Recalculate margin (GP Margin)
        self::calculateMargin($totalRevenue, $totalDirectCost, $set);
    }

    public static function calculateItemMonthlyCost(Get $get): float
    {
        $qty = (float) ($get('quantity') ?? 0);
        $costPrice = (float) ($get('unit_cost_price') ?? 0);
        $deprMonths = (float) ($get('depreciation_months') ?? 1);

        if ($deprMonths <= 0) {
            $deprMonths = 1;
        }

        return ($costPrice / $deprMonths) * $qty;
    }

    public static function calculateItemMonthlySale(Get $get): float
    {
        $monthlyCost = self::calculateItemMonthlyCost($get);
        $markup = (float) ($get('markup_percentage') ?? 0);

        return $monthlyCost * (1 + ($markup / 100));
    }

    protected static function calculateMargin($revenue, $cost, $set): void
    {
        $revenue = (float) ($revenue ?? 0);
        $cost = (float) ($cost ?? 0);

        if ($revenue > 0) {
            $margin = (($revenue - $cost) / $revenue) * 100;
            $set('margin_percentage', round($margin, 2));
        } else {
            $set('margin_percentage', 0);
        }
    }
}
