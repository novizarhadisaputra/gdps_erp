<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\MasterData\Enums\ApprovalSignatureType;
use Modules\MasterData\Services\SignatureService;

class ViewProposal extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make(__('downloadPdf'))
                ->label(__('Download Draft PDF'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(function () {
                    $this->record->load([
                        'customer',
                        'profitabilityAnalysis.workScheme',
                        'profitabilityAnalysis.paymentTerm',
                        'profitabilityAnalysis.productCluster',
                        'lead.user',
                        'lead.ams',
                        'lead.manpowerTemplates.items.jobPosition',
                        'lead.costingTemplates.costingTemplateItems.item',
                        'lead.latestGeneralInformation',
                        'lead.salesPlan',
                    ]);

                    if ($this->record->is_manual && $media = $this->record->getFirstMedia('final_proposal')) {
                        return $media;
                    }

                    $pdf = Pdf::loadView('crm::pdf.proposal', ['record' => $this->record])
                        ->setPaper('a4', 'portrait')
                        ->setOptions([
                            'isRemoteEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'defaultFont' => 'sans-serif',
                        ]);
                    $name = str_replace(['/', '\\'], '-', $this->record->number);
                    $fileName = "{$name}.pdf";

                    return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                }),

            ActionGroup::make([
                Action::make(__('signProposal'))
                    ->label(__('Sign Proposal'))
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->modalHeading(__('Sign & Approve Proposal'))
                    ->schema([
                        TextInput::make('pin')
                            ->label(__('Signature PIN'))
                            ->password()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $service = app(SignatureService::class);
                        if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                            Notification::make()->title(__('Incorrect PIN'))->danger()->send();

                            return;
                        }

                        // Record the signature directly
                        $this->record->addSignature(
                            auth()->user(),
                            ApprovalSignatureType::Approver,
                            'Account Manager'
                        );

                        Notification::make()
                            ->title(__('Proposal Signed'))
                            ->body('The signature has been successfully recorded.')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status']);
                    })
                    ->visible(function () {
                        $isSubmitted = $this->record->status === ProposalStatus::Submitted;
                        $isOwner = auth()->id() == $this->record->lead?->user_id;
                        $alreadySigned = $this->record->signatures()->where('user_id', auth()->id())->exists();

                        return $isSubmitted && $isOwner && ! $alreadySigned;
                    }),

                Action::make(__('incompleteWarning'))
                    ->label(__('Submit'))
                    ->color('gray')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->disabled()
                    ->tooltip(__('Please complete all required fields, including uploading the Final Proposal and filling in the Meeting Date, before submitting.'))
                    ->visible(fn () => $this->record->status === ProposalStatus::Draft && ! $this->isReadyToSubmit()),

                Action::make(__('Submit'))
                    ->label(__('Submit'))
                    ->color('info')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update(['status' => ProposalStatus::Submitted]);
                        app(SignatureService::class)->notifyNextApprovers($this->record);

                        Notification::make()->title(__('Proposal Submitted Successfully'))->success()->send();
                    })
                    ->visible(fn () => $this->record->status === ProposalStatus::Draft && $this->isReadyToSubmit()),

                Action::make(__('convertToMoA'))
                    ->label(__('Convert to MoA (BA)'))
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->color('info')
                    ->visible(fn () => $this->record->status === ProposalStatus::Approved && ! $this->record->minutesOfAgreements()->exists())
                    ->requiresConfirmation()
                    ->action(function () {
                        $gi = $this->record->lead?->generalInformations()
                            ->where('status', GeneralInformationStatus::Approved)
                            ->latest('created_at')
                            ->first() ?? $this->record->lead?->generalInformations()->latest('created_at')->first();

                        $timeline = '';
                        if ($gi && $gi->estimated_start_date && $gi->estimated_end_date) {
                            $timeline = $gi->estimated_start_date->format('d/m/Y').' - '.$gi->estimated_end_date->format('d/m/Y');
                        }

                        $moa = MinutesOfAgreement::create([
                            'customer_id' => $this->record->customer_id,
                            'lead_id' => $this->record->lead_id,
                            'proposal_id' => $this->record->id,
                            'amount' => $this->record->amount,
                            'scope_of_work' => $gi?->scope_of_work ?? '',
                            'timeline' => $timeline,
                            'terms' => $gi?->billing_requirements ?? '',
                            'negotiation_date' => now(),
                            'status' => MoAStatus::Draft,
                        ]);

                        Notification::make()
                            ->title(__('Converted to Minutes of Agreement'))
                            ->body('Scope of work, timeline, and terms have been transferred from General Information.')
                            ->success()
                            ->send();

                        $this->redirect(MinutesOfAgreementResource::getUrl('edit', ['record' => $moa->id, 'lead' => $this->record->lead_id]));
                    }),

                Action::make(__('sendEmail'))
                    ->label(fn () => $this->record->status === ProposalStatus::Sent ? 'Resend Email' : 'Send Email')
                    ->color('info')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->url(fn () => route('filament.admin.crm.resources.leads.proposals.send', [
                        'lead' => $this->record->lead_id,
                        'record' => $this->record->id,
                    ]))
                    ->visible(fn () => in_array($this->record->status, [ProposalStatus::Submitted, ProposalStatus::Sent, ProposalStatus::Approved]) && ($this->record->profitabilityAnalysis?->is_margin_approved ?? true)),

                Action::make(__('revise'))
                    ->label(__('Revise Proposal'))
                    ->color('warning')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->modalHeading(__('Revise Proposal'))
                    ->modalDescription(__('This will move the proposal back to Draft stage, allowing you to make changes. A revision snapshot will be created, and the Lead status will be set back to Approach.'))
                    ->schema([
                        TextInput::make('reason')
                            ->label(__('Reason for Revision'))
                            ->placeholder(__('Briefly explain why this proposal is being revised...'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $this->record->revision_reason = $data['reason'];
                        $this->record->update(['status' => ProposalStatus::Draft]);

                        Notification::make()
                            ->title(__('Proposal Revision Started'))
                            ->body('The proposal has been moved back to Draft. You can now edit the details.')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status']);
                    })
                    ->visible(fn () => in_array($this->record->status, [ProposalStatus::Sent, ProposalStatus::Submitted, ProposalStatus::Approved])),

                EditAction::make()
                    ->url(fn () => route('filament.admin.crm.resources.leads.proposals.edit', [
                        'lead' => $this->record->lead_id,
                        'record' => $this->record->id,
                    ]))
                    ->visible(fn () => $this->record->status === ProposalStatus::Draft),

                Action::make(__('Reject'))
                    ->color('danger')
                    ->icon(Heroicon::OutlinedXMark)
                    ->requiresConfirmation()
                    ->modalHeading(__('Reject Proposal'))
                    ->schema([
                        TextInput::make('reason')
                            ->label(__('Reason for Rejection'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $this->record->update(['status' => ProposalStatus::Rejected]);
                        app(SignatureService::class)->notifyOwnerOnRejection($this->record, $data['reason']);
                        $this->refreshFormData(['status']);

                        Notification::make()
                            ->title(__('Proposal Rejected'))
                            ->warning()
                            ->send();
                    })
                    ->visible(fn () => $this->record->status === ProposalStatus::Submitted),

                DeleteAction::make()
                    ->successRedirectUrl(fn () => route('filament.admin.crm.resources.leads.proposals.index', [
                        'lead' => $this->record->lead_id,
                    ])),
            ])
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('gray')
                ->button(),
        ];
    }

    protected function isReadyToSubmit(): bool
    {
        return $this->record->isComplete()
            && $this->record->hasMedia('final_proposal')
            && filled($this->record->meeting_date);
    }
}
