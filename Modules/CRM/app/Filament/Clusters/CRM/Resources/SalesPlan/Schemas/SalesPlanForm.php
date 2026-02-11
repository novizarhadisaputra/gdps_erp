<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Schemas;

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
use Modules\CRM\Enums\PriorityLevel;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Employees\Schemas\EmployeeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas\RevenueSegmentForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Schemas\ServiceLineForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\SkillCategories\Schemas\SkillCategoryForm;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\ServiceLine;
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
                Step::make('Core Information')
                    ->description('Identify the sales lead and account manager.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('lead_id')
                                    ->relationship('lead', 'title', fn ($query) => $query->where('status', '!=', 'lead'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->helperText('Select the associated lead or prospect for this sales plan.')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (! $state) {
                                            return;
                                        }

                                        $lead = Lead::find($state);
                                        if ($lead) {
                                            $set('estimated_value', $lead->estimated_amount);
                                            $set('confidence_level', $lead->confidence_level);
                                            $set('revenue_segment_id', $lead->revenue_segment_id);
                                            $set('product_cluster_id', $lead->product_cluster_id);
                                            $set('project_type_id', $lead->project_type_id);
                                            $set('service_line_id', $lead->service_line_id);
                                            $set('industrial_sector_id', $lead->industrial_sector_id);
                                            $set('project_area_id', $lead->project_area_id);
                                        }
                                    }),
                                Select::make('ams_id')
                                    ->label('AMS (Account Manager/Sales)')
                                    ->relationship('ams', 'name')
                                    ->default(auth()->id())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Auto-detected from login, but can be adjusted if needed.')
                                    ->createOptionForm(EmployeeForm::schema())
                                    ->createOptionAction(fn (Action $action) => $action->slideOver())
                                    ->editOptionForm(EmployeeForm::schema())
                                    ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            ]),
                    ]),

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
                        Select::make('service_line_id')
                            ->label('Service Line')
                            ->relationship('serviceLine', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(ServiceLineForm::schema())
                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                            ->createOptionUsing(fn (array $data) => ServiceLine::create($data)->id)
                            ->editOptionForm(ServiceLineForm::schema())
                            ->editOptionAction(fn (Action $action) => $action->slideOver()),
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
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('estimated_value')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->required()
                                    ->live()
                                    ->helperText('Total estimated contract value.'),
                                TextInput::make('management_fee_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->required()
                                    ->helperText('Management fee % charged internally.'),
                                TextInput::make('margin_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->required()
                                    ->helperText('Target profit margin percentage.'),
                            ]),
                        TextInput::make('top_days')
                            ->label('ToP (Days)')
                            ->numeric()
                            ->default(30)
                            ->helperText('Terms of Payment (days from invoice).'),
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
                                        $startDate = $get('start_date');
                                        $endDate = $get('end_date');
                                        $totalValue = (float) str_replace(',', '', $get('estimated_value') ?? 0);

                                        if (! $startDate || ! $endDate || $totalValue <= 0) {
                                            return;
                                        }

                                        $start = Carbon::parse($startDate)->startOfMonth();
                                        $end = Carbon::parse($endDate)->startOfMonth();

                                        $months = [];
                                        $current = $start->copy();

                                        $count = 0;
                                        while ($current <= $end) {
                                            $count++;
                                            $current->addMonth();
                                        }

                                        if ($count === 0) {
                                            return;
                                        }

                                        $average = $totalValue / $count;

                                        $current = $start->copy();
                                        for ($i = 0; $i < $count; $i++) {
                                            $months[] = [
                                                'month' => $current->format('F Y'),
                                                'amount' => round($average, 2),
                                            ];
                                            $current->addMonth();
                                        }

                                        $set('revenue_distribution_planning', $months);
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
                                        TextInput::make('amount')
                                            ->label('Amount (IDR)')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->required()
                                            ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0),
                                    ])
                                    ->columns(2)
                                    ->reorderable(false),
                            ]),
                    ]),

                Step::make('Governance')
                    ->description('Review priority and confidence levels.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('priority_level')
                                    ->options(PriorityLevel::class)
                                    ->required()
                                    ->helperText('The level of business priority assigned to this project.'),
                                Select::make('confidence_level')
                                    ->options(ConfidenceLevel::class)
                                    ->required()
                                    ->helperText('The degree of confidence or probability of success for this project.'),
                            ]),
                    ]),
            ])->columnSpanFull()->persistStepInQueryString(),
        ];
    }
}
