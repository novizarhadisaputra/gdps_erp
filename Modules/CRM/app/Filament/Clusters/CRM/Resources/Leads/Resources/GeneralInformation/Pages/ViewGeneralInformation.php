<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Pages;

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
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\GeneralInformationResource;
use Modules\MasterData\Enums\ApprovalSignatureType;
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
            EditAction::make()
                ->visible(fn () => ! $this->getRecord()->isLocked() && $this->getRecord()->user_id === auth()->id()),

            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(function () {
                    $record = $this->getRecord();
                    $pdf = Pdf::loadView('crm::pdf.general_information', ['record' => $record]);

                    $number = str_replace(['/', '\\'], '-', $record->number ?? 'Draft');
                    $fileName = "{$number}.pdf";

                    return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                }),

            ActionGroup::make([
                Action::make('Submit')
                    ->color('info')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->getRecord()->update(['status' => GeneralInformationStatus::Submitted]);
                        // No notifications to approvers needed as requested
                        $this->refreshFormData(['status']);

                        Notification::make()
                            ->title('General Information Submitted')
                            ->body('You can now proceed to Sign and Approve this document.')
                            ->info()
                            ->send();
                    })
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Draft && $this->getRecord()->isComplete() && $this->getRecord()->user_id === auth()->id()),

                Action::make('incompleteWarning')
                    ->label('Submit')
                    ->color('gray')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->disabled()
                    ->tooltip('Harap lengkapi semua data wajib (Required) dan minimal 1 PIC untuk dapat melakukan Submit.')
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Draft && ! $this->getRecord()->isComplete()),

                Action::make('Approve')
                    ->label('Sign & Approve')
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->modalHeading('Sign & Approve General Information')
                    ->modalDescription('As the creator, you are signing this document to finalize it.')
                    ->schema([
                        TextInput::make('pin')
                            ->label('Signature PIN')
                            ->password()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $record = $this->getRecord();
                        $service = app(SignatureService::class);

                        if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                            Notification::make()->title('Incorrect PIN')->danger()->send();

                            return;
                        }

                        // Add Creator Signature
                        $record->addSignature(auth()->user(), ApprovalSignatureType::Approver, 'Account Manager');

                        // Automatically approve since no further approvals are needed per user request
                        $record->update(['status' => GeneralInformationStatus::Approved]);

                        Notification::make()
                            ->title('General Information Approved')
                            ->body('Document has been signed and finalized.')
                            ->success()
                            ->send();

                        $this->refreshFormData(['status']);
                    })
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Submitted && $this->getRecord()->user_id === auth()->id()),

                Action::make('revise')
                    ->label('Revise')
                    ->color('warning')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->modalHeading('Move back to Draft')
                    ->action(function () {
                        $this->getRecord()->update(['status' => GeneralInformationStatus::Draft]);
                        $this->refreshFormData(['status']);

                        Notification::make()
                            ->title('Moved to Draft')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Submitted && $this->getRecord()->user_id === auth()->id()),
            ])
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('primary')
                ->button(),

            DeleteAction::make(),
        ];
    }
}
