<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
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
            ->tooltip('Harap lengkapi semua data wajib (Required) dan minimal 1 item costing untuk dapat melakukan Submit.')
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
            ->form([
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

    protected function getApprovePAAction(): Action
    {
        return Action::make('Approve PA')
            ->color('primary')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your digital signature PIN to approve the Profitability Analysis.'),
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
                    ->placeholder('Example: Proposal '.($record?->proposal?->proposal_number ?? '...'))
                    ->default(fn ($record) => $record?->proposal?->proposal_number ?? 'Project for '.$record?->customer?->name),
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
                    ->body("Project Code: {$project->code}")
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
                            'document_number',
                            'year',
                            'sequence_number',
                            'status',
                            'proposal_id',
                            'project_number',
                        ]);

                        $newRecord->status = ProfitabilityAnalysisStatus::Draft;
                        $newRecord->save();

                        // Duplicate items
                        foreach ($record->items as $item) {
                            $newItem = $item->replicate(['profitability_analysis_id']);
                            $newItem->profitability_analysis_id = $newRecord->id;
                            $newItem->save();
                        }

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
            $this->getApproveMarginAction(),
            $this->getApprovePAAction(),
            $this->getSubmitAction(),
            $this->getIncompleteSubmitWarningAction(),
            $this->getGenerateProjectAction(),

            ActionGroup::make([
                $this->getDuplicateAction(),
                $this->getRejectAction(),
                $this->getCreateProposalAction(),
            ])
                ->label('Options')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('gray')
                ->button(),
        ];
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
