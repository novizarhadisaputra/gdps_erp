<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\TaxSchemeResource;

class CreateTaxScheme extends CreateRecord
{
    protected static string $resource = TaxSchemeResource::class;
}
