<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas\IndustrialSectorForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Schemas\ProductClusterForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Schemas\ProjectAreaForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectTypes\Schemas\ProjectTypeForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueSegments\Schemas\RevenueSegmentForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\WorkScheme;

class LeadForm
{
    public static function schema(): array
    {
        return [
            Section::make('Lead Details')
                ->description('Basic information about the sales lead.')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Example: IT Support Outsourcing or Laptop Procurement')
                        ->columnSpanFull(),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->label('Customer')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->placeholder('Select customer')
                        ->createOptionForm(CustomerForm::schema(isCreateOption: true))
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->createOptionUsing(fn (array $data) => Customer::create($data)->id)
                        ->editOptionForm(CustomerForm::schema(isCreateOption: true))
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Select::make('work_scheme_id')
                        ->relationship('workScheme', 'name')
                        ->label('Work Scheme')
                        ->required()
                        ->placeholder('Select work scheme')
                        ->createOptionForm(WorkSchemeForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->createOptionUsing(fn (array $data) => WorkScheme::create($data)->id)
                        ->editOptionForm(WorkSchemeForm::schema())
                        ->editOptionAction(fn (Action $action) => $action->slideOver()),
                    Textarea::make('description')
                        ->rows(3)
                        ->placeholder('Provide a brief description of the scope of work or the purpose of this project')
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->columnSpanFull()
                        ->schema([
                            Select::make('job_positions')
                                ->label('Job Positions')
                                ->multiple()
                                ->options(\Modules\MasterData\Models\JobPosition::where('is_active', true)->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->placeholder('Select required positions')
                                ->helperText('Select one or more positions to be placed in this project.'),
                            Select::make('pic_costing_id')
                                ->label('Costing PIC')
                                ->relationship('picCosting', 'name')
                                ->options(function () {
                                    return User::where('id', auth()->id())
                                        ->orWhere('unit_id', '10000016')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->default(auth()->id())
                                ->placeholder('Select Costing PIC')
                                ->helperText('User responsible for preparing the costing template.'),
                        ]),
                ])
                ->columnSpanFull()
                ->columns(2),

            Section::make('Categorization')
                ->description('Early identification of project type and segment.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('revenue_segment_id')
                                ->label('Revenue Segment')
                                ->relationship('revenueSegment', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Select revenue segment')
                                ->createOptionForm(RevenueSegmentForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => RevenueSegment::create($data)->id)
                                ->editOptionForm(RevenueSegmentForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            Select::make('product_cluster_id')
                                ->label('Product Cluster')
                                ->relationship('productCluster', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Select product cluster')
                                ->createOptionForm(ProductClusterForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => ProductCluster::create($data)->id)
                                ->editOptionForm(ProductClusterForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            Select::make('project_type_id')
                                ->label('Project Type')
                                ->relationship('projectType', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Select project type')
                                ->createOptionForm(ProjectTypeForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => ProjectType::create($data)->id)
                                ->editOptionForm(ProjectTypeForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('industrial_sector_id')
                                ->label('Industrial Sector')
                                ->relationship('industrialSector', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Select industrial sector')
                                ->createOptionForm(IndustrialSectorForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => IndustrialSector::create($data)->id)
                                ->editOptionForm(IndustrialSectorForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                            Select::make('project_area_id')
                                ->label('Project Area')
                                ->relationship('projectArea', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Select project area')
                                ->createOptionForm(ProjectAreaForm::schema())
                                ->createOptionAction(fn (Action $action) => $action->slideOver())
                                ->createOptionUsing(fn (array $data) => ProjectArea::create($data)->id)
                                ->editOptionForm(ProjectAreaForm::schema())
                                ->editOptionAction(fn (Action $action) => $action->slideOver()),
                        ]),
                ])
                ->columnSpanFull(),

            Section::make('Pipeline & Forecast')
                ->description('Status and financial projections.')
                ->schema([
                    Select::make('confidence_level')
                        ->options(ConfidenceLevel::class)
                        ->placeholder('Select confidence level')
                        ->native(false),
                    TextInput::make('estimated_amount')
                        ->prefix('IDR')
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        ->maxValue(2147483647)
                        ->default(0)
                        ->placeholder('1.000.000.000')
                        ->helperText('Estimated total project value during the contract duration.')
                        ->dehydrateStateUsing(fn ($state) => self::parseCurrency($state))
                        ->nullable(),
                    DatePicker::make('start_date')
                        ->label('Estimated Start Date')
                        ->native(false)
                        ->placeholder('Select start date')
                        ->nullable(),
                    DatePicker::make('end_date')
                        ->label('Estimated End Date')
                        ->native(false)
                        ->placeholder('Select end date')
                        ->nullable(),
                    DatePicker::make('expected_closing_date')
                        ->native(false)
                        ->placeholder('Select closing date')
                        ->nullable(),
                ])
                ->columnSpanFull()
                ->columns(2),

            // Documents section removed in favor of ManageProposals page
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(Lead::class)
            ->components(static::schema());
    }

    protected static function parseCurrency($value): float
    {
        if (! $value) {
            return 0;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = str_replace('.', '', $value);
        $clean = str_replace(',', '.', $clean);

        return (float) $clean;
    }
}
