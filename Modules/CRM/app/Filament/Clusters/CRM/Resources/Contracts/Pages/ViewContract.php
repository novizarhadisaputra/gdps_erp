<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
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

            EditAction::make()
                ->visible(fn () => $this->record->status === ContractStatus::Draft),
        ];
    }
}
