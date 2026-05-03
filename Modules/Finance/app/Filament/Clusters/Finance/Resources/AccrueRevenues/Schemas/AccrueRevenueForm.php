<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas;

use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\Finance\Models\Invoice;
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
                        Grid::make(4)
                            ->schema([
                                TextInput::make('number')
                                    ->label('Document Number')
                                    ->default('Auto-generated')
                                    ->disabled()
                                    ->dehydrated(),
                                Select::make('project_id')
                                    ->label('Project Code')
                                    ->relationship('project', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "[{$record->code}] {$record->name}")
                                    ->searchable(['code', 'name'])
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
                                Select::make('company_code')
                                    ->label('Business Unit (SAP)')
                                    ->options([
                                        'GA' => 'Garuda Indonesia',
                                        'CIT' => 'Citilink',
                                        'GMF' => 'GMF AeroAsia',
                                        'ACS' => 'ACS (Aerofood)',
                                        'POCO' => 'Poco Garuda',
                                        'GDPS' => 'GDPS (Internal)',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->placeholder('Select SAP entity'),
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

                Section::make('Revenue & Expense Details')
                    ->description('Record revenue and corresponding costs for matching logic.')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('revenue_type_id')
                                            ->label('Revenue Segment')
                                            ->relationship('revenueType', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live(),
                                        Toggle::make('has_management_fee')
                                            ->label('Apply Mgmt Fee (10%)')
                                            ->inline(false)
                                            ->dehydrated()
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, ?bool $state) {
                                                if ($state) {
                                                    $expense = (float) $get('amount_expense_estimated');
                                                    $set('amount_estimated', round($expense * 1.1, 2));
                                                }
                                            }),
                                        Select::make('invoice_id')
                                            ->label('Associated Invoice')
                                            ->options(fn (Get $get) => Invoice::where('customer_id', $get('../../customer_id'))->pluck('number', 'id'))
                                            ->searchable()
                                            ->placeholder('Optional')
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if ($state) {
                                                    $invoice = Invoice::find($state);
                                                    if ($invoice) {
                                                        $set('amount_actual', $invoice->total_amount);
                                                    }
                                                }
                                            }),
                                        Select::make('work_completion_report_id')
                                            ->label('BAPP')
                                            ->relationship('workCompletionReport', 'number')
                                            ->searchable()
                                            ->preload()
                                            ->placeholder('Select BAPP')
                                            ->helperText('Link to Work Completion Report.'),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('amount_expense_estimated')
                                            ->label('Estimated Expense (Cost)')
                                            ->prefix('IDR')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if ($get('has_management_fee')) {
                                                    $set('amount_estimated', round((float) $state * 1.1, 2));
                                                }
                                            }),
                                        TextInput::make('amount_estimated')
                                            ->label('Estimated Revenue (Invoiced)')
                                            ->prefix('IDR')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->rules(fn (Get $get): array => [
                                                function (string $attribute, $value, Closure $fail) use ($get) {
                                                    $expense = (float) $get('amount_expense_estimated');
                                                    if ((float) $value < $expense) {
                                                        $fail('Revenue must cover at least the estimated expense.');
                                                    }
                                                },
                                            ])
                                            ->helperText(function (Get $get) {
                                                $rev = (float) $get('amount_estimated');
                                                $exp = (float) $get('amount_expense_estimated');
                                                if ($exp > $rev && $rev > 0) {
                                                    return '❌ Revenue must be greater than or equal to expense.';
                                                }

                                                return 'Calculated as Expense + 10% Mgmt Fee (if applicable).';
                                            })
                                            ->extraInputAttributes(fn (Get $get) => [
                                                'style' => (float) $get('amount_expense_estimated') > (float) $get('amount_estimated')
                                                    ? 'color: #dc2626; font-weight: bold;'
                                                    : '',
                                            ]),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('amount_expense_actual')
                                            ->label('Actual Expense')
                                            ->prefix('IDR')
                                            ->numeric()
                                            ->live(onBlur: true)
                                            ->placeholder('Actual cost')
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if ($get('has_management_fee') && ! $get('invoice_id')) {
                                                    $set('amount_actual', round((float) $state * 1.1, 2));
                                                }
                                            }),
                                        TextInput::make('amount_actual')
                                            ->label('Actual Revenue')
                                            ->prefix('IDR')
                                            ->numeric()
                                            ->placeholder('Final billed amount')
                                            ->rules(fn (Get $get): array => [
                                                function (string $attribute, $value, Closure $fail) use ($get) {
                                                    $expense = (float) $get('amount_expense_actual');
                                                    if ($value && (float) $value < $expense) {
                                                        $fail('Actual revenue must cover at least the actual expense.');
                                                    }
                                                },
                                            ]),
                                    ]),
                                Textarea::make('description')
                                    ->label('Item Notes')
                                    ->placeholder('Detailed description of work...')
                                    ->columnSpanFull(),
                            ])
                            ->itemLabel(fn (array $state): ?string => (! empty($state['revenue_type_id'])) ? RevenueType::find($state['revenue_type_id'])?->name : 'Work Item')
                            ->addActionLabel('Add Work Item')
                            ->collapsible()
                            ->defaultItems(1)
                            ->columnSpanFull(),

                        TextInput::make('sap_reference')
                            ->label('SAP Document Reference')
                            ->placeholder('e.g. 100001234')
                            ->helperText('Record the SAP document number once uploaded.'),

                        Textarea::make('description')
                            ->label('General Submission Notes')
                            ->placeholder('Additional context for Finance team...')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }
}
