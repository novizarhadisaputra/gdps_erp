<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\MinimumWageResource;

class CreateMinimumWage extends CreateRecord
{
    protected static string $resource = MinimumWageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
