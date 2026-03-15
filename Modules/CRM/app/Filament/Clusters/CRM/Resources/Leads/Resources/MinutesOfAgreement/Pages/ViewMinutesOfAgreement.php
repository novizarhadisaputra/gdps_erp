<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\MoAStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\MasterData\Services\SignatureService;

class ViewMinutesOfAgreement extends ViewRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = MinutesOfAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.minutes_of_agreement', ['record' => $this->record]);
                    $filename = str_replace(['/', '\\'], '-', $this->record->document_number);

                    return response()->streamDownload(fn () => print ($pdf->output()), "moa-{$filename}.pdf");
                }),
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
                        $userRoles = $user->roles->pluck('name')->toArray();
                        $ruleRoles = $matchingRule->approver_role ?? [];
                        $commonRoles = array_intersect($userRoles, $ruleRoles);
                        $recordedRole = reset($commonRoles);
                    }

                    // Add signature
                    $this->record->addSignature($user, $matchingRule->signature_type, $recordedRole);

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
                ->icon('heroicon-o-exclamation-triangle')
                ->disabled()
                ->tooltip('Harap lengkapi semua data wajib (Required) MoA untuk dapat melakukan Submit.')
                ->visible(fn () => $this->record->status === MoAStatus::Draft && ! $this->record->isComplete()),

            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => MoAStatus::Submitted]);
                    $this->refreshFormData(['status']);
                })
                ->visible(fn () => $this->record->status === MoAStatus::Draft && $this->record->isComplete()),
            Action::make('convertToContract')
                ->label('Convert to Contract')
                ->icon('heroicon-o-document-duplicate')
                ->color('primary')
                ->visible(fn (MinutesOfAgreement $record) => $record->status === MoAStatus::Approved && ! $record->proposal?->contracts()->exists())
                ->requiresConfirmation()
                ->action(function (MinutesOfAgreement $record) {
                    $contract = \Modules\CRM\Models\Contract::create([
                        'customer_id' => $record->customer_id,
                        'lead_id' => $record->lead_id,
                        'proposal_id' => $record->proposal_id,
                        'status' => \Modules\CRM\Enums\ContractStatus::Draft,
                    ]);

                    Notification::make()
                        ->title('MoA Converted to Contract')
                        ->success()
                        ->send();

                    $this->redirect(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource::getUrl('edit', ['record' => $contract->id, 'lead' => $record->lead_id]));
                }),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
