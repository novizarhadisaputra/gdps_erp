<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SalesOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('General Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('so_number')
                                            ->label('SO Number')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        DatePicker::make('order_date')
                                            ->required()
                                            ->default(now()),
                                        Select::make('project_id')
                                            ->relationship('project', 'code')
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if (!$state) return;
                                                $project = \Modules\Project\Models\Project::find($state);
                                                if ($project) {
                                                    $set('customer_id', $project->customer_id);
                                                    
                                                    $proposal = $project->lead?->proposals()->where('status', \Modules\CRM\Enums\ProposalStatus::Approved)->first();
                                                    if ($proposal) {
                                                        $set('proposal_id', $proposal->id);
                                                        $set('amount', $proposal->amount);
                                                    }

                                                    $analysis = $project->profitabilityAnalysis;
                                                    if ($analysis) {
                                                        $set('manpower_initial_qty', $analysis->total_manpower);
                                                        $set('manpower_composition', $analysis->manpower_requirements);
                                                        $set('management_fee_percentage', $analysis->management_fee_rate);
                                                        $set('tax_percentage', $analysis->tax?->percentage ?? 11);
                                                    }

                                                    $lead = $project->lead;
                                                    if ($lead) {
                                                        $set('sales_pic_id', $lead->ams_id);
                                                        $set('project_manager_id', $lead->oprep_id);
                                                    }
                                                }
                                            }),
                                        Select::make('customer_id')
                                            ->relationship('customer', 'name')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),
                                        Select::make('proposal_id')
                                            ->relationship('proposal', 'proposal_number')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),
                                        Select::make('type')
                                            ->options(\Modules\CRM\Enums\SalesOrderType::class)
                                            ->required(),
                                        Select::make('status')
                                            ->options(\Modules\CRM\Enums\SalesOrderStatus::class)
                                            ->required()
                                            ->default(\Modules\CRM\Enums\SalesOrderStatus::Draft),
                                    ]),
                            ]),
                        Tab::make('Execution & Staffing')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('sales_pic_id')
                                            ->label('Sales PIC (AMS)')
                                            ->relationship('salesPic', 'name')
                                            ->searchable(),
                                        Select::make('project_manager_id')
                                            ->label('Project Manager (Oprep)')
                                            ->relationship('projectManager', 'name')
                                            ->searchable(),
                                        TextInput::make('service_type'),
                                        TextInput::make('job_location'),
                                        TextInput::make('manpower_initial_qty')
                                            ->numeric()
                                            ->default(0),
                                        KeyValue::make('manpower_composition')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Financials & Terms')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('amount')
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->required(),
                                        TextInput::make('management_fee_percentage')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(10),
                                        TextInput::make('tax_percentage')
                                            ->numeric()
                                            ->suffix('%')
                                            ->default(11),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Textarea::make('payment_terms'),
                                        TextInput::make('probation_period'),
                                        TextInput::make('replacement_sla')
                                            ->label('Replacement SLA'),
                                        TextInput::make('reporting_schedule'),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
