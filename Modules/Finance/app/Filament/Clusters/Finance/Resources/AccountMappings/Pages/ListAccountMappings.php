<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\AccountMappingResource;

class ListAccountMappings extends ListRecords
{
    protected static string $resource = AccountMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
