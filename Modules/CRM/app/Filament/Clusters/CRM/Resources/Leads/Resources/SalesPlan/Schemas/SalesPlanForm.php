<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\SalesPlan\Schemas;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Enums\ProrationMethod;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;
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
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Wizard::make([
                Step::make('Service Categorization')

                    ->description('Classify the project into master data segments.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('revenue_segment_id')
                                    ->label('Revenue Segment')
                                    ->relationship('revenueSegment', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('The category of the revenue segment.')
                                    ->createOptionForm(RevenueSegmentForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => RevenueSegment::create($data)->id)
                                    ->editOptionForm(RevenueSegmentForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('product_cluster_id')
                                    ->label('Product Cluster')
                                    ->relationship('productCluster', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('The grouping of the product or service.')
                                    ->createOptionForm(ProductClusterForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => ProductCluster::create($data)->id)
                                    ->editOptionForm(ProductClusterForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                                Select::make('project_type_id')
                                    ->label('Project Type')
                                    ->relationship('projectType', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('The contractual type of the project.')
                                    ->createOptionForm(ProjectTypeForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => ProjectType::create($data)->id)
                                    ->editOptionForm(ProjectTypeForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('skill_category_id')
                                    ->label('Skill Category')
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
                                    ->label('Industrial Sector')
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
                                    ->label('Project Area')
                                    ->relationship('projectArea', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm(ProjectAreaForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->createOptionUsing(fn (array $data) => ProjectArea::create($data)->id)
                                    ->editOptionForm(ProjectAreaForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            ]),
                    ]),

                Step::make('Financials & Timeline')
                    ->description('Set project dates and estimated revenue.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->native(false)
                                    ->required()
                                    ->live()
                                    ->helperText('Expected starting date.'),
                                DatePicker::make('end_date')
                                    ->native(false)
                                    ->required()
                                    ->live()
                                    ->helperText('Expected completion date.'),
                                TextInput::make('cutoff_day')
                                    ->label('Cut-off Day')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(28)
                                    ->default(25)
                                    ->required()
                                    ->live()
                                    ->helperText('Day of the month to cut off the revenue cycle (e.g., 25th).'),
                                Select::make('proration_method')
                                    ->label('Proration Method')
                                    ->options(ProrationMethod::class)
                                    ->default(ProrationMethod::Equal)
                                    ->required()
                                    ->live()
                                    ->helperText('How to distribute revenue across the timeline.'),
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
                                    ->helperText('Total estimated contract value.'),
                                TextInput::make('management_fee_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->required()
                                    ->helperText('Management fee % charged internally.'),
                                TextInput::make('npm_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->required()
                                    ->hidden()
                                    ->helperText('Target profit margin percentage. Automatically populated from Profitability Analysis.'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('payment_term_id')
                                    ->label('Payment Term')
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
                                    ->helperText('Select from master data to auto-fill days.'),
                                TextInput::make('top_days')
                                    ->label('ToP (Days)')
                                    ->numeric()
                                    ->default(30)
                                    ->readOnly()
                                    ->dehydrated()
                                    ->helperText('Terms of Payment (days from invoice).'),
                            ]),
                    ]),

                Step::make('Revenue Distribution')
                    ->description('Define resource needs and monthly breakdown.')
                    ->schema([
                        Section::make('Job Positions')
                            ->description('Required resource types for this project.')
                            ->schema([
                                Select::make('job_positions')
                                    ->multiple()
                                    ->options(JobPosition::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select the required job positions for project headcount mapping.'),
                            ]),
                        Section::make('Revenue Distribution Planning')
                            ->headerActions([
                                Action::make('generate')
                                    ->label('Generate from Timeline')
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
                                            for ($i = 0; $i < $count; $i++) {
                                                $months[] = [
                                                    'month' => $current->format('F Y'),
                                                    'budget_amount' => round($average, 2),
                                                    'forecast_amount' => round($average, 2),
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

                                        // Round amounts
                                        foreach ($distribution as &$item) {
                                            $item['budget_amount'] = round($item['budget_amount'], 2);
                                            $item['forecast_amount'] = round($item['forecast_amount'], 2);
                                        }

                                        $set('revenue_distribution_planning', $distribution);
                                    }),
                            ])
                            ->description('Monthly revenue breakdown. Use the button above to auto-generate based on dates and total value.')
                            ->hiddenOn(operations: ['create'])
                            ->schema([
                                Repeater::make('revenue_distribution_planning')
                                    ->label('Monthly Breakdown')
                                    ->schema([
                                        TextInput::make('month')
                                            ->label('Month')
                                            ->readOnly()
                                            ->required(),
                                        TextInput::make('budget_amount')
                                            ->label('Budget (IDR)')
                                            ->prefix('IDR')
                                            ->required()
                                            ->default(0)
                                            ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2),
                                        TextInput::make('forecast_amount')
                                            ->label('Forecast (IDR)')
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

                Step::make('Governance')
                    ->description('Review confidence levels.')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Select::make('confidence_level')
                                    ->options(ConfidenceLevel::class)
                                    ->required()
                                    ->helperText('The degree of confidence or probability of success for this project.'),
                            ]),
                    ]),
            ])->columnSpanFull()->persistStepInQueryString(),
        ];
    }

    protected static function parseCurrency($value): float
    {
        if (! $value) {
            return 0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove dots (thousand separator) and replace comma with dot (decimal separator)
        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }
}
