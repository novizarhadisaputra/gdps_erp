<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\TaxPtkpConfigResource;

class ListTaxPtkpConfigs extends ListRecords
{
    protected static string $resource = TaxPtkpConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Configuration for Non-Taxable Income (PTKP) limits according to tax categories.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
