<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\CooperationAgreementResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;
use Modules\CRM\Models\CooperationAgreement;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\MasterData\Services\SignatureService;

class ViewMinutesOfAgreement extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = MinutesOfAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->schema([
                    Select::make('language')
                        ->label('Select Document Language')
                        ->options([
                            'id' => 'Indonesia (Bahasa)',
                            'en' => 'English',
                        ])
                        ->default('id')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $language = $data['language'];
                    $pdf = Pdf::loadView('crm::pdf.minutes_of_agreement', [
                        'record' => $this->record,
                        'language' => $language,
                    ]);

                    $name = str_replace(['/', '\\'], '-', $this->record->number ?? 'Draft');
                    $leadName = \Illuminate\Support\Str::slug($this->record->lead?->company_name ?? $this->record->lead?->title ?? 'Unknown-Lead', '-');
                    $fileName = "MoA_{$name}_{$leadName}_{$language}.pdf";

                    return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                }),
            Action::make('sign')
                ->label('Digital Signature')
                ->color('primary')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->modalWidth('md')
                ->schema([
                    TextInput::make('pin')
                        ->label('Signature PIN')
                        ->password()
                        ->required()
                        ->helperText('Enter your digital signature PIN to approve this MoA.'),
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

                    // Check if signature already exists for this rule
                    if ($this->record->isRuleSatisfied($matchingRule)) {
                        Notification::make()
                            ->title('Already Signed')
                            ->body('This document has already been signed by the appropriate role(s) you represent.')
                            ->warning()
                            ->send();

                        return;
                    }

                    // Determine the role to record for this signature
                    $recordedRole = null;
                    if ($matchingRule->approver_type === 'Role') {
                        $userRoleIds = $user->roles->pluck('id')->toArray();
                        $ruleRoles = $matchingRule->approver_role ?? [];
                        $commonRoles = array_intersect($userRoleIds, $ruleRoles);
                        $recordedRole = reset($commonRoles);
                    }

                    // Add signature
                    $this->record->addSignature($user, $matchingRule->signature_type, $recordedRole);

                    // Notify next approvers
                    $service->notifyNextApprovers($this->record);

                    // Notify owner
                    $service->notifyOwnerOnSignature($this->record, $user, $matchingRule->signature_type);

                    Notification::make()
                        ->title('Document Successfully Signed')
                        ->success()
                        ->send();

                    if ($this->record->isFullyApproved()) {
                        $this->record->update(['status' => MoAStatus::Approved]);

                        Notification::make()
                            ->title('MoA Fully Approved')
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn (MinutesOfAgreement $record) => $record->status === MoAStatus::Submitted),

            Action::make('incompleteWarning')
                ->label('Submit')
                ->color('gray')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->disabled()
                ->tooltip('Harap lengkapi semua data wajib (Required) MoA untuk dapat melakukan Submit.')
                ->visible(fn () => $this->record->status === MoAStatus::Draft && ! $this->record->isComplete()),

            Action::make('Submit')
                ->color('info')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => MoAStatus::Submitted]);
                    app(SignatureService::class)->notifyNextApprovers($this->record);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->record->status === MoAStatus::Draft && $this->record->isComplete()),
            Action::make('convertToPKS')
                ->label('Convert to PKS')
                ->icon(Heroicon::OutlinedDocumentDuplicate)
                ->color('primary')
                ->visible(fn (MinutesOfAgreement $record) => $record->status === MoAStatus::Approved && ! $record->proposal?->cooperationAgreements()->exists())
                ->requiresConfirmation()
                ->action(function (MinutesOfAgreement $record) {
                    $pks = CooperationAgreement::create([
                        'customer_id' => $record->customer_id,
                        'lead_id' => $record->lead_id,
                        'proposal_id' => $record->proposal_id,
                        'status' => 'draft',
                    ]);

                    Notification::make()
                        ->title('MoA Converted to Cooperation Agreement (PKS)')
                        ->success()
                        ->send();

                    // Note: We need to make sure CooperationAgreementResource exists and has the correct path
                    $this->redirect(CooperationAgreementResource::getUrl('view', ['record' => $pks->id, 'lead' => $record->lead_id]));
                }),
            Action::make('Reject')
                ->color('danger')
                ->icon(Heroicon::OutlinedXMark)
                ->requiresConfirmation()
                ->modalHeading('Reject MoA')
                ->schema([
                    TextInput::make('reason')
                        ->label('Reason for Rejection')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['status' => MoAStatus::Rejected]);
                    app(SignatureService::class)->notifyOwnerOnRejection($this->record, $data['reason']);
                    $this->refreshFormData(['status']);

                    Notification::make()
                        ->title('MoA Rejected')
                        ->warning()
                        ->send();
                })
                ->visible(fn () => $this->record->status === MoAStatus::Submitted),

            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
