<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates\TaxPasal17RateResource;

class ListTaxPasal17Rates extends ListRecords
{
    protected static string $resource = TaxPasal17RateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
