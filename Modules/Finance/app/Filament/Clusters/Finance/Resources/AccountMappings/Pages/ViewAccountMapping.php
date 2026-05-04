<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\AccountMappingResource;

class ViewAccountMapping extends ViewRecord
{
    protected static string $resource = AccountMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
