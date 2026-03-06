<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\ContractTypeResource;

class CreateContractType extends CreateRecord
{
    protected static string $resource = ContractTypeResource::class;
}
