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
                    $revenueItems = $record->revenueItems;

                    if ($revenueItems->isEmpty()) {
                        Notification::make()
                            ->title('Incomplete Data')
                            ->body('You must add at least one revenue item before submitting.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $totalRevenue = $revenueItems->sum('amount_estimated');

                    if ($totalRevenue <= 0) {
                        Notification::make()
                            ->title('Invalid Amount')
                            ->body('Total accrued revenue must be greater than zero.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->update(['status' => AccrueRevenueStatus::Open]);

                    Notification::make()
                        ->title('Accrual Submitted')
                        ->body('The accrual has been submitted and is now ready for SAP export.')
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Widgets\AccrualInvoicingProgressWidget::class,
        ];
    }
}
