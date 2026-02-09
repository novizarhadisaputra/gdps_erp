<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Enums\LeadStatus;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas\CustomerForm;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;
use Modules\MasterData\Models\Customer;
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
                        ->columnSpanFull(),
                    Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->label('Customer')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm(CustomerForm::schema())
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->createOptionUsing(fn (array $data) => Customer::create($data)->id),
                    Select::make('work_scheme_id')
                        ->relationship('workScheme', 'name')
                        ->label('Work Scheme')
                        ->required()
                        ->createOptionForm(WorkSchemeForm::schema())
                        ->createOptionAction(fn (\Filament\Actions\Action $action) => $action->slideOver())
                        ->createOptionUsing(fn (array $data) => WorkScheme::create($data)->id),
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('Sales PIC')
                        ->default(auth()->id())
                        ->disabled(fn () => ! auth()->user()->hasRole('super_admin'))
                        ->dehydrated()
                        ->required()
                        ->searchable()
                        ->preload(),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
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
                                ->preload(),
                            Select::make('product_cluster_id')
                                ->label('Product Cluster')
                                ->relationship('productCluster', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('project_type_id')
                                ->label('Project Type')
                                ->relationship('projectType', 'name')
                                ->searchable()
                                ->preload(),
                        ]),
                    Grid::make(3)
                        ->schema([
                            Select::make('service_line_id')
                                ->label('Service Line')
                                ->relationship('serviceLine', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('industrial_sector_id')
                                ->label('Industrial Sector')
                                ->relationship('industrialSector', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('project_area_id')
                                ->label('Project Area')
                                ->relationship('projectArea', 'name')
                                ->searchable()
                                ->preload(),
                        ]),
                ])
                ->columnSpanFull(),

            Section::make('Pipeline & Forecast')
                ->description('Status and financial projections.')
                ->schema([
                    Select::make('status')
                        ->options(LeadStatus::class)
                        ->required()
                        ->default(LeadStatus::Lead)
                        ->native(false)
                        ->disabled()
                        ->dehydrated(),
                    Select::make('confidence_level')
                        ->options(ConfidenceLevel::class)
                        ->placeholder('Select confidence level')
                        ->native(false),
                    TextInput::make('estimated_amount')
                        ->numeric()
                        ->prefix('IDR')
                        ->maxValue(42949672.95),
                    TextInput::make('probability')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->maxValue(100),
                    DatePicker::make('expected_closing_date')
                        ->native(false),
                ])
                ->columnSpanFull()
                ->columns(2),

            // Documents section removed in favor of ManageProposals page
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::schema());
    }
}
