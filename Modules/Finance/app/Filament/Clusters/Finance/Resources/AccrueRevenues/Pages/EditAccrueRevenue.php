<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource;

class EditAccrueRevenue extends EditRecord
{
    protected static string $resource = AccrueRevenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label('Submit to Finance')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Submit Accrual')
                ->modalDescription('Are you sure you want to submit this accrual? This will make it available for SAP export.')
                ->visible(fn ($record) => $record->status === AccrueRevenueStatus::Draft)
                ->action(function ($record) {
                    $record->update(['status' => AccrueRevenueStatus::Open]);
                    \Filament\Notifications\Notification::make()
                        ->title('Accrual Submitted')
                        ->success()
                        ->send();
                }),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
