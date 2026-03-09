<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\HtmlString;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas\CustomerForm;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\Finance\Enums\AssetOwnership;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Enums\RiskLevel;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas\DirectCostCategoryForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\DirectCostCategory;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\PtkpConfig;

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

    protected static function parseNumericValue(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            // Remove thousand separators (.) and replace decimal separator (,) with (.)
            $cleanValue = str_replace(['.', ','], ['', '.'], $value);

            return (float) $cleanValue;
        }

        return 0.0;
    }

    public static function configure(Schema $schema, int|Closure $startStep = 1): Schema
    {
        return $schema->components(self::schema($startStep));
    }

    public static function schema(int|Closure $startStep = 1): array
    {
        return [
            Hidden::make('depreciation'),
            Hidden::make('management_fee'),

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
                                    ->label('GI Form')
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

                    ]),

                Step::make('Parameters & Assets')
                    ->label('Operational Parameters')
                    ->description('Configure project scope, work scheme, area, and asset ownership.')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->schema([
                        Grid::make(1)
                            ->schema([
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
                                            ->hidden(fn (Get $get) => (bool) $get('is_manual_cost'))
                                            ->live(),
                                        Toggle::make('require_operational_costing')
                                            ->label('Require Operational Costing')
                                            ->default(true)
                                            ->hidden(fn (Get $get) => (bool) $get('is_manual_cost'))
                                            ->live(),
                                        Toggle::make('is_manual_cost')
                                            ->label('Manual Cost Entry')
                                            ->default(false)
                                            ->helperText('Skip detail costing and enter totals manually.')
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if ($state) {
                                                    $set('require_manpower_costing', false);
                                                    $set('require_operational_costing', false);
                                                }
                                            }),
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
                                TextInput::make('interest_rate')
                                    ->label('Interest Rate (%)')
                                    ->numeric()
                                    ->default(1.50)
                                    ->placeholder('1.50')
                                    ->helperText('Estimasi biaya bunga atau biaya modal (Cost of Money).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('tax_rate')
                                    ->label('Corp Tax Rate (%)')
                                    ->numeric()
                                    ->default(22.00)
                                    ->placeholder('22.00')
                                    ->helperText('Tarif Pajak Penghasilan Badan (Corporate Income Tax).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                                TextInput::make('management_fee_rate')
                                    ->label('Mgmt Fee / Target GPM (%)')
                                    ->numeric()
                                    ->default(fn (Get $get, $livewire) => $get('/management_fee_rate') ?? ($livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->lead?->salesPlan?->management_fee_percentage : 0) ?? 15.00)
                                    ->placeholder('15.00')
                                    ->helperText('Target persentase margin laba kotor (Fee Proyek).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                                Select::make('payment_term_id')
                                    ->relationship('paymentTerm', 'name')
                                    ->label('Payment Term (TOP)')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->default(fn (Get $get, $livewire) => $get('/payment_term_id') ?? ($livewire instanceof ManageRelatedRecords ? $livewire->getOwnerRecord()->lead?->salesPlan?->payment_term_id : null))
                                    ->live(onBlur: true)
                                    ->placeholder('Select payment term')
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                            ]),
                    ]),

                Step::make('Manpower Requirements')
                    ->label('Manpower Planning')
                    ->description('Determine personnel needs based on job positions or manpower packets.')
                    ->icon('heroicon-m-user-group')
                    ->visible(fn (Get $get) => ! $get('is_manual_cost'))
                    ->schema([
                        Repeater::make('manpowerItems')
                            ->relationship('manpowerItems')
                            ->label('Personnel Requirements')
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
                                    ->placeholder('Pilih tipe sumber daya')
                                    ->helperText('Pilih antara Jabatan tunggal atau Paket Template.')
                                    ->afterStateUpdated(fn (Set $set) => $set('costable_id', null))
                                    ->columnSpan(1),
                                Select::make('direct_cost_category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->default(fn () => DirectCostCategory::where('code', 'manpower')->first()?->id)
                                    ->live(onBlur: true)
                                    ->columnSpan(1),
                                Select::make('costable_id')
                                    ->label('Resource')
                                    ->options(fn (Get $get) => filled($get('costable_type')) ? $get('costable_type')::pluck('name', 'id') : [])
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->placeholder('Pilih data Resource')
                                    ->helperText('Pilih Jabatan atau Template yang akan digunakan.')
                                    ->afterStateUpdated(function ($state, $get, Set $set) {
                                        if (! $state || ! $get('costable_type')) {
                                            return;
                                        }

                                        // Auto-set category to Man Power
                                        $set('direct_cost_category_id', DirectCostCategory::where('code', 'manpower')->first()?->id);

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
                                            foreach ($record->fixedAllowances ?? [] as $allowance) {
                                                $breakdown[] = [
                                                    'name' => $allowance->name,
                                                    'type' => 'nominal',
                                                    'value' => $allowance->pivot->amount,
                                                    'is_fixed' => true,
                                                ];
                                            }
                                            foreach ($record->nonFixedAllowances ?? [] as $allowance) {
                                                $breakdown[] = [
                                                    'name' => $allowance->name,
                                                    'type' => 'nominal',
                                                    'value' => $allowance->pivot->amount,
                                                    'is_fixed' => false,
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
                                                foreach ($jp->fixedAllowances ?? [] as $allowance) {
                                                    $allowances[] = [
                                                        'name' => $allowance->name,
                                                        'type' => 'nominal',
                                                        'value' => (float) $allowance->pivot->amount,
                                                        'is_fixed' => true,
                                                    ];
                                                }
                                                foreach ($jp->nonFixedAllowances ?? [] as $allowance) {
                                                    $allowances[] = [
                                                        'name' => $allowance->name,
                                                        'type' => 'nominal',
                                                        'value' => (float) $allowance->pivot->amount,
                                                        'is_fixed' => false,
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

                                    })
                                    ->columnSpan(3),
                                Hidden::make('is_manpower'),
                                Select::make('risk_level')
                                    ->options(RiskLevel::class)
                                    ->default(RiskLevel::VeryLow)
                                    ->visible(fn (Get $get) => $get('costable_type') === JobPosition::class)
                                    ->live(onBlur: true)
                                    ->placeholder('Pilih tingkat risiko')
                                    ->helperText('Menentukan tarif BPJS Ketenagakerjaan (JKK).')
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                    })
                                    ->columnSpan(1),
                                Toggle::make('is_labor_intensive')
                                    ->label('Labor')
                                    ->visible(fn (Get $get) => $get('costable_type') === JobPosition::class)
                                    ->helperText('Aktifkan jika pekerjaan padat karya (diskon 50% JKK).')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                    })
                                    ->columnSpan(1),
                                Select::make('ptkp_config_id')
                                    ->label('PTKP')
                                    ->relationship('ptkpConfig', 'code')
                                    ->visible(fn (Get $get) => $get('costable_type') === JobPosition::class)
                                    ->placeholder('Select PTKP')
                                    ->default(fn () => PtkpConfig::where('code', 'TK/0')->first()?->id)
                                    ->searchable()
                                    ->preload()
                                    ->live(onBlur: true)
                                    ->placeholder('Pilih status PTKP')
                                    ->helperText('Status Pajak untuk perhitungan metode TER.')
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->placeholder('Jumlah personil')
                                    ->helperText('Jumlah tenaga kerja yang dibutuhkan.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
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
                                    })
                                    ->columnSpan(1),
                                TextInput::make('depreciation_months')->visible(fn (Get $get) => $get('costable_type') !== ManpowerTemplate::class)
                                    ->label('Depr (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('markup_percentage')->visible(fn (Get $get) => $get('costable_type') === JobPosition::class)
                                    ->label('Markup %')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculateDirectCost($get, $set);
                            }),
                    ]),

                Step::make('Operational & Equipment Costs')
                    ->label('Operational & Equipment Costs')
                    ->description('Determine material, equipment, services, and other cost requirements.')
                    ->icon('heroicon-m-shopping-cart')
                    ->visible(fn (Get $get) => ! $get('is_manual_cost'))
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
                                Select::make('direct_cost_category_id')
                                    ->label('Category')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->default(fn () => DirectCostCategory::where('code', 'tools_equipment')->first()?->id)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
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

                                        // Auto-set category to Tools & Equipment
                                        $set('direct_cost_category_id', DirectCostCategory::where('code', 'tools_equipment')->first()?->id);

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

                                    })
                                    ->columnSpan(2),
                                Select::make('calculation_type')
                                    ->label('Calc Type')
                                    ->options([
                                        'nominal' => 'Nominal',
                                        'percentage' => 'Percentage',
                                    ])
                                    ->default('nominal')
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),
                                Select::make('percentage_basis')
                                    ->label('Basis')
                                    ->options([
                                        'revenue' => 'Total Revenue',
                                        'direct_cost' => 'Total Direct Cost',
                                    ])
                                    ->visible(fn (Get $get) => $get('calculation_type') === 'percentage')
                                    ->required(fn (Get $get) => $get('calculation_type') === 'percentage')
                                    ->live()
                                    ->columnSpan(1),
                                Hidden::make('is_manpower')->default(false),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->placeholder('1')
                                    ->helperText('Quantity of goods or services.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
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
                                    })
                                    ->columnSpan(2),
                                TextInput::make('depreciation_months')->visible(fn (Get $get) => $get('costable_type') !== CostingTemplate::class)
                                    ->label('Depr (Mo)')
                                    ->numeric()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('markup_percentage')->visible(fn (Get $get) => $get('costable_type') === Item::class)
                                    ->label('Markup %')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::updateItemTotals($get, $set);
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculateDirectCost($get, $set);
                            }),
                    ]),

                Step::make('Manual Costing')
                    ->label('Manual Cost Entry')
                    ->description('Enter high-level monthly direct costs and revenue.')
                    ->icon('heroicon-m-calculator')
                    ->visible(fn (Get $get) => (bool) $get('is_manual_cost'))
                    ->schema([
                        Section::make('Monthly Budgeting')
                            ->description('Provide estimated monthly totals for direct cost categories.')
                            ->schema([
                                TextInput::make('analysis_details.manual_revenue')
                                    ->label('Total Direct Cost Breakdown (Subtotal)')
                                    ->numeric()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->readOnly()
                                    ->helperText('Otomatis menghitung jumlah dari breakdown biaya di bawah.')
                                    ->columnSpan(2),
                                TextInput::make('manual_depreciation')
                                    ->label('Manual Depreciation')
                                    ->numeric()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('IDR ')
                                    ->default(0)
                                    ->helperText('Enter monthly depreciation amount manually.')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                    ->columnSpan(1),
                                TextEntry::make('lead_documents')
                                    ->label('Existing Lead/GI Documents')
                                    ->columnSpanFull()
                                    ->state(function ($get, $record) {
                                        $leadId = $get('lead_id') ?? $record?->lead_id;
                                        if (! $leadId) {
                                            return 'Pilih Lead terlebih dahulu.';
                                        }

                                        $lead = Lead::find($leadId);
                                        if (! $lead) {
                                            return 'Lead tidak ditemukan.';
                                        }

                                        $links = collect();

                                        // Standard Heroicons SVGs for consistent rendering in HtmlString
                                        $docIcon = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625Z" /><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z" /></svg>';
                                        $dupIcon = '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7.5 3.375c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v1.875C13.5 8.161 14.34 9 15.375 9h1.875A3.75 3.75 0 0 1 21 12.75v3.375C21 17.16 20.16 18 19.125 18h-9.75A1.875 1.875 0 0 1 7.5 16.125V3.375Z" /><path d="M15 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 17.25 7.5h-1.875A.375.375 0 0 1 15 7.125V5.25ZM4.875 6c-1.036 0-1.875.84-1.875 1.875v12.75c0 1.035.84 1.875 1.875 1.875h9.75c1.035 0 1.875-.84 1.875-1.875V17.25a.75.75 0 0 0-1.5 0v2.25c0 .207-.168.375-.375.375h-9.75a.375.375 0 0 1-.375-.375V7.875c0-.207.168-.375.375-.375H7.5a.75.75 0 0 0 0-1.5H4.875Z" /></svg>';

                                        // Get Lead Media (RFI, RFP, etc)
                                        $lead->getMedia('*')->each(function ($media) use ($links, $docIcon) {
                                            $url = $media->disk === 's3'
                                                ? $media->getTemporaryUrl(now()->addMinutes(30))
                                                : $media->getUrl();

                                            $links->push(sprintf(
                                                '<a href="%s" target="_blank" class="text-primary-600 hover:text-primary-500 transition-colors flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                                    %s
                                                    <span class="truncate">%s</span>
                                                    <span class="text-[10px] uppercase font-bold text-gray-400 bg-white dark:bg-gray-900 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700 ml-auto">%s</span>
                                                </a>',
                                                $url,
                                                $docIcon,
                                                $media->file_name,
                                                $media->collection_name
                                            ));
                                        });

                                        // Get GI Media
                                        $lead->generalInformations->each(function ($gi) use ($links, $dupIcon) {
                                            $gi->getMedia('*')->each(function ($media) use ($links, $dupIcon) {
                                                $url = $media->disk === 's3'
                                                    ? $media->getTemporaryUrl(now()->addMinutes(30))
                                                    : $media->getUrl();

                                                $links->push(sprintf(
                                                    '<a href="%s" target="_blank" class="text-primary-600 hover:text-primary-500 transition-colors flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                                        %s
                                                        <span class="truncate">%s</span>
                                                        <span class="text-[10px] uppercase font-bold text-blue-400 bg-white dark:bg-gray-900 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-900/50 ml-auto">%s</span>
                                                    </a>',
                                                    $url,
                                                    $dupIcon,
                                                    $media->file_name,
                                                    'GI - '.$media->collection_name
                                                ));
                                            });
                                        });

                                        if ($links->isEmpty()) {
                                            return new HtmlString('<div class="text-sm text-gray-500 italic p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-dashed border-gray-200 dark:border-gray-700">Tidak ada dokumen yang ditemukan di Lead/GI.</div>');
                                        }

                                        return new HtmlString('<div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">'.$links->implode('').'</div>');
                                    })
                                    ->html(),
                                Repeater::make('analysis_details.manual_costs')
                                    ->label('Manual Cost Breakdown')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('direct_cost_category_id')
                                                    ->label('Category')
                                                    ->options(fn () => DirectCostCategory::where('type', 'direct')->whereNull('parent_id')->pluck('name', 'id'))
                                                    ->required()
                                                    ->distinct()
                                                    ->live()
                                                    ->createOptionForm(DirectCostCategoryForm::schema(type: 'direct'))
                                                    ->createOptionUsing(fn (array $data) => DirectCostCategory::create($data)->id)
                                                    ->editOptionForm(DirectCostCategoryForm::schema(type: 'direct'))
                                                    ->fillEditOptionActionFormUsing(fn (Select $component): ?array => DirectCostCategory::find($component->getState())?->toArray())
                                                    ->updateOptionUsing(fn (Select $component, array $data) => DirectCostCategory::find($component->getState())?->update($data)),
                                                TextInput::make('amount')
                                                    ->label('Category Total')
                                                    ->numeric()
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('IDR ')
                                                    ->required()
                                                    ->placeholder('Enter total or add breakdown below')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set))
                                                    ->extraAttributes(['class' => 'font-bold bg-gray-50']),
                                            ]),
                                        TextInput::make('description')
                                            ->label('Description/Notes'),
                                        Repeater::make('sub_items')
                                            ->label('Sub-component Breakdown')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->label('Sub-item Name')
                                                            ->required()
                                                            ->placeholder('e.g. Security Guard Salary'),
                                                        TextInput::make('amount')
                                                            ->label('Amount')
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix('IDR ')
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                                // Calculate category total from sub-items
                                                                $subItems = $get('../../sub_items') ?? [];
                                                                $total = collect($subItems)->sum(fn ($i) => self::parseNumericValue($i['amount'] ?? 0));
                                                                $set('../../amount', $total);
                                                            }),
                                                    ]),
                                            ])
                                            ->collapsible()
                                            ->defaultItems(0)
                                            ->reorderableWithButtons()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                                    ])
                                    ->columnSpanFull()
                                    ->itemLabel(fn (array $state): ?string => filled($state['direct_cost_category_id'] ?? null) ? DirectCostCategory::find($state['direct_cost_category_id'])?->name : 'New Manual Cost')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                            ]),
                    ]),

                Step::make('Indirect Costs')
                    ->label('Indirect Costs')
                    ->description('Set management expenses, entertainment, and other indirect fees.')
                    ->icon('heroicon-m-receipt-percent')
                    ->schema([
                        TextInput::make('analysis_details.manual_indirect_total')
                            ->label('Total Indirect Cost (Subtotal)')
                            ->numeric()
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('IDR ')
                            ->readOnly()
                            ->helperText('Otomatis menghitung jumlah dari rincian biaya tidak langsung di bawah.')
                            ->columnSpanFull(),
                        Repeater::make('indirectItems')
                            ->label('Indirect Cost Items')
                            ->relationship('indirectItems')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('direct_cost_category_id')
                                            ->label('Category')
                                            ->options(fn () => DirectCostCategory::where('type', 'indirect')->whereNull('parent_id')->pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->createOptionForm(DirectCostCategoryForm::schema(type: 'indirect'))
                                            ->createOptionUsing(fn (array $data) => DirectCostCategory::create($data)->id)
                                            ->editOptionForm(DirectCostCategoryForm::schema(type: 'indirect'))
                                            ->fillEditOptionActionFormUsing(fn (Select $component): ?array => DirectCostCategory::find($component->getState())?->toArray())
                                            ->updateOptionUsing(fn (Select $component, array $data) => DirectCostCategory::find($component->getState())?->update($data)),
                                        Select::make('calculation_type')
                                            ->label('Calculation Type')
                                            ->options([
                                                'nominal' => 'Nominal',
                                                'percentage' => 'Percentage',
                                            ])
                                            ->required()
                                            ->default('nominal')
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                                        Select::make('percentage_basis')
                                            ->label('Basis')
                                            ->options([
                                                'revenue' => 'Total Revenue',
                                                'direct_cost' => 'Total Direct Cost',
                                            ])
                                            ->required(fn (Get $get) => $get('calculation_type') === 'percentage')
                                            ->visible(fn (Get $get) => $get('calculation_type') === 'percentage')
                                            ->default('revenue')
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                                        TextInput::make('unit_cost_price')
                                            ->label(fn (Get $get) => $get('calculation_type') === 'percentage' ? 'Percentage (%)' : 'Amount')
                                            ->numeric()
                                            ->currencyMask(
                                                thousandSeparator: '.',
                                                decimalSeparator: ',',
                                                precision: 2
                                            )
                                            ->prefix(fn (Get $get) => $get('calculation_type') === 'percentage' ? null : 'IDR ')
                                            ->suffix(fn (Get $get) => $get('calculation_type') === 'percentage' ? '%' : null)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                                    ]),
                                TextInput::make('description')
                                    ->label('Notes/Description')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->itemLabel(fn (array $state): ?string => DirectCostCategory::find($state['direct_cost_category_id'])?->name ?? 'New Indirect Cost')
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateDirectCost($get, $set)),
                    ]),

                Step::make('Financial Performance')
                    ->label('Financial Performance')
                    ->description('Key monthly and project-wide financial metrics.')
                    ->icon('heroicon-m-presentation-chart-line')
                    ->schema([
                        Section::make('Automated Cost Review')
                            ->description('Aggregated monthly costs calculated from personnel, tools, and indirect inputs.')
                            ->visible(fn (Get $get) => ! $get('is_manual_cost'))
                            ->icon('heroicon-m-magnifying-glass-circle')
                            ->collapsible()
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('direct_cost_manpower')
                                            ->label('Personnel (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->helperText('Otomatis dari tab Manpower.'),
                                        TextInput::make('direct_cost_tools')
                                            ->label('Tools & Eq (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->helperText('Otomatis dari tab Operational.'),
                                        TextInput::make('direct_cost_material')
                                            ->label('Materials (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->helperText('Otomatis dari tab Operational.'),
                                        TextInput::make('avg_monthly_indirect_cost')
                                            ->label('Indirect (Monthly)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(false)
                                            ->helperText('Otomatis dari tab Indirect.'),
                                    ]),
                            ]),

                        Section::make('Monthly Performance Metrics')
                            ->description('Monthly average revenue, cost, and profit.')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('revenue_per_month')
                                            ->label('MONTHLY REVENUE')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'text-2xl font-black text-success-600']),
                                        TextInput::make('direct_cost')
                                            ->label('MONTHLY DIRECT COST')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'text-2xl font-black text-danger-600']),
                                        TextInput::make('gross_profit')
                                            ->label('MONTHLY GROSS PROFIT')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'text-2xl font-black text-primary-600']),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('ebitda')
                                            ->label('EBITDA')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold']),
                                        TextInput::make('ebit')
                                            ->label('EBIT')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold']),
                                        TextInput::make('ebt')
                                            ->label('EBT')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold']),
                                        TextInput::make('net_profit')
                                            ->label('NET PROFIT (MONTHLY)')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-success-700']),
                                    ])->extraAttributes(['class' => 'border-t border-gray-100 pt-4 mt-4']),
                            ]),

                        Section::make('Cost Breakdown Details')
                            ->collapsed()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('direct_cost_manpower')
                                            ->label('Personnel')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(),
                                        TextInput::make('direct_cost_tools')
                                            ->label('Tools & Eq')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(),
                                        TextInput::make('direct_cost_material')
                                            ->label('Materials')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated(),
                                    ]),
                            ]),

                        Section::make('Total Project Value (Full Duration)')
                            ->compact()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('total_project_revenue')
                                            ->label('PROJECT REVENUE')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-success-600']),
                                        TextInput::make('total_project_cost')
                                            ->label('PROJECT COST')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-danger-600']),
                                        TextInput::make('margin_percentage')
                                            ->label('GPM (%)')
                                            ->suffix('%')
                                            ->readOnly()
                                            ->dehydrated()
                                            ->extraAttributes(['class' => 'font-bold text-primary-600']),
                                    ]),
                            ]),
                    ]),
            ])
                ->columnSpanFull()
                ->startOnStep($startStep)
                ->persistStepInQueryString(),
        ];
    }

    public static function getProjectDurationMonths($get): float
    {
        $giId = $get('/general_information_id');
        $gi = self::getCachedModel(GeneralInformation::class, $giId);

        if ($gi && $gi->estimated_start_date && $gi->estimated_end_date) {
            $days = $gi->estimated_start_date->diffInDays($gi->estimated_end_date);

            return max(1, round($days / 30, 2));
        }

        return 1.0;
    }

    public static function calculateDirectCost($get, $set): void
    {
        // 1. Calculate Project Duration
        $projectDurationMonths = self::getProjectDurationMonths($get);

        $totalProjectCost = 0;
        $totalProjectRevenue = 0;
        $totalProjectDepreciation = 0;
        $totalProjectIndirectCost = 0;

        $manpowerCostMonthly = 0;
        $toolsCostMonthly = 0;
        $materialCostMonthly = 0;

        $isManual = (bool) ($get('is_manual_cost') ?? false);

        if ($isManual) {
            $totalManualCost = 0;
            $manualCosts = $get('analysis_details.manual_costs') ?? [];

            $catManpowerId = DirectCostCategory::where('code', 'manpower')->first()?->id;
            $catToolsId = DirectCostCategory::where('code', 'tools_equipment')->first()?->id;
            $catMaterialId = DirectCostCategory::where('code', 'material')->first()?->id;

            foreach ($manualCosts as $item) {
                $amount = self::parseNumericValue($item['amount'] ?? 0);
                $totalManualCost += $amount;

                if (isset($item['direct_cost_category_id'])) {
                    if ((string) $item['direct_cost_category_id'] === (string) $catManpowerId) {
                        $manpowerCostMonthly += $amount;
                    } elseif ((string) $item['direct_cost_category_id'] === (string) $catToolsId) {
                        $toolsCostMonthly += $amount;
                    } elseif ((string) $item['direct_cost_category_id'] === (string) $catMaterialId) {
                        $materialCostMonthly += $amount;
                    }
                }
            }

            $totalProjectCost = $totalManualCost;
            $set('analysis_details.manual_revenue', $totalManualCost);

            $totalProjectRevenue = $totalManualCost;
            $totalProjectDepreciation = self::parseNumericValue($get('manual_depreciation') ?? 0) * $projectDurationMonths;

            $manpowerCostMonthly = $projectDurationMonths > 0 ? $manpowerCostMonthly / $projectDurationMonths : 0;
            $toolsCostMonthly = $projectDurationMonths > 0 ? $toolsCostMonthly / $projectDurationMonths : 0;
            $materialCostMonthly = $projectDurationMonths > 0 ? $materialCostMonthly / $projectDurationMonths : 0;
        } else {
            // First pass: Calculate Revenue and Direct Costs from Fixed/Nominal items
            // (We need an initial revenue estimate for percentage-based costs)
            $manpowerItems = $get('manpowerItems') ?? [];
            $operationalItems = $get('operationalItems') ?? [];

            // To handle percentages correctly, we do it in a way that avoids circular dependency
            // Initial revenue is often set by the user or calculated from cost + markup.

            $tempTotalCost = 0;
            $tempTotalRevenue = 0;
            $tempTotalDepreciation = 0;

            // 1. Calculate Manpower and Operational Costs (Fixed/Nominal)
            foreach (array_merge($manpowerItems, $operationalItems) as $item) {
                $itemGet = function ($path) use ($item, $get) {
                    if (str_starts_with($path, '/')) {
                        return $get(ltrim($path, '/'));
                    }

                    return data_get($item, $path);
                };
                $monthlyCost = self::calculateItemMonthlyCost($itemGet);
                $markup = (float) ($item['markup_percentage'] ?? 0);
                $duration = (float) ($item['duration_months'] ?? $projectDurationMonths);

                $monthlySale = $monthlyCost * (1.0 + ($markup / 100));

                $tempTotalCost += ($monthlyCost * $duration);
                $tempTotalRevenue += ($monthlySale * $duration);

                // Aggregate Breakdowns (Monthly Cost Equivalent)
                $catId = $item['direct_cost_category_id'] ?? null;
                $cat = $catId ? DirectCostCategory::find($catId) : null;
                $isManpowerAssumed = $item['is_manpower'] ?? false;

                if ($cat?->code === 'manpower' || $isManpowerAssumed) {
                    $manpowerCostMonthly += $monthlyCost;
                } elseif ($cat?->code === 'tools_equipment') {
                    $toolsCostMonthly += $monthlyCost;
                } elseif ($cat?->code === 'material') {
                    $materialCostMonthly += $monthlyCost;
                } else {
                    $materialCostMonthly += $monthlyCost; // default fallback if unrecognized operational
                }

                // Depreciation track
                if (! ($item['is_manpower'] ?? false)) {
                    $deprMonths = (float) ($item['depreciation_months'] ?? 1);
                    if ($deprMonths > 0) {
                        $costPrice = (float) ($item['unit_cost_price'] ?? 0);
                        $qty = (float) ($item['quantity'] ?? 1);
                        $monthlyDepreciation = ($costPrice / $deprMonths) * $qty;
                        $tempTotalDepreciation += ($monthlyDepreciation * $duration);
                    }
                }
            }

            $totalProjectCost = $tempTotalCost;
            $totalProjectRevenue = $tempTotalRevenue;
            $totalProjectDepreciation = $tempTotalDepreciation;
        }

        // 2. Always Calculate Indirect Items (OPEX)
        $indirectItems = $get('indirectItems') ?? [];
        $totalProjectIndirectCost = 0;
        foreach ($indirectItems as $item) {
            $itemGet = function ($path) use ($item, $get) {
                if (str_starts_with($path, '/')) {
                    return $get(ltrim($path, '/'));
                }

                return data_get($item, $path);
            };
            $monthlyCost = self::calculateItemMonthlyCost(
                $itemGet,
                $projectDurationMonths > 0 ? $totalProjectRevenue / $projectDurationMonths : 0,
                $projectDurationMonths > 0 ? $totalProjectCost / $projectDurationMonths : 0
            );
            $duration = (float) ($item['duration_months'] ?? $projectDurationMonths);
            $totalProjectIndirectCost += ($monthlyCost * $duration);
        }

        // Handle Management Fee from Rate
        $mgmtFeeRate = self::parseNumericValue($get('management_fee_rate') ?? 0);
        $avgMonthlyDirectCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : 0;
        $avgMonthlyIndirectCost = $projectDurationMonths > 0 ? ($totalProjectIndirectCost / $projectDurationMonths) : 0;

        if ($mgmtFeeRate > 0) {
            $calculatedMgmtFee = $avgMonthlyDirectCost * ($mgmtFeeRate / 100);
            $set('management_fee', $calculatedMgmtFee);
            $mgmtFee = $calculatedMgmtFee;
        } else {
            $mgmtFee = (float) ($get('management_fee') ?? 0);
        }

        // Add Management Fee to Revenue (Pro-rated monthly)
        $totalProjectRevenue += ($mgmtFee * $projectDurationMonths);

        $set('total_project_cost', $totalProjectCost);
        $set('total_project_revenue', $totalProjectRevenue);

        // Update Indirect Subtotal
        $set('analysis_details.manual_indirect_total', $totalProjectIndirectCost);

        // Pro-rated values back to "Standard Monthly" for high-level summary
        $avgMonthlyRevenue = $projectDurationMonths > 0 ? ($totalProjectRevenue / $projectDurationMonths) : 0;
        $avgMonthlyCost = $projectDurationMonths > 0 ? ($totalProjectCost / $projectDurationMonths) : 0;
        $avgMonthlyDepreciation = $projectDurationMonths > 0 ? ($totalProjectDepreciation / $projectDurationMonths) : 0;

        $set('direct_cost', $avgMonthlyCost);
        $set('depreciation', $avgMonthlyDepreciation);
        $set('revenue_per_month', $avgMonthlyRevenue);

        // Push Cost Breakdown to State
        $set('direct_cost_manpower', $manpowerCostMonthly);
        $set('direct_cost_tools', $toolsCostMonthly);
        $set('direct_cost_material', $materialCostMonthly);
        $set('avg_monthly_indirect_cost', $avgMonthlyIndirectCost);
        $set('gross_profit', $avgMonthlyRevenue - $avgMonthlyCost);

        // Advanced Financial Tiers
        $interestRate = self::parseNumericValue($get('interest_rate') ?? 0.0);
        $taxRate = self::parseNumericValue($get('tax_rate') ?? 22.0);

        // EBITDA = Revenue - (Direct Cost Excl Depr) - Total Indirect Cost (Dynamic)
        $avgMonthlyCostExclDepr = $avgMonthlyCost - $avgMonthlyDepreciation;
        $ebitda = ($avgMonthlyRevenue - $avgMonthlyCostExclDepr) - $avgMonthlyIndirectCost;

        // EBIT = EBITDA - Depreciation
        $ebit = $ebitda - $avgMonthlyDepreciation;

        // Interest (Cost of Fund)
        $paymentTermId = $get('payment_term_id');
        $paymentTerm = $paymentTermId ? PaymentTerm::find($paymentTermId) : null;
        $topDays = (float) ($paymentTerm?->days ?? 30);
        $interest = ($topDays / 30.0 * (self::parseNumericValue($interestRate) / 100)) * $avgMonthlyCost;

        $ebt = $ebit - $interest;

        $tax = $ebt > 0 ? ($ebt * ($taxRate / 100)) : 0;
        $netProfit = $ebt - $tax;
        $netProfitMargin = $avgMonthlyRevenue > 0 ? ($netProfit / $avgMonthlyRevenue) * 100 : 0;

        $set('ebitda', $ebitda);
        $set('ebit', $ebit);
        $set('ebt', $ebt);
        $set('net_profit', $netProfit);
        $set('net_profit_margin', round($netProfitMargin, 2));

        // Recalculate margin (GP Margin)
        self::calculateMargin($avgMonthlyRevenue, $avgMonthlyCost, $set);

        // Debug state
        \Illuminate\Support\Facades\Log::info('calculateDirectCost Setting State:', call_user_func(function () use ($get) {
            // Try to dump the whole root state
            $rootState = [];
            try {
                $rootState = $get('');
            } catch (\Exception $e) {
            }
            if (empty($rootState)) {
                try {
                    $rootState = $get('../../..');
                } catch (\Exception $e) {
                }
            }

            return [
                'root' => $rootState,
                'target_revenue' => $get('/revenue_per_month'),
            ];
        }));
    }

    protected static function updateItemTotals($get, $set): void
    {
        $set('total_monthly_cost', self::calculateItemMonthlyCost($get));
        $set('total_monthly_sale', self::calculateItemMonthlySale($get));
    }

    public static function calculateItemMonthlyCost($get, ?float $totalRevenue = null, ?float $totalDirectCost = null): float
    {
        $qty = self::parseNumericValue($get('quantity') ?? 1);
        $costPrice = self::parseNumericValue($get('unit_cost_price') ?? 0);
        $deprMonths = self::parseNumericValue($get('depreciation_months') ?? 1);
        $calcType = $get('calculation_type') ?? 'nominal';
        $basis = $get('percentage_basis') ?? 'none';

        if ($calcType === 'percentage') {
            $basisValue = 0;
            if ($basis === 'revenue') {
                $basisValue = $totalRevenue ?? self::parseNumericValue($get('/revenue_per_month') ?? 0);
            } elseif ($basis === 'direct_cost') {
                // Warning: Potential circular dependency if called during direct cost calculation
                $basisValue = $totalDirectCost ?? self::parseNumericValue($get('/direct_cost') ?? 0);
            }

            return ($basisValue * ($costPrice / 100)) * $qty;
        }

        $costBreakdown = $get('cost_breakdown') ?? [];

        $isManpower = $get('is_manpower');
        if (! $isManpower && $get('costable_type') && $get('costable_id')) {
            if ($get('costable_type') === Item::class) {
                $dbItem = Item::find($get('costable_id'));
                $isManpower = $dbItem?->category?->name === 'Manpower';
            } elseif ($get('costable_type') === JobPosition::class) {
                $isManpower = true;
            } elseif ($get('costable_type') === ManpowerTemplate::class) {
                $isManpower = false; // ManpowerTemplate calculates its own total
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
                isLaborIntensive: (bool) $get('is_labor_intensive'),
                ptkpCode: PtkpConfig::find($get('ptkp_config_id'))?->code ?? 'TK/0'
            );

            return (float) ($result['total_direct_cost'] ?? 0) * $qty;
        }

        if ($deprMonths <= 0) {
            $deprMonths = 1.0;
        }

        $addOnTotal = 0.0;
        foreach ($costBreakdown as $addon) {
            $val = 0.0;
            if (! empty($addon['details'])) {
                $val = collect($addon['details'])->sum(fn ($detail) => self::parseNumericValue($detail['value'] ?? 0));
            } else {
                $val = self::parseNumericValue($addon['value'] ?? 0);
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

    public static function calculateItemMonthlySale($get): float
    {
        $monthlyCost = self::calculateItemMonthlyCost($get);
        $markup = self::parseNumericValue($get('markup_percentage') ?? 0);

        return $monthlyCost * (1.0 + ($markup / 100));
    }

    protected static function calculateMargin($revenue, $cost, $set): void
    {
        $revenue = self::parseNumericValue($revenue ?? 0);
        $cost = self::parseNumericValue($cost ?? 0);

        if ($revenue > 0) {
            $margin = (($revenue - $cost) / $revenue) * 100;
            $set('margin_percentage', round($margin, 2));
        } else {
            $set('margin_percentage', 0);
        }
    }
}
