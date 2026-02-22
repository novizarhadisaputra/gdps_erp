<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas;

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
use Filament\Schemas\Schema;
use Modules\CRM\Models\SalesPlan;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas\RevenueSegmentForm;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\SkillCategory;

class SalesPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(SalesPlan::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Core Project Information')
                ->description('Fundamental project categorization.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('revenue_segment_id')
                                ->relationship('revenueSegment', 'name')
                                ->label('Revenue Segment')
                                ->required()
                                ->createOptionForm(RevenueSegmentForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => RevenueSegment::create($data)->id)
                                ->editOptionForm(RevenueSegmentForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            Select::make('industrial_sector_id')
                                ->relationship('industrialSector', 'name')
                                ->label('Industrial Sector')
                                ->required()
                                ->createOptionForm(IndustrialSectorForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => IndustrialSector::create($data)->id)
                                ->editOptionForm(IndustrialSectorForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('project_type_id')
                                ->relationship('projectType', 'name')
                                ->label('Project Type')
                                ->required()
                                ->createOptionForm(ProjectTypeForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => ProjectType::create($data)->id)
                                ->editOptionForm(ProjectTypeForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            Select::make('skill_category_id')
                                ->relationship('skillCategory', 'name')
                                ->label('Skill Category')
                                ->required(),
                            Select::make('job_positions')
                                ->label('Job Positions')
                                ->multiple()
                                ->options([
                                    'Security' => 'Security',
                                    'Driver' => 'Driver',
                                    'SPG' => 'SPG',
                                    'Merchandizer' => 'Merchandizer',
                                    'Cleaner' => 'Cleaner',
                                    'Engineer' => 'Engineer',
                                    'Office Boy' => 'Office Boy',
                                    'Receptionist' => 'Receptionist',
                                ])
                                ->required(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('agreement_id')
                                ->relationship('agreement', 'contract_number', fn ($query, Get $get) => $query->where('lead_id', $get('../../lead_id'))->where('type', 'agreement'))
                                ->label('Agreement No.')
                                ->searchable()
                                ->preload()
                                ->helperText('Linked to formal Agreement (PKS).'),
                            Select::make('work_order_id')
                                ->relationship('workOrder', 'contract_number', fn ($query, Get $get) => $query->where('lead_id', $get('../../lead_id'))->where('type', 'work_order'))
                                ->label('Work Order No.')
                                ->searchable()
                                ->preload()
                                ->helperText('Linked to informal Work Order (SPK).'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('po_number')
                                ->label('Purchase Order (PO) No.'),
                            TextInput::make('ba_number')
                                ->label('Minutes/Handover (BA) No.'),
                            TextInput::make('so_number')
                                ->label('Sales Order (SO) No.'),
                        ]),
                        ]),

            Section::make('Financials & Timeline')
                ->description('Revenue forecasting and credit terms.')
                ->schema([
                    Grid::make(4)
                        ->schema([
                            TextInput::make('estimated_value')
                                ->prefix('IDR')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->label('Total Estimated Value')
                                ->required()
                                ->default(0)
                                ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, Get $get, Set $set) => self::distributeRevenue($state, $get, $set)),
                            TextInput::make('management_fee_percentage')
                                ->numeric()
                                ->suffix('%')
                                ->label('M. Fee %')
                                ->default(0),
                            TextInput::make('npm_percentage')
                                ->numeric()
                                ->suffix('%')
                                ->label('NPM %')
                                ->default(0),
                            TextInput::make('top_days')
                                ->numeric()
                                ->suffix('Days')
                                ->label('Terms of Payment'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            DatePicker::make('start_date')
                                ->label('Project Start Date')
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, Get $get, Set $set) => self::distributeRevenue($get('estimated_value'), $get, $set)),
                            DatePicker::make('end_date')
                                ->label('Project End Date')
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, Get $get, Set $set) => self::distributeRevenue($get('estimated_value'), $get, $set)),
                        ]),
                ]),

            Section::make('Governance & Confidence')
                ->description('Reference codes and forecasting probability.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('priority_level')
                                ->options([
                                    1 => 'Level 1 (High)',
                                    2 => 'Level 2 (Medium)',
                                    3 => 'Level 3 (Low)',
                                ])
                                ->label('Priority Level')
                                ->required(),
                            Select::make('confidence_level')
                                ->options([
                                    'optimistic' => 'Optimistic',
                                    'moderate' => 'Moderate',
                                    'pessimistic' => 'Pessimistic',
                                ])
                                ->label('Confidence Level')
                                ->required(),
                            TextInput::make('project_code')
                                ->label('Project Code')
                                ->placeholder('PC-XXXXX'),
                        ]),
                    TextInput::make('proposal_number')
                        ->label('Proposal Number')
                        ->disabled()
                        ->placeholder('Auto-synced from Proposal'),
                ]),

            Section::make('Monthly Revenue Distribution')
                ->description('Automatic split of total value across the timeline.')
                ->schema([
                    Repeater::make('revenue_distribution_planning')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('month')
                                        ->label('Month')
                                        ->disabled()
                                        ->dehydrated(),
                                    TextInput::make('budget_amount')
                                        ->label('Budget Plan')
                                        ->prefix('IDR')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                        ->default(0)
                                        ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                        ->required(),
                                    TextInput::make('forecast_amount')
                                        ->label('Forecast')
                                        ->prefix('IDR')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                        ->default(0)
                                        ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                        ->required(),
                                    TextInput::make('actual_amount')
                                        ->label('Actual Revenue')
                                        ->prefix('IDR')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 2)
                                        ->default(0)
                                        ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                                        ->required(),
                                ]),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ]),
        ];
    }

    protected static function distributeRevenue($total, Get $get, Set $set): void
    {
        $start = $get('start_date');
        $end = $get('end_date');

        $totalFloat = self::parseCurrency($total);

        if ($totalFloat <= 0 || ! $start || ! $end) {
            return;
        }

        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->startOfDay();

        // Ensure dates are valid
        if ($endDate->lt($startDate)) {
            $set('revenue_distribution_planning', []);

            return;
        }

        $totalDays = $startDate->diffInDays($endDate) + 1;
        if ($totalDays <= 0) {
            return;
        }

        $dailyAmount = $totalFloat / $totalDays;
        $distribution = [];

        $current = $startDate->copy()->startOfMonth()->startOfDay();
        $endOfMonthValue = $endDate->copy()->endOfMonth()->startOfDay();

        while ($current->lte($endOfMonthValue)) {
            $monthStart = $current->copy()->startOfMonth()->startOfDay();
            $monthEnd = $current->copy()->endOfMonth()->startOfDay();

            // Calculate overlap days
            $overlapStart = $startDate->gt($monthStart) ? $startDate : $monthStart;
            $overlapEnd = $endDate->lt($monthEnd) ? $endDate : $monthEnd;

            if ($overlapStart->lte($overlapEnd)) {
                $daysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
                $monthlyBudget = $daysInMonth * $dailyAmount;

                $distribution[] = [
                    'month' => $current->format('F Y'),
                    'budget_amount' => round($monthlyBudget, 2),
                    'forecast_amount' => round($monthlyBudget, 2),
                    'actual_amount' => 0,
                    'year_val' => $current->year,
                    'month_val' => $current->month,
                ];
            }

            $current->addMonth();
        }

        $set('revenue_distribution_planning', $distribution);
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
