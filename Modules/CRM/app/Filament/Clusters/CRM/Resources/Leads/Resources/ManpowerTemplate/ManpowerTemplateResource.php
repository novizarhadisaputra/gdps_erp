<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
use Filament\Support\Icons\Heroicon;
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

    protected static ?string $navigationLabel = 'Manpower Costing';

    protected static ?string $pluralLabel = 'Manpower Costing';

    protected static ?string $singularLabel = 'Manpower Costing';

    protected static ?string $slug = 'manpower-costing';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Costing Identification')
                        ->description('Define basic costing details and project area.')
                        ->icon('heroicon-m-identification')
                        ->schema([
                            TextEntry::make('import_status')
                                ->label('Source')
                                ->state('Imported')
                                ->visible(fn ($record) => $record?->is_imported)
                                ->columnSpanFull(),
                            TextInput::make('code')
                                ->hidden(fn (string $operation): bool => $operation === 'create')
                                ->disabled()
                                ->dehydrated(false)
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
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
                            Select::make('contract_type_id')
                                ->label('Contract Type')
                                ->relationship('contractType', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
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
                                ->label('Job Positions & Quantities')
                                ->schema([
                                    Select::make('job_position_id')
                                        ->label('Job Position')
                                        ->relationship('jobPosition', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->placeholder('Select job position')
                                        ->helperText('Select job position to register personnel.')
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
                                        ->placeholder('Personnel quantity')
                                        ->helperText('Number of personnel for this position.')
                                        ->columnSpan(1),
                                    TextInput::make('basic_salary')
                                        ->label('Basic Salary')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->placeholder('Gaji pokok')
                                        ->helperText('Besaran gaji pokok per bulan (Default UMK).')
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
                                        ->helperText('Menentukan tarif premi JKK.')
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
                                        ->helperText('Metode keikutsertaan BPJS.')
                                        ->options([
                                            'ppu' => 'Penerima Upah (PPU)',
                                            'pbpu' => 'Bukan Penerima Upah (PBPU/Mandiri)',
                                        ])
                                        ->required()
                                        ->default('ppu')
                                        ->live(),
                                    Toggle::make('is_labor_intensive')
                                        ->label('Is Labor Intensive?')
                                        ->helperText('Aktifkan jika pekerjaan padat karya (diskon 50% JKK).')
                                        ->default(false)
                                        ->live(),
                                    Toggle::make('bill_thr_monthly')
                                        ->label('Bill THR Monthly')
                                        ->helperText('Apakah THR ditagihkan secara akrual bulanan?')
                                        ->default(true)
                                        ->live(),
                                    Toggle::make('bill_compensation_monthly')
                                        ->label('Bill Compensation Monthly')
                                        ->helperText('Apakah dana kompensasi ditagihkan secara akrual bulanan?')
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

                                        $jp = JobPosition::with(['fixedAllowances', 'nonFixedAllowances'])->find($jpId);
                                        if (! $jp) {
                                            continue;
                                        }

                                        $allowances = [];
                                        foreach ($jp->fixedAllowances ?? [] as $allowance) {
                                            $allowances[] = [
                                                'name' => $allowance->name,
                                                'type' => 'nominal',
                                                'value' => $allowance->pivot->amount,
                                                'is_fixed' => true,
                                            ];
                                        }
                                        foreach ($jp->nonFixedAllowances ?? [] as $allowance) {
                                            $allowances[] = [
                                                'name' => $allowance->name,
                                                'type' => 'nominal',
                                                'value' => $allowance->pivot->amount,
                                                'is_fixed' => false,
                                            ];
                                        }

                                        $basicSalary = (float) ($item['basic_salary'] ?? 0);

                                        $res = $service->calculate(
                                            basicSalary: $basicSalary,
                                            allowances: $allowances,
                                            projectAreaId: $areaId,
                                            year: date('Y'),
                                            contractTypeId: $get('contract_type_id'),
                                            workSchemeId: $get('work_scheme_id'),
                                            riskLevel: $riskLevel,
                                            isLaborIntensive: $isLaborIntensive,
                                            employeeType: $employeeType,
                                            billThrMonthly: $billThr,
                                            billCompensationMonthly: $billComp,
                                            ptkpCode: 'TK/0'
                                        );

                                        $unitCost = $res['total_direct_cost'];
                                        $lineTotal = $unitCost * $qty;
                                        $totalTemplateCost += $lineTotal;

                                        $fmt = fn ($val) => number_format($val, 0, ',', '.');

                                        $rows .= "
                                            <tr class='border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors'>
                                                <td class='px-4 py-3'>
                                                    <div class='font-medium text-gray-900 dark:text-gray-100'>{$jp->code}</div>
                                                </td>
                                                <td class='px-4 py-3 text-center'>{$qty}</td>
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
                                                        <th scope='col' class='px-4 py-3'>Code</th>
                                                        <th scope='col' class='px-4 py-3 text-center'>Qty</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>Direct Cost / Person</th>
                                                        <th scope='col' class='px-4 py-3 text-right'>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {$rows}
                                                </tbody>
                                                <tfoot>
                                                    <tr class='font-bold text-gray-900 dark:text-white bg-gray-100/50 dark:bg-gray-800/50'>
                                                        <td colspan='2' class='px-4 py-4 text-right uppercase tracking-wider'>Total Estimated Monthly Cost</td>
                                                        <td colspan='2' class='px-4 py-4 text-right text-lg text-primary-600'>Rp ".number_format($totalTemplateCost, 0, ',', '.')."</td>
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
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->toolbarActions([
                //
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
