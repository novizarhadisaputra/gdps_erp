<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\ServiceLineResource;

class CreateServiceLine extends CreateRecord
{
    protected static string $resource = ServiceLineResource::class;
}
