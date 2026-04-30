<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\WorkSchemeResource;

class CreateWorkScheme extends CreateRecord
{
    protected static string $resource = WorkSchemeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
