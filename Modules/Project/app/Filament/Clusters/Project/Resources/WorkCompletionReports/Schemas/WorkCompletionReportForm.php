<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Set;
use Modules\CRM\Models\SalesOrder;
use Modules\Project\Models\Project;
use Modules\Project\Enums\WorkCompletionStatus;

class WorkCompletionReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('report_number')
                                    ->label('Report Number')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                DatePicker::make('document_date')
                                    ->required()
                                    ->default(now()),
                                Select::make('project_id')
                                    ->relationship('project', 'code')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (!$state) return;
                                        $project = Project::find($state);
                                        if ($project) {
                                            $set('customer_id', $project->customer_id);
                                        }
                                    }),
                                Select::make('sales_order_id')
                                    ->label('Sales Order')
                                    ->options(fn ($get) => SalesOrder::where('project_id', $get('project_id'))->pluck('so_number', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (!$state) return;
                                        $so = SalesOrder::find($state);
                                        if ($so) {
                                            $set('service_period_start', $so->order_date);
                                        }
                                    }),
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('status')
                                    ->options(WorkCompletionStatus::class)
                                    ->required()
                                    ->default(WorkCompletionStatus::Draft),
                            ]),
                    ]),
                Section::make('Work Period & Progress')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('service_period_start')
                                    ->required(),
                                DatePicker::make('service_period_end')
                                    ->required(),
                                TextInput::make('work_progress_percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(100)
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required(),
                                Textarea::make('description')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
