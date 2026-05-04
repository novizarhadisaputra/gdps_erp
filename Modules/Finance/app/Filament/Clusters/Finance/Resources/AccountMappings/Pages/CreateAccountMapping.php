<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccountMappings\AccountMappingResource;

class CreateAccountMapping extends CreateRecord
{
    protected static string $resource = AccountMappingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
