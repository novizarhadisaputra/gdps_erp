<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\ContractResource;

class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
                    $this->record->status === ContractStatus::Active
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
                        ->placeholder(fn () => 'Project for '.$this->record->customer?->name),
                ])
                ->action(function (array $data) {
                    $pa = $this->record->proposal?->profitabilityAnalysis;

                    if (! $pa) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Analisis Profitabilitas (PA) tidak ditemukan untuk kontrak ini.')
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
