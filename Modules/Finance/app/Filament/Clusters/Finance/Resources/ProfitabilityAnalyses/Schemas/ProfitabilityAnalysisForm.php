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
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Taxes\Schemas\TaxForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
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
                    ->live()
                    ->disabled()
                    ->dehydrated(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->helperText('Customer associated with the project.')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled()
                    ->dehydrated()
                    ->createOptionForm(CustomerForm::schema()),
                TextInput::make('document_number')
                    ->label('Document Number')
                    ->hiddenOn('create'),

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
                            ->disabled()
                            ->dehydrated()
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
                                TextInput::make('duration_months')
                                    ->label('Duration (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('unit_cost_price')
                                    ->label('Base Price')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('depreciation_months')
                                    ->label('Asset Depr (Mo)')
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
                                
                                // Dynamic Cost Breakdown
                                Repeater::make('cost_breakdown')
                                    ->label('Additional Costs (Allowances, Taxes, etc.)')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Component Name')
                                            ->placeholder('e.g., BPJS, Transport')
                                            ->required(),
                                        Select::make('type')
                                            ->options([
                                                'nominal' => 'Nominal (Rp)',
                                                'percentage' => 'Percentage (%)',
                                            ])
                                            ->default('nominal')
                                            ->live()
                                            ->required(),
                                        Select::make('calculate_from')
                                            ->label('Base On')
                                            ->options([
                                                'unit_cost_price' => 'Base Price',
                                            ])
                                            ->default('unit_cost_price')
                                            ->visible(fn (Get $get) => $get('type') === 'percentage')
                                            ->live(),
                                        TextInput::make('value')
                                            ->label('Amount/Rate')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->columnSpan(1),

                                        Repeater::make('details')
                                            ->label('Breakdown Details')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Sub-Component')
                                                    ->required(),
                                                TextInput::make('value')
                                                    ->label('Amount')
                                                    ->numeric()
                                                    ->required()
                                                    ->live(onBlur: true),
                                            ])
                                            ->columns(2)
                                            ->columnSpanFull()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                // Auto-sum details to parent value
                                                $sum = collect($state ?? [])->sum('value');
                                                $set('value', $sum);
                                                
                                                // Trigger main calculation
                                                self::calculateDirectCost($get, $set);
                                            }),
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),

                                TextInput::make('total_monthly_cost')
                                    ->label('Total Cost/Mo')
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
                            ->columns(6)
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
            $durationMonths = (float) ($item['duration_months'] ?? 1); // Get duration
            $markup = (float) ($item['markup_percentage'] ?? 0);
            $costBreakdown = $item['cost_breakdown'] ?? []; // Get Addons

            if ($deprMonths <= 0) {
                $deprMonths = 1;
            }

            // Calculate Add-ons
            $addOnTotal = 0;
            foreach ($costBreakdown as $addon) {
                $val = (float) ($addon['value'] ?? 0);
                
                // If details exist, use their sum (though UI should have already updated 'value', we double check/fallback if needed)
                if (!empty($addon['details'])) {
                   $val = collect($addon['details'])->sum('value');
                }

                $type = $addon['type'] ?? 'nominal';
                
                if ($type === 'percentage') {
                    // Logic: Base on Unit Cost Price by default
                    $addOnTotal += $costPrice * ($val / 100);
                } else {
                    $addOnTotal += $val;
                }
            }

            // Total Monthly Cost = (Base/Depr + Addons) * Qty
            // Note: Addons are usually "Per Month" costs (like Allowance), unless specified otherwise.
            // Assuming Addons are recurring monthly costs.
            $monthlyUnitCost = ($costPrice / $deprMonths) + $addOnTotal;
            $monthlyCost = $monthlyUnitCost * $qty;
            
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
        $costBreakdown = $get('cost_breakdown') ?? [];

        if ($deprMonths <= 0) {
            $deprMonths = 1;
        }

        $addOnTotal = 0;
        foreach ($costBreakdown as $addon) {
            $val = (float) ($addon['value'] ?? 0);
             // If details exist, use their sum
            if (!empty($addon['details'])) {
                $val = collect($addon['details'])->sum('value');
            }

            $type = $addon['type'] ?? 'nominal';
            
            if ($type === 'percentage') {
                $addOnTotal += $costPrice * ($val / 100);
            } else {
                $addOnTotal += $val;
            }
        }

        return (($costPrice / $deprMonths) + $addOnTotal) * $qty;
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
