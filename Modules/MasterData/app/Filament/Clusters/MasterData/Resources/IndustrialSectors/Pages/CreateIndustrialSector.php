<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\IndustrialSectorResource;

class CreateIndustrialSector extends CreateRecord
{
    protected static string $resource = IndustrialSectorResource::class;
}
