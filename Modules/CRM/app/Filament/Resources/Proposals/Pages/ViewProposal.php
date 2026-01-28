<?php

namespace Modules\CRM\Filament\Resources\Proposals\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Filament\Resources\Proposals\ProposalResource;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ProposalStatus::Submitted]))
                ->visible(fn () => $this->record->status === ProposalStatus::Draft),

            Action::make('Approve')
                ->color('success')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ProposalStatus::Approved]))
                ->visible(fn () => $this->record->status === ProposalStatus::Submitted),

            Action::make('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => ProposalStatus::Rejected]))
                ->visible(fn () => $this->record->status === ProposalStatus::Submitted),

            EditAction::make()
                ->visible(fn () => $this->record->status === ProposalStatus::Draft),
        ];
    }
}
