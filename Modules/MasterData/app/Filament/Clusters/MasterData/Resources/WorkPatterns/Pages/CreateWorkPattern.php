<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkPatterns\WorkPatternResource;

class CreateWorkPattern extends CreateRecord
{
    protected static string $resource = WorkPatternResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
