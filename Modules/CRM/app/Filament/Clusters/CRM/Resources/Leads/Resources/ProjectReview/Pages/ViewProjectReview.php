<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas\GeneralInformationInfolist;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\ProjectReviewResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Schemas\ProposalInfolist;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisInfolist;
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
            Action::make('print')
                ->label('Print Report')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes(['onclick' => 'window.print()']),
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

    public function approveMarginAction(): Action
    {
        return Action::make('approveMargin')
            ->label('Approve Margin')
            ->icon('heroicon-m-check-badge')
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
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title('Incorrect PIN')->danger()->send();

                    return;
                }

                $pa->addSignature(auth()->user(), 'margin_approval');
                $pa->update(['is_margin_approved' => true]);

                Notification::make()->title('Margin Approved')->success()->send();
            })
            ->visible(function ($record) {
                $pa = $record->profitabilityAnalysis;

                return $pa &&
                       $pa->status === ProfitabilityAnalysisStatus::Submitted &&
                       ! $pa->is_margin_approved;
            });
    }

    public function approveGIAction(): Action
    {
        return $this->getApprovalAction('generalInformation', 'GI')
            ->label('Approve GI');
    }

    public function approvePAAction(): Action
    {
        return $this->getApprovalAction('profitabilityAnalysis', 'PA')
            ->label('Approve PA');
    }

    public function approveProposalAction(): Action
    {
        return $this->getApprovalAction('proposal', 'Proposal')
            ->label('Approve Proposal');
    }

    protected function getApprovalAction(string $relation, string $label): Action
    {
        return Action::make("approve{$label}")
            ->icon('heroicon-m-check-badge')
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

                $required = $service->getRequiredApprovers($subRecord);
                $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if (! $matchingRule) {
                    Notification::make()->title('You are not an authorized approver for this document')->warning()->send();

                    return;
                }

                if ($subRecord->isRuleSatisfied($matchingRule)) {
                    Notification::make()->title('You have already signed this document')->warning()->send();

                    return;
                }

                $recordedRole = null;
                if ($matchingRule->approver_type === 'Role') {
                    $userRoles = auth()->user()->roles->pluck('name')->toArray();
                    $ruleRoles = $matchingRule->approver_role ?? [];
                    $commonRoles = array_intersect($userRoles, $ruleRoles);
                    $recordedRole = reset($commonRoles);
                }

                $subRecord->addSignature(auth()->user(), $matchingRule->signature_type, $recordedRole);
                $service->notifyNextApprovers($subRecord);

                Notification::make()->title("{$label} Signed Successfully")->success()->send();

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

                // PA Hierarchy: Margin must be approved before final PA approval
                if ($relation === 'profitabilityAnalysis') {
                    return (bool) $subRecord->is_margin_approved;
                }

                return true;
            });
    }
}
