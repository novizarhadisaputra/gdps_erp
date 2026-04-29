<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\TaxObjectResource;

class ListTaxObjects extends ListRecords
{
    protected static string $resource = TaxObjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
