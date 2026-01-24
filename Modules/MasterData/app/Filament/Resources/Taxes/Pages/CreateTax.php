<?php

namespace Modules\MasterData\Filament\Resources\Taxes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Resources\Taxes\TaxResource;

class CreateTax extends CreateRecord
{
    protected static string $resource = TaxResource::class;
}
