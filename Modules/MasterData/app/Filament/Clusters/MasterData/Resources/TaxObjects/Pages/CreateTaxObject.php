<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxObjects\TaxObjectResource;

class CreateTaxObject extends CreateRecord
{
    protected static string $resource = TaxObjectResource::class;
}
