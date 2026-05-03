<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\AccountMappingResource;

class EditAccountMapping extends EditRecord
{
    protected static string $resource = AccountMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
