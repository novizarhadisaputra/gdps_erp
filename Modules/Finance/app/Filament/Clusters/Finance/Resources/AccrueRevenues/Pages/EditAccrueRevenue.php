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
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
