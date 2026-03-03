<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\ProposalResource;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.proposal', ['record' => $this->record]);

                    return response()->streamDownload(fn () => print ($pdf->output()), "proposal-{$this->record->proposal_number}.pdf");
                }),

            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ProposalStatus::Submitted]))
                ->visible(fn () => $this->record->status === ProposalStatus::Draft),

            Action::make('Approve')
                ->color('success')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('pin')
                        ->label('Signature PIN')
                        ->password()
                        ->required()
                        ->helperText('Masukkan PIN tanda tangan digital Anda untuk menyetujui proposal ini.'),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();

                    if (! $user->signature_pin) {
                        Notification::make()
                            ->title('PIN Belum Diatur')
                            ->body('Anda belum mengatur PIN tanda tangan. Mohon atur di profil Anda.')
                            ->danger()
                            ->actions([
                                Action::make('profile')
                                    ->label('Ke Profil')
                                    ->button()
                                    ->url(\App\Filament\Pages\EditProfile::getUrl()),
                            ])
                            ->send();

                        return;
                    }

                    if (! $user->hasMedia('signature')) {
                        Notification::make()
                            ->title('Tanda Tangan Belum Diupload')
                            ->body('Anda belum mengupload gambar tanda tangan. Mohon upload di profil Anda.')
                            ->danger()
                            ->actions([
                                Action::make('profile')
                                    ->label('Ke Profil')
                                    ->button()
                                    ->url(\App\Filament\Pages\EditProfile::getUrl()),
                            ])
                            ->send();

                        return;
                    }

                    $service = app(\Modules\MasterData\Services\SignatureService::class);

                    if (! $service->verifyPin($user, $data['pin'])) {
                        Notification::make()
                            ->title('PIN Salah')
                            ->danger()
                            ->send();

                        return;
                    }

                    // Add signature
                    $qrData = $service->createSignatureData($user, $this->record, 'approved');
                    $qrCode = $service->generateQRCode($qrData);

                    $this->record->addSignature(auth()->user(), 'approved', $qrCode);

                    $this->record->update(['status' => ProposalStatus::Approved]);

                    Notification::make()
                        ->title('Proposal Disetujui')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->status === ProposalStatus::Submitted),

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

                    $this->redirect(\Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\ContractResource::getUrl('index'));
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
