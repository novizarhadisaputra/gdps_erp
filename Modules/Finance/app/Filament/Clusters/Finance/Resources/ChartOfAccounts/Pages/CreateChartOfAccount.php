<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ChartOfAccounts\ChartOfAccountResource;

class CreateChartOfAccount extends CreateRecord
{
    protected static string $resource = ChartOfAccountResource::class;
}
