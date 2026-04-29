<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates\TaxTerRateResource;

class ListTaxTerRates extends ListRecords
{
    protected static string $resource = TaxTerRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
