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
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\ContractResource;
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

                    if (! $user->hasMedia('signature')) {
                        Notification::make()
                            ->title('Signature Not Uploaded')
                            ->body('You have not uploaded a signature image. Please upload it in your profile.')
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

            Action::make('Activate')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ContractStatus::Active]))
                ->visible(fn () => $this->record->status === ContractStatus::Draft),

            Action::make('Terminate')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->form([
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

            Action::make('generateProject')
                ->label('Generate Project')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->lead && ! $this->record->lead->projects()->exists() &&
                    $this->record->status === ContractStatus::Active &&
                    ($pa = $this->record->proposal?->profitabilityAnalysis) &&
                    $pa->status === 'approved'
                )
                ->schema([
                    \Filament\Forms\Components\TextInput::make('summary')
                        ->label('Summary')
                        ->default(fn () => "You are about to generate a Project for '{$this->record->customer?->name}'. This will consume the next sequence number for this customer and work scheme.")
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    \Filament\Forms\Components\TextInput::make('project_name_override')
                        ->label('Project Name (Optional)')
                        ->placeholder(fn () => $this->record->proposal?->proposal_number ?? 'Project for '.$this->record->customer?->name),
                ])
                ->action(function (array $data) {
                    $pa = $this->record->proposal?->profitabilityAnalysis;

                    if (! $pa) {
                        Notification::make()
                            ->title('Failed')
                            ->body('Profitability Analysis (PA) not found for this contract.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $service = app(\Modules\Finance\Classes\ProjectGenerationService::class);
                    $project = $service->generateFromPA($pa);

                    if (! empty($data['project_name_override'])) {
                        $project->update(['name' => $data['project_name_override']]);
                    }

                    Notification::make()
                        ->title('Project Generated')
                        ->body("Project Code: {$project->code}")
                        ->success()
                        ->send();

                    $this->redirect(\Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource::getUrl('edit', ['record' => $project]));
                }),

            EditAction::make()
                ->visible(fn () => $this->record->status === ContractStatus::Draft),
        ];
    }
}
