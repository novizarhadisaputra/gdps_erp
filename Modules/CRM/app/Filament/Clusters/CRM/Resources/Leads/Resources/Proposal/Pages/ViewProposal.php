<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource;
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
                        ->helperText('Masukkan PIN tanda tangan digital Anda untuk menyetujui proposal ini.'),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    $service = app(SignatureService::class);

                    if (! $service->verifyPin($user, $data['pin'])) {
                        Notification::make()
                            ->title('PIN Salah')
                            ->danger()
                            ->send();

                        return;
                    }

                    $required = $service->getRequiredApprovers($this->record);
                    $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, $user));

                    if (! $matchingRule) {
                        Notification::make()
                            ->title('Akses Ditolak')
                            ->body('Anda tidak memiliki otoritas untuk menandatangani dokumen ini berdasarkan aturan approval saat ini.')
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($this->record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                        Notification::make()
                            ->title('Sudah Ditandatangani')
                            ->body('Dokumen ini sudah ditandatangani oleh peran yang sesuai.')
                            ->warning()
                            ->send();

                        return;
                    }

                    // Add signature
                    $this->record->addSignature($user, $matchingRule->signature_type);

                    Notification::make()
                        ->title('Dokumen Berhasil Ditandatangani')
                        ->success()
                        ->send();

                    if ($this->record->isFullyApproved()) {
                        $this->record->update(['status' => ProposalStatus::Approved]);

                        Notification::make()
                            ->title('Proposal Disetujui Sepenuhnya')
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn () => in_array($this->record->status, [ProposalStatus::Submitted, ProposalStatus::Draft])),

            Action::make('convertToContract')
                ->label('Convert to Contract')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->visible(fn () => $this->record->status === ProposalStatus::Approved && ! $this->record->contracts()->exists())
                ->requiresConfirmation()
                ->action(function () {
                    $contract = \Modules\CRM\Models\Contract::create([
                        'customer_id' => $this->record->customer_id,
                        'lead_id' => $this->record->lead_id,
                        'proposal_id' => $this->record->id,
                        'contract_number' => 'CONTRACT-'.$this->record->proposal_number,
                        'status' => \Modules\CRM\Enums\ContractStatus::Draft,
                    ]);

                    $this->record->update(['status' => ProposalStatus::Converted]);

                    Notification::make()
                        ->title('Converted to Contract')
                        ->success()
                        ->send();

                    $this->redirect(ContractResource::getUrl('index', ['lead' => $this->record->lead_id]));
                }),

            Action::make('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ProposalStatus::Rejected]))
                ->visible(fn () => $this->record->status === ProposalStatus::Submitted),

            EditAction::make()
                ->visible(fn () => $this->record->status === ProposalStatus::Draft),
        ];
    }
}
