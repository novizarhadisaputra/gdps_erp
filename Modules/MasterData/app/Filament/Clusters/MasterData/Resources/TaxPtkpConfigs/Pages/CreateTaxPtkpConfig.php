<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\TaxPtkpConfigResource;

class CreateTaxPtkpConfig extends CreateRecord
{
    protected static string $resource = TaxPtkpConfigResource::class;

    public function getSubheading(): ?string
    {
        return 'Define a new PTKP code with its corresponding annual amount.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
