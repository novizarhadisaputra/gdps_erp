<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\TaxPtkpConfig;

class ManpowerTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(self::schema());
    }

    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make('Costing Identification')
                    ->label('Costing Identification')
                    ->description('Define basic costing details and project area.')
                    ->icon('heroicon-m-identification')
                    ->schema([
                        TextInput::make('code')
                            ->hidden(fn (string $operation): bool => $operation === 'create')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label('Costing Name')
                            ->placeholder('e.g., Security Level 1, Admin Staff Proyek A')
                            ->required()
                            ->maxLength(255)
                            ->helperText('A descriptive name to identify this costing template.'),
                        Select::make('project_area_id')
                            ->label('Project Area')
                            ->relationship(
                                name: 'projectArea',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query, $livewire) {
                                    $customerId = $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords
                                        ? $livewire->getOwnerRecord()->customer_id
                                        : null;

                                    return $query->when($customerId, fn ($q) => $q->whereHas('customers', fn ($c) => $c->where($c->qualifyColumn('id'), $customerId)));
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder('Select City/Regency')
                            ->helperText('Determines the applicable UMK/Minimum Wage for calculations.')
                            ->createOptionForm(ProjectAreaForm::schema())
                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                            ->createOptionUsing(function (array $data, $livewire) {
                                $area = ProjectArea::create($data);
                                $customerId = $livewire instanceof \Filament\Resources\Pages\ManageRelatedRecords
                                    ? $livewire->getOwnerRecord()->customer_id
                                    : null;

                                if ($customerId) {
                                    $area->customers()->attach($customerId);
                                }

                                return $area->id;
                            })
                            ->editOptionForm(ProjectAreaForm::schema())
                            ->editOptionAction(fn (Action $action) => $action->slideOver())
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (! $state) {
                                    return;
                                }

                                // If Project Area changes, reset all items' basic salary to the new area's UMK
                                $clusters = $get('clusters') ?? [];
                                foreach ($clusters as $cKey => $cluster) {
                                    $clusterItems = $cluster['items'] ?? [];
                                    foreach ($clusterItems as $iKey => $item) {
                                        $umk = MinimumWage::where('project_area_id', $state)
                                            ->where('year', $get('year') ?? date('Y'))
                                            ->where('is_active', true)
                                            ->first();

                                        if ($umk) {
                                            $set("clusters.{$cKey}.items.{$iKey}.basic_salary", $umk->amount);
                                        }
                                    }
                                }
                            }),
                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->default(date('Y'))
                            ->required()
                            ->live()
                            ->helperText('Determines the UMK year and tax regulations applied.'),
                        Select::make('work_scheme_id')
                            ->label('Work Scheme')
                            ->relationship('workScheme', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder('Select operational schedule')
                            ->helperText('Affects working days per month and overtime calculations.'),
                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Provide additional context if needed...')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->helperText('Only active templates can be selected in Profitability Analysis.')
                            ->required()
                            ->default(true)
                            ->hidden(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columns(2),

                Step::make('Personnel Composition')
                    ->label('Personnel Composition')
                    ->description('Organize personnel into clusters (e.g., Aviation, FM) and set basic salaries.')
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        Repeater::make('clusters')
                            ->relationship('clusters')
                            ->label('Product Clusters / Sections')
                            ->itemLabel(fn (array $state): ?string => ProductCluster::find($state['product_cluster_id'] ?? null)?->name ?? 'New Cluster')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        Select::make('product_cluster_id')
                                            ->label('Product Cluster (Master)')
                                            ->relationship('productCluster', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Cluster Defaults')
                                    ->description('Default policies for all personnel in this cluster.')
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('jkn_category')
                                                    ->label('JKN Category')
                                                    ->options([
                                                        'PPU' => 'PPU (Salaried)',
                                                        'PBPU' => 'PBPU (Informal)',
                                                    ])
                                                    ->default('PPU')
                                                    ->required()
                                                    ->helperText('Default participation type for personnel in this cluster.'),
                                                Select::make('thr_billing_method')
                                                    ->label('THR Billing')
                                                    ->options([
                                                        'monthly_accrual' => 'Monthly Accrual',
                                                        'one_time' => 'One-time Payment',
                                                    ])
                                                    ->default('monthly_accrual')
                                                    ->required()
                                                    ->helperText('Determines if THR is accrued monthly or billed in full.'),
                                                Select::make('compensation_billing_method')
                                                    ->label('Comp. Billing')
                                                    ->options([
                                                        'monthly_accrual' => 'Monthly Accrual',
                                                        'one_time' => 'One-time Payment',
                                                    ])
                                                    ->default('monthly_accrual')
                                                    ->required()
                                                    ->helperText('Determines if compensation is accrued monthly or billed in full.'),
                                            ]),
                                    ]),

                                Repeater::make('items')
                                    ->relationship('items')
                                    ->label('Personnel within this Cluster')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('job_position_id')
                                                    ->label('Job Position')
                                                    ->placeholder('Select Position...')
                                                    ->relationship('jobPosition', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                        if (! $state) {
                                                            return;
                                                        }
                                                        $areaId = $get('../../../project_area_id');
                                                        if (! $areaId) {
                                                            return;
                                                        }
                                                        $umk = MinimumWage::where('project_area_id', $areaId)
                                                            ->where('year', $get('../../../year') ?? date('Y'))
                                                            ->where('is_active', true)
                                                            ->first();
                                                        if ($umk) {
                                                            $set('basic_salary', $umk->amount);
                                                        }
                                                    })
                                                    ->createOptionForm(JobPositionForm::schema())
                                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                                    ->columnSpan(2),
                                                TextInput::make('quantity')
                                                    ->label('Qty (Pax)')
                                                    ->numeric()
                                                    ->placeholder('e.g., 5')
                                                    ->default(1)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->helperText('Number of personnel for this position.')
                                                    ->columnSpan(1),
                                                TextInput::make('basic_salary')
                                                    ->label('Basic Salary')
                                                    ->placeholder('0.00')
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('IDR ')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->helperText('Base monthly salary (before allowances).')
                                                    ->columnSpan(1),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('future_adjustment_rate')
                                                    ->label('Salary Scaling (%)')
                                                    ->numeric()
                                                    ->placeholder('e.g., 5.0')
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->helperText('Percentage increase for future salary forecasts/scaling.')
                                                    ->columnSpan(1),
                                                Select::make('ptkp_status')
                                                    ->label('Tax Status (PTKP)')
                                                    ->placeholder('Select Status')
                                                    ->options(TaxPtkpConfig::pluck('code', 'code'))
                                                    ->default('TK/0')
                                                    ->required()
                                                    ->searchable()
                                                    ->live()
                                                    ->helperText('Used to calculate PPh 21 personal tax relief.')
                                                    ->columnSpan(1),
                                                Select::make('work_pattern_id')
                                                    ->label('Work Pattern')
                                                    ->relationship('workPattern', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->columnSpan(1),
                                            ]),

                                        Section::make('Remuneration & BPJS Policy')
                                            ->compact()
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('thr_basis_id')
                                                            ->label('THR Basis')
                                                            ->placeholder('Select Basis')
                                                            ->relationship('thrBasis', 'name')
                                                            ->preload()
                                                            ->required()
                                                            ->helperText('Salary components included in THR calculation.'),
                                                        Select::make('compensation_basis_id')
                                                            ->label('Comp. Basis')
                                                            ->placeholder('Select Basis')
                                                            ->relationship('compensationBasis', 'name')
                                                            ->preload()
                                                            ->required()
                                                            ->helperText('Salary components included in Compensation calculation.'),
                                                        Select::make('bpjs_basis_id')
                                                            ->label('BPJS Basis')
                                                            ->placeholder('Select Basis')
                                                            ->relationship('bpjsBasis', 'name')
                                                            ->preload()
                                                            ->required()
                                                            ->helperText('Salary basis used for BPJS percentage calculations.'),
                                                    ]),

                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('risk_level')
                                                            ->label('JKK Risk Level')
                                                            ->options([
                                                                'very_low' => 'Very Low (0.24%)',
                                                                'low' => 'Low (0.54%)',
                                                                'medium' => 'Medium (0.89%)',
                                                                'high' => 'High (1.27%)',
                                                                'very_high' => 'Very High (1.74%)',
                                                            ])
                                                            ->default('very_low')
                                                            ->required()
                                                            ->helperText('Determines the BPJS JKK (Work Accident) premium rate.'),
                                                        Select::make('employee_type')
                                                            ->label('Standard Participation')
                                                            ->options([
                                                                'ppu' => 'PPU (Salaried)',
                                                                'pbpu' => 'PBPU (Freelancers)',
                                                            ])
                                                            ->default('ppu')
                                                            ->required()
                                                            ->helperText('Standard BPJS participation type for this specific role.'),
                                                        Toggle::make('use_ter_method')
                                                            ->label('Use TER Method (PPh 21)')
                                                            ->default(true)
                                                            ->live()
                                                            ->helperText('Enable to use the 2024 TER method. Disable for Progressive (Pasal 17).'),
                                                    ]),

                                                Section::make('Company Coverage')
                                                    ->schema([
                                                        Grid::make(3)
                                                            ->schema([
                                                                Toggle::make('is_bpjs_active')
                                                                    ->label('BPJS Active')
                                                                    ->default(true)
                                                                    ->live()
                                                                    ->helperText('Enable/disable all BPJS calculations for this role.'),
                                                                Toggle::make('is_tax_borne_by_company')
                                                                    ->label('Tax Borne by Co.')
                                                                    ->default(false)
                                                                    ->live()
                                                                    ->helperText('If enabled, the company pays the employee\'s PPh 21 tax.'),
                                                                Toggle::make('is_employee_jkn_borne_by_company')
                                                                    ->label('Cover Employee JKN')
                                                                    ->default(false)
                                                                    ->helperText('Company covers the 1% employee portion of BPJS Health.'),
                                                                Toggle::make('is_employee_jkk_borne_by_company')
                                                                    ->label('Cover Employee JKK')
                                                                    ->default(false),
                                                                Toggle::make('is_employee_jkm_borne_by_company')
                                                                    ->label('Cover Employee JKM')
                                                                    ->default(false),
                                                                Toggle::make('is_employee_jht_borne_by_company')
                                                                    ->label('Cover Employee JHT')
                                                                    ->default(false)
                                                                    ->helperText('Company covers the 2% employee portion of BPJS JHT.'),
                                                                Toggle::make('is_employee_jp_borne_by_company')
                                                                    ->label('Cover Employee JP')
                                                                    ->default(false)
                                                                    ->helperText('Company covers the 1% employee portion of BPJS JP.'),
                                                            ]),
                                                    ]),
                                            ]),

                                        Section::make('Allowances & Extra Costs')
                                            ->compact()
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                Repeater::make('allowances')
                                                    ->schema([
                                                        Grid::make(4)
                                                            ->schema([
                                                                TextInput::make('name')
                                                                    ->placeholder('e.g., Tunjangan Makan')
                                                                    ->required()
                                                                    ->columnSpan(2),
                                                                Select::make('type')
                                                                    ->options(['nominal' => 'IDR', 'percentage' => '%'])
                                                                    ->default('nominal')
                                                                    ->required()
                                                                    ->live()
                                                                    ->columnSpan(1),
                                                                Select::make('frequency')
                                                                    ->options(['monthly' => 'Monthly', 'daily' => 'Daily'])
                                                                    ->default('monthly')
                                                                    ->required()
                                                                    ->live()
                                                                    ->columnSpan(1)
                                                                    ->helperText('Daily allowances are multiplied by working days.'),
                                                                Toggle::make('is_fixed')
                                                                    ->label('Fixed')
                                                                    ->default(true)
                                                                    ->columnSpan(1)
                                                                    ->helperText('Fixed allowances are included in THR/Compensation basis by default.'),
                                                                TextInput::make('value')
                                                                    ->numeric()
                                                                    ->placeholder('0.00')
                                                                    ->required()
                                                                    ->live(onBlur: true)
                                                                    ->columnSpanFull(),
                                                            ]),
                                                    ])->defaultItems(0)->addActionLabel('Add Allowance'),

                                                Repeater::make('extra_costs')
                                                    ->schema([
                                                        Grid::make(2)
                                                            ->schema([
                                                                TextInput::make('name')
                                                                    ->placeholder('e.g., Seragam, Pelatihan Tahunan')
                                                                    ->required(),
                                                                TextInput::make('annual_amount')
                                                                    ->label('Annual Budget')
                                                                    ->numeric()
                                                                    ->placeholder('0.00')
                                                                    ->required()
                                                                    ->live(onBlur: true)
                                                                    ->afterStateUpdated(fn ($state, Set $set) => $set('amount', round((float) ($state ?? 0) / 12, 0)))
                                                                    ->helperText('The total annual budget. This will be divided by 12 for monthly costing.'),
                                                                Hidden::make('amount')->dehydrated(),
                                                            ]),
                                                    ])->defaultItems(0)->addActionLabel('Add Extra Cost'),
                                            ]),
                                    ])
                                    ->columnSpanFull()
                                    ->defaultItems(1)
                                    ->addActionLabel('Add Personnel Position')
                                    ->itemLabel(fn (array $state): ?string => filled($state['job_position_id'] ?? null) ? JobPosition::find($state['job_position_id'])?->name : 'New Position')
                                    ->live(),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->addActionLabel('Add Cluster / Section')
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'New Cluster')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('simulation_trigger', uniqid());
                            }),
                    ])
                    ->columns(2),

                Step::make('Cost Simulation')
                    ->label('Cost Simulation')
                    ->description('Review projected monthly cost details grouped by cluster.')
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        TextEntry::make('cost_simulation_table')
                            ->label('Projected Cost Breakdown')
                            ->html()
                            ->state(function (Get $get) {
                                $clusters = $get('clusters') ?? [];
                                $areaId = $get('project_area_id');
                                $workSchemeId = $get('work_scheme_id');

                                if (empty($clusters) || ! $areaId) {
                                    return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500"><p class="text-sm">Please complete the previous steps to view the cost simulation.</p></div>');
                                }

                                $service = app(ManpowerCostingService::class);
                                $totalTemplateCost = 0;
                                $tableContent = '';

                                foreach ($clusters as $cluster) {
                                    $clusterName = $cluster['name'] ?? 'Unnamed Cluster';
                                    $clusterItems = $cluster['items'] ?? [];

                                    if (empty($clusterItems)) {
                                        continue;
                                    }

                                    $tableContent .= "<tr class='bg-gray-100/50 dark:bg-gray-800/80'><td colspan='8' class='px-2 py-2 font-bold text-gray-900 dark:text-white uppercase text-[10px]'>Cluster: {$clusterName}</td></tr>";

                                    foreach ($clusterItems as $item) {
                                        $jpId = $item['job_position_id'] ?? null;
                                        $qty = (int) ($item['quantity'] ?? 0);
                                        if (! $jpId || $qty <= 0) {
                                            continue;
                                        }

                                        $jp = JobPosition::find($jpId);
                                        if (! $jp) {
                                            continue;
                                        }

                                        // Merge cluster policies if item doesn't have them (though Repeater usually has defaults)
                                        $jknCategory = $item['jkn_category'] ?? $cluster['jkn_category'] ?? 'PPU';
                                        $thrMethod = $item['thr_billing_method'] ?? $cluster['thr_billing_method'] ?? 'monthly_accrual';
                                        $compMethod = $item['compensation_billing_method'] ?? $cluster['compensation_billing_method'] ?? 'monthly_accrual';

                                        $res = $service->calculate(
                                            basicSalary: (float) ($item['basic_salary'] ?? 0),
                                            allowances: $item['allowances'] ?? [],
                                            projectAreaId: $areaId,
                                            year: $get('year') ?? date('Y'),
                                            workSchemeId: $workSchemeId,
                                            workPatternId: $item['work_pattern_id'] ?? null,
                                            riskLevel: $item['risk_level'] ?? 'very_low',
                                            employeeType: $item['employee_type'] ?? 'ppu',
                                            jknCategory: $jknCategory,
                                            thrBillingMethod: $thrMethod,
                                            compensationBillingMethod: $compMethod,
                                            thrBasisId: $item['thr_basis_id'] ?? null,
                                            compensationBasisId: $item['compensation_basis_id'] ?? null,
                                            bpjsBasisId: $item['bpjs_basis_id'] ?? null,
                                            billThrMonthly: (bool) ($item['bill_thr_monthly'] ?? true),
                                            billCompensationMonthly: (bool) ($item['bill_compensation_monthly'] ?? true),
                                            includeNonFixedInAccruals: (bool) ($item['include_non_fixed_in_accruals'] ?? false),
                                            extraCosts: $item['extra_costs'] ?? [],
                                            ptkpCode: $item['ptkp_status'] ?? 'TK/0',
                                            isBpjsActive: (bool) ($item['is_bpjs_active'] ?? true),
                                            useTerMethod: (bool) ($item['use_ter_method'] ?? true),
                                            borneByCompany: [
                                                'tax' => (bool) ($item['is_tax_borne_by_company'] ?? false),
                                                'jkn' => (bool) ($item['is_employee_jkn_borne_by_company'] ?? false),
                                                'jkk' => (bool) ($item['is_employee_jkk_borne_by_company'] ?? false),
                                                'jkm' => (bool) ($item['is_employee_jkm_borne_by_company'] ?? false),
                                                'jht' => (bool) ($item['is_employee_jht_borne_by_company'] ?? false),
                                                'jp' => (bool) ($item['is_employee_jp_borne_by_company'] ?? false),
                                            ]
                                        );

                                        $scale = 1 + ((float) ($item['future_adjustment_rate'] ?? 0) / 100);
                                        $unitCost = $res['total_direct_cost'] * $scale;
                                        $lineTotal = $unitCost * $qty;
                                        $totalTemplateCost += $lineTotal;

                                        $fmt = fn ($val) => number_format($val, 0, ',', '.');
                                        $subA = ($res['upah'] + $res['allowances']['non_fixed']) * $scale;
                                        $subB = ($res['accruals']['thr'] + $res['accruals']['compensation']) * $scale;
                                        $subC = $res['bpjs_total'] * $scale;
                                        $subD = $res['pph21']['total'] * $scale;
                                        $subE = $res['extra_costs_total'] * $scale;

                                        $tableContent .= "
                                            <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-xs'>
                                                <td class='px-2 py-3'>
                                                    <div class='font-medium text-gray-900 dark:text-gray-100'>{$jp->name}</div>
                                                    <div class='text-[9px] text-gray-400'>{$jp->code} | PTKP: ".($item['ptkp_status'] ?? 'TK/0')."</div>
                                                </td>
                                                <td class='px-2 py-3 text-center font-bold'>{$qty}</td>
                                                <td class='px-2 py-3 text-right'>Rp {$fmt($subA)}</td>
                                                <td class='px-2 py-3 text-right'>Rp {$fmt($subB)}</td>
                                                <td class='px-2 py-3 text-right'>Rp {$fmt($subC)}</td>
                                                <td class='px-2 py-3 text-right'>Rp {$fmt($subD)}</td>
                                                <td class='px-2 py-3 text-right'>Rp {$fmt($subE)}</td>
                                                <td class='px-2 py-3 text-right font-bold text-primary-600'>Rp {$fmt($unitCost)}</td>
                                            </tr>
                                        ";
                                    }
                                }

                                return new HtmlString("
                                    <div class='relative overflow-x-auto shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700'>
                                        <table class='w-full text-[11px] text-left text-gray-500 dark:text-gray-400'>
                                            <thead class='text-[10px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400'>
                                                <tr>
                                                    <th scope='col' class='px-2 py-3'>Position</th>
                                                    <th scope='col' class='px-2 py-3 text-center'>Qty</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>Wage</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>Accruals</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>BPJS</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>PPh 21</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>Extra</th>
                                                    <th scope='col' class='px-2 py-3 text-right bg-blue-50/50 dark:bg-blue-900/10 text-primary-600'>Total/Pax</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {$tableContent}
                                            </tbody>
                                            <tfoot>
                                                <tr class='font-bold text-gray-900 dark:text-white bg-gray-100/50 dark:bg-gray-800/50'>
                                                    <td colspan='7' class='px-2 py-4 text-right uppercase tracking-wider text-[10px]'>Total Estimated Monthly Cost</td>
                                                    <td class='px-2 py-4 text-right text-sm text-primary-600'>Rp ".number_format($totalTemplateCost, 0, ',', '.')."</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class='mt-4 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800'>
                                        <div class='flex gap-2 text-xs text-blue-700 dark:text-blue-300'>
                                            <span class='font-bold uppercase shrink-0'>Information:</span>
                                            <ul class='list-disc list-inside space-y-1 opacity-80'>
                                                <li>Calculations follow BPJS PPU and latest JKK risk categories.</li>
                                                <li>PPh 21 Tax supports both TER and Progressive (Pasal 17) methods.</li>
                                                <li>Extra costs are monthly accruals from annual budgets.</li>
                                                <li>Policies can be defined at Cluster level or overridden per Personnel.</li>
                                            </ul>
                                        </div>
                                    </div>
                                ");
                            }),
                        Hidden::make('simulation_trigger'),
                    ]),
            ])->columnSpanFull(),
        ];
    }
}
