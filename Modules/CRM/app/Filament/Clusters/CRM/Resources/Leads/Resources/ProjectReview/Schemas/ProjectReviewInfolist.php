<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProjectReview\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\MasterData\Services\SignatureService;

class ProjectReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('General Information'))
                    ->description(__('Project scope and basic details.'))
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('generalInformation.number')
                                    ->label(__('Document #'))
                                    ->placeholder(__('Not created yet')),
                                TextEntry::make('generalInformation.status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->placeholder(__('-')),
                                TextEntry::make('generalInformation.customer.name')
                                    ->label(__('Customer'))
                                    ->placeholder(__('-')),
                                TextEntry::make('generalInformation.scope_of_work')
                                    ->label(__('Scope of Work'))
                                    ->columnSpanFull()
                                    ->placeholder(__('-')),
                            ]),
                    ])
                    ->headerActions([
                        static::getApprovalAction('generalInformation'),
                    ])
                    ->visible(fn ($record) => filled($record?->general_information_id)),

                Section::make(__('Profitability Analysis'))
                    ->description(__('Financial analysis and margin calculations.'))
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('profitabilityAnalysis.number')
                                    ->label(__('Document #'))
                                    ->placeholder(__('Not created yet')),
                                TextEntry::make('profitabilityAnalysis.status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->placeholder(__('-')),
                                TextEntry::make('profitabilityAnalysis.revenue_per_month')
                                    ->label(__('Monthly Revenue'))
                                    ->money('IDR')
                                    ->placeholder(__('-')),
                                TextEntry::make('profitabilityAnalysis.margin_percentage')
                                    ->label(__('Margin'))
                                    ->suffix('%')
                                    ->placeholder(__('-')),
                            ]),
                    ])
                    ->headerActions([
                        static::getApproveMarginAction(),
                        static::getApprovalAction('profitabilityAnalysis'),
                    ])
                    ->visible(fn ($record) => filled($record?->profitability_analysis_id)),

                Section::make(__('Proposal'))
                    ->description(__('Final proposal document and submission details.'))
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('proposal.number')
                                    ->label(__('Proposal #'))
                                    ->placeholder(__('Not created yet')),
                                TextEntry::make('proposal.status')
                                    ->label(__('Status'))
                                    ->badge()
                                    ->placeholder(__('-')),
                                TextEntry::make('proposal.amount')
                                    ->label(__('Total Amount'))
                                    ->money('IDR')
                                    ->placeholder(__('-')),
                                TextEntry::make('proposal.submission_date')
                                    ->label(__('Submission Date'))
                                    ->date()
                                    ->placeholder(__('-')),
                            ]),
                    ])
                    ->headerActions([
                        static::getApprovalAction('proposal'),
                    ])
                    ->visible(fn ($record) => filled($record?->proposal_id)),
            ]);
    }

    protected static function getApprovalAction(string $relation): Action
    {
        return Action::make("approve_{$relation}")
            ->label(__('Approve Document'))
            ->icon('heroicon-o-pencil-square')
            ->color('primary')
            ->schema([
                TextInput::make('pin')
                    ->label(__('Signature PIN'))
                    ->password()
                    ->required(),
            ])
            ->action(function (array $data, $record) use ($relation) {
                $subRecord = $record->{$relation};
                if (! $subRecord) {
                    return;
                }

                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title(__('Incorrect PIN'))->danger()->send();

                    return;
                }

                $required = $service->getRequiredApprovers($subRecord);
                $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if (! $matchingRule) {
                    Notification::make()->title(__('Access Denied'))->warning()->send();

                    return;
                }

                if ($subRecord->isRuleSatisfied($matchingRule)) {
                    Notification::make()->title(__('Already Signed'))->warning()->send();

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

                Notification::make()->title(__('Document Signed'))->success()->send();

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
                if (! $record) {
                    return false;
                }
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

                if ($subRecord->status !== $submittedStatus) {
                    return false;
                }

                // PA specific checks
                if ($relation === 'profitabilityAnalysis') {
                    if (! $subRecord->is_margin_approved) {
                        return false;
                    }

                    $proposal = $record->proposal;
                    if (! $proposal || $proposal->status !== ProposalStatus::Approved) {
                        return false;
                    }
                }

                return true;
            });
    }

    protected static function getApproveMarginAction(): Action
    {
        return Action::make(__('approve_margin'))
            ->label(__('Approve Margin'))
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->schema([
                TextInput::make('pin')
                    ->label(__('Signature PIN'))
                    ->password()
                    ->required(),
            ])
            ->action(function (array $data, $record) {
                $pa = $record->profitabilityAnalysis;
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title(__('Incorrect PIN'))->danger()->send();

                    return;
                }

                $pa->addSignature(auth()->user(), 'MarginApproval');
                $pa->update(['is_margin_approved' => true]);

                Notification::make()->title(__('Margin Approved'))->success()->send();
            })
            ->visible(function ($record) {
                if (! $record) {
                    return false;
                }
                $pa = $record->profitabilityAnalysis;

                return $pa &&
                       $pa->status === ProfitabilityAnalysisStatus::Submitted &&
                       ! $pa->is_margin_approved;
            });
    }
}
