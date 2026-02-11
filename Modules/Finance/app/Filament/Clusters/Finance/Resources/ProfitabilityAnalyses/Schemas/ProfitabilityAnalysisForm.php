<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Modules\Finance\Enums\AssetOwnership;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Taxes\Schemas\TaxForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ManpowerTemplate;

class ProfitabilityAnalysisForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::schema());
    }

    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make('Project Identification')
                    ->description('Identify the RR submission and customer.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
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
                                    ->createOptionForm(CustomerForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->editOptionForm(CustomerForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            ]),
                        TextInput::make('document_number')
                            ->label('Document Number')
                            ->hidden(fn ($record) => ! ($record instanceof ProfitabilityAnalysis))
                            ->columnSpanFull(),

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
                            ])->compact(),
                    ]),

                Step::make('Parameters & Assets')
                    ->description('Configure project scopes and asset ownership.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('work_scheme_id')
                                    ->relationship('workScheme', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled()
                                    ->dehydrated()
                                    ->createOptionForm(WorkSchemeForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('product_cluster_id')
                                    ->relationship('productCluster', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(ProductClusterForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('tax_id')
                                    ->relationship('tax', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(TaxForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('project_area_id')
                                    ->relationship('projectArea', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->createOptionForm(ProjectAreaForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                            ]),
                        Select::make('asset_ownership')
                            ->options(AssetOwnership::class)
                            ->default(AssetOwnership::GdpsOwned)
                            ->required()
                            ->native(false),
                    ]),

                Step::make('Financial Assumptions')
                    ->description('Set external rates and overhead expectations.')
                    ->schema([
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
                    ]),

                Step::make('Costing Details')
                    ->description('Specify manpower, items, and additional costs.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('costable_type')
                                    ->label('Cost Type')
                                    ->options([
                                        Item::class => 'Item (General)',
                                        JobPosition::class => 'Job Position (Manpower)',
                                        ManpowerTemplate::class => 'Manpower Template (Packet)',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('costable_id', null))
                                    ->columnSpan(1),
                                Select::make('costable_id')
                                    ->label('Resource')
                                    ->options(fn (Get $get) => filled($get('costable_type')) ? $get('costable_type')::pluck('name', 'id') : [])
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, Set $set) {
                                        if (! $state || ! $get('costable_type')) {
                                            return;
                                        }

                                        $type = $get('costable_type');
                                        $record = $type::find($state);

                                        if (! $record) {
                                            return;
                                        }

                                        if ($type === Item::class) {
                                            $set('unit_cost_price', $record->price);
                                            $set('unit_of_measure', $record->unitOfMeasure?->name ?? 'Unit');
                                            $isManpower = $record->category?->name === 'Manpower';
                                            $set('is_manpower', $isManpower);

                                            // Depreciation Logic
                                            $depreciation = $record->depreciation_months;
                                            if (empty($depreciation) || $depreciation <= 0) {
                                                $usefulLifeYears = $record->category?->assetGroup?->useful_life_years;
                                                if ($usefulLifeYears && $usefulLifeYears > 0) {
                                                    $depreciation = $usefulLifeYears * 12;
                                                }
                                            }
                                            $set('depreciation_months', $depreciation ?? 1);
                                        }

                                        if ($type === JobPosition::class) {
                                            $set('unit_cost_price', $record->basic_salary);
                                            $set('unit_of_measure', 'Person');
                                            $set('is_manpower', true);
                                            $set('risk_level', $record->risk_level);
                                            $set('is_labor_intensive', $record->is_labor_intensive);
                                            $set('depreciation_months', 1);

                                            // Sync Remuneration Components to cost_breakdown
                                            $breakdown = [];
                                            foreach ($record->remunerationComponents ?? [] as $component) {
                                                $breakdown[] = [
                                                    'name' => $component->name,
                                                    'type' => 'nominal',
                                                    'value' => $component->pivot->amount,
                                                    'is_fixed' => $component->is_fixed,
                                                ];
                                            }
                                            $set('cost_breakdown', $breakdown);
                                        }

                                        if ($type === ManpowerTemplate::class) {
                                            $service = app(ManpowerCostingService::class);
                                            $areaId = $record->project_area_id;
                                            $year = (int) ($get('../../year') ?? $get('/year') ?? date('Y'));

                                            $totalPacketCost = 0;
                                            foreach ($record->items as $item) {
                                                $jp = $item->jobPosition;
                                                if (! $jp) {
                                                    continue;
                                                }

                                                $allowances = [];
                                                foreach ($jp->remunerationComponents ?? [] as $component) {
                                                    $allowances[] = [
                                                        'name' => $component->name,
                                                        'type' => 'nominal',
                                                        'value' => $component->pivot->amount,
                                                        'is_fixed' => $component->is_fixed,
                                                    ];
                                                }

                                                $res = $service->calculate(
                                                    basicSalary: $jp->basic_salary,
                                                    allowances: $allowances,
                                                    projectAreaId: $areaId,
                                                    year: $year,
                                                    riskLevel: $jp->risk_level ?? 'very_low',
                                                    isLaborIntensive: $jp->is_labor_intensive ?? false
                                                );

                                                $totalPacketCost += ($res['total_direct_cost'] * $item->quantity);
                                            }

                                            $set('unit_cost_price', $totalPacketCost);
                                            $set('unit_of_measure', 'Packet');
                                            $set('is_manpower', false); // Treated as Fixed Cost
                                            $set('risk_level', 'very_low');
                                            $set('depreciation_months', 1);
                                            $set('cost_breakdown', []);
                                        }

                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(3),
                                Hidden::make('is_manpower'),
                                Select::make('risk_level')
                                    ->options(RiskLevel::class)
                                    ->default(RiskLevel::VeryLow)
                                    ->visible(fn (Get $get) => $get('is_manpower'))
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->columnSpan(1),
                                Toggle::make('is_labor_intensive')
                                    ->label('Labor')
                                    ->visible(fn (Get $get) => $get('is_manpower'))
                                    ->live()
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->columnSpan(1),
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
                                    ->label('Dur (Mo)')
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
                                    ->label('Depr (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                TextInput::make('markup_percentage')
                                    ->label('Markup %')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),

                                // Dynamic Cost Breakdown
                                Repeater::make('cost_breakdown')
                                    ->label('Additional Costs')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Component Name')
                                            ->required(),
                                        Select::make('type')
                                            ->options([
                                                'nominal' => 'Nominal (Rp)',
                                                'percentage' => 'Percentage (%)',
                                            ])
                                            ->default('nominal')
                                            ->live()
                                            ->required(),
                                        TextInput::make('value')
                                            ->label('Amount/Rate')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true),
                                        Hidden::make('is_fixed')->default(true),

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
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),

                                TextInput::make('total_monthly_cost')
                                    ->label('Total Cost')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->placeholder(fn (Get $get) => self::calculateItemMonthlyCost($get))
                                    ->columnSpan(1),
                                TextInput::make('total_monthly_sale')
                                    ->label('Selling')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->placeholder(fn (Get $get) => self::calculateItemMonthlySale($get))
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => filled($state['costable_type'] ?? null) && filled($state['costable_id'] ?? null) ? $state['costable_type']::find($state['costable_id'])?->name : 'New Item')
                            ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                    ]),

                Step::make('Financial Review')
                    ->description('Review monthly revenue, costs, and profit.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('management_fee')
                                    ->label('Management Fee (Flat)')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('margin_percentage')
                                    ->label('Gross Profit Margin')
                                    ->numeric()
                                    ->suffix('%')
                                    ->readOnly()
                                    ->placeholder('Auto'),
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

                        Section::make('KPI Summary')
                            ->schema([
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
                            ])->compact(),
                    ]),
            ])->columnSpanFull()->persistStepInQueryString(),
        ];
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

            // Manpower Costing Logic
            $isManpower = ($item['is_manpower'] ?? false);
            if (! $isManpower && ! empty($item['costable_type']) && ! empty($item['costable_id'])) {
                if ($item['costable_type'] === Item::class) {
                    $dbItem = Item::find($item['costable_id']);
                    $isManpower = $dbItem?->category?->name === 'Manpower';
                } elseif ($item['costable_type'] === JobPosition::class) {
                    $isManpower = true;
                }
            }

            if ($isManpower) {
                $service = app(ManpowerCostingService::class);
                $result = $service->calculate(
                    basicSalary: $costPrice,
                    allowances: $item['cost_breakdown'] ?? [],
                    projectAreaId: (string) ($get('/project_area_id') ?? $get('project_area_id')),
                    year: (int) ($get('/year') ?? $get('year') ?? date('Y')),
                    riskLevel: $item['risk_level'] ?? 'very_low',
                    isLaborIntensive: $item['is_labor_intensive'] ?? false
                );

                $monthlyUnitCost = $result['total_direct_cost'];
                $monthlyCost = $monthlyUnitCost * $qty;

                // Optional: sync back certain calculated fields if needed
            } else {
                // Calculate Add-ons (Material/Equipment)
                $addOnTotal = 0;
                foreach ($costBreakdown as $addon) {
                    $val = (float) ($addon['value'] ?? 0);
                    // ... rest of existing addon logic ...
                    if (! empty($addon['details'])) {
                        $val = collect($addon['details'])->sum('value');
                    }
                    $type = $addon['type'] ?? 'nominal';
                    if ($type === 'percentage') {
                        $addOnTotal += $costPrice * ($val / 100);
                    } else {
                        $addOnTotal += $val;
                    }
                }

                // Total Monthly Cost = (Base/Depr + Addons) * Qty
                $monthlyUnitCost = ($costPrice / $deprMonths) + $addOnTotal;
                $monthlyCost = $monthlyUnitCost * $qty;
            }

            $monthlySale = $monthlyCost * (1 + ($markup / 100));

            $totalDirectCost += $monthlyCost;
            $totalRevenue += $monthlySale;
        }

        $set('direct_cost', $totalDirectCost);
        $set('revenue_per_month', $totalRevenue);

        // Advanced Financial Tiers
        $mgmtExpenseRate = (float) ($get('/management_expense_rate') ?? $get('management_expense_rate') ?? 3.0);
        $interestRate = (float) ($get('/interest_rate') ?? $get('interest_rate') ?? 1.5);
        $taxRate = (float) ($get('/tax_rate') ?? $get('tax_rate') ?? 22.0);

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

        $isManpower = $get('is_manpower');
        if (! $isManpower && $get('costable_type') && $get('costable_id')) {
            if ($get('costable_type') === Item::class) {
                $dbItem = Item::find($get('costable_id'));
                $isManpower = $dbItem?->category?->name === 'Manpower';
            } elseif ($get('costable_type') === JobPosition::class) {
                $isManpower = true;
            }
        }

        if ($isManpower) {
            $service = app(ManpowerCostingService::class);
            $result = $service->calculate(
                basicSalary: $costPrice,
                allowances: $costBreakdown,
                projectAreaId: (string) ($get('/project_area_id') ?? $get('../../project_area_id')),
                year: (int) ($get('/year') ?? $get('../../year') ?? date('Y')),
                riskLevel: $get('risk_level') ?? 'very_low',
                isLaborIntensive: (bool) $get('is_labor_intensive')
            );

            return $result['total_direct_cost'] * $qty;
        }

        if ($deprMonths <= 0) {
            $deprMonths = 1;
        }

        $addOnTotal = 0;
        foreach ($costBreakdown as $addon) {
            $val = (float) ($addon['value'] ?? 0);
            // If details exist, use their sum
            if (! empty($addon['details'])) {
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
