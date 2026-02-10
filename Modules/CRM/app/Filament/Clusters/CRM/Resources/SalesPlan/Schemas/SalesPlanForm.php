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
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Enums\PriorityLevel;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\JobPosition;

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
            Section::make('Core Information')
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
                        ->helperText('Auto-detected from login, but can be adjusted if needed.'),
                ])->columns(2),

            Section::make('Master Categorization')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('revenue_segment_id')
                                ->label('Revenue Segment')
                                ->relationship('revenueSegment', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('The category of the revenue segment.'),
                            Select::make('product_cluster_id')
                                ->label('Product Cluster')
                                ->relationship('productCluster', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('The grouping of the product or service.'),
                            Select::make('project_type_id')
                                ->label('Project Type')
                                ->relationship('projectType', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('The contractual type of the project (e.g., Time & Material, Fixed Price).'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('skill_category_id')
                                ->label('Skill Category')
                                ->relationship('skillCategory', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('The primary skill category required for this project.'),
                            Select::make('industrial_sector_id')
                                ->label('Industrial Sector')
                                ->relationship('industrialSector', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('The industry sector the client belongs to.'),
                            Select::make('project_area_id')
                                ->label('Project Area')
                                ->relationship('projectArea', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->helperText('The geographical location or area of the project.'),
                        ]),
                    Select::make('service_line_id')
                        ->label('Service Line')
                        ->relationship('serviceLine', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('The specific service line this project falls under.'),
                ]),

            Section::make('Job Positions')
                ->description('Select one or more job positions for this project.')
                ->schema([
                    Select::make('job_positions')
                        ->multiple()
                        ->options(JobPosition::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->searchable()
                        ->preload()
                        ->helperText('Select the required job positions for this project to map the headcount.'),
                ]),

            Section::make('Financials & Timeline')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('estimated_value')
                                ->numeric()
                                ->prefix('IDR')
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->required()
                                ->live()
                                ->helperText('Total estimated contract value including all fees.'),
                            TextInput::make('management_fee_percentage')
                                ->numeric()
                                ->suffix('%')
                                ->required()
                                ->helperText('The percentage charged for project management.'),
                            TextInput::make('margin_percentage')
                                ->numeric()
                                ->suffix('%')
                                ->required()
                                ->helperText('The target profit margin percentage for this project.'),
                        ]),
                    Grid::make(3)
                        ->schema([
                            DatePicker::make('start_date')
                                ->native(false)
                                ->required()
                                ->live()
                                ->helperText('The expected starting date of project activities.'),
                            DatePicker::make('end_date')
                                ->native(false)
                                ->required()
                                ->live()
                                ->helperText('The expected completion date of project activities.'),
                            TextInput::make('top_days')
                                ->label('ToP (Days)')
                                ->numeric()
                                ->helperText('Terms of Payment (the number of days allowed for payment).'),
                        ]),
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
                ->description('Monthly breakdown of the estimated project revenue. You can generate this automatically from the timeline.')
                ->hiddenOn(operations: ['create'])
                ->schema([
                    Repeater::make('revenue_distribution_planning')
                        ->label('Monthly Breakdown')
                        ->schema([
                            TextInput::make('month')
                                ->label('Month')
                                ->readOnly()
                                ->required()
                                ->helperText('The specific month for this revenue entry.'),
                            TextInput::make('amount')
                                ->label('Amount (IDR)')
                                ->numeric()
                                ->prefix('IDR')
                                ->required()
                                ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                ->helperText('The revenue amount allocated for this specific month.'),
                        ])
                        ->columns(2)
                        ->reorderable(false),
                ]),

            Section::make('Confidence & Priority')
                ->schema([
                    Select::make('priority_level')
                        ->options(PriorityLevel::class)
                        ->required()
                        ->helperText('The level of business priority assigned to this project.'),
                    Select::make('confidence_level')
                        ->options(ConfidenceLevel::class)
                        ->required()
                        ->helperText('The degree of confidence or probability of success for this project.'),
                ])->columns(2),
        ];
    }
}
