<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;

class ViewProfitabilityAnalysis extends ViewRecord
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Submit')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'submitted']))
                ->visible(fn () => $this->record->status === 'draft'),

            Action::make('Approve')
                ->color('success')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'approved']))
                ->visible(fn () => $this->record->status === 'submitted'),

            Action::make('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'rejected']))
                ->visible(fn () => $this->record->status === 'submitted'),

            EditAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }
}
