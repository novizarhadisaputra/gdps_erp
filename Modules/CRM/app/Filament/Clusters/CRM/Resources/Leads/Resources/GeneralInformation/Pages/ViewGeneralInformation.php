<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;
use Modules\MasterData\Services\SignatureService;

class ViewGeneralInformation extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = GeneralInformationResource::class;

    public function getSubheading(): ?string
    {
        return 'Detailed view of project general information.';
    }

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
            EditAction::make()
                ->hidden(fn () => $this->getRecord()->isLocked()),
            Action::make('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Reject General Information')
                ->modalDescription('Are you sure you want to reject this General Information? The status will return to Rejected and it can be edited again.')
                ->action(function () {
                    $this->getRecord()->update(['status' => GeneralInformationStatus::Rejected]);
                    $this->refreshFormData(['status']);

                    Notification::make()
                        ->title('General Information Rejected')
                        ->warning()
                        ->send();
                })
                ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Submitted),
            Action::make('Sign')
                ->label('Digital Signature')
                ->color('primary')
                ->icon('heroicon-o-pencil-square')
                ->form([
                    TextInput::make('pin')
                        ->label('Signature PIN')
                        ->password()
                        ->required()
                        ->helperText('Enter your digital signature PIN.'),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    $service = app(SignatureService::class);

                    if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                        Notification::make()
                            ->title('Incorrect PIN')
                            ->danger()
                            ->send();

                        return;
                    }

                    $required = $service->getRequiredApprovers($record);
                    $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                    if (! $matchingRule) {
                        Notification::make()
                            ->title('Access Denied')
                            ->body('You do not have the authority to sign this document based on the current approval rules.')
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($record->hasSignatureFrom($matchingRule->approver_role ?? $matchingRule->approver_type)) {
                        Notification::make()
                            ->title('Already Signed')
                            ->body('This document has already been signed by the appropriate role.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $qrData = $service->createSignatureData(auth()->user(), $record, $matchingRule->signature_type);
                    $record->addSignature(auth()->user(), $matchingRule->signature_type);

                    Notification::make()
                        ->title('Document Successfully Signed')
                        ->success()
                        ->send();

                    if ($record->isFullyApproved()) {
                        $record->update(['status' => GeneralInformationStatus::Approved]);
                    }

                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => in_array($this->getRecord()->status, [GeneralInformationStatus::Submitted])),

            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(function () {
                    $this->getRecord()->update(['status' => GeneralInformationStatus::Submitted]);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Draft),

            Action::make('createPA')
                ->label('Create PA')
                ->icon('heroicon-o-presentation-chart-bar')
                ->color('success')
                ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Approved)
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
