<?php

namespace Modules\CRM\Filament\Resources\Contracts\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Resources\Contracts\ContractResource;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;
}
