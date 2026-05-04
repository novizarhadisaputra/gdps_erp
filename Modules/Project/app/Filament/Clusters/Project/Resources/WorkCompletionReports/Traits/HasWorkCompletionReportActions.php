<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
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
use Modules\MasterData\Services\SignatureService;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\WorkCompletionReport;

trait HasWorkCompletionReportActions
{
    protected function getWorkCompletionReportHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->hidden(fn () => $this instanceof ViewRecord),
            EditAction::make()
                ->hidden(fn () => $this instanceof EditRecord),

            ActionGroup::make([
                $this->getExportPdfAction(),
                $this->getGenerateAccrueRevenueAction(),
                $this->getDiscussionsAction(),
            ])
                ->label('Options')
                ->icon('heroicon-m-cog-6-tooth')
                ->color('gray')
                ->button(),

            ActionGroup::make([
                $this->getSubmitAction(),
                $this->getApproveAction(),
                $this->getSendToCustomerAction(),
                $this->getResendEmailAction(),
                $this->getConfirmCustomerSignatureAction(),
                $this->getReviseAction(),
                $this->getRejectAction(),
            ])
                ->label('Workflow')
                ->icon(Heroicon::OutlinedChevronDown)
                ->color('primary')
                ->button(),
        ];
    }

    protected function getExportPdfAction(): Action
    {
        return Action::make('pdf')
            ->label('Export PDF')
            ->color('gray')
            ->icon('heroicon-o-arrow-down-tray')
            ->schema([
                Select::make('language')
                    ->label('Template Language')
                    ->options([
                        'id' => 'Indonesian (Bahasa Indonesia)',
                        'en' => 'English (International)',
                    ])
                    ->default('id')
                    ->required(),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                app()->setLocale($data['language']);

                $pdf = Pdf::loadView('project::pdf.work_completion_report', [
                    'record' => $record,
                    'language' => $data['language'],
                ]);

                $name = str_replace(['/', '\\'], '-', $record->number);
                $fileName = "{$name}.pdf";

                return response()->streamDownload(
                    fn () => print ($pdf->output()),
                    $fileName
                );
            });
    }

    protected function getDiscussionsAction(): Action
    {
        return Action::make('discussions')
            ->label('Discussions')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('info')
            ->url(fn (WorkCompletionReport $record) => "/admin/projects/{$record->project_id}/work-completion-reports/{$record->id}/discussions");
    }

    protected function getGenerateAccrueRevenueAction(): Action
    {
        return Action::make('generateAccrueRevenue')
            ->label('Generate Financial Documents')
            ->icon('heroicon-o-presentation-chart-bar')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Financial Document Splitter')
            ->modalDescription('Specify the revenue split for this BAPP. This will generate one Accrual and multiple Invoices.')
            ->slideOver()
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Approved && ! $record->accrueRevenueItems()->exists())
            ->fillForm(function (WorkCompletionReport $record) {
                // 1. Get the common revenue types IDs
                $manpowerType = RevenueTypeModel::where('code', 'manpower')->first();
                $mgmtFeeType = RevenueTypeModel::where('code', 'mgmt_fee')->first();
                $defaultTypes = array_filter([$manpowerType?->id, $mgmtFeeType?->id]);

                // 2. Calculate Amounts from BAPP items
                $items = is_array($record->items) && isset($record->items['id']) ? $record->items['id'] : ($record->items ?? []);
                $totalBapp = collect($items)->sum('total_price');
                $totalFee = collect($items)->sum('management_fee');
                $mainWorkAmount = $totalBapp - $totalFee;

                // 3. Prepare Splits with COA Resolution
                $newSplits = [];
                foreach ($defaultTypes as $typeId) {
                    $type = RevenueTypeModel::find($typeId);
                    $amount = ($type->code === 'manpower') ? $mainWorkAmount : $totalFee;

                    // Resolve COA from mapping
                    $area = ProjectArea::find($record->project_area_id);
                    $coaId = null;

                    // Hierarchical lookup for COA
                    while ($area) {
                        $coaId = \Modules\Finance\Models\AccountMapping::where('mappable_type', ProjectArea::class)
                            ->where('mappable_id', $area->id)
                            ->where('revenue_type_id', $typeId)
                            ->first()?->chart_of_account_id;

                        if ($coaId) {
                            break;
                        }

                        $area = ($area->parentable_type === ProjectArea::class)
                            ? ProjectArea::find($area->parentable_id)
                            : null;
                    }

                    $newSplits[] = [
                        'revenue_type_id' => $typeId,
                        'revenue_type_name' => $type->name,
                        'amount_estimated' => $amount,
                        'amount_actual' => $amount,
                        'amount_expense_estimated' => 0,
                        'amount_expense_actual' => 0,
                        'chart_of_account_id' => $coaId,
                    ];
                }

                return [
                    'project_area_id' => $record->project_area_id,
                    'revenue_type_ids' => $defaultTypes,
                    'financial_splits' => $newSplits,
                    'tax_wording' => $record->getTranslation('tax_wording', 'id'),
                ];
            })
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
                        $splits = $get('financial_splits') ?? [];
                        foreach ($splits as $key => $split) {
                            $typeId = $split['revenue_type_id'] ?? null;
                            if (! $typeId) {
                                continue;
                            }

                            $area = ProjectArea::find($get('project_area_id'));
                            $coaId = null;

                            // Hierarchical lookup for COA
                            while ($area) {
                                $coaId = AccountMapping::where('mappable_type', ProjectArea::class)
                                    ->where('mappable_id', $area->id)
                                    ->where('revenue_type_id', $typeId)
                                    ->first()?->chart_of_account_id;

                                if ($coaId) {
                                    break;
                                }

                                $area = ($area->parentable_type === ProjectArea::class)
                                    ? ProjectArea::find($area->parentable_id)
                                    : null;
                            }

                            $set("financial_splits.{$key}.chart_of_account_id", $coaId);
                        }
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
                    ->afterStateUpdated(function ($state, Set $set, Get $get, WorkCompletionReport $record) {
                        if (empty($state)) {
                            $set('financial_splits', []);

                            return;
                        }

                        $items = is_array($record->items) && isset($record->items['id']) ? $record->items['id'] : $record->items;
                        $totalBapp = collect($items)->sum('total_price');
                        $totalFee = collect($items)->sum('management_fee');
                        $mainWorkAmount = $totalBapp - $totalFee;

                        $currentSplits = $get('financial_splits') ?? [];
                        $newSplits = [];

                        foreach ($state as $typeId) {
                            $type = RevenueTypeModel::find($typeId);

                            // Check if we already have data for this type
                            $existing = collect($currentSplits)->firstWhere('revenue_type_id', $typeId);

                            if ($existing) {
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

                            // Resolve COA from mapping
                            $area = ProjectArea::find($get('project_area_id'));
                            $coaId = null;

                            // Hierarchical lookup for COA
                            while ($area) {
                                $coaId = AccountMapping::where('mappable_type', ProjectArea::class)
                                    ->where('mappable_id', $area->id)
                                    ->where('revenue_type_id', $typeId)
                                    ->first()?->chart_of_account_id;

                                if ($coaId) {
                                    break;
                                }

                                $area = ($area->parentable_type === ProjectArea::class)
                                    ? ProjectArea::find($area->parentable_id)
                                    : null;
                            }

                            $newSplits[] = [
                                'revenue_type_id' => $typeId,
                                'revenue_type_name' => $type->name,
                                'amount_estimated' => $amount,
                                'amount_actual' => $amount,
                                'amount_expense_estimated' => 0,
                                'amount_expense_actual' => 0,
                                'chart_of_account_id' => $coaId,
                            ];
                        }
                        $set('financial_splits', $newSplits);
                    }),

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
                        Select::make('chart_of_account_id')
                            ->label('GL Account (COA)')
                            ->options(ChartOfAccount::all()->mapWithKeys(fn ($coa) => [$coa->id => "{$coa->code} - {$coa->name}"]))
                            ->required()
                            ->searchable()
                            ->preload()
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
                            ])
                            ->editOptionForm([
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
                                Toggle::make('is_active'),
                            ]),

                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columnSpanFull(),
                Textarea::make('tax_wording')
                    ->label('Tax Wording (Invoice)')
                    ->rows(3)
                    ->required(),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
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
                        'chart_of_account_id' => $split['chart_of_account_id'],
                    ]);

                    $manpowerType = RevenueTypeModel::where('code', 'manpower')->first();

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

                return redirect()->to(AccrueRevenueResource::getUrl('edit', ['record' => $accrual]));
            });
    }

    protected function getSubmitAction(): Action
    {
        return Action::make('submit')
            ->label('Submit for Approval')
            ->color('info')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Draft)
            ->requiresConfirmation()
            ->modalHeading('Submit BAPP for Approval')
            ->modalDescription('Are you sure you want to submit this Work Completion Report for internal approval? This will notify the first set of approvers.')
            ->action(function (WorkCompletionReport $record) {
                $record->update(['status' => WorkCompletionStatus::Submitted]);

                // Notify the first step approvers
                app(SignatureService::class)->notifyNextApprovers($record);

                Notification::make()->title('BAPP Submitted Successfully')->success()->send();
            });
    }

    protected function getSendToCustomerAction(): Action
    {
        return Action::make('send')
            ->label('Send to Customer')
            ->color('info')
            ->icon('heroicon-o-envelope')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Submitted)
            ->action(function (WorkCompletionReport $record) {
                if (! $record->hasMedia('draft_report')) {
                    Notification::make()
                        ->title('Missing Document')
                        ->body('Please upload Draft BAPP (Unsigned) before sending.')
                        ->warning()
                        ->send();

                    return;
                }

                $this->redirect(ClusterResource::getUrl('send', ['record' => $record]));
            });
    }

    protected function getResendEmailAction(): Action
    {
        return Action::make('resend')
            ->label('Resend Email')
            ->color('info')
            ->icon('heroicon-o-arrow-path')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Sent)
            ->action(function (WorkCompletionReport $record) {
                $this->redirect(ClusterResource::getUrl('send', ['record' => $record]));
            });
    }

    protected function getApproveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve & Sign')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->modalHeading('Authorize BAPP')
            ->modalDescription('Please enter your PIN to record your digital signature for this approval step.')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your digital signature PIN to approve.'),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()
                        ->title('Invalid PIN')
                        ->danger()
                        ->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record);
                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()
                        ->title('Access Denied')
                        ->body('You do not have the authority to approve this document.')
                        ->warning()
                        ->send();

                    return;
                }

                $matchingRule = $eligibleRules->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()
                        ->title('Already Signed')
                        ->body('You have already signed this approval step.')
                        ->warning()
                        ->send();

                    return;
                }

                $recordedRole = null;
                if ($matchingRule->approver_type === 'Role') {
                    $userRoles = auth()->user()->roles;
                    $ruleRoleIdentifiers = $matchingRule->approver_role ?? [];
                    $matchedRole = $userRoles->first(fn ($role) => in_array($role->id, $ruleRoleIdentifiers) || in_array($role->name, $ruleRoleIdentifiers));
                    $recordedRole = $matchedRole?->name;
                }

                $record->addSignature(auth()->user(), 'Approver', $recordedRole);

                // If this was the last internal approval, we don't automatically move to Approved
                // because it still needs to be Sent to Customer and get their signature.
                // However, we should notify the next person in line.
                $service->notifyNextApprovers($record);
                $service->notifyOwnerOnSignature($record, auth()->user(), 'Approver');

                Notification::make()
                    ->title('BAPP Signed')
                    ->body('Your signature has been recorded.')
                    ->success()
                    ->send();
            })
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Submitted &&
                app(SignatureService::class)->getRequiredApprovers($record)->contains(fn ($rule) => ! $record->isRuleSatisfied($rule) &&
                    app(SignatureService::class)->isEligibleApprover($rule, auth()->user())
                )
            );
    }

    protected function getConfirmCustomerSignatureAction(): Action
    {
        return Action::make('confirmCustomerSignature')
            ->label('Confirm Customer Signature')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Sent && $record->hasMedia('signed_report'))
            ->requiresConfirmation()
            ->modalHeading('Confirm BAPP Approval')
            ->modalDescription('By confirming this, you verify that the Signed BAPP (Final Scan) from the customer is valid. This will mark the BAPP as Approved.')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your PIN to confirm customer signature receipt.'),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                if (! app(SignatureService::class)->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title('Invalid PIN')->danger()->send();

                    return;
                }

                $record->update(['status' => WorkCompletionStatus::Approved]);
                Notification::make()->title('BAPP Approved Successfully')->success()->send();
            });
    }

    protected function getReviseAction(): Action
    {
        return Action::make('revise')
            ->label('Revise / Return to Draft')
            ->color('warning')
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (WorkCompletionReport $record) => in_array($record->status, [
                WorkCompletionStatus::Submitted,
                WorkCompletionStatus::Sent,
                WorkCompletionStatus::Approved,
            ]))
            ->requiresConfirmation()
            ->modalHeading('Revise BAPP')
            ->modalDescription('This will move the BAPP back to Draft stage, allowing you to make changes. A revision snapshot will be created, and all existing signatures will be cleared.')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Revision')
                    ->required(),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                $record->signatures()->delete();
                $record->update(['status' => WorkCompletionStatus::Draft]);

                app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);

                Notification::make()
                    ->title('BAPP Returned to Draft')
                    ->body('A new revision has been created.')
                    ->success()
                    ->send();
            });
    }

    protected function getRejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->color('danger')
            ->icon(Heroicon::OutlinedXMark)
            ->visible(fn (WorkCompletionReport $record) => in_array($record->status, [
                WorkCompletionStatus::Submitted,
                WorkCompletionStatus::Sent,
            ]))
            ->requiresConfirmation()
            ->modalHeading('Reject BAPP')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Rejection')
                    ->required(),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                $record->update(['status' => WorkCompletionStatus::Rejected]);
                app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);
                Notification::make()->title('BAPP Rejected')->danger()->send();
            });
    }
}
