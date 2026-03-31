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
                            ->placeholder('e.g., Standard Security Packet')
                            ->required()
                            ->maxLength(255),
                        Select::make('project_area_id')
                            ->label('Project Area')
                            ->relationship('projectArea', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('Target area for this template (determines UMK/Minimum Wage).'),
                        Select::make('work_scheme_id')
                            ->label('Work Scheme')
                            ->relationship('workScheme', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        Textarea::make('description')
                            ->label('Costing Description')
                            ->placeholder('Briefly describe the purpose of this manpower packet...')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active Status')
                            ->helperText('Whether this template is available for new project costing.')
                            ->required()
                            ->default(true),
                    ])
                    ->columns(2),

                Step::make('Personnel Composition')
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
                                            ->relationship('jobPosition', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->placeholder('Select role...')
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                if (! $state) {
                                                    return;
                                                }
                                                $areaId = $get('../../project_area_id');
                                                if (! $areaId) {
                                                    return;
                                                }
                                                $umk = RegencyMinimumWage::where('project_area_id', $areaId)
                                                    ->where('year', date('Y'))
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
                                            ->label('Quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->columnSpan(1),
                                        TextInput::make('basic_salary')
                                            ->label('Basic Salary')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('IDR ')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->helperText('Default to UMK.')
                                            ->suffixAction(
                                                Action::make('reset_to_umk')
                                                    ->icon('heroicon-m-arrow-path')
                                                    ->tooltip('Reset to UMK')
                                                    ->action(function (Set $set, Get $get) {
                                                        $areaId = $get('../../project_area_id');
                                                        if (! $areaId) {
                                                            return;
                                                        }
                                                        $umk = RegencyMinimumWage::where('project_area_id', $areaId)
                                                            ->where('year', date('Y'))
                                                            ->where('is_active', true)
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
                                        Select::make('risk_level')
                                            ->label('Risk Level (JKK)')
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
                                            ->columnSpan(1),
                                        Select::make('employee_type')
                                            ->label('Participation Category')
                                            ->options([
                                                'ppu' => 'Penerima Upah (PPU)',
                                                'pbpu' => 'Bukan Penerima Upah (PBPU)',
                                            ])
                                            ->required()
                                            ->default('ppu')
                                            ->live()
                                            ->columnSpan(1),
                                        TextInput::make('notes')
                                            ->label('Notes')
                                            ->placeholder('Optional notes...')
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                    ]),

                                \Filament\Schemas\Components\Section::make('Compensation & Accruals')
                                    ->compact()
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Toggle::make('is_labor_intensive')
                                                    ->label('Labor Intensive')
                                                    ->default(false)
                                                    ->live(),
                                                Toggle::make('bill_thr_monthly')
                                                    ->label('Bill THR Monthly')
                                                    ->default(true)
                                                    ->live(),
                                                Toggle::make('bill_compensation_monthly')
                                                    ->label('Bill Comp Monthly')
                                                    ->default(true)
                                                    ->live(),
                                                Toggle::make('include_non_fixed_in_accruals')
                                                    ->label('Incl. Non-Fixed')
                                                    ->helperText('Include non-fixed in THR/Comp basis.')
                                                    ->default(false)
                                                    ->live(),
                                            ]),
                                    ]),

                                \Filament\Schemas\Components\Section::make('Allowances')
                                    ->description('Define remuneration components for this position. These are project-specific and will override any defaults.')
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
                                                            ->placeholder('e.g. Position Allowance, Meal, Transport')
                                                            ->helperText('Name of the remuneration component.')
                                                            ->required()
                                                            ->columnSpan(2),
                                                        Select::make('type')
                                                            ->label('Calculation Type')
                                                            ->options([
                                                                'nominal' => 'Fixed Amount (Rp)',
                                                                'percentage' => 'Percentage of Basic Salary (%)',
                                                            ])
                                                            ->default('nominal')
                                                            ->required()
                                                            ->live()
                                                            ->helperText('How the allowance amount is calculated.')
                                                            ->columnSpan(1),
                                                        Toggle::make('is_fixed')
                                                            ->label('Fixed Allowance')
                                                            ->default(true)
                                                            ->helperText('Fixed = included in Upah (base for BPJS/tax). Non-fixed = accrual basis only.')
                                                            ->columnSpan(1),
                                                        TextInput::make('value')
                                                            ->label(fn (Get $get) => $get('type') === 'percentage' ? 'Rate (%)' : 'Amount (Rp)')
                                                            ->placeholder(fn (Get $get) => $get('type') === 'percentage' ? 'e.g. 5' : 'e.g. 500000')
                                                            ->helperText(fn (Get $get) => $get('type') === 'percentage'
                                                                ? 'Percentage of basic salary. e.g. 5 = 5% of basic salary.'
                                                                : 'Monthly nominal amount in Rupiah.')
                                                            ->numeric()
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
                                    ->label('Annual Equipment & Trainings')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->placeholder('Uniform, Gada Pratama...')
                                                    ->required()
                                                    ->columnSpan(1),
                                                TextInput::make('annual_amount')
                                                    ->label('Annual Budget')
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->required()
                                                    ->live(onBlur: true)
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
                            ->addActionLabel('Add Job Position')
                            ->itemLabel(fn (array $state): ?string => filled($state['job_position_id'] ?? null) ? JobPosition::find($state['job_position_id'])?->name : 'New Role')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $set('simulation_trigger', uniqid());
                            }),
                    ])
                    ->columns(2),

                Step::make('Cost Simulation')
                    ->description('Review estimated monthly costs for this template.')
                    ->icon('heroicon-m-calculator')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('cost_simulation_table')
                            ->label('Projected Monthly Cost Details')
                            ->html()
                            ->state(function (Get $get) {
                                $items = $get('items') ?? [];
                                $areaId = $get('project_area_id');

                                if (empty($items) || ! $areaId) {
                                    return new HtmlString('<div class="rounded-xl border border-dashed border-gray-300 p-8 text-center"><p class="text-sm text-gray-500">Please complete the previous steps to view the cost simulation.</p></div>');
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

                                    // Read allowances directly from item (defined per-project, not from master data)
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
                                        ptkpCode: 'TK/0'
                                    );

                                    $unitCost = $res['total_direct_cost'];
                                    $lineTotal = $unitCost * $qty;
                                    $totalTemplateCost += $lineTotal;

                                    $fmt = fn ($val) => number_format($val, 0, ',', '.');

                                    // Subtotals for matching spreadsheet
                                    $subA = $res['upah'] + $res['allowances']['non_fixed'];
                                    $subB = $res['accruals']['thr'] + $res['accruals']['compensation'];
                                    $subC = $res['bpjs_total'];
                                    $subD = $res['pph21']['total'];
                                    $subE = $res['extra_costs_total'];

                                    $rows .= "
                                        <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-xs'>
                                            <td class='px-2 py-3'>
                                                <div class='font-medium text-gray-900 dark:text-gray-100'>{$jp->code}</div>
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
                                                    <th scope='col' class='px-2 py-3'>Job</th>
                                                    <th scope='col' class='px-2 py-3 text-center'>Qty</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>Salary</th>
                                                    <th scope='col' class='px-2 py-3 text-right' title='THR & Compensation'>Accrual</th>
                                                    <th scope='col' class='px-2 py-3 text-right' title='BPJS Health & Employment'>BPJS</th>
                                                    <th scope='col' class='px-2 py-3 text-right'>Tax</th>
                                                    <th scope='col' class='px-2 py-3 text-right' title='Equipment & Training'>Extra</th>
                                                    <th scope='col' class='px-2 py-3 text-right bg-blue-50/50 dark:bg-blue-900/10'>Total/Pax</th>
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
                                            <span class='font-bold uppercase shrink-0'>Note:</span>
                                            <ul class='list-disc list-inside space-y-1 opacity-80'>
                                                <li>Calculation matches BPJS PPU/PBPU categories.</li>
                                                <li>Tax uses TER (Passel 17) method based on monthly projected billing.</li>
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
