<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\RegencyMinimumWage;

class ManpowerTemplateResource extends Resource
{
    protected static ?string $model = ManpowerTemplate::class;

    protected static bool $isNested = true;

    protected static ?string $parentResource = LeadResource::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Template Identification')
                        ->description('Define basic template details and project area.')
                        ->icon('heroicon-m-identification')
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('import_status')
                                ->label('Source')
                                ->content('Imported via AI')
                                ->visible(fn ($record) => $record?->is_imported)
                                ->columnSpanFull(),
                            TextInput::make('code')
                                ->hidden(fn (string $operation): bool => $operation === 'create')
                                ->disabled()
                                ->dehydrated(false)
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('name')
                                ->label('Template Name')
                                ->placeholder('e.g., Standard Security Packet')
                                ->required()
                                ->maxLength(255),
                            Select::make('project_area_id')
                                ->label('Project Area')
                                ->relationship('projectArea', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('Target area for this template (determines UMK/Minimum Wage).'),
                            Textarea::make('description')
                                ->label('Template Description')
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
                                ->label('Job Positions & Quantities')
                                ->schema([
                                    Select::make('job_position_id')
                                        ->label('Job Position')
                                        ->relationship('jobPosition', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
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
                                        ->label('Qty')
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
                                    TextInput::make('notes')
                                        ->label('Notes')
                                        ->placeholder('Additional notes for this role...')
                                        ->maxLength(255)
                                        ->columnSpan(4),
                                ])
                                ->columns(8)
                                ->defaultItems(1)
                                ->addActionLabel('Add Job Position')
                                ->itemLabel(fn (array $state): ?string => filled($state['job_position_id'] ?? null) ? JobPosition::find($state['job_position_id'])?->name : 'New Role')
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $set('simulation_trigger', uniqid());
                                }),
                        ]),

                    Step::make('Costing & BPJS Configuration')
                        ->description('Advanced parameters for BPJS, Tax, and Accruals.')
                        ->icon('heroicon-m-adjustments-horizontal')
                        ->schema([
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
                                        ->live(),
                                    Select::make('employee_type')
                                        ->label('Employee Participation')
                                        ->options([
                                            'ppu' => 'Penerima Upah (PPU)',
                                            'pbpu' => 'Bukan Penerima Upah (PBPU/Mandiri)',
                                        ])
                                        ->required()
                                        ->default('ppu')
                                        ->live(),
                                    Toggle::make('is_labor_intensive')
                                        ->label('Is Labor Intensive?')
                                        ->helperText('Enable for 50% JKK reduction if applicable.')
                                        ->default(false)
                                        ->live(),
                                    Toggle::make('bill_thr_monthly')
                                        ->label('Bill THR Monthly')
                                        ->default(true)
                                        ->live(),
                                    Toggle::make('bill_compensation_monthly')
                                        ->label('Bill Compensation Monthly')
                                        ->default(true)
                                        ->live(),
                                ]),
                        ]),

                    Step::make('Cost Simulation')
                        ->description('Review estimated monthly costs for this template.')
                        ->icon('heroicon-m-calculator')
                        ->schema([
                            TextEntry::make('cost_simulation_table')
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

                                    // Get template-level overrides
                                    $riskLevel = $get('risk_level') ?? 'very_low';
                                    $isLaborIntensive = (bool) ($get('is_labor_intensive') ?? false);
                                    $employeeType = $get('employee_type') ?? 'ppu';
                                    $billThr = (bool) ($get('bill_thr_monthly') ?? true);
                                    $billComp = (bool) ($get('bill_compensation_monthly') ?? true);

                                    foreach ($items as $item) {
                                        $jpId = $item['job_position_id'] ?? null;
                                        $qty = (int) ($item['quantity'] ?? 0);

                                        if (! $jpId || $qty <= 0) {
                                            continue;
                                        }

                                        $jp = JobPosition::with('remunerationComponents')->find($jpId);
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

                                        $basicSalary = (float) ($item['basic_salary'] ?? 0);

                                        $res = $service->calculate(
                                            basicSalary: $basicSalary,
                                            allowances: $allowances,
                                            projectAreaId: $areaId,
                                            year: date('Y'),
                                            riskLevel: $riskLevel,
                                            isLaborIntensive: $isLaborIntensive,
                                            employeeType: $employeeType,
                                            billThrMonthly: $billThr,
                                            billCompensationMonthly: $billComp
                                        );

                                        $unitCost = $res['total_direct_cost'];
                                        $lineTotal = $unitCost * $qty;
                                        $totalTemplateCost += $lineTotal;

                                        $fmt = fn ($val) => number_format($val, 0, ',', '.');

                                        $rows .= "
                                            <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors'>
                                                <td class='px-4 py-3'>
                                                    <div class='font-medium text-gray-900 dark:text-gray-100'>{$jp->name}</div>
                                                    <div class='text-xs text-gray-400'>Qty: {$qty}</div>
                                                </td>
                                                <td class='px-4 py-3 text-right text-gray-600 dark:text-gray-400'>Rp {$fmt($basicSalary)}</td>
                                                <td class='px-4 py-3 text-right text-gray-600 dark:text-gray-400'>Rp {$fmt($res['total_allowances'] ?? 0)}</td>
                                                <td class='px-4 py-3 text-right text-gray-600 dark:text-gray-400'>Rp {$fmt($res['bpjs_total'] ?? 0)}</td>
                                                <td class='px-4 py-3 text-right text-gray-600 dark:text-gray-400'>Rp {$fmt($res['thr_compensation'] ?? 0)}</td>
                                                <td class='px-4 py-3 text-right font-medium text-primary-600'>Rp {$fmt($unitCost)}</td>
                                                <td class='px-4 py-3 text-right font-bold text-gray-900 dark:text-white'>Rp {$fmt($lineTotal)}</td>
                                            </tr>
                                        ";
                                    }

                                    return new HtmlString("
                                        <div class='relative overflow-x-auto shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700'>
                                            <table class='w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400'>
                                                <thead class='text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400'>
                                                    <tr>
                                                        <th scope='col' class='px-4 py-3'>Position & Qty</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>Basic Salary</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>Allowance</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>BPJS & Tax</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>Holiday Allowance / Comp.</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>Total / Person</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {$rows}
                                                </tbody>
                                                <tfoot>
                                                    <tr class='font-bold text-gray-900 dark:text-white bg-gray-100/50 dark:bg-gray-800/50'>
                                                        <td colspan='6' class='px-4 py-4 text-right uppercase tracking-wider'>Total Estimated Monthly Cost</td>
                                                        <td class='px-4 py-4 text-right text-lg text-primary-600'>Rp ".number_format($totalTemplateCost, 0, ',', '.')."</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class='mt-4 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800'>
                                            <p class='text-xs text-blue-700 dark:text-blue-300'>
                                                <span class='font-bold uppercase mr-1'>Note:</span> The calculation above is an estimate based on the latest Minimum Wage (UMK) parameters and BPJS variables. The actual realization may differ depending on the specific BPJS configuration in the Finance module.
                                            </p>
                                        </div>
                                    ");
                                }),
                            Hidden::make('simulation_trigger'),
                        ]),
                ])->columnSpanFull()->persistStepInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('projectArea.name')
                    ->label('Project Area')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Positions'),
                ToggleColumn::make('is_active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $costSimulation = $record->getCostSimulation();
                        $pdf = Pdf::loadView('crm::pdf.manpower_template', [
                            'record' => $record,
                            'costSimulation' => $costSimulation,
                        ]);
                        $name = Str::slug($record->name, '-');

                        return response()->streamDownload(fn () => print ($pdf->output()), "manpower-template-{$name}.pdf");
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListManpowerTemplates::route('/'),
            'create' => Pages\CreateManpowerTemplate::route('/create'),
            'view' => Pages\ViewManpowerTemplate::route('/{record}'),
            'edit' => Pages\EditManpowerTemplate::route('/{record}/edit'),
        ];
    }
}
