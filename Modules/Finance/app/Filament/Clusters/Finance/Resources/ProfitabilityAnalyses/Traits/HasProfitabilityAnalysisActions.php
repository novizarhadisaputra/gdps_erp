<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\SalesOrderAmendment;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityThreshold;
use Modules\MasterData\Services\SignatureService;

trait HasProfitabilityAnalysisActions
{
    protected function getSubmitAction(): Action
    {
        return Action::make('Submit')
            ->color('info')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->requiresConfirmation()
            ->modalHeading('Submit for Approval')
            ->modalDescription('Are you sure you want to submit this Profitability Analysis for approval? This will notify the first set of approvers.')
            ->modalSubmitActionLabel('Submit Document')
            ->action(function ($record) {
                $record->update(['status' => ProfitabilityAnalysisStatus::Submitted]);
                app(SignatureService::class)->notifyNextApprovers($record);
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ($status === ProfitabilityAnalysisStatus::Draft->value || $status === 'draft') && ($rec?->isComplete() ?? false);
            });
    }

    protected function getIncompleteSubmitWarningAction(): Action
    {
        return Action::make('incompleteSubmitWarning')
            ->label('Submit')
            ->color('gray')
            ->icon(Heroicon::OutlinedExclamationTriangle)
            ->disabled()
            ->tooltip('Please complete all required data and add at least one costing item to submit.')
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ($status === ProfitabilityAnalysisStatus::Draft->value || $status === 'draft') && ! ($rec?->isComplete() ?? false);
            });
    }

    protected function getApproveMarginAction(): Action
    {
        return Action::make('Approve Margin')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->modalHeading('Authorize Margin')
            ->modalDescription('Please verify the project profitability margins. Entering your PIN will record your digital signature for this step.')
            ->modalSubmitActionLabel('Authorize Margin')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your digital signature PIN to approve the margin.'),
            ])
            ->action(function ($record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()
                        ->title('Invalid PIN')
                        ->danger()
                        ->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record)
                    ->where('signature_type', 'MarginApproval');

                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()
                        ->title('Access Denied')
                        ->body('You do not have the authority to approve margin for this document.')
                        ->warning()
                        ->send();

                    return;
                }

                $matchingRule = $eligibleRules->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()
                        ->title('Already Signed')
                        ->body('You have already signed this margin approval step.')
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

                $record->addSignature(auth()->user(), 'MarginApproval', $recordedRole);

                if ($record->isMarginApproved()) {
                    $record->update(['is_margin_approved' => true]);
                }

                $service->notifyNextApprovers($record);

                // Notify owner
                $service->notifyOwnerOnSignature($record, auth()->user(), 'MarginApproval');

                Notification::make()
                    ->title('Margin Approved')
                    ->body('Your signature has been recorded.')
                    ->success()
                    ->send();
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                if (! in_array($status, [ProfitabilityAnalysisStatus::Submitted->value, 'submitted'])) {
                    return false;
                }

                if ($record?->isMarginApproved()) {
                    return false;
                }

                $service = app(SignatureService::class);
                $required = $service->getRequiredApprovers($record)
                    ->where('signature_type', 'MarginApproval');

                // Parallel Approval: User can see the button if they match ANY remaining margin rule
                return $required->contains(fn ($rule) => ! $record->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }

    protected function getRejectAction(): Action
    {
        return Action::make('Reject')
            ->color('danger')
            ->icon(Heroicon::OutlinedXMark)
            ->requiresConfirmation()
            ->modalHeading(fn ($record) => 'Reject '.class_basename($record))
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Rejection')
                    ->required(),
            ])
            ->action(function ($record, array $data) {
                $record->update(['status' => ProfitabilityAnalysisStatus::Rejected]);
                app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);

                Notification::make()
                    ->title('Document Rejected')
                    ->success()
                    ->send();
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                return in_array($status, [ProfitabilityAnalysisStatus::Submitted->value, 'submitted']);
            });
    }

    protected function getReviseAction(): Action
    {
        return Action::make('revise')
            ->label('Revise Analysis')
            ->color('warning')
            ->icon(Heroicon::OutlinedArrowPath)
            ->requiresConfirmation()
            ->modalHeading('Revise Profitability Analysis')
            ->modalDescription('This will move the analysis back to Draft stage, allowing you to make changes. A revision snapshot will be created, and the Lead status will be set back to Approach.')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Revision')
                    ->placeholder('Briefly explain why this analysis is being revised...')
                    ->required(),
            ])
            ->action(function ($record, array $data) {
                $record->revision_reason = $data['reason'];
                $record->update(['status' => ProfitabilityAnalysisStatus::Draft]);

                Notification::make()
                    ->title('Analysis Revision Started')
                    ->body('The analysis has been moved back to Draft. You can now edit the details.')
                    ->success()
                    ->send();

                if (method_exists($this, 'refreshFormData')) {
                    $this->refreshFormData(['status']);
                }
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                return in_array($status, [
                    ProfitabilityAnalysisStatus::Submitted->value,
                    ProfitabilityAnalysisStatus::Approved->value,
                    'submitted',
                    'approved',
                ]);
            });
    }

    protected function getApprovePAAction(): Action
    {
        return Action::make('Approve PA')
            ->color('primary')
            ->label('Approve Profitability')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->modalHeading('Approve Profitability Analysis')
            ->modalDescription('You are about to give final approval for this analysis. Please enter your PIN to sign the document.')
            ->modalSubmitActionLabel('Approve & Sign')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your digital signature PIN to approve the Profitability Analysis.'),
            ])
            ->action(function ($record, array $data) {
                $service = app(SignatureService::class);

                // Validation: Linked Proposal must be Approved before PA Final Approval
                $proposal = $record->proposal;
                if (! $proposal || $proposal->status !== ProposalStatus::Approved) {
                    $notification = Notification::make()
                        ->title('Proposal Not Approved')
                        ->body('Final PA approval signature can only be performed after the Proposal is signed (Approved) by the client.')
                        ->warning();

                    if ($proposal) {
                        $notification->actions([
                            Action::make('view_proposal')
                                ->label('View Proposal')
                                ->url(route('filament.admin.crm.resources.leads.proposals.view', [
                                    'record' => $proposal->id,
                                    'lead' => $proposal->lead_id,
                                ]))
                                ->button(),
                        ]);
                    }

                    $notification->send();

                    return;
                }

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()
                        ->title('Invalid PIN')
                        ->danger()
                        ->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record)
                    ->where('signature_type', 'Approver');

                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()
                        ->title('Access Denied')
                        ->body('You do not have the authority to approve this PA.')
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

                if ($record->isFullyApproved()) {
                    $record->update(['status' => ProfitabilityAnalysisStatus::Approved]);
                }

                $service->notifyNextApprovers($record);

                // Notify owner
                $service->notifyOwnerOnSignature($record, auth()->user(), 'Approver');

                Notification::make()
                    ->title('PA Approved')
                    ->body('Your signature has been recorded.')
                    ->success()
                    ->send();
            })
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);
                if ($status instanceof \BackedEnum) {
                    $status = $status->value;
                }

                if (! in_array($status, [ProfitabilityAnalysisStatus::Submitted->value, 'submitted'])) {
                    return false;
                }

                // Removed sequential margin requirement for fully parallel workflow

                if ($record->isFullyApproved()) {
                    return false;
                }

                $service = app(SignatureService::class);
                $required = $service->getRequiredApprovers($record)
                    ->where('signature_type', 'Approver');

                // Parallel Approval: Match ANY remaining PA approval rule
                return $required->contains(fn ($rule) => ! $record->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }

    protected function getGenerateProjectAction(): Action
    {
        return Action::make('generateProject')
            ->label('Generate Project')
            ->icon(Heroicon::OutlinedPlusCircle)
            ->color('success')
            ->visible(function ($record) {
                if (! $record && method_exists($this, 'getRecord')) {
                    $record = $this->getRecord();
                }

                return $record
                    && ! $record->project()->exists()
                    && $record->status === ProfitabilityAnalysisStatus::Approved
                    && $record->revenue_per_month !== null
                    && $record->margin_percentage !== null
                    && ! empty($record->analysis_details);
            })
            ->schema([
                TextInput::make('summary')
                    ->label('Summary')
                    ->default(fn ($record) => "You are about to generate a Project for '".($record?->customer?->name ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->customer?->name : ''))."'. This will consume the next sequence number for this customer and work scheme.")
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                TextInput::make('project_name_override')
                    ->label('Project Name (Optional)')
                    ->placeholder('Example: Proposal '.($record?->proposal?->number ?? '...'))
                    ->default(fn ($record) => $record?->proposal?->number ?? 'Project for '.$record?->customer?->name),
            ])
            ->action(function ($record, array $data) {
                if (! $this->validateProfitability($record)) {
                    return;
                }

                $service = app(ProjectGenerationService::class);

                $project = $service->generateFromPA($record);

                if (! empty($data['project_name_override'])) {
                    $project->update(['name' => $data['project_name_override']]);
                }

                Notification::make()
                    ->title('Project Generated')
                    ->body("Project Code: {$project->number}")
                    ->success()
                    ->send();
            });
    }

    protected function getCreateProposalAction(): Action
    {
        return Action::make('createProposal')
            ->label('Create Proposal')
            ->icon(Heroicon::OutlinedDocumentPlus)
            ->color('primary')
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return $rec && ! $rec->proposal && $rec->is_margin_approved;
            })
            ->schema([
                TextInput::make('amount')
                    ->default(fn ($record) => ($record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null))?->revenue_per_month)
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                    ->prefix('IDR ')
                    ->required(),
                DatePicker::make('submission_date')
                    ->default(now())
                    ->required(),
            ])
            ->action(function ($record, array $data) {
                if (! $this->validateProfitability($record)) {
                    return;
                }

                DB::transaction(function () use ($record, $data) {
                    $proposal = Proposal::create([
                        'customer_id' => $record->customer_id,
                        'lead_id' => $record->lead_id,
                        'profitability_analysis_id' => $record->id,
                        'work_scheme_id' => $record->work_scheme_id,
                        'amount' => is_numeric($data['amount']) ? (float) $data['amount'] : (float) str_replace(['.', ','], ['', '.'], $data['amount']),
                        'submission_date' => $data['submission_date'],
                        'status' => ProposalStatus::Draft,
                    ]);

                    $record->updateQuietly(['proposal_id' => $proposal->id]);
                    $record->lead?->update(['status' => LeadStatus::Proposal]);
                });

                Notification::make()
                    ->title('Proposal Created')
                    ->success()
                    ->send();

                return redirect(request()->header('Referer'));
            });
    }

    protected function getDuplicateAction(): Action
    {
        return Action::make('duplicate')
            ->label('Duplicate')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (ProfitabilityAnalysis $record) {
                try {
                    DB::transaction(function () use ($record, &$newRecord) {
                        $newRecord = $record->replicate([
                            'number',
                            'year',
                            'sequence_number',
                            'status',
                            'proposal_id',
                            'project_number',
                        ]);

                        $newRecord->status = ProfitabilityAnalysisStatus::Draft;
                        $newRecord->save();

                        // Copy media (TOR, RFP, RFQ)
                        foreach (['tor', 'rfp', 'rfq'] as $collection) {
                            $media = $record->getFirstMedia($collection);
                            if ($media) {
                                $media->copy($newRecord, $collection);
                            }
                        }
                    });

                    Notification::make()
                        ->title('Profitability Analysis Duplicated')
                        ->success()
                        ->send();

                    $resource = method_exists($this, 'getResource')
                        ? $this->getResource()
                        : ProfitabilityAnalysisResource::class;

                    $parameters = ['record' => $newRecord];
                    if (str_contains($resource, 'Modules\CRM')) {
                        $parameters['lead'] = $newRecord->lead_id;
                    }

                    return redirect()->to(
                        $resource::getUrl('view', $parameters)
                    );
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Duplication Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function getEditManpowerAction(): Action
    {
        return Action::make('edit_manpower')
            ->label('Edit Manpower')
            ->icon(Heroicon::OutlinedUsers)
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 3))
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function ($record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    $record->update($data);

                    if (isset($data['manpowerItems'])) {
                        $record->manpowerItems()->delete();
                        foreach ($data['manpowerItems'] as $item) {
                            $record->manpowerItems()->create($item);
                        }
                    }
                });

                Notification::make()
                    ->title('Manpower Costing Updated')
                    ->success()
                    ->send();
            })
            ->modalHeading('Edit Manpower Costing')
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ! ($rec->is_manual_cost ?? false) && in_array($rec->status, ['draft', 'rejected']);
            });
    }

    public function getEditOperationalAction(): Action
    {
        return Action::make('edit_operational')
            ->label('Edit Operational')
            ->icon(Heroicon::OutlinedWrenchScrewdriver)
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 4))
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function ($record, array $data) {
                DB::transaction(function () use ($record, $data) {
                    $record->update($data);

                    if (isset($data['operationalItems'])) {
                        $record->operationalItems()->delete();
                        foreach ($data['operationalItems'] as $item) {
                            $record->operationalItems()->create($item);
                        }
                    }
                });

                Notification::make()
                    ->title('Operational Costing Updated')
                    ->success()
                    ->send();
            })
            ->modalHeading('Edit Operational Costing')
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ! ($rec->is_manual_cost ?? false) && in_array($rec->status, ['draft', 'rejected']);
            });
    }

    public function getEditManualAction(): Action
    {
        return Action::make('edit_manual')
            ->label('Edit Manual Costs')
            ->icon(Heroicon::OutlinedBanknotes)
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 5))
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function ($record, array $data) {
                $record->update($data);

                Notification::make()
                    ->title('Manual Costs Updated')
                    ->success()
                    ->send();
            })
            ->modalHeading('Edit Manual Cost Breakdown')
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return ($rec->is_manual_cost ?? false) && in_array($rec->status, ['draft', 'rejected']);
            });
    }

    public function getEditIndirectAction(): Action
    {
        return Action::make('edit_indirect')
            ->label('Edit Indirect Costs')
            ->icon(Heroicon::OutlinedPresentationChartLine)
            ->schema(fn () => ProfitabilityAnalysisForm::schema(startStep: 6))
            ->fillForm(fn ($record) => $record->toArray())
            ->action(function (array $data, ProfitabilityAnalysis $record): void {
                $record->update($data);

                Notification::make()
                    ->title('Indirect Costs updated')
                    ->success()
                    ->send();
            })
            ->modalHeading('Edit Indirect Costing')
            ->visible(function ($record) {
                $status = $record?->status ?? (method_exists($this, 'getRecord') ? $this->getRecord()?->status : null);

                return in_array($status, ['draft', 'rejected']);
            });
    }

    public function getStepActions(): array
    {
        return [
            $this->getEditManpowerAction(),
            $this->getEditOperationalAction(),
            $this->getEditManualAction(),
            $this->getEditIndirectAction(),
        ];
    }

    protected function getProfitabilityAnalysisActions(): array
    {
        return [
            ViewAction::make(),

            ActionGroup::make([
                $this->getApproveMarginAction(),
                $this->getApprovePAAction(),
            ])
                ->label('Approval')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->button(),

            $this->getSubmitAction(),
            $this->getIncompleteSubmitWarningAction(),
            $this->getGenerateProjectAction(),

            ActionGroup::make([
                $this->getDuplicateAction(),
                $this->getRejectAction(),
                $this->getReviseAction(),
                $this->getCreateProposalAction(),
                $this->getRegenerateSalesOrderAction(),
            ])
                ->label('Options')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('gray')
                ->button(),
        ];
    }

    protected function getRegenerateSalesOrderAction(): Action
    {
        return Action::make('regenerateSalesOrder')
            ->label('Generate Sales Order')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Generate Sales Order')
            ->modalDescription('This will recreate or update the Sales Order draft based on the current Profitability Analysis data. Use this if the Sales Order was deleted or needs to be refreshed.')
            ->action(function (ProfitabilityAnalysis $record) {
                // Ensure project exists first
                if (! $record->project()->exists()) {
                    Notification::make()
                        ->title('Project Missing')
                        ->body('A Project must be generated before a Sales Order can be created.')
                        ->danger()
                        ->send();

                    return;
                }

                $result = app(\Modules\CRM\Services\SalesOrderService::class)->createDraftFromAnalysis($record);

                if ($result instanceof \Modules\CRM\Models\SalesOrder) {
                    Notification::make()
                        ->title('Sales Order Generated')
                        ->body("Sales Order: {$result->number}")
                        ->success()
                        ->actions([
                            Action::make('view_so')
                                ->label('View Sales Order')
                                ->url(route('filament.admin.crm.resources.sales-orders.view', ['record' => $result->id]))
                                ->button(),
                        ])
                        ->send();
                } elseif ($result instanceof SalesOrderAmendment) {
                    Notification::make()
                        ->title('Sales Order Amendment Created')
                        ->body("Amendment No: {$result->number}")
                        ->success()
                        ->actions([
                            Action::make('view_so')
                                ->label('View Sales Order')
                                ->url(route('filament.admin.crm.resources.sales-orders.view', ['record' => $result->sales_order_id]))
                                ->button(),
                        ])
                        ->send();
                } else {
                    Notification::make()
                        ->title('Generation Failed')
                        ->body('Could not generate Sales Order. Please check if Proposal is approved.')
                        ->danger()
                        ->send();
                }
            })
            ->visible(function ($record) {
                $rec = $record ?? (method_exists($this, 'getRecord') ? $this->getRecord() : null);

                return $rec
                    && $rec->status === ProfitabilityAnalysisStatus::Approved
                    && $rec->project()->exists();
            });
    }

    protected function validateProfitability(ProfitabilityAnalysis $record): bool
    {
        $threshold = ProfitabilityThreshold::first();
        if (! $threshold) {
            return true;
        }

        if ($record->margin_percentage < $threshold->min_gpm) {
            Notification::make()
                ->title('GPM Below Threshold')
                ->body('The Gross Profit Margin ('.number_format($record->margin_percentage, 2).'%) is below the required minimum of '.number_format($threshold->min_gpm, 2).'%.')
                ->danger()
                ->send();

            return false;
        }

        if ($record->net_profit_margin < $threshold->min_npm) {
            Notification::make()
                ->title('NPM Below Threshold')
                ->body('The Net Profit Margin ('.number_format($record->net_profit_margin, 2).'%) is below the required minimum of '.number_format($threshold->min_npm, 2).'%.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }
}
