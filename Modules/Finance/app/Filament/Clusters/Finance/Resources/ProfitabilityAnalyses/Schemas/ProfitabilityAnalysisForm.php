<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\Finance\Enums\AssetOwnership;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;

class ProfitabilityAnalysisForm
{
    protected static array $modelCache = [];

    protected static function getCachedModel(string $modelClass, mixed $id): ?object
    {
        if (! $id) {
            return null;
        }
        $cacheKey = "{$modelClass}-{$id}";
        if (! isset(self::$modelCache[$cacheKey])) {
            self::$modelCache[$cacheKey] = $modelClass::find($id);
        }

        return self::$modelCache[$cacheKey];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::schema());
    }

    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make('Project Identification')
                    ->label('Project Identification')
                    ->description('Identify RR submission and associated customer.')
                    ->icon('heroicon-m-identification')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('general_information_id')
                                    ->relationship('generalInformation', 'document_number')
                                    ->label('GI Form (RR Submission)')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Select GI Form / RR Submission')
                                    ->helperText('Select the General Information (RR) submission as the PA data basis.')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            return;
                                        }
                                        $gi = GeneralInformation::with('lead')->find($state);
                                        if (! $gi) {
                                            return;
                                        }
                                        $set('lead_id', $gi->lead_id);
                                        $set('customer_id', $gi->customer_id ?? $gi->lead?->customer_id);
                                        $set('work_scheme_id', $gi->work_scheme_id ?? $gi->lead?->work_scheme_id);
                                        $set('project_area_id', $gi->project_area_id ?? $gi->lead?->project_area_id);
                                        $set('product_cluster_id', $gi->product_cluster_id ?? $gi->lead?->product_cluster_id);
                                        $set('tax_id', $gi->tax_id ?? $gi->lead?->tax_id);

                                        if ($gi->estimated_start_date) {
                                            $set('year', $gi->estimated_start_date->year);
                                        }
                                    })
                                    ->dehydrated()
                                    ->columnSpan(1),
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->label('Customer')
                                    ->helperText('Customer associated with the project.')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Select customer')
                                    ->helperText('The customer or employer entity.')
                                    ->columnSpan(1)
                                    ->createOptionForm(CustomerForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->editOptionForm(CustomerForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                                TextInput::make('document_number')
                                    ->label('Document Number')
                                    ->disabled()
                                    ->placeholder('Auto-generated')
                                    ->columnSpan(1),
                                Hidden::make('lead_id'),
                            ]),

                        Section::make('Project Documents')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('tor')
                                            ->collection('tor')

                                            ->label('ToR Document')
                                            ->hint('Terms of Reference'),
                                        SpatieMediaLibraryFileUpload::make('rfp')
                                            ->collection('rfp')

                                            ->label('RFP Document')
                                            ->hint('Request for Proposal'),
                                        SpatieMediaLibraryFileUpload::make('rfi')
                                            ->collection('rfi')
                                            ->label('RFI Document')
                                            ->hint('Request for Information'),
                                    ]),
                            ])->compact(),

                    ]),

                Step::make('Parameters & Assets')
                    ->label('Operational Parameters')
                    ->description('Configure project scope, work scheme, area, and asset ownership.')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('work_scheme_id')
                                    ->relationship('workScheme', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->dehydrated()
                                    ->placeholder('Select work scheme')
                                    ->helperText('Procurement method (e.g., Direct Appointment, Tender).')
                                    ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->work_scheme_id : null)
                                    ->createOptionForm(WorkSchemeForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('product_cluster_id')
                                    ->relationship('productCluster', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->dehydrated()
                                    ->placeholder('Select product cluster')
                                    ->helperText('Categorization of the main project services.')
                                    ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->product_cluster_id : null)
                                    ->createOptionForm(ProductClusterForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('project_area_id')
                                    ->relationship('projectArea', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Select project area')
                                    ->helperText('Main project location (affects minimum wage references).')
                                    ->default(fn ($livewire) => $livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->project_area_id : null)
                                    ->createOptionForm(ProjectAreaForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver()),
                                TextInput::make('year')
                                    ->label('Year')
                                    ->numeric()
                                    ->required()
                                    ->default(now()->year)
                                    ->placeholder(now()->year)
                                    ->helperText('Budget year for minimum wage references.')
                                    ->live(onBlur: true),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('asset_ownership')
                                    ->options(AssetOwnership::class)
                                    ->default(AssetOwnership::GdpsOwned)
                                    ->required()
                                    ->placeholder('Select asset ownership')
                                    ->helperText('Determines the asset depreciation calculation model.')
                                    ->native(false),
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('require_manpower_costing')
                                            ->label('Require Manpower Costing')
                                            ->default(true)
                                            ->live(),
                                        Toggle::make('require_operational_costing')
                                            ->label('Require Operational Costing')
                                            ->default(true)
                                            ->live(),
                                    ])->columnSpan(1),
                            ]),
                    ]),

                Step::make('Financial Assumptions')
                    ->label('Financial Assumptions')
                    ->description('Set expectations for overhead costs, interest, and company tax.')
                    ->icon('heroicon-m-banknotes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('management_expense_rate')
                                    ->label('Mgmt Expense (%)')
                                    ->numeric()
                                    ->default(2.50)
                                    ->placeholder('2.50')
                                    ->helperText('Central management overhead costs.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('management_fee_rate')
                                    ->label('Mgmt Fee / Target GPM (%)')
                                    ->numeric()
                                    ->default(fn (Get $get, $livewire) => $get('/management_fee_rate') ?? ($livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->lead?->salesPlan?->management_fee_percentage : 0) ?? 15.00)
                                    ->placeholder('15.00')
                                    ->helperText('Project Target Gross Profit Margin (Fee).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                Select::make('payment_term_id')
                                    ->relationship('paymentTerm', 'name')
                                    ->label('Payment Term (TOP)')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->default(fn (Get $get, $livewire) => $get('/payment_term_id') ?? ($livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->lead?->salesPlan?->payment_term_id : null))
                                    ->live(onBlur: true)
                                    ->placeholder('Select payment term')
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('interest_rate')
                                    ->label('Interest Rate (%)')
                                    ->numeric()
                                    ->default(1.50)
                                    ->placeholder('1.50')
                                    ->helperText('Estimated interest cost (Cost of Money).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('tax_rate')
                                    ->label('Corp Tax Rate (%)')
                                    ->numeric()
                                    ->default(22.00)
                                    ->placeholder('22.00')
                                    ->helperText('Corporate Income Tax (PPh) rate.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                            ]),
                    ]),

                Step::make('Manpower Requirements')
                    ->label('Manpower Planning')
                    ->description('Determine personnel needs based on job positions or manpower packets.')
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        Repeater::make('manpowerItems')
                            ->relationship('manpowerItems')
                            ->label('Personnel & Job Positions')
                            ->required(fn (Get $get) => $get('require_manpower_costing'))
                            ->minItems(fn (Get $get) => $get('require_manpower_costing') ? 1 : 0)
                            ->schema([
                                Select::make('costable_type')
                                    ->label('Type')
                                    ->options([
                                        JobPosition::class => 'Job Position',
                                        ManpowerTemplate::class => 'Manpower Template',
                                    ])
                                    ->required()
                                    ->live(onBlur: true)
                                    ->placeholder('Select resource type')
                                    ->afterStateUpdated(fn (Set $set) => $set('costable_id', null))
                                    ->columnSpan(1),
                                Select::make('costable_id')
                                    ->label('Resource')
                                    ->options(fn (Get $get) => filled($get('costable_type')) ? $get('costable_type')::pluck('name', 'id') : [])
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, Set $set) {
                                        if (! $state || ! $get('costable_type')) {
                                            return;
                                        }

                                        $type = $get('costable_type');
                                        $record = $type::find($state);

                                        if (! $record) {
                                            return;
                                        }

                                        if ($type === JobPosition::class) {
                                            $set('unit_cost_price', 0);
                                            $set('unit_of_measure', 'Person');
                                            $set('is_manpower', true);
                                            $set('risk_level', $record->risk_level);
                                            $set('is_labor_intensive', $record->is_labor_intensive);
                                            $set('depreciation_months', 1);

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

                                            $totalPacketCost = 0.0;
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
                                                        'value' => (float) $component->pivot->amount,
                                                        'is_fixed' => $component->is_fixed,
                                                    ];
                                                }

                                                $res = $service->calculate(
                                                    basicSalary: (float) $item->basic_salary,
                                                    allowances: $allowances,
                                                    projectAreaId: $areaId,
                                                    year: $year,
                                                    riskLevel: $jp->risk_level ?? 'very_low',
                                                    isLaborIntensive: $jp->is_labor_intensive ?? false
                                                );

                                                $totalPacketCost += ((float) $res['total_direct_cost'] * (float) $item->quantity);
                                            }

                                            $set('unit_cost_price', $totalPacketCost);
                                            $set('unit_of_measure', 'Packet');
                                            $set('is_manpower', false);
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
                                    ->visible(fn (Get $get) => $get('costable_type') === JobPosition::class)
                                    ->live(onBlur: true)
                                    ->placeholder('Select risk level')
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),
                                Toggle::make('is_labor_intensive')
                                    ->label('Labor')
                                    ->visible(fn (Get $get) => $get('costable_type') === JobPosition::class)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->placeholder('1')
                                    ->helperText('Number of personnel required.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
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
                                    ->placeholder('1')
                                    ->helperText('Assignment duration (in months).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('unit_cost_price')
                                    ->label('Base Price')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->required()
                                    ->placeholder('0')
                                    ->helperText('Base price per person per month.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('depreciation_months')->visible(fn (Get $get) => $get('costable_type') !== ManpowerTemplate::class)
                                    ->label('Depr (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('markup_percentage')->visible(fn (Get $get) => $get('costable_type') === JobPosition::class)
                                    ->label('Markup %')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),

                                Repeater::make('cost_breakdown')
                                    ->label('Additional Allowances')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Component Name')
                                            ->required()
                                            ->placeholder('e.g. Meal Allowance, Transport'),
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
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix(fn (Get $get) => $get('type') === 'nominal' ? 'IDR ' : null)
                                            ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : null)
                                            ->required()
                                            ->live(onBlur: true),
                                        Hidden::make('is_fixed')->default(true),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    }),

                                TextInput::make('total_monthly_cost')
                                    ->label('Total Cost')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->placeholder(fn (Get $get) => number_format(self::calculateItemMonthlyCost($get), 0, ',', '.'))
                                    ->helperText('Total monthly expenditure (Direct Cost).')
                                    ->columnSpan(3),
                                TextInput::make('total_monthly_sale')
                                    ->label('Selling Price')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->placeholder(fn (Get $get) => number_format(self::calculateItemMonthlySale($get), 0, ',', '.'))
                                    ->helperText('Total monthly selling price (Selling Price).')
                                    ->columnSpan(3),
                            ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => filled($state['costable_type'] ?? null) && filled($state['costable_id'] ?? null) ? self::getCachedModel($state['costable_type'], $state['costable_id'])?->name : 'New Personnel')
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateItemTotals($get, $set);
                                self::calculateDirectCost($get, $set);
                            }),
                    ]),

                Step::make('Operational & Equipment Costs')
                    ->label('Operational & Equipment Costs')
                    ->description('Determine material, equipment, services, and other cost requirements.')
                    ->icon('heroicon-m-shopping-cart')
                    ->schema([
                        Repeater::make('operationalItems')
                            ->relationship('operationalItems')
                            ->label('Equipment & Material Items')
                            ->required(fn (Get $get) => $get('require_operational_costing'))
                            ->minItems(fn (Get $get) => $get('require_operational_costing') ? 1 : 0)
                            ->schema([
                                Select::make('costable_type')
                                    ->label('Type')
                                    ->options([
                                        Item::class => 'Standard Item',
                                        CostingTemplate::class => 'Costing Template',
                                    ])
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set) => $set('costable_id', null))
                                    ->columnSpan(1),
                                Select::make('costable_id')
                                    ->label('Resource')
                                    ->options(fn (Get $get) => filled($get('costable_type')) ? $get('costable_type')::pluck('name', 'id') : [])
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(onBlur: true)
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
                                            $set('is_manpower', false);

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

                                        if ($type === CostingTemplate::class) {
                                            $set('unit_cost_price', $record->getTotalMonthlyCost());
                                            $set('unit_of_measure', 'Packet');
                                            $set('is_manpower', false);
                                            $set('depreciation_months', 1); // Costing template total is already monthly
                                        }

                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(3),
                                Hidden::make('is_manpower')->default(false),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->placeholder('1')
                                    ->helperText('Quantity of goods or services.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
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
                                    ->placeholder('1')
                                    ->helperText('Usage duration (in months).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('unit_cost_price')
                                    ->label('Base Price')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->required()
                                    ->placeholder('0')
                                    ->helperText('Unit price of goods or services.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(2),
                                TextInput::make('depreciation_months')->visible(fn (Get $get) => $get('costable_type') !== CostingTemplate::class)
                                    ->label('Depr (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('markup_percentage')->visible(fn (Get $get) => $get('costable_type') === Item::class)
                                    ->label('Markup %')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    })
                                    ->columnSpan(1),

                                Repeater::make('cost_breakdown')
                                    ->label('Add-ons (e.g. Shipping, Setup)')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Description')
                                            ->required()
                                            ->placeholder('e.g. Shipping, Installation'),
                                        Select::make('type')
                                            ->options([
                                                'nominal' => 'Nominal (Rp)',
                                                'percentage' => 'Percentage (%)',
                                            ])
                                            ->default('nominal')
                                            ->live(onBlur: true)
                                            ->required(),
                                        TextInput::make('value')
                                            ->label('Amount/Rate')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix(fn (Get $get) => $get('type') === 'nominal' ? 'IDR ' : null)
                                            ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : null)
                                            ->required()
                                            ->live(onBlur: true),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                        self::calculateDirectCost($get, $set);
                                    }),

                                TextInput::make('total_monthly_cost')
                                    ->label('Total Cost')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->placeholder(fn (Get $get) => number_format((float) self::calculateItemMonthlyCost($get), 0, ',', '.'))
                                    ->columnSpan(3),
                                TextInput::make('total_monthly_sale')
                                    ->label('Selling Price')
                                    ->disabled()
                                    ->dehydrated()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->placeholder(fn (Get $get) => number_format((float) self::calculateItemMonthlySale($get), 0, ',', '.'))
                                    ->columnSpan(3),
                            ])
                            ->columns(6)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => filled($state['costable_type'] ?? null) && filled($state['costable_id'] ?? null) ? self::getCachedModel($state['costable_type'], $state['costable_id'])?->name : 'New Item')
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::updateItemTotals($get, $set);
                                self::calculateDirectCost($get, $set);
                            }),
                    ]),

                // Step::make('Financial Review')
                //     ->label('Financial Review')
                //     ->description('Review projected revenue, costs, and monthly net profit.')
                //     ->icon('heroicon-m-presentation-chart-line')
                //     ->schema([
                //         Section::make('Revenue & Fees')
                //             ->schema([
                //                 Grid::make(2)
                //                     ->schema([
                //                         TextInput::make('management_fee')
                //                             ->label('Management Fee (Flat)')
                //                             ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                //                             ->prefix('IDR ')
                //                             ->default(0)
                //                             ->live(onBlur: true)
                //                             ->afterStateUpdated(fn ($get, $set) => self::calculateDirectCost($get, $set)),
                //                         TextEntry::make('margin_percentage')
                //                             ->label('Target GP Margin')
                //                             ->state(fn (Get $get) => (float) ($get('margin_percentage') ?? 0))
                //                             ->suffix('%')
                //                             ->color(fn ($state) => $state >= 30 ? 'success' : ($state >= 15 ? 'warning' : 'danger'))
                //                             ->weight(FontWeight::Bold),
                //                     ]),
                //             ])->compact(),

                //         Section::make('Project Totals & Monthly Avg')
                //             ->schema([
                //                 Grid::make(2)
                //                     ->schema([
                //                         TextEntry::make('total_project_revenue')
                //                             ->label('Total Project Revenue')
                //                             ->state(fn (Get $get) => (float) ($get('total_project_revenue') ?? 0))
                //                             ->money('IDR'),
                //                         TextEntry::make('total_project_cost')
                //                             ->label('Total Project Cost')
                //                             ->state(fn (Get $get) => (float) ($get('total_project_cost') ?? 0))
                //                             ->money('IDR'),
                //                         TextEntry::make('revenue_per_month')
                //                             ->label('Avg. Revenue/Mo')
                //                             ->state(fn (Get $get) => (float) ($get('revenue_per_month') ?? 0))
                //                             ->money('IDR'),
                //                         TextEntry::make('direct_cost')
                //                             ->label('Avg. Direct Cost/Mo')
                //                             ->state(fn (Get $get) => (float) ($get('direct_cost') ?? 0))
                //                             ->money('IDR'),
                //                     ]),
                //             ])->compact(),

                //         Section::make('KPI Summary')
                //             ->schema([
                //                 Grid::make(5)
                //                     ->schema([
                //                         TextEntry::make('ebitda')
                //                             ->label('EBITDA')
                //                             ->state(fn (Get $get) => (float) ($get('ebitda') ?? 0))
                //                             ->money('IDR')
                //                             ->weight(FontWeight::Bold),
                //                         TextEntry::make('ebit')
                //                             ->label('EBIT')
                //                             ->state(fn (Get $get) => (float) ($get('ebit') ?? 0))
                //                             ->money('IDR'),
                //                         TextEntry::make('ebt')
                //                             ->label('EBT')
                //                             ->state(fn (Get $get) => (float) ($get('ebt') ?? 0))
                //                             ->money('IDR'),
                //                         TextEntry::make('net_profit')
                //                             ->label('Net Profit')
                //                             ->state(fn (Get $get) => (float) ($get('net_profit') ?? 0))
                //                             ->money('IDR')
                //                             ->color('success')
                //                             ->weight(FontWeight::Bold),
                //                         TextEntry::make('net_profit_margin')
                //                             ->label('Net Profit Margin')
                //                             ->state(fn (Get $get) => (float) ($get('net_profit_margin') ?? 0))
                //                             ->suffix('%')
                //                             ->weight(FontWeight::Bold),
                //                     ]),
                //             ])->compact(),
                //     ]),
            ])->columnSpanFull()->persistStepInQueryString(),
        ];
    }

    protected static function calculateDirectCost($get, $set): void
    {
        // 1. Calculate Project Duration
        $giId = $get('/general_information_id');
        $gi = self::getCachedModel(GeneralInformation::class, $giId);

        $projectDurationMonths = 1;
        if ($gi && $gi->estimated_start_date && $gi->estimated_end_date) {
            $days = $gi->estimated_start_date->diffInDays($gi->estimated_end_date);
            $projectDurationMonths = max(1, round($days / 30, 2));
        }

        $manpowerItems = $get('/manpowerItems') ?? [];
        $operationalItems = $get('/operationalItems') ?? [];
        $items = array_merge($manpowerItems, $operationalItems);

        $totalProjectCost = 0;
        $totalProjectRevenue = 0;
        $totalProjectDepreciation = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $costPrice = (float) ($item['unit_cost_price'] ?? 0);
            $deprMonths = (float) ($item['depreciation_months'] ?? 1);
            $durationMonths = (float) ($item['duration_months'] ?? $projectDurationMonths);
            $markup = (float) ($item['markup_percentage'] ?? 0);
            $costBreakdown = $item['cost_breakdown'] ?? [];

            if ($deprMonths <= 0) {
                $deprMonths = 1.0;
            }

            // Manpower Costing Logic
            $isManpower = ($item['is_manpower'] ?? false);
            if (! $isManpower && ! empty($item['costable_type']) && ! empty($item['costable_id'])) {
                if ($item['costable_type'] === Item::class) {
                    $dbItem = Item::find($item['costable_id']);
                    $isManpower = $dbItem?->category?->name === 'Manpower';
                } elseif ($item['costable_type'] === JobPosition::class) {
                    $isManpower = true;
                } elseif ($item['costable_type'] === CostingTemplate::class) {
                    $isManpower = false;
                }
            }

            $monthlyDepreciation = 0.0;
            if ($isManpower) {
                $service = app(ManpowerCostingService::class);
                $result = $service->calculate(
                    basicSalary: $costPrice,
                    allowances: $item['cost_breakdown'] ?? [],
                    projectAreaId: (string) ($get('/project_area_id')),
                    year: (int) ($get('/year') ?? date('Y')),
                    riskLevel: $item['risk_level'] ?? 'very_low',
                    isLaborIntensive: $item['is_labor_intensive'] ?? false
                );

                $monthlyUnitCost = (float) ($result['total_direct_cost'] ?? 0);
                $monthlyCost = $monthlyUnitCost * $qty;
            } else {
                $addOnTotal = 0.0;
                foreach ($costBreakdown as $addon) {
                    $val = 0.0;
                    if (! empty($addon['details'])) {
                        $val = (float) collect($addon['details'])->sum(fn ($detail) => (float) ($detail['value'] ?? 0));
                    } else {
                        $val = (float) ($addon['value'] ?? 0);
                    }
                    $type = $addon['type'] ?? 'nominal';
                    if ($type === 'percentage') {
                        $addOnTotal += $costPrice * ($val / 100);
                    } else {
                        $addOnTotal += $val;
                    }
                }

                $monthlyUnitDepreciation = ($costPrice / $deprMonths);
                $monthlyUnitCost = $monthlyUnitDepreciation + $addOnTotal;
                $monthlyCost = $monthlyUnitCost * $qty;
                $monthlyDepreciation = $monthlyUnitDepreciation * $qty;
            }

            $monthlySale = $monthlyCost * (1.0 + ($markup / 100));

            // Accumulate Project Totals
            $totalProjectCost += ($monthlyCost * $durationMonths);
            $totalProjectRevenue += ($monthlySale * $durationMonths);
            $totalProjectDepreciation += ($monthlyDepreciation * $durationMonths);
        }

        // Handle Management Fee from Rate
        $mgmtFeeRate = (float) ($get('/management_fee_rate') ?? 0);
        $avgMonthlyDirectCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : 0;

        if ($mgmtFeeRate > 0) {
            $calculatedMgmtFee = $avgMonthlyDirectCost * ($mgmtFeeRate / 100);
            $set('/management_fee', $calculatedMgmtFee);
            $mgmtFee = $calculatedMgmtFee;
        } else {
            $mgmtFee = (float) ($get('/management_fee') ?? 0);
        }

        // Add Management Fee to Revenue (Pro-rated monthly)
        $totalProjectRevenue += ($mgmtFee * $projectDurationMonths);

        $set('/total_project_cost', $totalProjectCost);
        $set('/total_project_revenue', $totalProjectRevenue);

        // Pro-rated values back to "Standard Monthly" for high-level summary
        $avgMonthlyRevenue = $projectDurationMonths > 0 ? ($totalProjectRevenue / $projectDurationMonths) : 0;
        $avgMonthlyCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : 0;
        $avgMonthlyDepreciation = $projectDurationMonths > 0 ? ($totalProjectDepreciation / $projectDurationMonths) : 0;

        $set('/direct_cost', $avgMonthlyCost);
        $set('/depreciation', $avgMonthlyDepreciation);
        $set('/revenue_per_month', $avgMonthlyRevenue);

        // Advanced Financial Tiers
        $mgmtExpenseRate = (float) ($get('/management_expense_rate') ?? $get('management_expense_rate') ?? 3.0);
        $interestRate = (float) ($get('/interest_rate') ?? $get('interest_rate') ?? 1.5);
        $taxRate = (float) ($get('/tax_rate') ?? $get('tax_rate') ?? 22.0);

        // EBITDA = Revenue - (Direct Cost Excl Depr) - Mgmt Expense
        $mgmtExpense = $avgMonthlyRevenue * ($mgmtExpenseRate / 100);
        $avgMonthlyCostExclDepr = $avgMonthlyCost - $avgMonthlyDepreciation;
        $ebitda = ($avgMonthlyRevenue - $avgMonthlyCostExclDepr) - $mgmtExpense;

        // EBIT = EBITDA - Depreciation
        $ebit = $ebitda - $avgMonthlyDepreciation;

        // Interest (Cost of Fund) = (TOP / 30 * InterestRate %) * Direct Cost
        $paymentTermId = $get('/payment_term_id');
        $paymentTerm = $paymentTermId ? PaymentTerm::find($paymentTermId) : null;
        $topDays = (float) ($paymentTerm?->days ?? 30);

        $interest = ($topDays / 30.0 * ($interestRate / 100)) * $avgMonthlyCost;

        $ebt = $ebit - $interest;

        $tax = $ebt > 0 ? ($ebt * ($taxRate / 100)) : 0;
        $netProfit = $ebt - $tax;
        $netProfitMargin = $avgMonthlyRevenue > 0 ? ($netProfit / $avgMonthlyRevenue) * 100 : 0;

        $set('/ebitda', $ebitda);
        $set('/ebit', $ebit);
        $set('/ebt', $ebt);
        $set('/net_profit', $netProfit);
        $set('/net_profit_margin', round($netProfitMargin, 2));

        // Recalculate margin (GP Margin)
        self::calculateMargin($avgMonthlyRevenue, $avgMonthlyCost, $set);
    }

    protected static function updateItemTotals(Get $get, Set $set): void
    {
        $set('total_monthly_cost', self::calculateItemMonthlyCost($get));
        $set('total_monthly_sale', self::calculateItemMonthlySale($get));
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
            } elseif ($get('costable_type') === CostingTemplate::class) {
                $isManpower = false;
            }
        }

        if ($isManpower) {
            $service = app(ManpowerCostingService::class);
            $result = $service->calculate(
                basicSalary: $costPrice,
                allowances: $costBreakdown,
                projectAreaId: (string) ($get('/project_area_id')),
                year: (int) ($get('/year') ?? date('Y')),
                riskLevel: $get('risk_level') ?? 'very_low',
                isLaborIntensive: (bool) $get('is_labor_intensive')
            );

            return (float) ($result['total_direct_cost'] ?? 0) * $qty;
        }

        if ($deprMonths <= 0) {
            $deprMonths = 1.0;
        }

        $addOnTotal = 0.0;
        foreach ($costBreakdown as $addon) {
            $val = 0.0;
            // If details exist, use their sum
            if (! empty($addon['details'])) {
                $val = (float) collect($addon['details'])->sum(fn ($detail) => (float) ($detail['value'] ?? 0));
            } else {
                $val = (float) ($addon['value'] ?? 0);
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

        return $monthlyCost * (1.0 + ($markup / 100));
    }

    protected static function calculateMargin($revenue, $cost, $set): void
    {
        $revenue = (float) ($revenue ?? 0);
        $cost = (float) ($cost ?? 0);

        if ($revenue > 0) {
            $margin = (($revenue - $cost) / $revenue) * 100;
            $set('/margin_percentage', round($margin, 2));
        } else {
            $set('/margin_percentage', 0);
        }
    }
}
