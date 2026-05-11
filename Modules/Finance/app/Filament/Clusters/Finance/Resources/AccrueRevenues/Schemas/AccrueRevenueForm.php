<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Modules\MasterData\Models\RevenueType;
use Modules\Project\Models\Project;

class AccrueRevenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Information')
                    ->description('Provide the project and time period for this revenue accrual.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('number')
                                    ->label('Document Number')
                                    ->default('Auto-generated')
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('project_id')
                                    ->label('Project Code')
                                    ->relationship('project', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->number}] {$record->name}")
                                    ->searchable(['number', 'name'])
                                    ->preload()
                                    ->placeholder('Select project code')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                        if ($state) {
                                            $project = Project::with(['customer', 'projectArea'])->find($state);
                                            if ($project) {
                                                $set('customer_id', $project->customer_id);
                                                $set('project_area_id', $project->project_area_id);
                                            }
                                        }
                                    }),
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->placeholder('Auto-filled'),
                                Select::make('project_area_id')
                                    ->label('Project Area / Branch')
                                    ->relationship('projectArea', 'name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->placeholder('Auto-filled'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('work_period')
                                    ->label('Work Month / Period')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('F Y')
                                    ->format('Y-m-d')
                                    ->helperText('The month the work was actually performed.'),
                                DatePicker::make('accrual_period')
                                    ->label('Accrual Month / Period')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('F Y')
                                    ->format('Y-m-d')
                                    ->default(now()->startOfMonth())
                                    ->helperText('The month this revenue is being recorded in the books.'),
                            ]),
                    ])->columnSpanFull(),

                Tabs::make('Accrual Details')
                    ->tabs([
                        Tab::make('Revenue Accruals')
                            ->icon('heroicon-o-arrow-trending-up')
                            ->schema([
                                Section::make('Revenue Items')
                                    ->description('List of items we intend to bill to the customer for this period.')
                                    ->schema([
                                        Repeater::make('revenueItems')
                                            ->relationship('revenueItems')
                                            ->schema([
                                                TextInput::make('type')
                                                    ->hidden()
                                                    ->default('revenue'),
                                                Grid::make(3)
                                                    ->schema([
                                                        Select::make('revenue_type_id')
                                                            ->label('Revenue Category')
                                                            ->relationship('revenueType', 'name', fn ($query) => $query->whereJsonContains('applicable_to', 'revenue'))
                                                            ->required()
                                                            ->searchable()
                                                            ->preload()
                                                            ->placeholder('Select category')
                                                            ->live()
                                                            ->columnSpan(1),
                                                        TextInput::make('amount_estimated')
                                                            ->label('Planned Revenue')
                                                            ->prefix('IDR')
                                                            ->numeric()
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->columnSpan(1),
                                                        Select::make('work_completion_report_id')
                                                            ->label('BAPP Reference')
                                                            ->relationship('workCompletionReport', 'number')
                                                            ->searchable()
                                                            ->preload()
                                                            ->placeholder('Optional')
                                                            ->columnSpan(1),
                                                    ]),
                                                Textarea::make('description')
                                                    ->label('Notes')
                                                    ->rows(1)
                                                    ->placeholder('Details about this revenue item...')
                                                    ->columnSpanFull(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => (! empty($state['revenue_type_id'])) ? RevenueType::find($state['revenue_type_id'])?->name : 'Revenue Item')
                                            ->addActionLabel('Add Revenue Item')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->defaultItems(1),
                                    ]),
                            ]),

                        Tab::make('Expense Accruals')
                            ->icon('heroicon-o-arrow-trending-down')
                            ->schema([
                                Section::make('Expense Items')
                                    ->description('Internal costs and provisions we need to record this month.')
                                    ->schema([
                                        Repeater::make('expenseItems')
                                            ->relationship('expenseItems')
                                            ->schema([
                                                TextInput::make('type')
                                                    ->hidden()
                                                    ->default('expense'),
                                                Grid::make(2)
                                                    ->schema([
                                                        Select::make('revenue_type_id')
                                                            ->label('Expense Category')
                                                            ->relationship('revenueType', 'name', fn ($query) => $query->whereJsonContains('applicable_to', 'expense'))
                                                            ->required()
                                                            ->searchable()
                                                            ->preload()
                                                            ->placeholder('Select category')
                                                            ->live()
                                                            ->columnSpan(1),
                                                        TextInput::make('amount_expense_estimated')
                                                            ->label('Planned Cost')
                                                            ->prefix('IDR')
                                                            ->numeric()
                                                            ->required()
                                                            ->live(onBlur: true)
                                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                            ->columnSpan(1),
                                                    ]),
                                                Textarea::make('description')
                                                    ->label('Notes')
                                                    ->rows(1)
                                                    ->placeholder('Details about this expense...')
                                                    ->columnSpanFull(),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => (! empty($state['revenue_type_id'])) ? RevenueType::find($state['revenue_type_id'])?->name : 'Expense Item')
                                            ->addActionLabel('Add Expense Item')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->defaultItems(1),
                                    ]),
                            ]),

                        Tab::make('Summary & Notes')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('total_revenue')
                                            ->label('Total Accrued Revenue')
                                            ->prefix('IDR')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn (Get $get) => number_format(collect($get('revenueItems'))->sum('amount_estimated'), 0, ',', '.')),
                                        TextInput::make('total_expense')
                                            ->label('Total Accrued Expense')
                                            ->prefix('IDR')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn (Get $get) => number_format(collect($get('expenseItems'))->sum('amount_expense_estimated'), 0, ',', '.')),

                                        TextEntry::make('margin_warning')
                                            ->label('')
                                            ->state(fn (Get $get): HtmlString => new HtmlString('
                                                <div class="flex p-4 mb-4 text-sm text-amber-800 border border-amber-300 rounded-lg bg-amber-50 dark:bg-gray-800 dark:text-amber-300 dark:border-amber-800" role="alert">
                                                  <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                                                  </svg>
                                                  <span class="sr-only">Warning</span>
                                                  <div>
                                                    <span class="font-medium">Business Rule Warning:</span> Potential Margin Deficit detected.
                                                    <ul class="mt-1.5 list-disc list-inside">
                                                        <li>Accrued Expenses exceed Accrued Revenue for this period.</li>
                                                        <li>Please verify the figures to ensure project profitability alignment.</li>
                                                    </ul>
                                                  </div>
                                                </div>
                                            '))
                                            ->html()
                                            ->columnSpanFull()
                                            ->hidden(fn (Get $get) => (float) collect($get('revenueItems'))->sum('amount_estimated') >= (float) collect($get('expenseItems'))->sum('amount_expense_estimated')),
                                    ]),
                                Textarea::make('description')
                                    ->label('Submission Notes')
                                    ->placeholder('Additional context for Finance team...')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
