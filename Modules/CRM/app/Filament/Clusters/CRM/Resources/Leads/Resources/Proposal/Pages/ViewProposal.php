<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\ProposalResource;
use Modules\MasterData\Services\SignatureService;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        if ($this->record->is_manual && $media = $this->record->getFirstMedia('final_proposal')) {
                            return $media;
                        }

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.proposal', ['record' => $this->record]);
                        $filename = str_replace(['/', '\\'], '-', $this->record->proposal_number);

                        return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$filename}.pdf");
                    }),

                Action::make('excel')
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-table-cells')
                    ->action(function () {
                        $filename = str_replace(['/', '\\'], '-', $this->record->proposal_number);

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \Modules\CRM\Exports\ProposalExport($this->record),
                            "proposal-{$filename}.xlsx"
                        );
                    }),
            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),

            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ProposalStatus::Submitted]))
                ->visible(fn () => $this->record->status === ProposalStatus::Draft),

            Action::make('sign')
                ->label('Digital Signature')
                ->color('primary')
                ->icon('heroicon-o-pencil-square')
                ->modalWidth('md')
                ->schema([
                    \Filament\Forms\Components\TextInput::make('pin')
                        ->label('Signature PIN')
                        ->password()
                        ->required()
                        ->helperText('Enter your digital signature PIN to approve this proposal.'),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    $service = app(SignatureService::class);

                    if (! $service->verifyPin($user, $data['pin'])) {
                        Notification::make()
                            ->title('Incorrect PIN')
                            ->danger()
                            ->send();

                        return;
                    }

                    $required = $service->getRequiredApprovers($this->record);
                    $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, $user));

                    if (! $matchingRule) {
                        Notification::make()
                            ->title('Access Denied')
                            ->body('You do not have the authority to sign this document based on the current approval rules.')
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($this->record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                        Notification::make()
                            ->title('Already Signed')
                            ->body('This document has already been signed by the appropriate role.')
                            ->warning()
                            ->send();

                        return;
                    }

                    // Add signature
                    $this->record->addSignature($user, $matchingRule->signature_type);

                    Notification::make()
                        ->title('Document Successfully Signed')
                        ->success()
                        ->send();

                    if ($this->record->isFullyApproved()) {
                        $this->record->update(['status' => ProposalStatus::Approved]);

                        Notification::make()
                            ->title('Proposal Fully Approved')
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn () => in_array($this->record->status, [ProposalStatus::Submitted])),

            Action::make('convertToMoA')
                ->label('Convert to MoA (BA)')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->visible(fn () => $this->record->status === ProposalStatus::Approved && ! $this->record->minutesOfAgreements()->exists())
                ->requiresConfirmation()
                ->action(function () {
                    // Fetch General Information from the Lead to transfer data
                    $gi = $this->record->lead?->generalInformations()
                        ->where('status', \Modules\CRM\Enums\GeneralInformationStatus::Approved)
                        ->latest()
                        ->first() ?? $this->record->lead?->generalInformations()->latest()->first();

                    $timeline = '';
                    if ($gi && $gi->estimated_start_date && $gi->estimated_end_date) {
                        $timeline = $gi->estimated_start_date->format('d/m/Y').' - '.$gi->estimated_end_date->format('d/m/Y');
                    }

                    $moa = \Modules\CRM\Models\MinutesOfAgreement::create([
                        'customer_id' => $this->record->customer_id,
                        'lead_id' => $this->record->lead_id,
                        'proposal_id' => $this->record->id,
                        'amount' => $this->record->amount,
                        'scope_of_work' => $gi?->scope_of_work ?? '',
                        'timeline' => $timeline,
                        'terms' => $gi?->billing_requirements ?? '', // Billing requirements often contain payment terms
                        'negotiation_date' => now(),
                        'status' => \Modules\CRM\Enums\MoAStatus::Draft,
                    ]);

                    Notification::make()
                        ->title('Converted to Minutes of Agreement')
                        ->body('Scope of work, timeline, and terms have been transferred from General Information.')
                        ->success()
                        ->send();

                    $this->redirect(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource::getUrl('edit', ['record' => $moa->id, 'lead' => $this->record->lead_id]));
                }),

            Action::make('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ProposalStatus::Rejected]))
                ->visible(fn () => $this->record->status === ProposalStatus::Submitted),

            Action::make('sendEmail')
                ->label('Send Email')
                ->color('info')
                ->icon('heroicon-o-envelope')
                ->url(fn () => $this->getResource()::getUrl('send', ['record' => $this->record]))
                ->visible(fn () => in_array($this->record->status, [ProposalStatus::Submitted, ProposalStatus::Approved])),

            EditAction::make()
                ->visible(fn () => $this->record->status === ProposalStatus::Draft),
            DeleteAction::make(),
        ];
    }
}
