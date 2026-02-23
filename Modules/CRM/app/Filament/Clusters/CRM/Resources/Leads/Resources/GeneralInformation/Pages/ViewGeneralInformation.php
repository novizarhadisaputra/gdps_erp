<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;
use Modules\MasterData\Services\SignatureService;

class ViewGeneralInformation extends ViewRecord
{
    protected static string $resource = GeneralInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $record = $this->getRecord();
                    $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $record]);
                    $name = Str::slug($record->document_number, '-');

                    return response()->streamDownload(fn () => print ($pdf->output()), "general-information-{$name}.pdf");
                }),
            EditAction::make(),
            Action::make('Sign')
                ->label('Digital Signature')
                ->color('primary')
                ->icon('heroicon-o-pencil-square')
                ->form([
                    TextInput::make('pin')
                        ->label('Signature PIN')
                        ->password()
                        ->required()
                        ->helperText('Masukkan PIN tanda tangan digital Anda.'),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $service = app(SignatureService::class);

                    if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                        Notification::make()
                            ->title('PIN Salah')
                            ->danger()
                            ->send();

                        return;
                    }

                    $required = $service->getRequiredApprovers($record);
                    $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                    if (! $matchingRule) {
                        Notification::make()
                            ->title('Akses Ditolak')
                            ->body('Anda tidak memiliki otoritas untuk menandatangani dokumen ini berdasarkan aturan approval saat ini.')
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                        Notification::make()
                            ->title('Sudah Ditandatangani')
                            ->body('Dokumen ini sudah ditandatangani oleh peran yang sesuai.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $qrData = $service->createSignatureData(auth()->user(), $record, $matchingRule->signature_type);
                    $record->addSignature(auth()->user(), $matchingRule->signature_type);

                    Notification::make()
                        ->title('Dokumen Berhasil Ditandatangani')
                        ->success()
                        ->send();

                    if ($record->isFullyApproved()) {
                        $record->update(['status' => 'approved']);
                    }

                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => in_array($this->getRecord()->status, ['submitted', 'draft'])),

            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(function () {
                    $this->getRecord()->update(['status' => 'submitted']);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->getRecord()->status === 'draft'),

            Action::make('createPA')
                ->label('Create PA')
                ->icon('heroicon-o-presentation-chart-bar')
                ->color('success')
                ->visible(fn () => $this->getRecord()->status === 'approved')
                ->action(function () {
                    $record = $this->getRecord();
                    $lead = $record->lead;

                    $lead->profitabilityAnalyses()->create([
                        'customer_id' => $lead->customer_id,
                        'general_information_id' => $record->id,
                        'work_scheme_id' => $lead->work_scheme_id,
                        'project_area_id' => $record->project_area_id,
                        'product_cluster_id' => $lead->product_cluster_id,
                        'status' => 'draft',
                    ]);

                    Notification::make()
                        ->title('Profitability Analysis Created')
                        ->success()
                        ->send();

                    return redirect()->to(LeadResource::getUrl('profitability-analyses', ['record' => $lead]));
                }),
        ];
    }
}
