<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas\GeneralInformationInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\ProjectReviewResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas\ProposalInfolist;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist;
use Modules\MasterData\Enums\ApprovalSignatureType;
use Modules\MasterData\Services\SignatureService;

class ViewProjectReview extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProjectReviewResource::class;

    protected string $view = 'crm::filament.clusters.crm.resources.project-reviews.pages.summary';

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Download PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(function () {
                    $pdf = Pdf::loadView('crm::pdf.project-review', ['record' => $this->record]);

                    $number = str_replace(['/', '\\'], '-', $this->record->number ?? 'Draft');
                    $fileName = "{$number}.pdf";

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        "{$fileName}"
                    );
                }),
        ];
    }

    public function getGiSchemaProperty(): Schema
    {
        return GeneralInformationInfolist::configure(
            Schema::make($this)
                ->record($this->record->generalInformation)
        );
    }

    public function getPaSchemaProperty(): Schema
    {
        return ProfitabilityAnalysisInfolist::configure(
            Schema::make($this)
                ->record($this->record->profitabilityAnalysis)
        );
    }

    public function getProposalSchemaProperty(): Schema
    {
        return ProposalInfolist::configure(
            Schema::make($this)
                ->record($this->record->proposal)
        );
    }

    public function approveProjectAction(): Action
    {
        return Action::make('approveProject')
            ->label('Authorize Margin')
            ->icon(Heroicon::CheckBadge)
            ->color('success')
            ->size('xs')
            ->extraAttributes(['class' => 'flex-1'])
            ->record($this->record)
            ->modalHeading('Authorize Margin')
            ->modalDescription('Please verify the project profitability before authorizing the margin. Your digital signature will be recorded.')
            ->modalSubmitActionLabel('Authorize')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required(),
            ])
            ->action(function (array $data, $record) {
                $pa = $record->profitabilityAnalysis;
                if (! $pa) {
                    Notification::make()->title('Profitability Analysis not found')->danger()->send();

                    return;
                }

                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title('Incorrect PIN')->danger()->send();

                    return;
                }

                $required = $service->getRequiredApprovers($pa)
                    ->where('signature_type', ApprovalSignatureType::MarginApproval->value);

                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()->title('Access Denied')->body('You do not have the authority to approve margin.')->warning()->send();

                    return;
                }

                $matchingRule = $eligibleRules->first(fn ($rule) => ! $pa->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()->title('Already Signed')->body('You have already signed this margin approval step.')->warning()->send();

                    return;
                }

                $recordedRole = null;
                if ($matchingRule->approver_type === 'Role') {
                    $userRoles = auth()->user()->roles;
                    $ruleRoleIdentifiers = $matchingRule->approver_role ?? [];

                    $matchedRole = $userRoles->first(fn ($role) => in_array($role->id, $ruleRoleIdentifiers) || in_array($role->name, $ruleRoleIdentifiers));
                    $recordedRole = $matchedRole?->name;
                }

                $pa->addSignature(auth()->user(), ApprovalSignatureType::MarginApproval, $recordedRole);

                if ($pa->isMarginApproved()) {
                    $pa->update(['is_margin_approved' => true]);
                }

                $service->notifyNextApprovers($pa);

                // Notify owner
                $service->notifyOwnerOnSignature($pa, auth()->user(), ApprovalSignatureType::MarginApproval->value);

                Notification::make()->title('Project Approved Successfully')->success()->send();
            })
            ->visible(function ($record) {
                $pa = $record->profitabilityAnalysis;
                if (! $pa || $pa->isMarginApproved()) {
                    return false;
                }

                if (! in_array($pa->status->value, [ProfitabilityAnalysisStatus::Submitted->value, 'submitted'])) {
                    return false;
                }

                $service = app(SignatureService::class);
                $required = $service->getRequiredApprovers($pa)
                    ->where('signature_type', ApprovalSignatureType::MarginApproval->value);

                if ($required->isEmpty()) {
                    return false;
                }

                // Parallel Approval: Check if user is eligible for ANY of the unsatisfied rules
                return $required->contains(fn ($rule) => ! $pa->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }

    public function rejectProjectAction(): Action
    {
        return Action::make('rejectProject')
            ->label('Reject Margin')
            ->outlined()
            ->icon(Heroicon::XCircle)
            ->color('danger')
            ->size('xs')
            ->extraAttributes(['class' => 'flex-1'])
            ->record($this->record)
            ->requiresConfirmation()
            ->modalHeading('Reject Margin Authorization')
            ->modalDescription('Are you sure you want to reject the margin for this project? This will notify the project owner for revision.')
            ->modalSubmitActionLabel('Reject Margin')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Rejection')
                    ->required(),
            ])
            ->action(function (array $data, $record) {
                $pa = $record->profitabilityAnalysis;
                if (! $pa) {
                    Notification::make()->title('Profitability Analysis not found')->danger()->send();

                    return;
                }

                $pa->update(['status' => ProfitabilityAnalysisStatus::Rejected]);

                app(SignatureService::class)->notifyOwnerOnRejection($pa, $data['reason']);

                Notification::make()->title('Project (Margin) Rejected')->success()->send();
            })
            ->visible(function ($record) {
                $pa = $record->profitabilityAnalysis;
                if (! $pa || $pa->isMarginApproved()) {
                    return false;
                }

                if (! in_array($pa->status->value, [ProfitabilityAnalysisStatus::Submitted->value, 'submitted'])) {
                    return false;
                }

                $service = app(SignatureService::class);
                $required = $service->getRequiredApprovers($pa)
                    ->where('signature_type', ApprovalSignatureType::MarginApproval->value);

                if ($required->isEmpty()) {
                    return false;
                }

                // Only eligible margin approvers can reject
                return $required->contains(fn ($rule) => ! $pa->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }

    public function approveGIAction(): Action
    {
        return $this->getApprovalAction('approveGI', 'generalInformation', 'General Info')
            ->label('Verify General Info')
            ->extraAttributes(['class' => 'flex-1']);
    }

    public function rejectGIAction(): Action
    {
        return $this->getRejectionAction('rejectGI', 'generalInformation', 'General Info')
            ->label('Reject General Info');
    }

    public function approvePAAction(): Action
    {
        return $this->getApprovalAction('approvePA', 'profitabilityAnalysis', 'Profitability')
            ->label('Approve Profitability')
            ->extraAttributes(['class' => 'flex-1']);
    }

    public function rejectPAAction(): Action
    {
        return $this->getRejectionAction('rejectPA', 'profitabilityAnalysis', 'Profitability')
            ->label('Reject Profitability');
    }

    public function approveProposalAction(): Action
    {
        return $this->getApprovalAction('approveProposal', 'proposal', 'Proposal')
            ->label('Approve Proposal');
    }

    public function rejectProposalAction(): Action
    {
        return $this->getRejectionAction('rejectProposal', 'proposal', 'Proposal')
            ->label('Reject Proposal');
    }

    protected function getApprovalAction(string $name, string $relation, string $label): Action
    {
        return Action::make($name)
            ->icon(Heroicon::CheckBadge)
            ->color('success')
            ->size('xs')
            ->extraAttributes(['class' => 'flex-1'])
            ->record($this->record)
            ->modalHeading(fn () => "Approve {$label}")
            ->modalDescription(fn () => "You are about to approve the {$label} document. Please enter your PIN to sign.")
            ->modalSubmitActionLabel('Approve & Sign')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required(),
            ])
            ->action(function (array $data, $record) use ($relation, $label) {
                $subRecord = $record->{$relation};
                if (! $subRecord) {
                    Notification::make()->title('Document not found')->danger()->send();

                    return;
                }

                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title('Incorrect PIN')->danger()->send();

                    return;
                }

                $signatureType = ApprovalSignatureType::Approver; // Standard Enum
                $required = $service->getRequiredApprovers($subRecord)
                    ->where('signature_type', $signatureType->value);

                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()->title('Access Denied')->body('You do not have authorization for this document.')->warning()->send();

                    return;
                }

                $matchingRule = $eligibleRules->first(fn ($rule) => ! $subRecord->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()->title('Already Signed')->body('You have already signed this approval step.')->warning()->send();

                    return;
                }

                $recordedRole = null;
                if ($matchingRule->approver_type === 'Role') {
                    $userRoles = auth()->user()->roles;
                    $ruleRoleIdentifiers = $matchingRule->approver_role ?? [];

                    $matchedRole = $userRoles->first(fn ($role) => in_array($role->id, $ruleRoleIdentifiers) || in_array($role->name, $ruleRoleIdentifiers));
                    $recordedRole = $matchedRole?->name;
                }

                $subRecord->addSignature(auth()->user(), $signatureType, $recordedRole);

                $service->notifyNextApprovers($subRecord);

                // Notify owner
                $service->notifyOwnerOnSignature($subRecord, auth()->user(), $signatureType->value);

                if ($subRecord->isFullyApproved()) {
                    $status = match ($relation) {
                        'generalInformation' => GeneralInformationStatus::Approved,
                        'profitabilityAnalysis' => ProfitabilityAnalysisStatus::Approved,
                        'proposal' => ProposalStatus::Approved,
                        default => null,
                    };
                    if ($status) {
                        $subRecord->update(['status' => $status]);
                    }

                    Notification::make()->title("{$label} Fully Approved")->success()->send();
                } else {
                    $notification = Notification::make()
                        ->title("{$label} Signed Successfully")
                        ->success();

                    // Specific feedback for GI Risk Register
                    if ($relation === 'generalInformation' && ! $subRecord->hasRiskRegisterApproval() && $subRecord->isTypeApproved(ApprovalSignatureType::Approver)) {
                        $notification->body('Digital signatures are complete, but status remains "Submitted" pending Risk Register approval.');
                    }

                    $notification->send();
                }
            })
            ->visible(function ($record) use ($relation) {
                $subRecord = $record->{$relation};
                if (! $subRecord) {
                    return false;
                }

                $submittedStatus = match ($relation) {
                    'generalInformation' => GeneralInformationStatus::Submitted,
                    'profitabilityAnalysis' => ProfitabilityAnalysisStatus::Submitted,
                    'proposal' => ProposalStatus::Submitted,
                    default => null,
                };

                if ($subRecord->status->value !== $submittedStatus->value) {
                    return false;
                }

                // PA Hierarchy: Approve PA (Approver type) only if Margin is already authorized
                if ($relation === 'profitabilityAnalysis' && ! $subRecord->is_margin_approved) {
                    return false;
                }

                if ($subRecord->isFullyApproved()) {
                    return false;
                }

                $service = app(SignatureService::class);
                $signatureType = match ($relation) {
                    'profitabilityAnalysis' => ApprovalSignatureType::Approver,
                    'generalInformation' => ApprovalSignatureType::Approver,
                    'proposal' => ApprovalSignatureType::Approver,
                    default => ApprovalSignatureType::Approver,
                };

                $required = $service->getRequiredApprovers($subRecord)
                    ->where('signature_type', $signatureType->value);

                if ($required->isEmpty()) {
                    return false;
                }

                // Parallel Approval: User can see the button if they are eligible for ANY unsatisfied rule
                return $required->contains(fn ($rule) => ! $subRecord->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }

    protected function getRejectionAction(string $name, string $relation, string $label): Action
    {
        return Action::make($name)
            ->outlined()
            ->icon(Heroicon::XCircle)
            ->color('danger')
            ->size('xs')
            ->extraAttributes(['class' => 'flex-1'])
            ->record($this->record)
            ->requiresConfirmation()
            ->modalHeading(fn () => "Reject {$label}")
            ->modalDescription(fn () => "Are you sure you want to reject the {$label}? A notification will be sent to the owner for further revision.")
            ->modalSubmitActionLabel('Reject Document')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Rejection')
                    ->required(),
            ])
            ->action(function (array $data, $record) use ($relation, $label) {
                $subRecord = $record->{$relation};
                if (! $subRecord) {
                    Notification::make()->title('Document not found')->danger()->send();

                    return;
                }

                $status = match ($relation) {
                    'generalInformation' => GeneralInformationStatus::Rejected,
                    'profitabilityAnalysis' => ProfitabilityAnalysisStatus::Rejected,
                    'proposal' => ProposalStatus::Rejected,
                    default => null,
                };

                if ($status) {
                    $subRecord->update(['status' => $status]);
                    app(SignatureService::class)->notifyOwnerOnRejection($subRecord, $data['reason']);

                    Notification::make()->title("{$label} Rejected")->success()->send();
                }
            })
            ->visible(function ($record) use ($relation) {
                $subRecord = $record->{$relation};
                if (! $subRecord) {
                    return false;
                }

                $submittedStatus = match ($relation) {
                    'generalInformation' => GeneralInformationStatus::Submitted,
                    'profitabilityAnalysis' => ProfitabilityAnalysisStatus::Submitted,
                    'proposal' => ProposalStatus::Submitted,
                    default => null,
                };

                if ($subRecord->status->value !== $submittedStatus->value) {
                    return false;
                }

                $service = app(SignatureService::class);
                $signatureType = match ($relation) {
                    'profitabilityAnalysis' => ApprovalSignatureType::Approver,
                    'generalInformation' => ApprovalSignatureType::Approver,
                    'proposal' => ApprovalSignatureType::Approver,
                    default => ApprovalSignatureType::Approver,
                };

                $required = $service->getRequiredApprovers($subRecord)
                    ->where('signature_type', $signatureType->value);

                if ($required->isEmpty()) {
                    return false;
                }

                // Only eligible approvers who haven't signed yet (or in parallel) can reject
                return $required->contains(fn ($rule) => ! $subRecord->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }
}
