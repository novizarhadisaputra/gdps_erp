<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages;

use App\Filament\Pages\EditProfile;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource;
use Modules\CRM\Models\Contract;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\MasterData\Services\SignatureService;

class ViewContract extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('Export PDF')
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.contract', ['record' => $this->record]);
                    $filename = str_replace(['/', '\\'], '-', $this->record->contract_number);

                    return response()->streamDownload(fn () => print ($pdf->output()), "contract-{$filename}.pdf");
                }),
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
                    $user = auth()->user();

                    if (! $user->signature_pin) {
                        Notification::make()
                            ->title('PIN Not Set')
                            ->body('You have not set your signature PIN. Please set it in your profile.')
                            ->danger()
                            ->actions([
                                Action::make('profile')
                                    ->label('To Profile')
                                    ->button()
                                    ->url(EditProfile::getUrl()),
                            ])
                            ->send();

                        return;
                    }

                    $service = app(SignatureService::class);

                    if (! $service->verifyPin($user, $data['pin'])) {
                        Notification::make()
                            ->title('Incorrect PIN')
                            ->danger()
                            ->send();

                        return;
                    }

                    $required = $service->getRequiredApprovers($this->record);
                    $matchingRule = $required->first(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

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

                    $this->record->addSignature(auth()->user(), $matchingRule->signature_type);

                    Notification::make()
                        ->title('Document Successfully Signed')
                        ->success()
                        ->send();

                    if ($this->record->isFullyApproved()) {
                        $this->record->update(['status' => ContractStatus::Active]);
                    }
                })
                ->visible(fn () => in_array($this->record->status, [ContractStatus::Draft])),

            Action::make('Terminate')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('termination_reason')
                        ->label('Reason for Termination')
                        ->required(),
                ])
                ->action(fn () => $this->record->update(['status' => ContractStatus::Terminated]))
                ->visible(fn () => $this->record->status === ContractStatus::Active),

            Action::make('Mark Expired')
                ->color('warning')
                ->icon('heroicon-o-clock')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ContractStatus::Expired]))
                ->visible(fn () => $this->record->status === ContractStatus::Active),
            Action::make('Renew')
                ->label('Renew Contract')
                ->color('warning')
                ->icon(Heroicon::OutlinedArrowPath)
                ->requiresConfirmation()
                ->action(function (Contract $record) {
                    $renewal = $record->replicate();
                    $renewal->status = ContractStatus::Draft;
                    $renewal->contract_number = $renewal->contract_number.'-RENEW';
                    $renewal->save();

                    Notification::make()
                        ->title('Contract Renewed')
                        ->body('A project sequence increment will apply when you generate a project for this new contract.')
                        ->success()
                        ->send();

                    $this->redirect(ContractResource::getUrl('edit', ['record' => $renewal]));
                })
                ->visible(fn (Contract $record) => $record->status === ContractStatus::Active || $record->status === ContractStatus::Expired),

            Action::make('generateProject')
                ->label(fn (Contract $record): string => $record->project()->exists() ? 'Regenerate Project' : 'Generate Project')
                ->icon(Heroicon::OutlinedRocketLaunch)
                ->color(fn (Contract $record): string => $record->project()->exists() ? 'warning' : 'primary')
                ->requiresConfirmation()
                ->hidden(fn (Contract $record): bool => ! ($record->status->value === 'active' &&
                    ($record->proposal?->profitabilityAnalysis?->status === ProfitabilityAnalysisStatus::Approved ||
                    $record->proposal?->profitabilityAnalysis?->status->value === 'converted')
                ))
                ->action(function (Contract $record, ProjectGenerationService $service) {
                    $pa = $record->proposal->profitabilityAnalysis;
                    $project = $service->generateFromPA($pa);

                    Notification::make()
                        ->title($record->project()->exists() ? 'Project updated successfully' : 'Project generated successfully')
                        ->body("Project code: {$project->code}")
                        ->success()
                        ->send();
                }),

            EditAction::make()
                ->visible(fn () => $this->record->status === ContractStatus::Draft),
        ];
    }
}
