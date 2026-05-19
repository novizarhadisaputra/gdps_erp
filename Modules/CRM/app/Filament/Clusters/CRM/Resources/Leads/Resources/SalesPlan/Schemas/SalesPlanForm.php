<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Schemas;

use App\Traits\ParsesCurrency;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Enums\ProrationMethod;
use Modules\CRM\Models\SalesPlan;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Schemas\JobPositionForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas\RevenueSegmentForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Schemas\SkillCategoryForm;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\SkillCategory;

class SalesPlanForm
{
    use ParsesCurrency;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make(__('Service Categorization'))

                    ->description(__('Classify the project into master data segments.'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('revenue_segment_id')
                                    ->label(__('Revenue Segment'))
                                    ->relationship('revenueSegment', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The category of the revenue segment.'))
                                    ->createOptionForm(RevenueSegmentForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => RevenueSegment::create($data)->id)
                                    ->editOptionForm(RevenueSegmentForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('product_cluster_id')
                                    ->label(__('Product Cluster'))
                                    ->relationship('productCluster', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The grouping of the product or service.'))
                                    ->createOptionForm(ProductClusterForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => ProductCluster::create($data)->id)
                                    ->editOptionForm(ProductClusterForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('project_type_id')
                                    ->label(__('Project Type'))
                                    ->relationship('projectType', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('The contractual type of the project.'))
                                    ->createOptionForm(ProjectTypeForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => ProjectType::create($data)->id)
                                    ->editOptionForm(ProjectTypeForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('skill_category_id')
                                    ->label(__('Skill Category'))
                                    ->relationship('skillCategory', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(SkillCategoryForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => SkillCategory::create($data)->id)
                                    ->editOptionForm(SkillCategoryForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('industrial_sector_id')
                                    ->label(__('Industrial Sector'))
                                    ->relationship('industrialSector', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(IndustrialSectorForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => IndustrialSector::create($data)->id)
                                    ->editOptionForm(IndustrialSectorForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('project_area_id')
                                    ->label(__('Project Area'))
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
                                    ->required()
                                    ->searchable()
                                    ->preload()
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
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            ]),
                    ]),

                Step::make(__('Financials & Timeline'))
                    ->description(__('Set project dates and estimated revenue.'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->native(false)
                                    ->required()
                                    ->live()
                                    ->helperText(__('Expected starting date.')),
                                DatePicker::make('end_date')
                                    ->native(false)
                                    ->required()
                                    ->live()
                                    ->helperText(__('Expected completion date.')),
                                TextInput::make('cutoff_day')
                                    ->label(__('Cut-off Day'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(28)
                                    ->default(25)
                                    ->required()
                                    ->live()
                                    ->helperText(__('Day of the month to cut off the revenue cycle (e.g., 25th).')),
                                Select::make('proration_method')
                                    ->label(__('Proration Method'))
                                    ->options(ProrationMethod::class)
                                    ->default(ProrationMethod::Equal)
                                    ->required()
                                    ->live()
                                    ->helperText(__('How to distribute revenue across the timeline.')),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('estimated_value')
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->required()
                                    ->default(0)
                                    ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                    ->live()
                                    ->helperText(__('Total estimated contract value.')),
                                TextInput::make('management_fee_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->required()
                                    ->helperText(__('Management fee % charged internally.')),
                                TextInput::make('npm_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->required()
                                    ->hidden()
                                    ->helperText(__('Target profit margin percentage. Automatically populated from Profitability Analysis.')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('payment_term_id')
                                    ->label(__('Payment Term'))
                                    ->relationship('paymentTerm', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            return;
                                        }
                                        $term = PaymentTerm::find($state);
                                        if ($term) {
                                            $set('top_days', $term->days);
                                        }
                                    })
                                    ->helperText(__('Select from master data to auto-fill days.')),
                                TextInput::make('top_days')
                                    ->label(__('ToP (Days)'))
                                    ->numeric()
                                    ->default(30)
                                    ->readOnly()
                                    ->dehydrated()
                                    ->helperText(__('Terms of Payment (days from invoice).')),
                            ]),
                    ]),

                Step::make(__('Revenue Distribution'))
                    ->description(__('Define resource needs and monthly breakdown.'))
                    ->schema([
                        Section::make(__('Job Positions'))
                            ->description(__('Required resource types for this project.'))
                            ->schema([
                                Select::make('job_positions')
                                    ->multiple()
                                    ->options(JobPosition::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(JobPositionForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => JobPosition::create($data)->id)
                                    ->editOptionForm(JobPositionForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver())
                                    ->helperText(__('Select the required job positions for project headcount mapping.')),
                            ]),
                        Section::make(__('Revenue Distribution Planning'))
                            ->headerActions([
                                Action::make(__('generate'))
                                    ->label(__('Generate from Timeline'))
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function (Get $get, Set $set) {
                                        $startDateInput = $get('start_date');
                                        $endDateInput = $get('end_date');
                                        $totalValue = self::parseCurrency($get('estimated_value'));
                                        $cutoffDay = (int) ($get('cutoff_day') ?? 25);
                                        $methodInput = $get('proration_method');
                                        $method = $methodInput instanceof ProrationMethod
                                            ? $methodInput
                                            : ProrationMethod::tryFrom($methodInput);

                                        if (! $startDateInput || ! $endDateInput || $totalValue <= 0 || ! $method) {
                                            return;
                                        }

                                        $startDate = Carbon::parse($startDateInput);
                                        $endDate = Carbon::parse($endDateInput);
                                        $topDays = (int) ($get('top_days') ?? 0);

                                        if ($method === ProrationMethod::Equal) {
                                            $start = $startDate->copy()->startOfMonth();
                                            $end = $endDate->copy()->startOfMonth();

                                            $count = 0;
                                            $temp = $start->copy();
                                            while ($temp <= $end) {
                                                $count++;
                                                $temp->addMonth();
                                            }

                                            if ($count === 0) {
                                                return;
                                            }

                                            $average = $totalValue / $count;
                                            $months = [];
                                            $current = $start->copy();
                                            $runningSum = 0;

                                            for ($i = 0; $i < $count; $i++) {
                                                if ($i === $count - 1) {
                                                    // Last month: adjustment to match total
                                                    $amount = $totalValue - $runningSum;
                                                } else {
                                                    $amount = round($average, 2);
                                                    $runningSum += $amount;
                                                }

                                                $months[] = [
                                                    'month' => $current->format('F Y'),
                                                    'budget_amount' => $amount,
                                                    'forecast_amount' => $amount,
                                                ];
                                                $current->addMonth();
                                            }
                                            $set('revenue_distribution_planning', $months);

                                            return;
                                        }

                                        // Daily Prorated Logic with Cut-off Day
                                        $totalDays = $startDate->diffInDays($endDate) + 1;
                                        if ($totalDays <= 0) {
                                            return;
                                        }

                                        $distribution = [];
                                        $current = $startDate->copy();

                                        while ($current <= $endDate) {
                                            // Determine the end of the current cycle
                                            // A cycle for "Month M" with cutoff D is: (D+1) of M-1 to D of M
                                            if ($current->day <= $cutoffDay) {
                                                $cycleEnd = $current->copy()->day($cutoffDay);
                                            } else {
                                                $cycleEnd = $current->copy()->addMonthNoOverflow()->day($cutoffDay);
                                            }

                                            // Clamp cycle end to project end date
                                            if ($cycleEnd > $endDate) {
                                                $cycleEnd = $endDate->copy();
                                            }

                                            $daysInCycle = $current->diffInDays($cycleEnd) + 1;
                                            $amount = ($daysInCycle / $totalDays) * $totalValue;

                                            // The revenue is recognized in the month of cycleEnd
                                            $monthLabel = $cycleEnd->format('F Y');

                                            // Accumulate if same month (unlikely with this logic but safe)
                                            $found = false;
                                            foreach ($distribution as &$item) {
                                                if ($item['month'] === $monthLabel) {
                                                    $item['budget_amount'] += $amount;
                                                    $item['forecast_amount'] += $amount;
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (! $found) {
                                                $distribution[] = [
                                                    'month' => $monthLabel,
                                                    'budget_amount' => $amount,
                                                    'forecast_amount' => $amount,
                                                ];
                                            }
                                            $current = $cycleEnd->copy()->addDay();
                                        }

                                        // Round amounts and ensure total matches
                                        $runningSum = 0;
                                        $count = count($distribution);
                                        foreach ($distribution as $index => &$item) {
                                            if ($index === $count - 1) {
                                                $item['budget_amount'] = round($totalValue - $runningSum, 2);
                                                $item['forecast_amount'] = round($totalValue - $runningSum, 2);
                                            } else {
                                                $item['budget_amount'] = round($item['budget_amount'], 2);
                                                $item['forecast_amount'] = round($item['forecast_amount'], 2);
                                                $runningSum += $item['budget_amount'];
                                            }
                                        }

                                        $set('revenue_distribution_planning', $distribution);
                                    }),
                            ])
                            ->description(__('Monthly revenue breakdown. Use the button above to auto-generate based on dates and total value.'))
                            ->hiddenOn(operations: ['create'])
                            ->schema([
                                Repeater::make('revenue_distribution_planning')
                                    ->label(__('Monthly Breakdown'))
                                    ->schema([
                                        TextInput::make('month')
                                            ->label(__('Month'))
                                            ->readOnly()
                                            ->required(),
                                        TextInput::make('budget_amount')
                                            ->label(__('Budget (IDR)'))
                                            ->prefix('IDR')
                                            ->required()
                                            ->default(0)
                                            ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2),
                                        TextInput::make('forecast_amount')
                                            ->label(__('Forecast (IDR)'))
                                            ->prefix('IDR')
                                            ->required()
                                            ->default(0)
                                            ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2),
                                    ])
                                    ->columns(2)
                                    ->reorderable(false),
                            ]),
                    ]),

                Step::make(__('Governance'))
                    ->description(__('Review confidence levels.'))
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Select::make('confidence_level')
                                    ->options(ConfidenceLevel::class)
                                    ->required()
                                    ->helperText(__('The degree of confidence or probability of success for this project.')),
                                Section::make(__('Document Tracking'))
                                    ->description(__('Reference numbers for generated documents. Automatically synced from respective modules.'))
                                    ->icon(Heroicon::OutlinedDocumentCheck)
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('proposal_number')
                                                    ->label(__('Proposal Number'))
                                                    ->state(fn (SalesPlan $record): ?string => $record->proposal?->number ?? $record->proposal_number ?? 'Pending...'),

                                                TextEntry::make('contract_number')
                                                    ->label(__('Contract / PKS Number'))
                                                    ->state(fn (SalesPlan $record): ?string => $record->agreement?->number ?? $record->contract_number ?? 'Pending...'),

                                                TextEntry::make('po_number')
                                                    ->label(__('Purchase Order Number'))
                                                    ->state(fn (SalesPlan $record): ?string => $record->purchaseOrder?->number ?? $record->po_number ?? 'Pending...'),

                                                TextEntry::make('so_number')
                                                    ->label(__('Sales Order Number'))
                                                    ->state(fn (SalesPlan $record): ?string => $record->salesOrder?->number ?? $record->so_number ?? 'Pending...'),

                                                TextEntry::make('wo_number')
                                                    ->label(__('Work Order / SPK Number'))
                                                    ->state(fn (SalesPlan $record): ?string => $record->workOrder?->number ?? $record->wo_number ?? 'Pending...'),

                                                TextEntry::make('ba_number')
                                                    ->label(__('BAPP / BA Number'))
                                                    ->state(fn (SalesPlan $record): ?string => $record->ba_number ?? 'Pending...'),
                                            ]),
                                    ]),
                            ]),
                    ]),
            ])->columnSpanFull()->persistStepInQueryString(),
        ];
    }
}
