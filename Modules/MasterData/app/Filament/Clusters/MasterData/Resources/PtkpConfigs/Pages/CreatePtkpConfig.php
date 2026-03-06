<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\PtkpConfigResource;

class CreatePtkpConfig extends CreateRecord
{
    protected static string $resource = PtkpConfigResource::class;
}
