<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
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

                    $filename = 'Project-Review-'.($this->record->lead?->reference_no ?: $this->record->id);
                    $filename = str_replace(['/', '\\'], '-', $filename);

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        "{$filename}.pdf"
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
            ->label('Approve Project')
            ->icon(Heroicon::CheckBadge)
            ->color('success')
            ->size('xs')
            ->extraAttributes(['class' => 'flex-1'])
            ->record($this->record)
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
                return $required->contains(fn ($rule) => 
                    ! $pa->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }

    public function approveGIAction(): Action
    {
        return $this->getApprovalAction('generalInformation', 'GI')
            ->label('Approve GI')
            ->extraAttributes(['class' => 'flex-1']);
    }

    public function rejectGIAction(): Action
    {
        return $this->getRejectionAction('generalInformation', 'GI')
            ->label('Reject GI');
    }


    public function approvePAAction(): Action
    {
        return $this->getApprovalAction('profitabilityAnalysis', 'PA')
            ->label('Approve PA')
            ->extraAttributes(['class' => 'flex-1']);
    }

    public function rejectPAAction(): Action
    {
        return $this->getRejectionAction('profitabilityAnalysis', 'PA')
            ->label('Reject PA');
    }

    public function approveProposalAction(): Action
    {
        return $this->getApprovalAction('proposal', 'Proposal')
            ->label('Approve Proposal');
    }

    public function rejectProposalAction(): Action
    {
        return $this->getRejectionAction('proposal', 'Proposal')
            ->label('Reject Proposal');
    }

    protected function getApprovalAction(string $relation, string $label): Action
    {
        return Action::make("approve{$label}")
            ->icon(Heroicon::CheckBadge)
            ->color('success')
            ->size('xs')
            ->extraAttributes(['class' => 'flex-1'])
            ->record($this->record)
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

                // PA Hierarchy: In fully parallel mode, we don't wait for Margin to be approved
                // unless specifically requested by business rules. Removing wait-for-margin.

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
                return $required->contains(fn ($rule) => 
                    ! $subRecord->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user())
                );
            });
    }

    protected function getRejectionAction(string $relation, string $label): Action
    {
        return Action::make("reject{$label}")
            ->outlined()
            ->icon(Heroicon::XCircle)
            ->color('danger')
            ->size('xs')
            ->extraAttributes(['class' => 'flex-1'])
            ->record($this->record)
            ->requiresConfirmation()
            ->modalHeading("Reject {$label}")
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

                return $subRecord->status->value === $submittedStatus->value;
            });
    }
}
