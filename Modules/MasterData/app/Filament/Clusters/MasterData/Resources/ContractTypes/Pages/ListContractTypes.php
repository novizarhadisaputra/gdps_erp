<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\ContractTypeResource;

class ListContractTypes extends ListRecords
{
    protected static string $resource = ContractTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
