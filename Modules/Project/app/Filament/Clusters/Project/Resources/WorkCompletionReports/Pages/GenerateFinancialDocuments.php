<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Modules\CRM\Models\Customer;
use Modules\Finance\Enums\AccrueInvoiceMappingStatus;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource;
use Modules\Finance\Models\AccountMapping;
use Modules\Finance\Models\AccrueInvoiceMapping;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;
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

    protected static ?string $title = 'Review & Generate Financial Documents';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

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
        $reportMonth = $record->service_period_start?->month ?? now()->month;
        $reportYear = $record->service_period_start?->year ?? now()->year;

        $items = is_array($record->items) && isset($record->items['id']) ? $record->items['id'] : ($record->items ?? []);
        $totalBapp = collect($items)->sum('total_price');

        // 1. Try to find existing Draft Accrual for this project and month (from PA)
        $existingAccrual = AccrueRevenue::where('project_id', $record->project_id)
            ->where('month', $reportMonth)
            ->where('year', $reportYear)
            ->where('status', AccrueRevenueStatus::Draft)
            ->with(['items.revenueType'])
            ->first();

        $newSplits = [];
        $revenueTypeIds = [];

        if ($existingAccrual) {
            foreach ($existingAccrual->items->where('type', 'revenue') as $item) {
                $revenueTypeIds[] = $item->revenue_type_id;

                // Autofill: Use estimated if actual is still 0
                $actualRevenue = $item->amount_actual > 0 ? $item->amount_actual : $item->amount_estimated;
                $actualExpense = $item->amount_expense_actual > 0 ? $item->amount_expense_actual : $item->amount_expense_estimated;

                $newSplits[] = [
                    'revenue_type_id' => $item->revenue_type_id,
                    'revenue_type_name' => $item->revenueType->name,
                    'amount_estimated' => $item->amount_estimated,
                    'amount_actual' => $actualRevenue,
                    'amount_expense_estimated' => $item->amount_expense_estimated,
                    'amount_expense_actual' => $actualExpense,
                    'accrual_chart_of_account_id' => $item->accrual_chart_of_account_id ?? $this->resolveCoa($item->revenue_type_id, $record->project_area_id, 'accrual'),
                    'revenue_chart_of_account_id' => $item->revenue_chart_of_account_id ?? $this->resolveCoa($item->revenue_type_id, $record->project_area_id, 'revenue'),
                    'expense_chart_of_account_id' => $item->expense_chart_of_account_id ?? $this->resolveCoa($item->revenue_type_id, $record->project_area_id, 'expense'),
                    'accrued_expense_chart_of_account_id' => $item->accrued_expense_chart_of_account_id ?? $this->resolveCoa($item->revenue_type_id, $record->project_area_id, 'expense_accrual'),
                ];
            }

            $taxWording = $record->getTranslation('tax_wording', 'id');
            if (! $taxWording || $taxWording === '-') {
                $taxWording = 'Value Added Tax (VAT) '.($record->tax_percentage ?? 12).'% is included.';
            }

            // Optional: If there is a mismatch, we could proportionally adjust, but for now let's just warn via UI.

            return [
                'project_area_id' => $record->project_area_id,
                'accrue_revenue_id' => $existingAccrual->id,
                'financial_splits' => $newSplits,
                'tax_wording' => $taxWording,
                'total_bapp_amount' => $totalBapp,
            ];
        }

        // Fallback fallback logic
        $mainType = RevenueTypeModel::where('code', 'manpower')->first();

        if ($mainType) {
            $newSplits[] = [
                'revenue_type_id' => $mainType->id,
                'revenue_type_name' => $mainType->name,
                'amount_estimated' => $totalBapp,
                'amount_actual' => $totalBapp,
                'amount_expense_estimated' => 0,
                'amount_expense_actual' => 0,
                'accrual_chart_of_account_id' => $this->resolveCoa($mainType->id, $record->project_area_id, 'accrual'),
                'revenue_chart_of_account_id' => $this->resolveCoa($mainType->id, $record->project_area_id, 'revenue'),
                'expense_chart_of_account_id' => $this->resolveCoa($mainType->id, $record->project_area_id, 'expense'),
                'accrued_expense_chart_of_account_id' => $this->resolveCoa($mainType->id, $record->project_area_id, 'expense_accrual'),
            ];
        }

        $taxWording = $record->getTranslation('tax_wording', 'id');
        if (! $taxWording || $taxWording === '-') {
            $taxWording = 'Value Added Tax (VAT) '.($record->tax_percentage ?? 12).'% is included.';
        }

        return [
            'project_area_id' => $record->project_area_id,
            'financial_splits' => $newSplits,
            'tax_wording' => $taxWording,
            'total_bapp_amount' => $totalBapp,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Information')
                    ->description('Verification of project area and linked operational records.')
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
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        TextEntry::make('accrue_revenue_number')
                            ->label('Linked Accrual Record')
                            ->state(fn (Get $get) => $get('accrue_revenue_id') ? AccrueRevenue::find($get('accrue_revenue_id'))?->number : 'New record will be created.')
                            ->columnSpan(1),
                        TextInput::make('accrue_revenue_id')
                            ->hidden()
                            ->dehydrated(),
                    ])->columns(2),

                Section::make('Financial Segmentation')
                    ->description('Review and distribute actual amounts based on work completion (BAPP).')
                    ->schema([
                        Repeater::make('financial_splits')
                            ->label('Revenue & Expense Breakdown')
                            ->schema([
                                Select::make('revenue_type_id')
                                    ->label('Type')
                                    ->options(RevenueTypeModel::all()->pluck('name', 'id'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state) {
                                            return;
                                        }

                                        $projectAreaId = $get('../../project_area_id');
                                        $type = RevenueTypeModel::find($state);

                                        $set('revenue_type_name', $type?->name);
                                        $set('accrual_chart_of_account_id', $this->resolveCoa($state, $projectAreaId, 'accrual'));
                                        $set('revenue_chart_of_account_id', $this->resolveCoa($state, $projectAreaId, 'revenue'));
                                        $set('expense_chart_of_account_id', $this->resolveCoa($state, $projectAreaId, 'expense'));
                                        $set('accrued_expense_chart_of_account_id', $this->resolveCoa($state, $projectAreaId, 'expense_accrual'));
                                    })
                                    ->columnSpan(2),
                                TextInput::make('amount_actual')
                                    ->label('Actual Revenue (BAPP)')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->helperText('Amount to be invoiced.')
                                    ->columnSpan(2),
                                TextInput::make('amount_expense_actual')
                                    ->label('Actual Expense')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->helperText('Actual cost incurred.')
                                    ->columnSpan(2),

                                // Hidden Accounting Fields
                                TextInput::make('revenue_type_name')->hidden(),
                                TextInput::make('amount_estimated')->default(0)->hidden(),
                                TextInput::make('amount_expense_estimated')->default(0)->hidden(),
                                TextInput::make('accrual_chart_of_account_id')->hidden(),
                                TextInput::make('revenue_chart_of_account_id')->hidden(),
                                TextInput::make('expense_chart_of_account_id')->hidden(),
                                TextInput::make('accrued_expense_chart_of_account_id')->hidden(),
                            ])
                            ->columns(6)
                            ->addActionLabel('Add Segment')
                            ->reorderable(false)
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('total_bapp_amount')
                            ->label('Target BAPP Total')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->prefix('IDR')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->extraAttributes(['class' => 'font-bold text-primary-600']),

                        TextEntry::make('allocation_status')
                            ->label('Allocation Balance')
                            ->state(function (Get $get) {
                                $target = (float) $get('total_bapp_amount');
                                $splits = $get('financial_splits') ?? [];

                                // De-mask currency values (remove dots/commas) for calculation
                                $current = collect($splits)->sum(function ($item) {
                                    $val = $item['amount_actual'] ?? 0;

                                    return (float) str_replace(['.', ','], '', (string) $val);
                                });

                                $diff = $target - $current;

                                if (abs($diff) < 1) {
                                    return new HtmlString('<span style="color: #10b981; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">Balanced</span>');
                                }

                                $statusText = $diff > 0 ? 'Shortfall: ' : 'Over: ';
                                $text = $statusText.'IDR '.number_format(abs($diff));

                                return new HtmlString('<span style="color: #ef4444; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;">'.$text.'</span>');
                            }),
                    ])->columns(2),

                Section::make('Invoicing Details')
                    ->description('Specify final details for the generated invoice documents.')
                    ->schema([
                        Textarea::make('tax_wording')
                            ->label('Tax Wording')
                            ->placeholder('Example: Value Added Tax (VAT) 12%...')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        Actions::make([
                            Action::make('generate_bottom')
                                ->label('Generate & Sync Documents')
                                ->icon('heroicon-o-check-circle')
                                ->color('success')
                                ->size(Size::Large)
                                ->requiresConfirmation()
                                ->modalHeading('Confirm Financial Generation')
                                ->modalDescription(function (Get $get) {
                                    $target = (float) $get('total_bapp_amount');
                                    $current = collect($get('financial_splits'))->sum('amount_actual');
                                    $diff = $target - $current;

                                    if ($diff < -1) {
                                        return new HtmlString('<div style="color: #b91c1c; font-size: 0.875rem;">The total allocation exceeds the BAPP target. This discrepancy will be reflected in the generated documents. Are you sure you want to proceed?</div>');
                                    }

                                    return 'Proceed with generating financial documents for this report?';
                                })
                                ->action(fn () => $this->generate()),
                        ])->fullWidth()->alignment(Alignment::Right),
                    ]),
            ])
            ->statePath('data');
    }

    protected function resolveCoa(string|int $typeId, string|int|null $areaId, string $mappingType = 'revenue'): string|int|null
    {
        $area = $areaId ? ProjectArea::find($areaId) : null;
        $coaId = null;

        while ($area) {
            $coaId = AccountMapping::where('mappable_type', ProjectArea::class)
                ->where('mappable_id', $area->id)
                ->where('revenue_type_id', $typeId)
                ->where('type', $mappingType)
                ->first()?->chart_of_account_id;

            if ($coaId) {
                return $coaId;
            }

            $area = ($area->parentable_type === ProjectArea::class) ? ProjectArea::find($area->parentable_id) : null;
        }

        return AccountMapping::where('mappable_type', Customer::class)
            ->where('mappable_id', $this->record->customer_id)
            ->where('revenue_type_id', $typeId)
            ->where('type', $mappingType)
            ->first()?->chart_of_account_id;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function generate(): void
    {
        $data = $this->form->getState();
        $record = $this->record;

        $items = is_array($record->items) && isset($record->items['id']) ? $record->items['id'] : $record->items;
        $totalBapp = (float) collect($items)->sum('total_price');
        $totalSplit = collect($data['financial_splits'])->sum(function ($item) {
            $val = $item['amount_actual'] ?? 0;

            return (float) str_replace(['.', ','], '', (string) $val);
        });

        if ($totalSplit < $totalBapp - 1) {
            Notification::make()
                ->title('Shortfall Detected')
                ->body('Total allocation is still below BAPP total (IDR '.number_format($totalBapp).'). Please distribute the remaining amount.')
                ->danger()
                ->send();

            return;
        }

        // Note: Over-allocation is now allowed because the user is warned via dynamic confirmation modal.

        // Integrity Check: Ensure all hidden COAs are actually resolved
        foreach ($data['financial_splits'] as $key => $split) {
            $typeId = $split['revenue_type_id'];
            $areaId = $data['project_area_id'];

            // Auto-resolve if missing (safety net for late mappings)
            if (empty($split['accrual_chart_of_account_id'])) {
                $data['financial_splits'][$key]['accrual_chart_of_account_id'] = $this->resolveCoa($typeId, $areaId, 'accrual');
            }
            if (empty($split['revenue_chart_of_account_id'])) {
                $data['financial_splits'][$key]['revenue_chart_of_account_id'] = $this->resolveCoa($typeId, $areaId, 'revenue');
            }
            if (empty($split['expense_chart_of_account_id'])) {
                $data['financial_splits'][$key]['expense_chart_of_account_id'] = $this->resolveCoa($typeId, $areaId, 'expense');
            }
            if (empty($split['accrued_expense_chart_of_account_id'])) {
                $data['financial_splits'][$key]['accrued_expense_chart_of_account_id'] = $this->resolveCoa($typeId, $areaId, 'expense_accrual');
            }

            // Final check
            if (empty($data['financial_splits'][$key]['accrual_chart_of_account_id']) || empty($data['financial_splits'][$key]['revenue_chart_of_account_id'])) {
                $typeName = $split['revenue_type_name'] ?? (isset($typeId) ? RevenueTypeModel::find($typeId)?->name : 'Unknown Type');

                Notification::make()
                    ->title('Account Mapping Missing')
                    ->body("Accounting mappings for '{$typeName}' are still not configured in Master Data.")
                    ->danger()
                    ->send();

                return;
            }
        }

        // Re-assign back to local split variable for the loop below
        $finalSplits = $data['financial_splits'];

        // 1. Get or Create Accrue Revenue
        $accrual = ! empty($data['accrue_revenue_id']) ? AccrueRevenue::find($data['accrue_revenue_id']) : null;

        if (! $accrual) {
            $accrual = AccrueRevenue::create([
                'project_id' => $record->project_id,
                'customer_id' => $record->customer_id,
                'project_area_id' => $data['project_area_id'],
                'company_code' => $record->project?->information?->company_code ?? 'GDPS',
                'month' => $record->service_period_start?->month ?? now()->month,
                'year' => $record->service_period_start?->year ?? now()->year,
                'work_period' => $record->service_period_start ?? now(),
                'accrual_period' => $record->service_period_end ?? now(),
                'status' => AccrueRevenueStatus::Draft,
            ]);
        }

        $accrual->update([
            'sourceable_id' => $record->id,
            'sourceable_type' => WorkCompletionReport::class,
            'description' => "Synced from BAPP {$record->number}",
        ]);

        $taxRate = (float) ($record->tax_percentage ?? 12);

        foreach ($finalSplits as $split) {
            $revenueType = RevenueTypeModel::find($split['revenue_type_id']);

            // Clean currency strings before casting
            $rawSplitAmount = (float) str_replace(['.', ','], '', (string) ($split['amount_actual'] ?? 0));
            $rawExpenseAmount = (float) str_replace(['.', ','], '', (string) ($split['amount_expense_actual'] ?? 0));

            // Calculate DPP (Net) from Gross. Standard: Revenue should be recognized as DPP.
            $splitAmount = floor($rawSplitAmount / (1 + ($taxRate / 100)));
            $expenseAmount = $rawExpenseAmount; // Expenses are usually already DPP or handled separately.

            $taxAmount = $rawSplitAmount - $splitAmount;

            // 2. Update/Create Accrue Revenue Item
            $accrualItem = AccrueRevenueItem::updateOrCreate([
                'accrue_revenue_id' => $accrual->id,
                'revenue_type_id' => $revenueType->id,
                'type' => 'revenue',
            ], [
                'amount_estimated' => $splitAmount,
                'amount_actual' => $splitAmount,
                'amount_expense_estimated' => $expenseAmount,
                'amount_expense_actual' => $expenseAmount,
                'work_completion_report_id' => $record->id,
                'description' => $revenueType->name.' - '.$record->number,
                'revenue_chart_of_account_id' => $split['revenue_chart_of_account_id'],
                'expense_chart_of_account_id' => $split['expense_chart_of_account_id'],
                'accrual_chart_of_account_id' => $split['accrual_chart_of_account_id'],
                'accrued_expense_chart_of_account_id' => $split['accrued_expense_chart_of_account_id'],
            ]);

            // 3. Create Invoice
            $itemName = $revenueType->name;
            $invoiceItems = [[
                'item_name' => $itemName,
                'report_item_name' => $itemName,
                'quantity' => 1,
                'uom' => 'Ls',
                'unit_price' => $splitAmount,
                'total_price' => $splitAmount,
                'remarks' => $record->number,
                'revenue_type_code' => $revenueType->code,
            ]];

            $invoice = Invoice::create([
                'sourceable_id' => $record->sourceable_id,
                'sourceable_type' => $record->sourceable_type,
                'customer_id' => $record->customer_id,
                'tax_id' => $record->tax_id,
                'project_area_id' => $data['project_area_id'],
                'work_completion_report_id' => $record->id,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'amount' => $splitAmount,
                'tax_base_amount' => $splitAmount,
                'tax_percentage' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $splitAmount + $taxAmount,
                'tax_wording' => ['id' => $data['tax_wording'], 'en' => $data['tax_wording']],
                'status' => InvoiceStatus::Draft,
                'revenue_type_id' => $revenueType->id,
                'items' => ['id' => $invoiceItems, 'en' => $invoiceItems],
                'content_config' => [
                    'revenue_type_code' => $revenueType->code,
                    'is_main_revenue' => ($revenueType->code === 'main' || $revenueType->code === 'manpower'),
                ],
            ]);

            AccrueInvoiceMapping::create([
                'accrue_revenue_item_id' => $accrualItem->id,
                'invoice_id' => $invoice->id,
                'allocated_amount' => $splitAmount,
                'reverse_amount' => $splitAmount,
                'status' => AccrueInvoiceMappingStatus::Active,
            ]);

            $accrualItem->update(['invoice_id' => $invoice->id]);
        }

        Notification::make()->title('Documents Successfully Synced')->success()->send();
        $this->redirect(AccrueRevenueResource::getUrl('edit', ['record' => $accrual]));
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }
}
