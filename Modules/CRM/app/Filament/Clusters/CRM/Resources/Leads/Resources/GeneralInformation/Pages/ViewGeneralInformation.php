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
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\LeadResource;
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
            ActionGroup::make([
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
            ])
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->button(),

            ActionGroup::make([
                EditAction::make()
                    ->hidden(fn () => $this->getRecord()->isLocked()),

                Action::make('createPA')
                    ->label('Create PA')
                    ->icon(Heroicon::OutlinedPresentationChartBar)
                    ->color('success')
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Approved)
                    ->action(function () {
                        $record = $this->getRecord();
                        $pa = $record->toProfitabilityAnalysis();
                        $lead = $record->lead;

                        Notification::make()
                            ->title('Profitability Analysis Created')
                            ->success()
                            ->send();

                        return redirect()->to(LeadResource::getUrl('profitability-analyses', ['record' => $lead]));
                    }),

                Action::make('Reject')
                    ->color('danger')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->requiresConfirmation()
                    ->modalHeading('Reject General Information')
                    ->schema([
                        TextInput::make('reason')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $record = $this->getRecord();
                        $record->update(['status' => GeneralInformationStatus::Rejected]);
                        app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);
                        $this->refreshFormData(['status']);

                        Notification::make()
                            ->title('General Information Rejected')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Submitted),

                Action::make('Approve')
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->modalHeading('Approve General Information')
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

                        $signatureType = ApprovalSignatureType::Approver;
                        $required = $service->getRequiredApprovers($record)
                            ->where('signature_type', $signatureType->value);

                        $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                        if ($eligibleRules->isEmpty()) {
                            Notification::make()->title('Access Denied')->body('You do not have authorization for this document.')->warning()->send();

                            return;
                        }

                        $matchingRule = $eligibleRules->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

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

                        $record->addSignature(auth()->user(), $signatureType, $recordedRole);
                        $service->notifyNextApprovers($record);
                        $service->notifyOwnerOnSignature($record, auth()->user(), $signatureType->value);

                        if ($record->isFullyApproved()) {
                            $record->update(['status' => GeneralInformationStatus::Approved]);
                            Notification::make()->title('General Information Fully Approved')->success()->send();
                        } else {
                            $notification = Notification::make()
                                ->title('General Information Signed Successfully')
                                ->success();

                            if (! $record->hasRiskRegisterApproval() && $record->isTypeApproved(ApprovalSignatureType::Approver)) {
                                $notification->body('Digital signatures are complete, but status remains "Submitted" pending Risk Register approval.');
                            }
                            $notification->send();
                        }

                        $this->refreshFormData(['status']);
                    })
                    ->visible(function () {
                        $record = $this->getRecord();
                        if ($record->status !== GeneralInformationStatus::Submitted) {
                            return false;
                        }

                        if ($record->isFullyApproved()) {
                            return false;
                        }

                        $service = app(SignatureService::class);
                        $required = $service->getRequiredApprovers($record)
                            ->where('signature_type', ApprovalSignatureType::Approver->value);

                        $nextRule = $required->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

                        return $nextRule && $service->isEligibleApprover($nextRule, auth()->user());
                    }),

                DeleteAction::make(),
            ])
                ->label('Options')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('gray')
                ->button(),

            ActionGroup::make([
                Action::make('Submit')
                    ->color('info')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->getRecord()->update(['status' => GeneralInformationStatus::Submitted]);
                        app(SignatureService::class)->notifyNextApprovers($this->getRecord());
                        $this->refreshFormData(['status']);
                    })
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Draft && $this->getRecord()->isComplete()),

                Action::make('incompleteWarning')
                    ->label('Submit')
                    ->color('gray')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->disabled()
                    ->tooltip('Harap lengkapi semua data wajib (Required) dan minimal 1 PIC untuk dapat melakukan Submit.')
                    ->visible(fn () => $this->getRecord()->status === GeneralInformationStatus::Draft && ! $this->getRecord()->isComplete()),
            ])
                ->label('Workflow')
                ->icon(Heroicon::OutlinedPlay)
                ->color('primary')
                ->button(),

        ];
    }
}
