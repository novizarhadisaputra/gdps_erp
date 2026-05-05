<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\RevenueType as RevenueTypeModel;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Models\WorkCompletionReport;

class GenerateFinancialDocuments extends Page
{
    use InteractsWithRecord;

    protected static string $resource = WorkCompletionReportResource::class;

    protected string $view = 'project::filament.clusters.project.resources.work-completion-reports.pages.generate-financial-documents';

    public ?array $data = [];

    protected static ?string $title = 'Generate Financial Documents';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        if ($this->record->accrueRevenueItems()->exists()) {
            Notification::make()
                ->title('Financial Documents Already Generated')
                ->body('This BAPP already has associated financial documents.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));

            return;
        }

        if ($this->record->status !== WorkCompletionStatus::Approved) {
            Notification::make()
                ->title('BAPP Not Approved')
                ->body('BAPP must be approved before generating financial documents.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));

            return;
        }

        $this->form->fill($this->getInitialFormData());
    }

    protected function getInitialFormData(): array
    {
        $record = $this->record;

        // 1. Get the common revenue types IDs
        $manpowerType = RevenueTypeModel::where('code', 'manpower')->first();
        $mgmtFeeType = RevenueTypeModel::where('code', 'mgmt_fee')->first();
        $defaultTypes = array_filter([$manpowerType?->id, $mgmtFeeType?->id]);

        // 2. Calculate Amounts from BAPP items
        $items = is_array($record->items) && isset($record->items['id']) ? $record->items['id'] : ($record->items ?? []);
        $totalBapp = collect($items)->sum('total_price');
        $totalFee = collect($items)->sum('management_fee');

        // Check if we have fee percentage in content_config or default to 0
        $mfRate = (float) ($record->content_config['management_fee_percentage'] ?? 0);

        if ($totalFee <= 0) {
            // Fallback to snapshot summary if available
            if (isset($record->snapshot['summary'])) {
                $price = (float) ($record->snapshot['summary']['total_price'] ?? 0);
                $cost = (float) ($record->snapshot['summary']['total_cost'] ?? 0);
                if ($price > $cost && $cost > 0) {
                    $totalFee = $price - $cost;
                    $mainWorkAmount = $cost;
                    // Derive rate if missing
                    if ($mfRate <= 0) {
                        $mfRate = round(($totalFee / $mainWorkAmount) * 100, 2);
                    }
                } else {
                    $mainWorkAmount = $totalBapp;
                }
            } elseif ($mfRate > 0) {
                // Calculate based on percentage (Bottom-up)
                $mainWorkAmount = round($totalBapp / (1 + ($mfRate / 100)), 0);
                $totalFee = $totalBapp - $mainWorkAmount;
            } else {
                $mainWorkAmount = $totalBapp - $totalFee;
            }
        } else {
            $mainWorkAmount = $totalBapp - $totalFee;
            // Derive rate if missing and we have amounts
            if ($mfRate <= 0 && $mainWorkAmount > 0) {
                $mfRate = round(($totalFee / $mainWorkAmount) * 100, 2);
            }
        }

        // 3. Prepare Splits with COA Resolution
        $newSplits = [];
        foreach ($defaultTypes as $typeId) {
            $type = RevenueTypeModel::find($typeId);
            $amount = ($type->code === 'manpower') ? $mainWorkAmount : $totalFee;

            // Resolve COA from mapping using the new helper
            $coaId = $this->resolveCoa($typeId, $record->project_area_id, 'revenue');
            $expenseCoaId = $this->resolveCoa($typeId, $record->project_area_id, 'expense');

            $newSplits[] = [
                'revenue_type_id' => $typeId,
                'revenue_type_name' => $type->name,
                'amount_estimated' => $amount,
                'amount_actual' => $amount,
                'amount_expense_estimated' => 0,
                'amount_expense_actual' => 0,
                'revenue_chart_of_account_id' => $coaId,
                'expense_chart_of_account_id' => $expenseCoaId,
            ];
        }

        return [
            'project_area_id' => $record->project_area_id,
            'revenue_type_ids' => $defaultTypes,
            'management_fee_percentage' => $mfRate > 0 ? $mfRate : null,
            'financial_splits' => $newSplits,
            'tax_wording' => $record->getTranslation('tax_wording', 'id'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Basic Configuration')
                        ->description('Select the project area and revenue types for this BAPP.')
                        ->schema([
                            Select::make('project_area_id')
                                ->label('Project Area')
                                ->options(ProjectArea::all()->mapWithKeys(function ($area) {
                                    $name = $area->name;
                                    if ($area->parentable_type === ProjectArea::class && $area->parentable) {
                                        $name = "{$area->parentable->name} - {$name}";
                                    }

                                    return [$area->id => $name];
                                }))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $this->refreshSplits($get, $set);
                                }),
                            Select::make('revenue_type_ids')
                                ->label('Select Revenue Types')
                                ->multiple()
                                ->options(RevenueTypeModel::pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required(),
                                    TextInput::make('code')
                                        ->helperText('Leave empty to generate automatically from name.'),
                                    Toggle::make('is_active')
                                        ->default(true),
                                ])
                                ->editOptionForm([
                                    TextInput::make('name')
                                        ->required(),
                                    TextInput::make('code')
                                        ->disabled(),
                                    Toggle::make('is_active'),
                                ])
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $this->refreshSplits($get, $set);
                                }),
                            TextInput::make('management_fee_percentage')
                                ->label('Extract Management Fee (%)')
                                ->numeric()
                                ->suffix('%')
                                ->helperText('If the source document does not provide a breakdown, enter the percentage to automatically extract Management Fee from the total.')
                                ->live()
                                ->visible(function (Get $get) {
                                    $mgmtFeeType = RevenueTypeModel::where('code', 'mgmt_fee')->first();

                                    return in_array($mgmtFeeType?->id, $get('revenue_type_ids') ?? []);
                                })
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $this->refreshSplits($get, $set);
                                }),
                        ])->columns(2),

                    Step::make('Financial Segmentation')
                        ->description('Allocate amounts and map GL accounts for each revenue type.')
                        ->schema([
                            Repeater::make('financial_splits')
                                ->label('Revenue Segmentation & COA Mapping')
                                ->schema([
                                    TextInput::make('revenue_type_name')
                                        ->label('Revenue Type')
                                        ->disabled()
                                        ->dehydrated(),
                                    TextInput::make('amount_estimated')
                                        ->label('Estimated Revenue')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0),
                                    TextInput::make('amount_actual')
                                        ->label('Actual Revenue')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0),
                                    TextInput::make('amount_expense_estimated')
                                        ->label('Estimated Expense')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0),
                                    TextInput::make('amount_expense_actual')
                                        ->label('Actual Expense')
                                        ->numeric()
                                        ->prefix('IDR')
                                        ->required()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0),
                                    Select::make('revenue_chart_of_account_id')
                                        ->label('Revenue Account (COA)')
                                        ->options(ChartOfAccount::all()->mapWithKeys(fn ($coa) => [$coa->id => "{$coa->code} - {$coa->name}"]))
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(2)
                                        ->createOptionForm([
                                            TextInput::make('code')
                                                ->required()
                                                ->unique(ChartOfAccount::class, 'code', ignoreRecord: true),
                                            TextInput::make('name')
                                                ->required(),
                                            Select::make('account_type')
                                                ->required()
                                                ->options([
                                                    'Asset' => 'Asset',
                                                    'Liability' => 'Liability',
                                                    'Equity' => 'Equity',
                                                    'Revenue' => 'Revenue',
                                                    'Expense' => 'Expense',
                                                    'Other' => 'Other',
                                                ]),
                                            Toggle::make('is_active')
                                                ->default(true),
                                        ]),
                                    Select::make('expense_chart_of_account_id')
                                        ->label('Expense Account (COA)')
                                        ->options(ChartOfAccount::all()->mapWithKeys(fn ($coa) => [$coa->id => "{$coa->code} - {$coa->name}"]))
                                        ->searchable()
                                        ->preload()
                                        ->columnSpan(2)
                                        ->createOptionForm([
                                            TextInput::make('code')
                                                ->required()
                                                ->unique(ChartOfAccount::class, 'code', ignoreRecord: true),
                                            TextInput::make('name')
                                                ->required(),
                                            Select::make('account_type')
                                                ->required()
                                                ->options([
                                                    'Asset' => 'Asset',
                                                    'Liability' => 'Liability',
                                                    'Equity' => 'Equity',
                                                    'Revenue' => 'Revenue',
                                                    'Expense' => 'Expense',
                                                    'Other' => 'Other',
                                                ]),
                                            Toggle::make('is_active')
                                                ->default(true),
                                        ]),
                                ])
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->columnSpanFull(),
                        ]),

                    Step::make('Finalization')
                        ->description('Review tax wording and complete the document generation.')
                        ->schema([
                            Textarea::make('tax_wording')
                                ->label('Tax Wording (Invoice)')
                                ->rows(5)
                                ->required()
                                ->columnSpanFull(),
                        ]),
                ])->submitAction($this->generateAction()),
            ])
            ->statePath('data');
    }

    protected function resolveCoa(string|int $typeId, string|int|null $areaId, string $mappingType = 'revenue'): string|int|null
    {
        $area = $areaId ? ProjectArea::find($areaId) : null;
        $coaId = null;

        // 1. Hierarchical lookup for Project Area mappings
        while ($area) {
            $coaId = AccountMapping::where('mappable_type', ProjectArea::class)
                ->where('mappable_id', $area->id)
                ->where('revenue_type_id', $typeId)
                ->where('type', $mappingType)
                ->first()?->chart_of_account_id;

            if ($coaId) {
                return $coaId;
            }

            $area = ($area->parentable_type === ProjectArea::class)
                ? ProjectArea::find($area->parentable_id)
                : null;
        }

        // 2. Fallback to Customer level mapping
        return AccountMapping::where('mappable_type', Customer::class)
            ->where('mappable_id', $this->record->customer_id)
            ->where('revenue_type_id', $typeId)
            ->where('type', $mappingType)
            ->first()?->chart_of_account_id;
    }

    protected function refreshSplits(Get $get, Set $set): void
    {
        $state = $get('revenue_type_ids') ?? [];
        if (empty($state)) {
            $set('financial_splits', []);

            return;
        }

        $items = is_array($this->record->items) && isset($this->record->items['id']) ? $this->record->items['id'] : $this->record->items;
        $totalBapp = collect($items)->sum('total_price');
        $totalFee = collect($items)->sum('management_fee');

        $mfRate = (float) ($get('management_fee_percentage') ?? 0);

        if ($totalFee <= 0) {
            // Fallback to snapshot summary if available
            if (isset($this->record->snapshot['summary'])) {
                $price = (float) ($this->record->snapshot['summary']['total_price'] ?? 0);
                $cost = (float) ($this->record->snapshot['summary']['total_cost'] ?? 0);
                if ($price > $cost && $cost > 0) {
                    $totalFee = $price - $cost;
                    $mainWorkAmount = $cost;
                } else {
                    $mainWorkAmount = $totalBapp;
                }
            } elseif ($mfRate > 0) {
                // Calculate based on percentage (Bottom-up)
                $mainWorkAmount = round($totalBapp / (1 + ($mfRate / 100)), 0);
                $totalFee = $totalBapp - $mainWorkAmount;
            } else {
                $mainWorkAmount = $totalBapp - $totalFee;
            }
        } else {
            $mainWorkAmount = $totalBapp - $totalFee;
        }

        $currentSplits = $get('financial_splits') ?? [];
        $newSplits = [];

        foreach ($state as $typeId) {
            $type = RevenueTypeModel::find($typeId);
            $existing = collect($currentSplits)->firstWhere('revenue_type_id', $typeId);

            if ($existing) {
                // Update amount based on calculation logic
                $amount = ($type->code === 'manpower') ? $mainWorkAmount : (($type->code === 'mgmt_fee') ? $totalFee : $existing['amount_actual']);
                $existing['amount_estimated'] = $amount;
                $existing['amount_actual'] = $amount;
                $existing['revenue_chart_of_account_id'] = $this->resolveCoa($typeId, $get('project_area_id'), 'revenue');
                $existing['expense_chart_of_account_id'] = $this->resolveCoa($typeId, $get('project_area_id'), 'expense');
                $newSplits[] = $existing;

                continue;
            }

            // Default Amount Logic
            $amount = 0;
            if ($type->code === 'manpower') {
                $amount = $mainWorkAmount;
            }
            if ($type->code === 'mgmt_fee') {
                $amount = $totalFee;
            }

            $newSplits[] = [
                'revenue_type_id' => $typeId,
                'revenue_type_name' => $type->name,
                'amount_estimated' => $amount,
                'amount_actual' => $amount,
                'amount_expense_estimated' => 0,
                'amount_expense_actual' => 0,
                'revenue_chart_of_account_id' => $this->resolveCoa($typeId, $get('project_area_id'), 'revenue'),
                'expense_chart_of_account_id' => $this->resolveCoa($typeId, $get('project_area_id'), 'expense'),
            ];
        }
        $set('financial_splits', $newSplits);
    }

    public function generateAction(): Action
    {
        return Action::make('generate')
            ->label('Generate Financial Documents')
            ->icon('heroicon-o-presentation-chart-bar')
            ->color('success')
            ->requiresConfirmation()
            ->action(fn () => $this->generate());
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $record = $this->record;

        $items = is_array($record->items) && isset($record->items['id']) ? $record->items['id'] : $record->items;
        $totalBapp = collect($items)->sum('total_price');
        $totalSplit = collect($data['financial_splits'])->sum('amount_actual');

        if (abs($totalBapp - $totalSplit) > 1) {
            Notification::make()
                ->title('Amount Mismatch')
                ->body('Total split (IDR '.number_format($totalSplit).') must match BAPP total (IDR '.number_format($totalBapp).').')
                ->danger()
                ->send();

            return;
        }

        // 1. Create Accrue Revenue
        $accrual = AccrueRevenue::create([
            'number' => null,
            'project_id' => $record->project_id,
            'customer_id' => $record->customer_id,
            'project_area_id' => $data['project_area_id'],
            'company_code' => $record->project?->information?->company_code ?? 'GDPS',
            'accrue_date' => now(),
            'month' => now()->month,
            'year' => now()->year,
            'work_period' => $record->report_date ?? now(),
            'accrual_period' => now()->startOfMonth(),
            'sourceable_id' => $record->id,
            'sourceable_type' => WorkCompletionReport::class,
            'status' => AccrueRevenueStatus::Draft,
        ]);

        $taxRate = (float) ($record->tax_percentage ?? 12);

        foreach ($data['financial_splits'] as $split) {
            $revenueType = RevenueTypeModel::find($split['revenue_type_id']);
            $splitAmount = (float) $split['amount_actual'];
            $taxAmount = round($splitAmount * ($taxRate / 100), 0);

            // 2. Create Accrue Revenue Item
            $accrualItem = AccrueRevenueItem::create([
                'accrue_revenue_id' => $accrual->id,
                'revenue_type_id' => $revenueType->id,
                'revenue_type' => $revenueType->name,
                'amount_estimated' => (float) $split['amount_estimated'],
                'amount_actual' => (float) $split['amount_actual'],
                'amount_expense_estimated' => (float) $split['amount_expense_estimated'],
                'amount_expense_actual' => (float) $split['amount_expense_actual'],
                'work_completion_report_id' => $record->id,
                'description' => $revenueType->name.' - '.($record->project->name ?? $record->number),
                'revenue_chart_of_account_id' => $split['revenue_chart_of_account_id'],
                'expense_chart_of_account_id' => $split['expense_chart_of_account_id'] ?? null,
            ]);

            $itemName = ($revenueType->code === 'manpower') ? 'Manpower' : $revenueType->name;
            $invoiceItems = [
                [
                    'item_name' => $itemName,
                    'report_item_name' => $itemName,
                    'quantity' => 1,
                    'uom' => 'Ls',
                    'unit_price' => $splitAmount,
                    'total_price' => $splitAmount,
                    'remarks' => $record->number,
                    'revenue_type_code' => $revenueType->code,
                ],
            ];

            // 3. Create Invoice for this Split
            $invoice = Invoice::create([
                'sourceable_id' => $record->sourceable_id,
                'sourceable_type' => $record->sourceable_type,
                'customer_id' => $record->customer_id,
                'tax_id' => $record->tax_id,
                'project_area_id' => $data['project_area_id'],
                'work_completion_report_id' => $record->id,
                'number' => null,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'amount' => $splitAmount,
                'tax_base_amount' => $splitAmount,
                'tax_percentage' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $splitAmount + $taxAmount,
                'tax_wording' => [
                    'id' => $data['tax_wording'],
                    'en' => $data['tax_wording'],
                ],
                'status' => InvoiceStatus::Draft,
                'revenue_type_id' => $revenueType->id,
                'items' => [
                    'id' => $invoiceItems,
                    'en' => $invoiceItems,
                ],
                'content_config' => [
                    'revenue_type_code' => $revenueType->code,
                    'is_manpower' => ($revenueType->code === 'manpower'),
                ],
            ]);

            // Link the Accrual Item to the Invoice
            $accrualItem->update(['invoice_id' => $invoice->id]);
        }

        Notification::make()
            ->title('Financial Documents Generated')
            ->body(count($data['financial_splits']).' Invoices and 1 Accrual have been created.')
            ->success()
            ->send();

        $this->redirect(AccrueRevenueResource::getUrl('edit', ['record' => $accrual]));
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
