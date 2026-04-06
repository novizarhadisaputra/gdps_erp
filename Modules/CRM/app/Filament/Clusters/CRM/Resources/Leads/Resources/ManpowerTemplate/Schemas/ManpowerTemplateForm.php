<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PtkpConfig;
use Modules\MasterData\Models\RegencyMinimumWage;

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
                            ->relationship('projectArea', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder('Select City/Regency')
                            ->helperText('Determines the applicable UMK/Minimum Wage for calculations.')
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (! $state) {
                                    return;
                                }

                                // If Project Area changes, reset all items' basic salary to the new area's UMK
                                $items = $get('items') ?? [];
                                foreach ($items as $key => $item) {
                                    $umk = RegencyMinimumWage::where('project_area_id', $state)
                                        ->whereIn('year', [2025, 2026])
                                        ->where('is_active', true)
                                        ->orderBy('year', 'desc')
                                        ->first();

                                    if ($umk) {
                                        $set("items.{$key}.basic_salary", $umk->amount);
                                    }
                                }
                            }),
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
                            ->default(true),
                    ])
                    ->columns(2),

                Step::make('Personnel Composition')
                    ->label('Personnel Composition')
                    ->description('Add job positions and set basic salaries.')
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->label('Personnel Requirements')
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
                                            ->helperText('Position or role for this project.')
                                            ->afterStateUpdated(function (Set $set, Get $get, $state, $component) {
                                                if (! $state) {
                                                    return;
                                                }

                                                // In Filament Wizards and Repeaters, finding a sibling outside the repeater
                                                // often requires multiple path attempts or absolute path.
                                                $areaId = $get('../../project_area_id') 
                                                    ?? $get('../../../project_area_id') 
                                                    ?? $get('project_area_id');

                                                if (! $areaId) {
                                                    return;
                                                }

                                                $umk = RegencyMinimumWage::where('project_area_id', $areaId)
                                                    ->where('is_active', true)
                                                    ->orderBy('year', 'desc')
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
                                            ->placeholder('e.g., 10')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->helperText('Number of personnel.')
                                            ->columnSpan(1),
                                        TextInput::make('basic_salary')
                                            ->label('Basic Salary')
                                            ->placeholder('Base Salary')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->helperText('Monthly base salary (Default to UMK).')
                                            ->suffixAction(
                                                Action::make('reset_to_umk')
                                                    ->icon('heroicon-m-arrow-path')
                                                    ->tooltip('Reset to UMK')
                                                    ->action(function (Set $set, Get $get) {
                                                        $areaId = $get('../../project_area_id') ?? $get('project_area_id');
                                                        if (! $areaId) {
                                                            return;
                                                        }
                                                        $umk = RegencyMinimumWage::where('project_area_id', $areaId)
                                                            ->whereIn('year', [2025, 2026])
                                                            ->where('is_active', true)
                                                            ->orderBy('year', 'desc')
                                                            ->first();
                                                        if ($umk) {
                                                            $set('basic_salary', $umk->amount);
                                                        }
                                                    })
                                            )
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('future_adjustment_rate')
                                            ->label('Salary Scaling (%)')
                                            ->placeholder('e.g., 5')
                                            ->numeric()
                                            ->default(0)
                                            ->step(0.1)
                                            ->live(onBlur: true)
                                            ->helperText('Est. future salary increase.')
                                            ->columnSpan(1),
                                        Select::make('ptkp_status')
                                            ->label('Tax Status (PTKP)')
                                            ->placeholder('Select PTKP...')
                                            ->options(PtkpConfig::pluck('code', 'code'))
                                            ->default('TK/0')
                                            ->required()
                                            ->preload()
                                            ->searchable()
                                            ->live()
                                            ->helperText('Taxable status (PPh 21 TER).')
                                            ->columnSpan(1),
                                        TextInput::make('notes')
                                            ->label('Notes')
                                            ->placeholder('Specific requirements...')
                                            ->maxLength(255)
                                            ->helperText('Internal notes for this role.')
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('risk_level')
                                            ->label('Insurance Level (JKK)')
                                            ->placeholder('Select hazard risk level...')
                                            ->options([
                                                'very_low' => 'Very Low (0.24%)',
                                                'low' => 'Low (0.54%)',
                                                'medium' => 'Medium (0.89%)',
                                                'high' => 'High (1.27%)',
                                                'very_high' => 'Very High (1.74%)',
                                            ])
                                            ->required()
                                            ->default('very_low')
                                            ->live()
                                            ->helperText('Determines BPJS JKK insurance rate.')
                                            ->columnSpan(1),
                                        Select::make('employee_type')
                                            ->label('Standard Participation')
                                            ->placeholder('Select participation type...')
                                            ->options([
                                                'ppu' => 'PPU (Salaried Employees)',
                                                'pbpu' => 'PBPU (Freelancers/Daily)',
                                            ])
                                            ->required()
                                            ->default('ppu')
                                            ->live()
                                            ->helperText('PPU category is the standard for staff.')
                                            ->columnSpan(1),
                                    ]),

                                \Filament\Schemas\Components\Section::make('Advanced Cost Configuration')
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                Toggle::make('is_bpjs_active')
                                                    ->label('BPJS Active')
                                                    ->helperText('Health & Employment coverage.')
                                                    ->default(true)
                                                    ->live(),
                                                Toggle::make('bill_thr_monthly')
                                                    ->label('Accrue THR')
                                                    ->helperText('Accrue THR cost monthly.')
                                                    ->default(true)
                                                    ->live(),
                                                Toggle::make('bill_compensation_monthly')
                                                    ->label('Accrue Compensation')
                                                    ->helperText('Accrue severance/comp monthly.')
                                                    ->default(true)
                                                    ->live(),
                                                Toggle::make('is_labor_intensive')
                                                    ->label('Labor Intensive')
                                                    ->helperText('Eligible for reduced JKK rate.')
                                                    ->default(false)
                                                    ->live(),
                                                Toggle::make('include_non_fixed_in_accruals')
                                                    ->label('Incl. Non-Fixed')
                                                    ->helperText('Include non-fixed allowances in THR basis.')
                                                    ->default(false)
                                                    ->live(),
                                            ]),
                                    ]),

                                \Filament\Schemas\Components\Section::make('Allowances')
                                    ->description('Define monthly remuneration components for this position.')
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Repeater::make('allowances')
                                            ->label('')
                                            ->schema([
                                                Grid::make(4)
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->label('Allowance Name')
                                                            ->placeholder('e.g., Meal, Transport, Position')
                                                            ->required()
                                                            ->columnSpan(2),
                                                        Select::make('type')
                                                            ->label('Calculation Type')
                                                            ->options([
                                                                'nominal' => 'Nominal (IDR)',
                                                                'percentage' => 'Percentage of Base (%)',
                                                            ])
                                                            ->default('nominal')
                                                            ->required()
                                                            ->live()
                                                            ->columnSpan(1),
                                                        Toggle::make('is_fixed')
                                                            ->label('Fixed Allowance')
                                                            ->default(true)
                                                            ->helperText('Fixed = BPJS/Tax basis.')
                                                            ->columnSpan(1),
                                                        TextInput::make('value')
                                                            ->label(fn (Get $get) => $get('type') === 'percentage' ? 'Rate (%)' : 'Amount (IDR)')
                                                            ->placeholder(fn (Get $get) => $get('type') === 'percentage' ? 'e.g., 5' : 'e.g., 500,000')
                                                            ->numeric()
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->prefix(fn (Get $get) => $get('type') === 'percentage' ? null : 'IDR ')
                                                            ->suffix(fn (Get $get) => $get('type') === 'percentage' ? '%' : null)
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->columnSpanFull(),
                                                    ]),
                                            ])
                                            ->defaultItems(0)
                                            ->addActionLabel('Add Allowance')
                                            ->live(),
                                    ]),

                                Repeater::make('extra_costs')
                                    ->label('Equipment & Training (Annual)')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Item/Training Name')
                                                    ->placeholder('e.g., Uniform, Certification')
                                                    ->required()
                                                    ->columnSpan(1),
                                                TextInput::make('annual_amount')
                                                    ->label('Annual Budget')
                                                    ->placeholder('Total cost per year')
                                                    ->numeric()
                                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                    ->prefix('IDR ')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->helperText('Est. cost per pax per year.')
                                                    ->afterStateUpdated(fn ($state, Set $set) => $set('amount', round((float) ($state ?? 0) / 12, 0)))
                                                    ->columnSpan(1),
                                                TextInput::make('monthly_display')
                                                    ->label('Monthly Cost')
                                                    ->prefix('Rp ')
                                                    ->disabled()
                                                    ->dehydrated(false)
                                                    ->afterStateHydrated(function (TextInput $component, Get $get) {
                                                        $annual = (float) ($get('annual_amount') ?? 0);
                                                        $component->state(number_format(round($annual / 12, 0), 0, ',', '.'));
                                                    })
                                                    ->columnSpan(1),
                                                Hidden::make('amount')
                                                    ->dehydrated(),
                                            ]),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Add Item')
                                    ->collapsible()
                                    ->collapsed(),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->addActionLabel('Add Position/Role')
                            ->itemLabel(fn (array $state): ?string => filled($state['job_position_id'] ?? null) ? JobPosition::find($state['job_position_id'])?->name : 'New Position')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $set('simulation_trigger', uniqid());
                            })
                            ->extraAttributes(['class' => 'ring-1 ring-gray-200 dark:ring-gray-800 rounded-xl p-4 bg-gray-50/30 dark:bg-gray-900/10']),
                    ])
                    ->columns(2),

                Step::make('Cost Simulation')
                    ->label('Cost Simulation')
                    ->description('Review projected monthly cost details for this template.')
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('cost_simulation_table')
                            ->label('Projected Cost Breakdown per Role')
                            ->html()
                            ->state(function (Get $get) {
                                $items = $get('items') ?? [];
                                $areaId = $get('project_area_id');

                                if (empty($items) || ! $areaId) {
                                    return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500"><p class="text-sm">Please complete the previous steps to view the cost simulation.</p></div>');
                                }

                                $service = app(ManpowerCostingService::class);
                                $totalTemplateCost = 0;
                                $rows = '';

                                foreach ($items as $item) {
                                    $jpId = $item['job_position_id'] ?? null;
                                    $qty = (int) ($item['quantity'] ?? 0);

                                    if (! $jpId || $qty <= 0) {
                                        continue;
                                    }

                                    // Get item-level configuration
                                    $riskLevel = $item['risk_level'] ?? 'very_low';
                                    $isLaborIntensive = (bool) ($item['is_labor_intensive'] ?? false);
                                    $employeeType = $item['employee_type'] ?? 'ppu';
                                    $billThr = (bool) ($item['bill_thr_monthly'] ?? true);
                                    $billComp = (bool) ($item['bill_compensation_monthly'] ?? true);
                                    $incNonFixed = (bool) ($item['include_non_fixed_in_accruals'] ?? false);
                                    $extraCosts = $item['extra_costs'] ?? [];

                                    $jp = JobPosition::find($jpId);
                                    if (! $jp) {
                                        continue;
                                    }

                                    // Read allowances directly from item
                                    $allowances = $item['allowances'] ?? [];

                                    $basicSalary = (float) ($item['basic_salary'] ?? 0);

                                    $res = $service->calculate(
                                        basicSalary: $basicSalary,
                                        allowances: $allowances,
                                        projectAreaId: $areaId,
                                        year: date('Y'),
                                        workSchemeId: $get('work_scheme_id'),
                                        riskLevel: $riskLevel,
                                        isLaborIntensive: $isLaborIntensive,
                                        employeeType: $employeeType,
                                        billThrMonthly: $billThr,
                                        billCompensationMonthly: $billComp,
                                        includeNonFixedInAccruals: $incNonFixed,
                                        extraCosts: $extraCosts,
                                        ptkpCode: $item['ptkp_status'] ?? 'TK/0',
                                        isBpjsActive: (bool) ($item['is_bpjs_active'] ?? true)
                                    );

                                    // Apply Future Scaling Factor if defined
                                    $scale = 1 + ((float) ($item['future_adjustment_rate'] ?? 0) / 100);
                                    $unitCost = $res['total_direct_cost'] * $scale;
                                    $lineTotal = $unitCost * $qty;
                                    $totalTemplateCost += $lineTotal;

                                    $fmt = fn ($val) => number_format($val, 0, ',', '.');

                                    // Subtotals for display
                                    $subA = ($res['upah'] + $res['allowances']['non_fixed']) * $scale;
                                    $subB = ($res['accruals']['thr'] + $res['accruals']['compensation']) * $scale;
                                    $subC = $res['bpjs_total'] * $scale;
                                    $subD = $res['pph21']['total'] * $scale;
                                    $subE = $res['extra_costs_total'] * $scale;

                                    $rows .= "
                                        <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-xs'>
                                            <td class='px-2 py-3'>
                                                <div class='font-medium text-gray-900 dark:text-gray-100'>{$jp->code}</div>
                                                <div class='text-[9px] text-gray-400'>PTKP: ".($item['ptkp_status'] ?? 'TK/0').' | Scale: '.($item['future_adjustment_rate'] ?? 0)."%</div>
                                            </td>
                                            <td class='px-2 py-3 text-center font-bold text-gray-900 dark:text-white'>{$qty}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subA)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subB)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subC)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subD)}</td>
                                            <td class='px-2 py-3 text-right'>Rp {$fmt($subE)}</td>
                                            <td class='px-2 py-3 text-right font-bold text-primary-600'>Rp {$fmt($unitCost)}</td>
                                        </tr>
                                    ";
                                }

                                return new HtmlString("
                                    <div class='relative overflow-x-auto shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700'>
                                        <table class='w-full text-[11px] text-left rtl:text-right text-gray-500 dark:text-gray-400'>
                                            <thead class='text-[10px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400'>
                                                <tr>
                                                    <th scope='col' class='px-2 py-3'>Position</th>
                                                    <th scope='col' class='px-2 py-3 text-center'>Qty</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>Monthly Wage</th>
                                                    <th scope='col' class='px-2 py-3 text-right' title='THR & Compensation'>Accruals</th>
                                                    <th scope='col' class='px-2 py-3 text-right' title='BPJS Health & Employment'>BPJS</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>Tax/PPh 21</th>
                                                    <th scope='col' class='px-2 py-3 text-right' title='Equipment & Training'>Extra</th>
                                                    <th scope='col' class='px-2 py-3 text-right bg-blue-50/50 dark:bg-blue-900/10 text-primary-600'>Total/Pax</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {$rows}
                                            </tbody>
                                            <tfoot>
                                                <tr class='font-bold text-gray-900 dark:text-white bg-gray-100/50 dark:bg-gray-800/50'>
                                                    <td colspan='7' class='px-2 py-4 text-right uppercase tracking-wider'>Total Estimated Monthly Cost</td>
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
                                                <li>PPh 21 Tax uses the TER (Monthly Effective Rate) method.</li>
                                                <li>Extra costs are monthly accruals from annual equipment/training budgets.</li>
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
