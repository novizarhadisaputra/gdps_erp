<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\ChartOfAccountResource;

class EditChartOfAccount extends EditRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
