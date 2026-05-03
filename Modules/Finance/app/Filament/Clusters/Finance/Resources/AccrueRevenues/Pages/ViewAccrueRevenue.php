<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource;

class ViewAccrueRevenue extends ViewRecord
{
    protected static string $resource = AccrueRevenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')
                ->label('Submit to Finance')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Submit Accrual')
                ->modalDescription('Are you sure you want to submit this accrual? This will make it available for SAP export.')
                ->visible(fn ($record) => $record->status === AccrueRevenueStatus::Draft)
                ->action(function ($record) {
                    $record->update(['status' => AccrueRevenueStatus::Open]);
                    Notification::make()
                        ->title('Accrual Submitted')
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
