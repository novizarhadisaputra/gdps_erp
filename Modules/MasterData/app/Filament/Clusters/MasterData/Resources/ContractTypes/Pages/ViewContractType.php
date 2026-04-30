<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\ContractTypeResource;

class ViewContractType extends ViewRecord
{
    protected static string $resource = ContractTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
