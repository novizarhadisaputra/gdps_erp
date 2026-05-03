<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\ChartOfAccountResource;

class ListChartOfAccounts extends ListRecords
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
